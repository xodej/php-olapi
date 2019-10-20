<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Xodej\Olapi\Filter\DataFilter;

/**
 * Class Connection.
 */
class Connection
{
    public const API_SERVER_ACTIVATE_LICENSE = '/server/activate_license';
    public const API_SERVER_BENCHMARK = '/server/benchmark';
    public const API_SERVER_CHANGE_PASSWORD = '/server/change_password';
    public const API_SERVER_DATABASES = '/server/databases';
    public const API_SERVER_INFO = '/server/info';
    public const API_SERVER_LICENSES = '/server/licenses';
    public const API_SERVER_LOAD = '/server/load';
    public const API_SERVER_LOCKS = '/server/locks';
    public const API_SERVER_LOGIN = '/server/login';
    public const API_SERVER_LOGOUT = '/server/logout';
    public const API_SERVER_SAVE = '/server/save';
    public const API_SERVER_SHUTDOWN = '/server/shutdown';
    public const API_SERVER_USER_INFO = '/server/user_info';

    public const API_DATABASE_CREATE = '/database/create';
    public const API_DATABASE_DESTROY = '/database/destroy';
    public const API_DATABASE_LOAD = '/database/load';
    public const API_DATABASE_RENAME = '/database/rename';
    public const API_DATABASE_SAVE = '/database/save';
    public const API_DATABASE_UNLOAD = '/database/unload';

    public const API_SVS_INFO = '/svs/info';
    public const API_SVS_RESTART = '/svs/restart';
    public const API_SVS_EDIT = '/svs/edit';

    public const API_EVENT_BEGIN = '/event/begin';
    public const API_EVENT_END = '/event/end';

    public const API_SAML_META_SP = '/meta-sp';

    public static $debugMode = false;

    private $host;
    private $user;
    private $pass;
    /**
     * @var null|Client
     */
    private $client;
    private $sessionId;
    private $dataToken;
    private $secret;

    /**
     * @var null|Connection
     */
    private $superConnection;

    /**
     * @var DatabaseStore
     */
    private $databases;

    /**
     * @var array
     */
    private $databaseList;

    /**
     * Connection constructor.
     *
     * @param null|string $host_with_port
     * @param null|string $username
     * @param null|string $password
     * @param null|string $sid
     *
     * @throws GuzzleException
     * @throws \ErrorException
     */
    public function __construct(
        ?string $host_with_port = null,
        ?string $username = null,
        ?string $password = null,
        ?string $sid = null
    ) {
        // Order:
        //    1. use given credentials, if not given
        //    2. use credentials from php.ini --> jedox.XXX variables, if not existent
        //    3. use defaults admin/admin etc.
        $this->host = $host_with_port ?? ('' === \get_cfg_var('jedox.host') ? 'http://127.0.0.1:7777' : \get_cfg_var('jedox.host'));
        $this->user = $username ?? ('' === \get_cfg_var('jedox.user') ? 'admin' : \get_cfg_var('jedox.user'));
        $this->pass = $password ?? ('' === \get_cfg_var('jedox.pass') ? 'admin' : \get_cfg_var('jedox.pass'));

        // @todo check if this needs to be done in a separate login
        if (null !== $sid) {
            $this->user = null;
            $this->pass = null;
            $this->sessionId = $sid;
        }

        // init ArrayObject for database objects
        $this->databases = new DatabaseStore();

        // initialize http client and read available databases from server
        $this->init();
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function close(): bool
    {
        if (null === ($client = $this->getClient())) {
            return true;
        }

        // @var Client $client
        $client->get(self::API_SERVER_LOGOUT, [
            'query' => [
                'sid' => $this->getSessionId(),
                'type' => 1,
            ],
        ]);

        $this->client = null;
        $this->databases = new DatabaseStore();
        $this->databaseList = [];

        return true;
    }

    /**
     * @param string $database_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createDatabase(string $database_name): bool
    {
        $response = $this->request(self::API_DATABASE_CREATE, [
            'query' => [
                'new_name' => $database_name,
                'type' => 0,
            ],
        ]);

        // @todo Connection::createDatabase() - reload databases

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param string $database_name
     *
     * @throws \Exception
     *
     * @return bool
     *
     * @see Connection::deleteDatabaseByName() alias
     */
    public function deleteDatabase(string $database_name): bool
    {
        return $this->deleteDatabaseByName($database_name);
    }

    /**
     * @param int $database_id
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteDatabaseById(int $database_id): bool
    {
        if (!$this->hasDatabaseById($database_id)) {
            throw new \InvalidArgumentException('Unknown database ID '.$database_id.' given.');
        }

        $response = $this->getConnection()->request(self::API_DATABASE_DESTROY, [
            'query' => [
                'database' => $database_id,
            ],
        ]);

        $flag_successful = ('1' === ($response[0][0] ?? '0'));

        if (true === $flag_successful) {
            if (isset($this->databases[$database_id])) {
                unset($this->databases[$database_id]);
            }
            if (isset($this->databaseList['olap_id'][$database_id])) {
                unset($this->databaseList['olap_id'][$database_id]);
            }

            $database_name = $this->getDatabaseNameFromId($database_id);

            if (null !== $database_name && isset($this->databaseList['olap_name'][$database_name])) {
                unset($this->databaseList['olap_name'][$database_name]);
            }
        }
        // @todo Database::deleteDimension() - reload dimensions

        return $flag_successful;
    }

    /**
     * @param string $database_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteDatabaseByName(string $database_name): bool
    {
        if (!$this->hasDatabaseByName($database_name)) {
            throw new \InvalidArgumentException('Unknown database '.$database_name.' given.');
        }

        return $this->deleteDatabaseById($this->getDatabaseIdFromName($database_name));
    }

    /**
     * @param string $user_sid
     * @param string $event_name
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    public function eventBegin(string $user_sid, string $event_name): bool
    {
        $response = $this->request(self::API_EVENT_BEGIN, [
            'query' => [
                'source' => $user_sid,
                'event' => $event_name,
            ],
        ]);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @throws \ErrorException
     *
     * @return bool
     */
    public function eventEnd(): bool
    {
        $response = $this->request(self::API_EVENT_END, []);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param string     $database_name
     * @param null|array $options
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateScript(string $database_name, ?array $options = null): string
    {
        $dimension_names = $options['name_dimensions'] ?? null;
        $cube_names = $options['name_cubes'] ?? null;

        return $this->getDatabaseByName($database_name)
            ->generateScript($dimension_names, $cube_names, $options)
        ;
    }

    /**
     * @throws \Exception
     *
     * @return $this
     */
    public function getConnection(): Connection
    {
        if (null === $this->getClient()) {
            throw new \DomainException('No Jedox connection initialized.');
        }

        return $this;
    }

    /**
     * @throws \ErrorException
     *
     * @return string
     */
    public function getConnectionUserName(): string
    {
        $user_info = $this->getUserInfo();

        return (string) ($user_info[0][1] ?? $this->user);
    }

    /**
     * @param string    $databaseNameSlashCubeName
     * @param null|bool $use_cache
     *
     * @throws \Exception
     *
     * @return Cube
     */
    public function getCube(string $databaseNameSlashCubeName, ?bool $use_cache = null): Cube
    {
        if (!\strpos($databaseNameSlashCubeName, '/')) {
            throw new \InvalidArgumentException('Connection::getCube() requires Database/Cube path');
        }

        [$db_name, $cube_name] = \explode('/', $databaseNameSlashCubeName);

        return $this->getDatabaseByName($db_name, $use_cache)->getCubeByName($cube_name);
    }

    /**
     * @throws \Exception
     *
     * @return null|string
     */
    public function getDataToken(): ?string
    {
        $server_info = $this->getInfo();

        // return 6th csv column of first row of server info => data token
        return $server_info[0][6] ?? null;
    }

    /**
     * @param string $database_name
     *
     * @throws \Exception
     *
     * @return Database|SystemDatabase
     *
     * @see Connection::getDatabaseByName() alias
     */
    public function getDatabase(string $database_name): Database
    {
        return $this->getDatabaseByName($database_name);
    }

    /**
     * @param int       $database_id
     * @param null|bool $use_cache
     *
     * @throws \Exception
     *
     * @return Database|SystemDatabase
     */
    public function getDatabaseById(int $database_id, ?bool $use_cache = null): Database
    {
        $use_cache = $use_cache ?? true;

        // database ID is unknown
        if ($use_cache && !$this->hasDatabaseById($database_id)) {
            throw new \InvalidArgumentException('Unknown database ID '.$database_id.' given');
        }

        // database not yet initialized
        if (!$use_cache || !isset($this->databases[$database_id])) {
            $db_list_record = $this->getDatabaseListRecordById($database_id);

            // create either SystemDatabase or normal Database object depending on database name
            if ('System' === $db_list_record[1]) {
                $this->databases[$database_id] = new SystemDatabase($this, $db_list_record);

                return $this->databases[$database_id];
            }

            $this->databases[$database_id] = new Database($this, $db_list_record);
        }

        return $this->databases[$database_id];
    }

    /**
     * @param string    $database_name
     * @param null|bool $use_cache
     *
     * @throws \Exception
     *
     * @return Database|SystemDatabase
     */
    public function getDatabaseByName(string $database_name, ?bool $use_cache = null): Database
    {
        if (!$this->hasDatabaseByName($database_name)) {
            throw new \InvalidArgumentException('Unknown database name '.$database_name.' given');
        }

        return $this->getDatabaseById($this->getDatabaseIdFromName($database_name), $use_cache);
    }

    /**
     * @param string $database_name
     *
     * @throws \DomainException
     *
     * @return int
     */
    public function getDatabaseIdFromName(string $database_name): int
    {
        if (isset($this->databaseList['olap_name'][$database_name])) {
            return $this->databaseList['olap_name'][$database_name];
        }

        throw new \DomainException('database '.$database_name.' not found');
    }

    /**
     * @param string $database_name
     *
     * @throws \InvalidArgumentException
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return array
     *
     * @see Connection::getDatabaseListRecordByName() alias
     */
    public function getDatabaseListRecord(string $database_name): array
    {
        return $this->getDatabaseListRecordByName($database_name);
    }

    /**
     * @param int $database_id
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return array
     */
    public function getDatabaseListRecordById(int $database_id): array
    {
        $this->listDatabases();
        if (!isset($this->databaseList['olap_id'][$database_id])) {
            throw new \InvalidArgumentException('Unknown database ID '.$database_id.' given.');
        }

        return $this->databaseList['olap_id'][$database_id];
    }

    /**
     * @param string $database_name
     *
     * @throws \InvalidArgumentException
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return array
     */
    public function getDatabaseListRecordByName(string $database_name): array
    {
        $this->listDatabases();
        if (!$this->hasDatabaseByName($database_name)) {
            throw new \InvalidArgumentException('Unknown database name '.$database_name.' given.');
        }

        return $this->getDatabaseListRecordById($this->getDatabaseIdFromName($database_name));
    }

    /**
     * @param int $databaseId
     *
     * @return null|string
     */
    public function getDatabaseNameFromId(int $databaseId): ?string
    {
        return $this->databaseList['olap_id'][$databaseId][1] ?? null;
    }

    /**
     * @param null|array{show_counters:int, show_enckey:int, show_user_info:int} $options
     *
     * @throws \ErrorException
     *
     * @return Store
     */
    public function getInfo(?array $options = null): Store
    {
        return $this->request(self::API_SERVER_INFO, [
            'query' => [
                'show_counters' => (int) ($options['show_counters'] ?? 0),
                'show_enckey' => (int) ($options['show_enckey'] ?? 0),
                'show_user_info' => (int) ($options['show_user_info'] ?? 0),
            ],
        ]);
    }

    /**
     * @param null|string $host_with_port
     * @param null|string $user
     * @param null|string $pass
     * @param null|string $sid
     *
     * @throws GuzzleException
     * @throws \ErrorException
     *
     * @return Connection
     */
    public static function getInstance(
        ?string $host_with_port = null,
        ?string $user = null,
        ?string $pass = null,
        ?string $sid = null
    ): Connection {
        return new self($host_with_port, $user, $pass, $sid);
    }

    /**
     * @param null|array{mode:string} $options
     *
     * @throws \ErrorException
     *
     * @return Store
     */
    public function getLicenseInfos(?array $options = null): Store
    {
        return $this->request(self::API_SERVER_LICENSES, [
            'query' => [
                'mode' => $options['mode'] ?? 0,
            ],
        ]);
    }

    /**
     * @return null|string
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * @throws \Exception
     */
    public function getSessions(): void
    {
        $sys_db = $this->getSystemDatabase();
        $sess_cube = $sys_db->getCube('#_SESSIONS_');
        $subset = $sess_cube->getSubset('#_SESSION_');

        $data_filter = new DataFilter($sys_db->getDimension('#_SESSION_'));
        $data_filter->addFlag(DataFilter::FLAG_STRING);
        $data_filter->useStrings(false);

        $subset->setDataFilter($data_filter);

        // @todo implement Connection::GetSessions()
    }

    /**
     * This function only works if lib and server both run on the same machine
     * retrieves the session of user _internal_suite via shared memory.
     *
     * @param null|string $secret
     *
     * @throws GuzzleException
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return null|Connection
     */
    public function getSuperConnection(?string $secret = null): ?Connection
    {
        if (null !== $this->superConnection) {
            return $this->superConnection;
        }

        if (null !== $secret) {
            $this->setSecret($secret);
        }

        if (null === $this->secret) {
            throw new \DomainException('missing secret information in Connection::getSuperConnection()');
        }

        $shmop_prefix = 'SSID$';
        $shmop_prefix_len = 5;
        $shmop_len = 37;

        $shmop_key = \hexdec(\hash('crc32b', $shmop_prefix.$this->secret));
        $shmop_id = \shmop_open($shmop_key, 'c', 0600, $shmop_len);
        $shmop_mask = \str_pad($this->secret, $shmop_len, $this->secret);

        if (false !== $shmop_id) {
            $super_sid = \shmop_read($shmop_id, 0, $shmop_len) ^ $shmop_mask;
            \shmop_close($shmop_id);

            if (0 === \strpos($super_sid, $shmop_prefix)) {
                $super_sid = \substr($super_sid, $shmop_prefix_len);

                return self::getInstance($this->host, null, null, $super_sid);
            }
        }

        return null;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return SystemDatabase
     */
    public function getSystemDatabase(): SystemDatabase
    {
        return $this->getDatabaseByName('System');
    }

    /**
     * @param null|string $user_name (default user name of connection)
     *
     * @throws \Exception
     *
     * @return User
     */
    public function getUser(?string $user_name = null): User
    {
        $user_name = $user_name ?? $this->getConnectionUserName();

        return $this->getSystemDatabase()
            ->getUser($user_name)
        ;
    }

    /**
     * @throws \ErrorException
     *
     * @return Store
     */
    public function getUserInfo(): Store
    {
        return $this->request(self::API_SERVER_USER_INFO, [
            'query' => [
                'show_permission' => 1,
                'show_info' => 1,
                'show_gpuflag' => 1,
            ],
        ]);
    }

    /**
     * @param string $database_name
     *
     * @return bool
     *
     * @see Connection::hasDatabaseByName() alias
     */
    public function hasDatabase(string $database_name): bool
    {
        return $this->hasDatabaseByName($database_name);
    }

    /**
     * @param int $databaseId
     *
     * @return bool
     */
    public function hasDatabaseById(int $databaseId): bool
    {
        return isset($this->databaseList['olap_id'][$databaseId]);
    }

    /**
     * @param string $databaseName
     *
     * @return bool
     */
    public function hasDatabaseByName(string $databaseName): bool
    {
        return isset($this->databaseList['olap_name'][$databaseName]);
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return (bool) self::$debugMode;
    }

    /**
     * @param null|bool  $cached
     * @param null|array{show_normal:int, show_system:int, show_user_info:int, show_permission:int} $options
     *
     * @throws \ErrorException
     *
     * @return null|array
     */
    public function listDatabases(?bool $cached = null, ?array $options = null): ?array
    {
        $cached = $cached ?? true;

        $options = (array) $options;

        if (true === $cached && null !== $this->databaseList) {
            return $this->databaseList;
        }

        $database_list = $this->request(self::API_SERVER_DATABASES, [
            'query' => [
                'show_normal' => (int) ($options['show_normal'] ?? 1),
                'show_system' => (int) ($options['show_system'] ?? 1),
                'show_user_info' => (int) ($options['show_user_info'] ?? 1),
                'show_permission' => (int) ($options['show_permission'] ?? 1),
            ],
        ]);

        $tmp_list = [];

        foreach ($database_list as $database_row) {
            $tmp_list['olap_id'][(int) $database_row[0]] = $database_row;
            $tmp_list['olap_name'][$database_row[1]] = (int) $database_row[0];
        }

        // CompatibilityLayer support
        if ((bool) ($options['palo_compat'] ?? false)) {
            return $tmp_list['olap_name'] ?? [];
        }

        // databases complete
        $this->databaseList = $tmp_list;

        return $this->databaseList;
    }

    /**
     * @param array $params
     * @param array $options
     *
     * @return array
     */
    public static function mergeParams(array $params, array $options): array
    {
        foreach ($options as $opt_key => $opt_val) {
            $params['query'][$opt_key] = $opt_val;
        }

        return $params;
    }

    /**
     * @throws GuzzleException
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return Connection
     */
    public function reconnect(): Connection
    {
        $this->close();

        return $this->init();
    }

    /**
     * @throws \Exception
     *
     * @return Connection
     */
    public function reload(): self
    {
        $this->databases = new DatabaseStore();
        $this->databaseList = [];
        $this->listDatabases(false);

        return $this;
    }

    /**
     * @param string $name_old
     * @param string $name_new
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    public function renameDatabase(string $name_old, string $name_new): bool
    {
        $db_id = $this->getDatabaseIdFromName($name_old);

        $response = $this->request(self::API_DATABASE_RENAME, [
            'query' => [
                'database' => $db_id,
                'new_name' => $name_new,
            ],
        ]);

        // @todo check if the response really gives a status --> undocumented
        return (bool) ($response[0] ?? false);
    }

    /**
     * @param string $url
     * @param array|null $params
     * @return Store
     * @throws \ErrorException
     */
    public function request(string $url, ?array $params = null): Store
    {
        if (null === ($client = $this->getClient())) {
            throw new \ErrorException('HTTP client not initialized. Cancelled HTTP request.');
        }

        if (null === $params) {
            $params = [];
        }

        $params['query']['sid'] = $this->getSessionId(); // add SESSIONID to request automatically
        //$params['headers'] = [
        // 'X-PALO-SV' => $this->getDataToken()
        //];
        // enable gzip for transfer
        $params['decode_content'] = 'gzip';
        $params['connect_timeout'] = 3;
        $params['timeout'] = 0;

        if ($this->isDebugMode()) {
            \file_put_contents('php://stderr', \print_r([$url, $params], true));
        }

        try {
            $response = $client->request('GET', $url, $params);
            $this->dataToken = $response->getHeader('X-PALO-SV');

            return $this->parseCsvResponse($response);
        } catch (GuzzleException $exception) {
            \file_put_contents('php://stderr', $exception->getMessage());
        } catch (\Exception $exception) {
            \file_put_contents('php://stderr', $exception->getMessage());
        }

        return new Store();
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return null|resource
     */
    public function requestRaw(string $url, array $params)
    {
        if (null === ($client = $this->getClient())) {
            throw new \ErrorException('HTTP client not initialized. Cancelled HTTP request.');
        }

        $params['query']['sid'] = $this->getSessionId(); // add SESSIONID to request automatically
        $params['decode_content'] = 'gzip';

        try {
            $response = $client->request('GET', $url, $params);
            $this->dataToken = $response->getHeader('X-PALO-SV');

            return $response->getBody()->detach();
        } catch (GuzzleException $exception) {
            throw new \ErrorException($exception->getMessage());
        } catch (\Exception $exception) {
            throw new \ErrorException($exception->getMessage());
        }
    }

    public function reset(): void
    {
        // @todo Connection::reset()
    }

    /**
     * @throws \BadMethodCallException
     */
    public function samlMetaSp(): void
    {
        // @todo implement Connection::samlMetaSp()
        throw new \BadMethodCallException('method '.__METHOD__.' not implemented');
    }

    /**
     * @param string $secret (see config.php --> CFG_SECRET)
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @param string $user_name
     * @param string $password_new
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    public function setUserPassword(string $user_name, string $password_new): bool
    {
        $response = $this->request(self::API_SERVER_CHANGE_PASSWORD, [
            'query' => [
                'user' => $user_name,
                'password' => $password_new,
            ],
        ]);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @throws \BadMethodCallException
     */
    public function svsEdit(): void
    {
        // @todo implement Connection::svsEdit()
        throw new \BadMethodCallException('method '.__METHOD__.' not implemented');
    }

    /**
     * @throws \ErrorException
     *
     * @return Store
     */
    public function svsInfo(): Store
    {
        return $this->request(self::API_SVS_INFO, []);
    }

    /**
     * @param null|array{mode:string} $options
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    public function svsRestart(?array $options = null): bool
    {
        $params = [
            'query' => [],
        ];

        if (isset($options['mode'])) {
            $params['query']['mode'] = $options['mode'];
        }

        $response = $this->request(self::API_SVS_RESTART, $params);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @return null|Client
     */
    private function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    private function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @throws GuzzleException
     * @throws \ErrorException
     *
     * @return self
     */
    private function init(): self
    {
        // already connected
        if (null !== $this->client) {
            return $this;
        }

        $this->client = new Client([
            'base_uri' => $this->host,
            'cookies' => true,
            'timeout' => 0,
        ]);

        // in case PHP crashes Connection::close() will be called
        // to close the Jedox OLAP connection
        \register_shutdown_function([$this, 'close']);

        if (null !== $this->sessionId) {
            $this->user = $this->getConnectionUserName();
        }

        if (null === $this->sessionId) {
            if ($this->isDebugMode()) {
                \file_put_contents('php://stderr', 'Connection: '.$this->host.' / user: '.$this->user.\PHP_EOL);
            }

            $response = $this->client->request('POST', self::API_SERVER_LOGIN, [
                'form_params' => [
                    'user' => $this->user,
                    'extern_password' => $this->pass,
                ],
                'connect_timeout' => 3,
                'timeout' => 3,
                'decode_content' => 'gzip',
            ]);

            $this->dataToken = $response->getHeader('X-PALO-SV');
            $login_data = \str_getcsv($response->getBody()->getContents(), ';', '"', '"');

            // check if login was successful
            if ('' === ($login_data[0] ?? '')) {
                throw new \ErrorException('login not possible '.$response->getBody()->getContents());
            }

            $this->sessionId = $login_data[0];

            if ($this->isDebugMode()) {
                \file_put_contents('php://stderr', 'Received SID: '.$this->getSessionId().\PHP_EOL);
            }
        }

        // load database infos
        $this->listDatabases();

        return $this;
    }

    /**
     * parses the Jedox OLAP server response csv.
     *
     * @param ResponseInterface $response
     *
     * @throws \Exception
     *
     * @return Store
     */
    private function parseCsvResponse(ResponseInterface $response): Store
    {
        if (200 !== $response->getStatusCode()) {
            throw new \ErrorException('OLAP server returned HTTP status code '.
                $response->getStatusCode().' instead of 200/OK');
        }

        // wrap in ArrayObject for memory optimized handling
        $return = new Store();

        // fetch body as stream from guzzle client
        $stream = $response->getBody()->detach();

        if (!\is_resource($stream)) {
            return $return;
        }

        \fseek($stream, 0);
        while (false !== ($data_row = \fgetcsv($stream, 0, ';', '"', '"'))) {
            if (null === $data_row) {
                // @todo use continue instead of break?
                break;
            }
            // consider to remove array_pop(), it is fixing
            // Jedox csv style with trailing delimiters :(
            \array_pop($data_row);
            $return[] = $data_row;
        }
        \fclose($stream);

        return $return;
    }
}

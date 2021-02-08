<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseCreate;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseDestroy;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseGenerateScript;
use Xodej\Olapi\ApiRequestParams\ApiDatabaseRename;
use Xodej\Olapi\ApiRequestParams\ApiEventBegin;
use Xodej\Olapi\ApiRequestParams\ApiEventEnd;
use Xodej\Olapi\ApiRequestParams\ApiServerChangePassword;
use Xodej\Olapi\ApiRequestParams\ApiServerDatabases;
use Xodej\Olapi\ApiRequestParams\ApiServerInfo;
use Xodej\Olapi\ApiRequestParams\ApiServerLicenses;
use Xodej\Olapi\ApiRequestParams\ApiServerShutdown;
use Xodej\Olapi\ApiRequestParams\ApiServerUserInfo;
use Xodej\Olapi\ApiRequestParams\ApiSvsInfo;
use Xodej\Olapi\ApiRequestParams\ApiSvsRestart;
use Xodej\Olapi\ApiRequestParams\IRequest;
use Xodej\Olapi\Filter\DataFilter;

/**
 * Class Connection.
 */
class Connection
{
    public static bool $debugMode = false;

    private ?string $host = null;
    private ?string $user = null;
    private ?string $pass = null;

    private ?Client $client = null;
    private ?string $sessionId = null;
    private ?string $dataToken = null;
    private ?string $secret = null;

    private ?Connection $superConnection = null;

    /**
     * @var DatabaseCollection<Database>
     */
    private DatabaseCollection $databases;

    /**
     * @var null|array<int, array<string>>
     */
    private ?array $databaseLookupByID = null;

    /**
     * @var null|array<string,int>
     */
    private ?array $databaseLookupByName = null;

    /**
     * Connection constructor.
     *
     * @param null|string $host_with_port (Optional) url with port (default: 127.0.0.1:7777)
     * @param null|string $username       (Optional) Jedox user name (default: admin)
     * @param null|string $password       (Optional) Jedox password (default: admin)
     * @param null|string $sid            (Optional) session ID
     *
     * @throws \ErrorException
     */
    public function __construct(
        ?string $host_with_port = null,
        ?string $username = null,
        ?string $password = null,
        ?string $sid = null
    ) {
        $ini_host = \get_cfg_var('jedox.host');
        if (!is_string($ini_host) || '' === $ini_host) {
            $ini_host = 'http://127.0.0.1:7777';
        }

        $ini_user = \get_cfg_var('jedox.user');
        if (!is_string($ini_user) || '' === $ini_user) {
            $ini_user = 'admin';
        }

        $ini_pass = \get_cfg_var('jedox.pass');
        if (!is_string($ini_pass) || '' === $ini_pass) {
            $ini_pass = 'admin';
        }

        // Order:
        //    1. use given credentials, if not given
        //    2. use credentials from php.ini --> jedox.xxxx variables, if not existent
        //    3. use defaults admin/admin etc.
        $this->host = $host_with_port ?? $ini_host;
        $this->user = $username ?? $ini_user;
        $this->pass = $password ?? $ini_pass;

        // @todo check if this needs to be done in a separate login
        if (null !== $sid) {
            $this->user = null;
            $this->pass = null;
            $this->sessionId = $sid;
        }

        // init ArrayObject for database objects
        $this->databases = new DatabaseCollection();

        // initialize http client and read available databases from server
        $this->init();
    }

    /**
     * Explicit destructor to close Jedox connection.
     *
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close connection, reset cached content and invalidate session ID.
     *
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
        $client->get('/server/logout', [
            'query' => [
                'sid' => $this->getSessionId(),
                'type' => 1,
            ],
        ]);

        $this->client = null;
        $this->databases = new DatabaseCollection();
        $this->databaseLookupByID = [];
        $this->databaseLookupByName = [];

        return true;
    }

    /**
     * Creates a database.
     *
     * @param string      $database_name       Name of the new database
     * @param null|string $external_identifier (Optional) Path to backup file where the database will be loaded from
     * @param null|string $password            (Optional) If in restore mode, password to provided encrypted archive with database
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createDatabase(string $database_name, ?string $external_identifier = null, ?string $password = null): bool
    {
        $request = new ApiDatabaseCreate();
        $request->new_name = $database_name;
        $request->type = 0;
        $request->external_identifier = $external_identifier;
        $request->password = $password;

        $response = $this->request($request);

        // reload databases after creation
        $this->reload();

        return (bool) ($response[0] ?? false);
    }

    /**
     * Deletes a database.
     *
     * @param string $database_name database name
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
     * Delete database by ID.
     *
     * @param int $database_id database ID
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

        $request = new ApiDatabaseDestroy();
        $request->database = $database_id;

        $response = $this->getConnection()->request($request);

        $this->reload();

        return '1' === ($response[0][0] ?? '0');
    }

    /**
     * Delete database by database name.
     *
     * @param string $database_name database name
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
     * Begin event.
     *
     * @param string $user_sid   session ID of user
     * @param string $event_name event name
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    public function eventBegin(string $user_sid, string $event_name): bool
    {
        $request = new ApiEventBegin();
        $request->source = $user_sid;
        $request->event = $event_name;

        $response = $this->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * End event.
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    public function eventEnd(): bool
    {
        $request = new ApiEventEnd();
        $response = $this->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Returns database script.
     *
     * @param string                         $database_name   database name
     * @param null|string[]                  $dimension_names (Optional) array of dimension names
     * @param null|string[]                  $cube_names      (Optional) array of cube names
     * @param null|ApiDatabaseGenerateScript $request          (Optional) array of options
     *
     * @throws \Exception
     *
     * @return null|string
     */
    public function generateScript(string $database_name, ?array $dimension_names = null, ?array $cube_names = null, ?ApiDatabaseGenerateScript $request = null): ?string
    {
        return $this->getDatabaseByName($database_name)
            ->generateScript($dimension_names, $cube_names, $request)
        ;
    }

    /**
     * Returns connection object.
     *
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
     * Returns user name used for connection.
     *
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
     * Returns cube object for given database/cube identifier.
     *
     * @param string    $databaseNameSlashCubeName Jedox database/cube identifier
     * @param null|bool $use_cache                 (Optional) use cache
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
     * Returns data token.
     *
     * @throws \Exception
     *
     * @return null|string
     */
    public function getDataToken(): ?string
    {
        $server_info = $this->getInfo();

        // return 6th csv column of first row of server info => data token
        $ret = $server_info[0][6] ?? null;
        if (null !== $ret) {
            return (string) $ret;
        }
        return null;
    }

    /**
     * Returns database object by database name.
     *
     * @param string $database_name database name
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
     * Returns database object by database ID.
     *
     * @param int       $database_id database ID
     * @param null|bool $use_cache   (Optional) use cache
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
     * Returns database object by database name.
     *
     * @param string    $database_name database name
     * @param null|bool $use_cache     (Optional) use cache
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
     * Returns database ID from database name.
     *
     * @param string $database_name database name
     *
     * @throws \DomainException
     *
     * @return int
     */
    public function getDatabaseIdFromName(string $database_name): int
    {
        if (isset($this->databaseLookupByName[\strtolower($database_name)])) {
            return $this->databaseLookupByName[\strtolower($database_name)];
        }

        throw new \DomainException('database '.$database_name.' not found');
    }

    /**
     * Returns database record by database name.
     *
     * @param string $database_name database name
     *
     * @throws \InvalidArgumentException
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return array<string>
     *
     * @see Connection::getDatabaseListRecordByName() alias
     */
    public function getDatabaseListRecord(string $database_name): array
    {
        return $this->getDatabaseListRecordByName($database_name);
    }

    /**
     * Returns database record by database ID.
     *
     * @param int $database_id database ID
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return array<string>
     */
    public function getDatabaseListRecordById(int $database_id): array
    {
        $this->listDatabases();
        if (!isset($this->databaseLookupByID[$database_id])) {
            throw new \InvalidArgumentException('Unknown database ID '.$database_id.' given.');
        }

        return $this->databaseLookupByID[$database_id];
    }

    /**
     * Returns database record by database name.
     *
     * @param string $database_name database name
     *
     * @throws \InvalidArgumentException
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return array<string>
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
     * Returns database object by database name.
     *
     * @param int $databaseId database ID
     *
     * @return null|string
     */
    public function getDatabaseNameFromId(int $databaseId): ?string
    {
        return $this->databaseLookupByID[$databaseId][1] ?? null;
    }

    /**
     * Returns array response of /server/info API call.
     *
     * @param ?ApiServerInfo $request
     *
     * @throws \ErrorException
     *
     * @return GenericCollection<array<string>>
     */
    public function getInfo(?ApiServerInfo $request = null): GenericCollection
    {
        $request = $request ?? new ApiServerInfo();

        return $this->request($request);
    }

    /**
     * Returns connection object.
     *
     * @param null|string $host_with_port (Optional) url with port (default: 127.0.0.1:7777)
     * @param null|string $username       (Optional) Jedox user name (default: admin)
     * @param null|string $password       (Optional) Jedox password (default: admin)
     * @param null|string $sid            (Optional) session ID
     *
     * @throws \ErrorException
     *
     * @return Connection
     */
    public static function getInstance(
        ?string $host_with_port = null,
        ?string $username = null,
        ?string $password = null,
        ?string $sid = null
    ): Connection {
        return new self($host_with_port, $username, $password, $sid);
    }

    /**
     * Returns array response of /server/licenses API call.
     *
     * @param null|ApiServerLicenses $request (Optional) options
     *
     * @throws \ErrorException
     *
     * @return GenericCollection<array<string>>
     */
    public function getLicenseInfos(?ApiServerLicenses $request = null): GenericCollection
    {
        $request ??= new ApiServerLicenses();

        return $this->request($request);
    }

    /**
     * Returns secret if set.
     *
     * @return null|string
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * Returns array of running user sessions.
     *
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
     * @param null|string $secret (Optional) secret
     *
     * @throws \ErrorException
     * @throws \Exception
     *
     * @return null|Connection
     *
     * @nodoc
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

        $shmop_key = (int) \hexdec(\hash('crc32b', $shmop_prefix.$this->secret));
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
     * Returns system database object.
     *
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
     * Returns user object.
     *
     * @param null|string $user_name (Optional) user name (default: user name of connection)
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
     * Returns array of user info.
     *
     * @throws \ErrorException
     *
     * @return GenericCollection<string>
     */
    public function getUserInfo(): GenericCollection
    {
        $request = new ApiServerUserInfo();
        $request->show_permission = true;
        $request->show_info = true;
        $request->show_gpuflag = true;

        return $this->request($request);
    }

    /**
     * Returns true if database exists.
     *
     * @param string $database_name database name
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
     * Returns true if database exists.
     *
     * @param int $databaseId database ID
     *
     * @return bool
     */
    public function hasDatabaseById(int $databaseId): bool
    {
        return isset($this->databaseLookupByID[$databaseId]);
    }

    /**
     * Returns true if database exists.
     *
     * @param string $databaseName database name
     *
     * @return bool
     */
    public function hasDatabaseByName(string $databaseName): bool
    {
        return isset($this->databaseLookupByName[\strtolower($databaseName)]);
    }

    /**
     * Returns true if debug mode is enabled.
     *
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return self::$debugMode;
    }

    /**
     * @param null|bool                     $cached
     * @param null|ApiServerDatabases       $request
     *
     * @throws \ErrorException
     *
     * @return null|array<int,array<string>>
     */
    public function listDatabases(?bool $cached = null, ?ApiServerDatabases $request = null): ?array
    {
        $cached = $cached ?? true;

        if (true === $cached && null !== $this->databaseLookupByID) {
            return $this->databaseLookupByID;
        }

        $request ??= new ApiServerDatabases();
        $request->show_normal = true;
        $request->show_system = true;
        $request->show_user_info = true;
        $request->show_permission = true;

        $database_list = $this->request($request);

        $this->databases = new DatabaseCollection();
        $this->databaseLookupByID = [];
        $this->databaseLookupByName = [];

        foreach ($database_list as $database_row) {
            $this->databaseLookupByID[(int) $database_row[0]] = $database_row;
            $this->databaseLookupByName[\strtolower($database_row[1])] = (int) $database_row[0];
        }

        return $this->databaseLookupByID;
    }

    /**
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
        $this->databases = new DatabaseCollection();
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
        $request = new ApiDatabaseRename();
        $request->database = $this->getDatabaseIdFromName($name_old);
        $request->new_name = $name_new;

        $response = $this->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @param IRequest $request
     *
     * @throws \ErrorException
     *
     * @return GenericCollection
     */
    public function request(IRequest $request): GenericCollection
    {
        if (null === ($client = $this->getClient())) {
            throw new \ErrorException('HTTP client not initialized. Cancelled HTTP request.');
        }

        if (null === ($url = $request->url())) {
            throw new \InvalidArgumentException('URL missing in request');
        }

        $params = $request->asArray();
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
            $this->dataToken = $response->getHeader('X-PALO-SV')[0] ?? null;

            if ($this->isDebugMode()) {
                $tmp = $response->getBody()->detach();
                if (null !== $tmp) {
                    \file_put_contents('php://stderr', \print_r([\stream_get_contents($tmp)], true));
                }
            }

            return $this->parseCsvResponse($response);
        } catch (\Exception $exception) {
            \file_put_contents('php://stderr', $exception->getMessage().PHP_EOL.\implode("\n", $exception->getTrace()));
        }

        return new GenericCollection();
    }

    /**
     * @param string $url
     * @param array<string, array<string, mixed>> $params
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
            $this->dataToken = $response->getHeader('X-PALO-SV')[0] ?? null;

            return $response->getBody()->detach();
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
        $request = new ApiServerChangePassword();
        $request->user = $user_name;
        $request->password = $password_new;

        $response = $this->request($request);

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
     * @return GenericCollection
     */
    public function svsInfo(): GenericCollection
    {
        $request = new ApiSvsInfo();

        return $this->request($request);
    }

    /**
     * @param null|ApiSvsRestart $request
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    public function svsRestart(?ApiSvsRestart $request = null): bool
    {
        $request ??= new ApiSvsRestart();
        $response = $this->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * @throws \ErrorException
     *
     * @return bool
     */
    public function shutdownServer(): bool
    {
        $request = new ApiServerShutdown();
        $response = $this->request($request);

        return (bool) ($response[0] ?? false);
    }

    /**
     * Returns client object.
     *
     * @return null|Client
     */
    private function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * Returns session ID.
     *
     * @return null|string
     */
    private function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * Initializes a connection.
     *
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

            $response = $this->client->request('POST', '/server/login', [
                'form_params' => [
                    'user' => $this->user,
                    'extern_password' => $this->pass,
                ],
                'connect_timeout' => 3,
                'timeout' => 3,
                'decode_content' => 'gzip',
            ]);

            $this->dataToken = $response->getHeader('X-PALO-SV')[0] ?? null;
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
        $this->listDatabases(false);

        return $this;
    }

    /**
     * Parses the Jedox OLAP server response csv.
     *
     * @param ResponseInterface $response response object
     *
     * @throws \Exception
     *
     * @return GenericCollection
     */
    private function parseCsvResponse(ResponseInterface $response): GenericCollection
    {
        if (200 !== $response->getStatusCode()) {
            throw new \ErrorException('OLAP server returned HTTP status code '.
                $response->getStatusCode().' instead of 200/OK');
        }

        // wrap in ArrayObject for memory optimized handling
        $return = new GenericCollection();

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

<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use Xodej\Olapi\ApiRequestParams\ApiCellExportParams;

/**
 * Class Role.
 */
class Role extends Element
{
    public const RIGHT_USER = 'user';
    public const RIGHT_PASSWORD = 'password';
    public const RIGHT_GROUP = 'group';
    public const RIGHT_DATABASE = 'database';
    public const RIGHT_CUBE = 'cube';
    public const RIGHT_DIMENSION = 'dimension';
    public const RIGHT_DIMENSION_ELEMENT = 'dimension element';
    public const RIGHT_CELL_DATA = 'cell data';
    public const RIGHT_RIGHTS = 'rights';
    public const RIGHT_SYSTEM_OPERATIONS = 'system operations';
    public const RIGHT_EVENT_PROCESSOR = 'event processor';
    public const RIGHT_SUB_SET_VIEW = 'sub-set view';
    public const RIGHT_USER_INFO = 'user info';
    public const RIGHT_RULE = 'rule';
    public const RIGHT_STE_REPORTS = 'ste_reports';
    public const RIGHT_STE_FILES = 'ste_files';
    public const RIGHT_STE_PALO = 'ste_palo';
    public const RIGHT_STE_USERS = 'ste_users';
    public const RIGHT_STE_ETL = 'ste_etl';
    public const RIGHT_STE_CONNS = 'ste_conns';
    public const RIGHT_STE_DRILLTHROUGH = 'drillthrough';
    public const RIGHT_STE_SCHEDULER = 'ste_scheduler';
    public const RIGHT_STE_LOGS = 'ste_logs';
    public const RIGHT_STE_LICENSES = 'ste_licenses';
    public const RIGHT_STE_MOBILE = 'ste_mobile';
    public const RIGHT_STE_ANALYZER = 'ste_analyzer';
    public const RIGHT_STE_SESSIONS = 'ste_sessions';
    public const RIGHT_STE_SETTINGS = 'ste_settings';
    public const RIGHT_AUDIT = 'audit';
    public const RIGHT_STE_PERF = 'ste_perf';
    public const RIGHT_STE_PACKAGES = 'ste_packages';
    public const RIGHT_STE_REPOSITORY = 'ste_repository';
    public const RIGHT_CELL_DATA_HOLD = 'cell data hold';

    /**
     * @var array{user: string, password: string, group: string, database: string, cube: string, dimension: string,
     *                  dimension element: string, cell data: string, rights: string, system operations: string,
     *                  event processor: string, sub-set view: string, user info: string, rule: string, ste_reports: string,
     *                  ste_files: string, ste_palo: string, ste_users: string, ste_etl: string, ste_conns: string,
     *                  drillthrough: string, ste_scheduler: string, ste_logs: string, ste_licenses: string,
     *                  ste_mobile: string, ste_analyzer: string, ste_sessions: string, ste_settings: string, audit: string,
     *                  ste_perf: string, ste_packages: string, ste_repository: string, cell data hold: string}
     */
    protected static array $rights = [
        self::RIGHT_USER => 'N',
        self::RIGHT_PASSWORD => 'N',
        self::RIGHT_GROUP => 'N',
        self::RIGHT_DATABASE => 'R',
        self::RIGHT_CUBE => 'R',
        self::RIGHT_DIMENSION => 'R',
        self::RIGHT_DIMENSION_ELEMENT => 'R',
        self::RIGHT_CELL_DATA => 'R',
        self::RIGHT_RIGHTS => 'N',
        self::RIGHT_SYSTEM_OPERATIONS => 'N',
        self::RIGHT_EVENT_PROCESSOR => 'N',
        self::RIGHT_SUB_SET_VIEW => 'R',
        self::RIGHT_USER_INFO => 'N',
        self::RIGHT_RULE => 'N',
        self::RIGHT_STE_REPORTS => 'R',
        self::RIGHT_STE_FILES => 'N',
        self::RIGHT_STE_PALO => 'N',
        self::RIGHT_STE_USERS => 'N',
        self::RIGHT_STE_ETL => 'N',
        self::RIGHT_STE_CONNS => 'N',
        self::RIGHT_STE_DRILLTHROUGH => 'D',
        self::RIGHT_STE_SCHEDULER => 'N',
        self::RIGHT_STE_LOGS => 'N',
        self::RIGHT_STE_LICENSES => 'N',
        self::RIGHT_STE_MOBILE => 'D',
        self::RIGHT_STE_ANALYZER => 'D',
        self::RIGHT_STE_SESSIONS => 'N',
        self::RIGHT_STE_SETTINGS => 'N',
        self::RIGHT_AUDIT => 'N',
        self::RIGHT_STE_PERF => 'N',
        self::RIGHT_STE_PACKAGES => 'N',
        self::RIGHT_STE_REPOSITORY => 'N',
        self::RIGHT_CELL_DATA_HOLD => 'N',
    ];

    /**
     * checks if right exists.
     *
     * @param string $right_name
     *
     * @return bool
     */
    public static function rightExists(string $right_name): bool
    {
        return isset(self::$rights[$right_name]);
    }

    /**
     * @return array<string,string>
     */
    public static function defaultRights(): array
    {
        return self::$rights;
    }

    /**
     * @param null|array<string,string> $rights_permissions
     *
     * @return bool
     */
    public static function isRightsPermissionsValid(?array $rights_permissions = null): bool
    {
        $rights_permissions = (array) $rights_permissions;

        foreach ($rights_permissions as $right => $permission) {
            if (!is_string($right) || !self::rightExists($right)) {
                return false;
            }
            if (!is_string($permission) || !preg_match('~^[SDWRN]$~i', $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param null|array<string,string> $rights_permissions
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setRightsPermissions(?array $rights_permissions = null): bool
    {
        $rights_permissions = (array) $rights_permissions;
        if (!self::isRightsPermissionsValid($rights_permissions)) {
            throw new \InvalidArgumentException('given rights <-> permission data set not valid');
        }

        // create an array of all clean right <=> permission sets
        // taking into account user choices per rights object
        // not given rights will be set with defaults from Role::defaultRights()
        // also check for valid values: Splash, Delete, Write, Read, None
        $right_object_paths = [];
        $right_object_values = [];
        foreach (self::defaultRights() as $right => $default_permission) {
            $permission = $rights_permissions[$right] ?? $default_permission;
            if (is_string($permission) && preg_match('~^[SDWRN]$~i', $permission)) {
                $right_object_paths[] = [$this->getName(), $right];
                $right_object_values[] = strtoupper($permission);

                continue;
            }

            $right_object_paths[] = [$this->getName(), $right];
            $right_object_values[] = strtoupper($default_permission);
        }

        // nothing to write to cube
        if (0 === \count($right_object_values)) {
            return false;
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_ROLE_RIGHT_OBJECT')
            ->setBulk($right_object_values, $right_object_paths)
            ;
    }

    /**
     * Adds role to a group // adds group to a role.
     *
     * @param string $group_name group name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function addGroup(string $group_name): bool
    {
        if (!$this->getConnection()->getSystemDatabase()->hasGroup($group_name)) {
            throw new \InvalidArgumentException('failed to add group '.$group_name.' to role '.$this->getName().'. Group not found.');
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_GROUP_ROLE')
            ->setValue('1', [$group_name, $this->getName()])
            ;
    }

    /**
     * @throws \Exception
     *
     * @return string[]
     */
    public function getGroups(): array
    {
        $cube_group_role = $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_GROUP_ROLE')
        ;

        $params = new ApiCellExportParams();
        $params->area = $cube_group_role->createArea(['#_ROLE_' => [$this->getName()]]);

        $role_groups = $cube_group_role->arrayExport($params, false);

        return \array_map(static function (array $v) {
            return $v[1];
        }, $role_groups);
    }

    /**
     * @param null|string[] $group_names
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function removeGroups(?array $group_names = null): bool
    {
        if (null === $group_names) {
            $group_names = $this->getGroups();
        }

        $active_groups = $this->getGroups();

        $remove_groups = \array_intersect($group_names, $active_groups);

        $values = [];
        $paths = [];
        foreach ($remove_groups as $remove_group) {
            $values[] = null;
            $paths[] = [$remove_group, $this->getName()];
        }

        // nothing to write to cube
        if (0 === \count($values)) {
            return false;
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_GROUP_ROLE')
            ->setBulk($values, $paths)
            ;
    }
}

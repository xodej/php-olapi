<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Right.
 */
class Right
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

    protected static $rights = [
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
        self::RIGHT_CELL_DATA_HOLD => 'N'
    ];

    /**
     * @param string $right_name
     * @return bool
     */
    public static function exists(string $right_name): bool
    {
        return isset(self::$rights[$right_name]);
    }

    /**
     * @return array
     */
    public static function defaultRights(): array
    {
        return self::$rights;
    }
}

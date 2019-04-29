<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Interface IBase.
 */
interface IBase
{
    /**
     * @return int
     */
    public function getOlapObjectId(): int;

    /**
     * @return Connection
     */
    public function getConnection(): Connection;

    /**
     * @return Database
     */
    public function getDatabase(): Database;

    /**
     * @return IBase
     */
    public function reload();

    /**
     * @return bool
     */
    public function isDebugMode(): bool;
}

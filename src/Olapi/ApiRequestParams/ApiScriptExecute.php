<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/script/execute.
 *
 * Executes provided script.
 */
class ApiScriptExecute extends ApiAbstractRequest
{
    /**
     * Content of the script to execute.
     *
     * Jedox-Doc type: string
     */
    public ?string $definition = null;

    /**
     * Identifier of the database.
     *
     * Jedox-Doc type: identifier
     */
    public int $database;

    /**
     * Name of the database. Used only if database parameter is omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $name_database = null;

    /**
     * Name of the new database to be created. Used only if database and
     * name_database parameters are omitted.
     *
     * Jedox-Doc type: string
     */
    public ?string $new_name = null;

    /**
     * Variables values. Variable name string + comma + type (1=NUMERIC, 2=STRING)
     * + comma + variable value, variables separated by semicolon. Example:
     * "var1",1,5;"var2",2,"Demo".
     *
     * Jedox-Doc type: string
     */
    public ?string $variables = null;

    public function url(): ?string
    {
        return '/script/execute';
    }
}

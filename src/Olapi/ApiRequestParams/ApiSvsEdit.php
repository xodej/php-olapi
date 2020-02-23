<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

/**
 * Parameters for /api/svs/edit.
 *
 * Editing and displaying content of SVS scripts
 */
class ApiSvsEdit extends ApiAbstractRequest
{
    /**
     * 1 - list all available SVS scripts, 2 - display the content of SVS script,
     * 3 - replace the content of SVS script.
     *
     * Jedox-Doc type: integer
     */
    public ?int $mode = null;

    /**
     * Parameter for modes 2 and 3. SVS script relative path.
     *
     * Jedox-Doc type: string
     */
    public ?string $external_identifier = null;

    /**
     * Content of the SVS script to be written to the file.
     *
     * Jedox-Doc type: string
     */
    public ?string $definition = null;

    public function url(): ?string
    {
        return '/svs/edit';
    }
}

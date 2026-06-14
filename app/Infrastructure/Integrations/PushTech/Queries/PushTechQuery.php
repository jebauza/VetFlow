<?php

namespace App\Infrastructure\Integrations\PushTech\Queries;

class PushTechQuery
{
    const PATH = 'path';
    const QUERY_PARAMETERS = 'query_parameters';

    public string $path;
    public array $query_parameters;
}

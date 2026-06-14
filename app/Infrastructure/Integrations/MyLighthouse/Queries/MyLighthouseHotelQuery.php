<?php

namespace App\Infrastructure\Integrations\MyLighthouse\Queries;

use App\Infrastructure\Integrations\MyLighthouse\Queries\MyLighthouseQuery;

class MyLighthouseHotelQuery extends MyLighthouseQuery
{
    const PAGE = 'page';
    const PER_PAGE = 'per_page';

    public function __construct()
    {
        $this->{self::PATH} = 'hotels';
        $this->{self::PARAMETERS} = [
            self::PAGE => 1,
            self::PER_PAGE => 100,
        ];
    }

    public function setPage(int $page): void
    {
        $this->{self::PARAMETERS}[self::PAGE] = $page;
    }
}

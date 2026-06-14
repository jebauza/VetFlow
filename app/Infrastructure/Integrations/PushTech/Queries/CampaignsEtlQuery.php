<?php

namespace App\Infrastructure\Integrations\PushTech\Queries;

use App\Infrastructure\Integrations\PushTech\Queries\PushTechQuery;

class CampaignsEtlQuery extends PushTechQuery
{
    public function __construct()
    {
        $this->path = '/v2/account/:account_id/campaigns';
        $this->query_parameters = [
            'limit' => 200, // limit of number campaigns returns, default: 200
            'order' => 'desc', // creation_date order asc or desc, default: desc
        ];
    }

    public function set_limit(int $limit = 200)
    {
        $this->query_parameters['limit'] = $limit;

        return $this;
    }

    public function set_order(string $order = 'desc')
    {
        $this->query_parameters['order'] = $order;

        return $this;
    }
}

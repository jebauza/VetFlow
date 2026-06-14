<?php

namespace App\ParqueSantiago\Syschronize\PushTech\Etl\Queries;

use App\Infrastructure\Integrations\PushTech\Queries\PushTechQuery;

class PurchasesEtlQuery extends PushTechQuery
{
    public function __construct()
    {
        $this->path = '/v2/account/:account_id/purchases';
        $this->query_parameters = [
            'limit' => 200, // limit of number campaigns returns, default: 200
            'order' => 'desc', // creation_date order asc or desc, default: desc
        ];
    }

    public function set_limit(int $limit)
    {
        $this->query_parameters['limit'] = $limit;

        return $this;
    }

    public function set_order(string $order = 'desc')
    {
        $this->query_parameters['order'] = $order;

        return $this;
    }

    public function set_from_id(string $from_id = null)
    {
        if ($from_id) {
            $this->query_parameters['from_id'] = $from_id;
        } else {
            unset($this->query_parameters['from_id']);
        }

        return $this;
    }
}

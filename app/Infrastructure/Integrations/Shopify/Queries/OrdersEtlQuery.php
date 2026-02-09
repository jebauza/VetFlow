<?php

namespace App\Infrastructure\Integrations\Shopify\Queries;

use App\Infrastructure\Integrations\Shopify\Queries\ShopifyQuery;

class OrdersEtlQuery extends ShopifyQuery
{
    public function __construct()
    {
        $this->path = '/orders.json';
        $this->query_parameters = [
            'limit' => 50, // The maximum number of results to show.(default: 50)(maximum: 250)
            'status' => 'any', // Filter orders by their status.(default: open) [open, closed,cancelled,any]
        ];
    }
}

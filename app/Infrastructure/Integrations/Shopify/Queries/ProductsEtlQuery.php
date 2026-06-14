<?php

namespace App\Infrastructure\Integrations\Shopify\Queries;

use App\Infrastructure\Integrations\Shopify\Queries\ShopifyQuery;

class ProductsEtlQuery extends ShopifyQuery
{
    public function __construct()
    {
        $this->path = '/products.json';
        $this->query_parameters = [
            'limit' => 50, // The maximum number of results to show.(default: 50)(maximum: 250)
        ];
    }
}

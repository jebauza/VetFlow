<?php

namespace App\Infrastructure\Integrations\Shopify\Queries;

use App\Infrastructure\Integrations\Shopify\Queries\ShopifyQuery;

class ProductsByCollectionIdEtlQuery extends ShopifyQuery
{
    public function __construct()
    {
        $this->path = '/collections/:collection_id/products.json';
        $this->query_parameters = [
            'limit' => 250, // The maximum number of results to show.(default: 50)(maximum: 250)
        ];
    }

    public function set_collection_id(int $collection_id)
    {
        $this->path = str_replace(':collection_id', $collection_id, $this->path);

        return $this;
    }
}

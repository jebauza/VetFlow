<?php

namespace App\Infrastructure\Integrations\Shopify\Queries;

use Carbon\CarbonImmutable;

class ShopifyQuery
{
    const PATH = 'path';
    const QUERY_PARAMETERS = 'query_parameters';

    public string $path;
    public array $query_parameters;

    public function set_limit(int $limit)
    {
        $this->query_parameters['limit'] = $limit;

        return $this;
    }

    public function set_created_at_min(CarbonImmutable $createdAtMin = null)
    {
        if ($createdAtMin == null) {
            unset($this->query_parameters['created_at_min']);
            return $this;
        }

        $this->query_parameters['created_at_min'] = $createdAtMin->format('Y-m-dTH:i:s') . '+00:00';
        unset($this->query_parameters['updated_at_min']);
        unset($this->query_parameters['updated_at_max']);

        return $this;
    }

    public function set_created_at_max(CarbonImmutable $createdAtMax = null)
    {
        if ($createdAtMax == null) {
            unset($this->query_parameters['created_at_max']);
            return $this;
        }

        $this->query_parameters['created_at_max'] = $createdAtMax->format('Y-m-dTH:i:s') . '+00:00';
        unset($this->query_parameters['updated_at_min']);
        unset($this->query_parameters['updated_at_max']);

        return $this;
    }

    public function set_updated_at_min(CarbonImmutable $updatedAtMin = null)
    {
        if ($updatedAtMin == null) {
            unset($this->query_parameters['updated_at_min']);
            return $this;
        }

        $this->query_parameters['updated_at_min'] = $updatedAtMin->format('Y-m-dTH:i:s') . '+00:00';
        unset($this->query_parameters['created_at_min']);
        unset($this->query_parameters['created_at_max']);

        return $this;
    }

    public function set_updated_at_max(CarbonImmutable $updatedAtMax = null)
    {
        if ($updatedAtMax == null) {
            unset($this->query_parameters['updated_at_max']);
            return $this;
        }

        $this->query_parameters['updated_at_max'] = $updatedAtMax->format('Y-m-dTH:i:s') . '+00:00';
        unset($this->query_parameters['created_at_min']);
        unset($this->query_parameters['created_at_max']);

        return $this;
    }

    public function set_page_info(string $pageInfo = null)
    {
        if ($pageInfo == null) {
            unset($this->query_parameters['page_info']);
            return $this;
        }

        $this->query_parameters = [
            'limit' => 250,
            'page_info' => $pageInfo,
        ];

        return $this;
    }
}

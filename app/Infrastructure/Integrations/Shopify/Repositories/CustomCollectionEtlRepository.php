<?php

namespace App\Infrastructure\Integrations\Shopify\Repositories;

use IteratorAggregate;
use Carbon\CarbonImmutable;
use Illuminate\Support\LazyCollection;
use App\Infrastructure\Integrations\Shopify\Connectors\ShopifyConnector;
use App\Infrastructure\Integrations\Shopify\Fields\CustomCollectionEtlFields;
use App\Infrastructure\Integrations\Shopify\Queries\CustomCollectionsEtlQuery;

class CustomCollectionEtlRepository
{
    protected ShopifyConnector $connector;
    protected CustomCollectionsEtlQuery $query;
    protected int $limit = 250;

    public function __construct()
    {
        $this->connector = resolve(ShopifyConnector::class);
        $this->query = resolve(CustomCollectionsEtlQuery::class);
    }

    public function getAll(): IteratorAggregate
    {
        return LazyCollection::make(function () {
            $shopifyServers = config('services.shopify_servers');

            foreach ($shopifyServers as $shopifyServer) {
                $nextPageInfo = null;

                do {
                    $response = $this->connector->set_domain($shopifyServer['domain'])
                        ->fetch($this->query
                            ->set_page_info($nextPageInfo)
                            ->set_limit($this->limit), true);

                    if (!empty($response[ShopifyConnector::BODY]->{CustomCollectionEtlFields::CUSTOM_COLLECTIONS})) {
                        foreach ($response[ShopifyConnector::BODY]->{CustomCollectionEtlFields::CUSTOM_COLLECTIONS} as $customCollection) {
                            $transformer = $this->transformer($shopifyServer['domain'], $customCollection);
                            yield $transformer;
                        }

                        $nextPageInfo = null;
                        if (!empty($response[ShopifyConnector::HEADERS][ShopifyConnector::LINK])) {
                            $nextPageInfo = $this->connector->getNextPageInfoByLinks($response[ShopifyConnector::HEADERS][ShopifyConnector::LINK]);
                        }
                    }
                } while (!empty($nextPageInfo) || $response === []);
            }
        });
    }

    private function transformer(string $domain, object $customCollection): array
    {
        return [
            TargetEntity::DOMAIN => $domain,
            TargetEntity::ID => $customCollection->{CustomCollectionEtlFields::ID},

            TargetEntity::HANDLE => $customCollection->{CustomCollectionEtlFields::HANDLE},
            TargetEntity::TITLE => $customCollection->{CustomCollectionEtlFields::TITLE},
            TargetEntity::UPDATED => CarbonImmutable::parse($customCollection->{CustomCollectionEtlFields::UPDATED_AT})->setTimezone('UTC')->toDateTimeString(),
            TargetEntity::PUBLISHED_AT => CarbonImmutable::parse($customCollection->{CustomCollectionEtlFields::PUBLISHED_AT})->setTimezone('UTC')->toDateTimeString(),
            TargetEntity::SORT_ORDER => $customCollection->{CustomCollectionEtlFields::SORT_ORDER},
            TargetEntity::PUBLISHED_SCOPE => $customCollection->{CustomCollectionEtlFields::PUBLISHED_SCOPE},
        ];
    }
}

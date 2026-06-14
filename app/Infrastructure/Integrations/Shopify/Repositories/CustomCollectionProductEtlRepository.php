<?php

namespace App\ZapatoFeroz\Syschronize\Shopify\Etl\Repositories;

use IteratorAggregate;
use Illuminate\Support\LazyCollection;
use Zeus\Synchronize\Base\Contracts\Container\OptionsRequestContract;
use App\ZapatoFeroz\Common\Entities\Etl\CustomCollectionProductEtl as TargetEntity;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Queries\ProductsByCollectionIdEtlQuery;
use CustomCollectionProductEtlFields as Fields;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Fields\CustomCollectionProductEtlFields;

class CustomCollectionProductEtlRepository
{
    protected ShopifyConnector $connector;
    protected ProductsByCollectionIdEtlQuery $query;
    protected OptionsRequestContract $options;
    protected int $limit = 250;

    public function __construct()
    {
        $this->connector = resolve(ShopifyConnector::class);
        $this->query = resolve(ProductsByCollectionIdEtlQuery::class);
        $this->options = resolve(OptionsRequestContract::class);
    }

    public function getAll(): IteratorAggregate
    {
        return LazyCollection::make(function () {
            $shopifyServers = config('services.shopify_servers');

            foreach ($shopifyServers as $shopifyServer) {
                $customCollectionEtlIds = CustomCollectionEtl::query()
                    ->where(CustomCollectionEtl::DOMAIN, $shopifyServer['domain'])
                    ->pluck(CustomCollectionEtl::ID);

                foreach ($customCollectionEtlIds as $collectionId) {
                    $nextPageInfo = null;

                    do {
                        $response = $this->connector->set_domain($shopifyServer['domain'])
                            ->fetch($this->query
                                ->set_page_info($nextPageInfo)
                                ->set_limit($this->limit)
                                ->set_collection_id($collectionId), true);

                        if (!empty($response[ShopifyConnector::BODY]->{Fields::PRODUCTS})) {
                            foreach ($response[ShopifyConnector::BODY]->{Fields::PRODUCTS} as $product) {
                                $transformer = $this->transformer($shopifyServer['domain'], $collectionId, $product);
                                yield $transformer;
                            }

                            $nextPageInfo = null;
                            if (!empty($response[ShopifyConnector::HEADERS][ShopifyConnector::LINK])) {
                                $nextPageInfo = $this->connector->getNextPageInfoByLinks($response[ShopifyConnector::HEADERS][ShopifyConnector::LINK]);
                            }
                        }
                    } while (!empty($nextPageInfo) || $response === []);
                }
            }
        });
    }

    private function transformer(string $domain, string $customCollectionId, object $product): array
    {
        return [
            TargetEntity::DOMAIN => $domain,
            TargetEntity::CUSTOM_COLLECTION_ID => $customCollectionId,
            TargetEntity::PRODUCT_ID => $product->{Fields::ID},
        ];
    }
}

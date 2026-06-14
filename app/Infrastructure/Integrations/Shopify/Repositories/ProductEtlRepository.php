<?php

namespace App\ZapatoFeroz\Syschronize\Shopify\Etl\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Support\LazyCollection;
use Zeus\Synchronize\Base\Contracts\Repo\Data\MultiRepo;
use App\Zeus\Synchronize\Shopify\Connectors\ShopifyConnector;
use Zeus\Synchronize\Base\ValueObjects\Data\BaseMultiValueObject;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Fields\OrderEtlFields;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Fields\ProductEtlFields;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Queries\ProductsEtlQuery;
use Zeus\Synchronize\Base\Contracts\Container\OptionsRequestContract;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Transformers\ProductEtlTransform;

class ProductEtlRepository implements MultiRepo
{
    protected ShopifyConnector $connector;
    protected ProductsEtlQuery $query;
    protected OptionsRequestContract $options;
    protected ProductEtlTransform $transform;
    protected int $limit = 250;

    public function __construct()
    {
        $this->connector = resolve(ShopifyConnector::class);
        $this->query = resolve(ProductsEtlQuery::class);
        $this->options = resolve(OptionsRequestContract::class);
        $this->transform = resolve(ProductEtlTransform::class);
    }

    public function getAll(): BaseMultiValueObject
    {
        $lazyCollection = LazyCollection::make(function () {
            $shopifyServers = config('services.shopify_servers');

            foreach ($shopifyServers as $shopifyServer) {
                $nextPageInfo = null;

                do {
                    $response = $this->connector->set_domain($shopifyServer['domain'])
                                    ->fetch($this->query
                                                ->set_page_info($nextPageInfo)
                                                ->set_created_at_min(!$nextPageInfo && $this->options->get('startDate') ? CarbonImmutable::parse($this->options->get('startDate'))->startOfDay() : null)
                                                ->set_created_at_max(!$nextPageInfo && $this->options->get('endDate') ? CarbonImmutable::parse($this->options->get('endDate'))->endOfDay() : null)
                                                ->set_updated_at_min(!$nextPageInfo && $this->options->get('updatedStartDate') ? CarbonImmutable::parse($this->options->get('updatedStartDate'))->startOfDay() : null)
                                                ->set_updated_at_max(!$nextPageInfo && $this->options->get('updatedEndDate') ? CarbonImmutable::parse($this->options->get('updatedEndDate'))->endOfDay() : null)
                                                ->set_limit($this->limit), true);

                    if (!empty($response[ShopifyConnector::BODY]->{ProductEtlFields::PRODUCTS})) {
                        foreach ($response[ShopifyConnector::BODY]->{ProductEtlFields::PRODUCTS} as $product) {
                            $product->{OrderEtlFields::DOMAIN} = $shopifyServer['domain'];
                            yield $product;
                        }

                        $nextPageInfo = null;
                        if (!empty($response[ShopifyConnector::HEADERS][ShopifyConnector::LINK])) {
                            $nextPageInfo = $this->connector->getNextPageInfoByLinks($response[ShopifyConnector::HEADERS][ShopifyConnector::LINK]);
                        }
                    }

                } while (!empty($nextPageInfo) || $response === []);
            }
        });

        return $this->transform->transform($lazyCollection);
    }
}

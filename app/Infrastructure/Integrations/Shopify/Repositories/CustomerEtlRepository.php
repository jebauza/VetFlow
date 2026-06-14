<?php

namespace App\ZapatoFeroz\Syschronize\Shopify\Etl\Repositories;

use IteratorAggregate;
use Illuminate\Support\LazyCollection;
use Zeus\Synchronize\Base\Contracts\Repo\Data\Repo;
use App\Zeus\Synchronize\Shopify\Connectors\ShopifyConnector;
use App\ZapatoFeroz\Common\Entities\Etl\CustomerEtl as TargetEntity;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Fields\CustomerEtlFields;
use Zeus\Synchronize\Base\Contracts\Container\OptionsRequestContract;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Queries\CustomersEtlQuery;
use Carbon\CarbonImmutable;

class CustomerEtlRepository implements Repo
{
    protected ShopifyConnector $connector;
    protected CustomersEtlQuery $query;
    protected OptionsRequestContract $options;
    protected int $limit = 250;

    public function __construct()
    {
        $this->connector = resolve(ShopifyConnector::class);
        $this->query = resolve(CustomersEtlQuery::class);
        $this->options = resolve(OptionsRequestContract::class);
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
                                                ->set_created_at_min(!$nextPageInfo && $this->options->get('startDate') ? CarbonImmutable::parse($this->options->get('startDate'))->startOfDay() : null)
                                                ->set_created_at_max(!$nextPageInfo && $this->options->get('endDate') ? CarbonImmutable::parse($this->options->get('endDate'))->endOfDay() : null)
                                                ->set_updated_at_min(!$nextPageInfo && $this->options->get('updatedStartDate') ? CarbonImmutable::parse($this->options->get('updatedStartDate'))->startOfDay() : null)
                                                ->set_updated_at_max(!$nextPageInfo && $this->options->get('updatedEndDate') ? CarbonImmutable::parse($this->options->get('updatedEndDate'))->endOfDay() : null)
                                                ->set_limit($this->limit), true);

                    if (!empty($response[ShopifyConnector::BODY]->{CustomerEtlFields::CUSTOMERS})) {
                        foreach ($response[ShopifyConnector::BODY]->{CustomerEtlFields::CUSTOMERS} as $customer) {
                            $transformer = $this->transformer($shopifyServer['domain'], $customer);
                            // QueryHelper::insertModel(TargetEntity::class, $transformer);

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

    private function transformer(string $domain, object $customer): array
    {
        return [
            TargetEntity::DOMAIN => $domain,
            TargetEntity::ID => $customer->{CustomerEtlFields::ID},

            TargetEntity::EMAIL => $customer->{CustomerEtlFields::EMAIL},
            TargetEntity::CREATED => CarbonImmutable::parse($customer->{CustomerEtlFields::CREATED_AT})->setTimezone('UTC')->toDateTimeString(),
            TargetEntity::UPDATED => CarbonImmutable::parse($customer->{CustomerEtlFields::UPDATED_AT})->setTimezone('UTC')->toDateTimeString(),
            TargetEntity::FIRST_NAME => !empty($customer->{CustomerEtlFields::FIRST_NAME}) ? $customer->{CustomerEtlFields::FIRST_NAME} : null,
            TargetEntity::LAST_NAME => !empty($customer->{CustomerEtlFields::LAST_NAME}) ? $customer->{CustomerEtlFields::LAST_NAME} : null,
            TargetEntity::ORDERS_COUNT => $customer->{CustomerEtlFields::ORDERS_COUNT},
            TargetEntity::STATE => $customer->{CustomerEtlFields::STATE},
            TargetEntity::TOTAL_SPENT => $customer->{CustomerEtlFields::TOTAL_SPENT},
            TargetEntity::LAST_ORDER_ID => !empty($customer->{CustomerEtlFields::LAST_ORDER_ID}) ? $customer->{CustomerEtlFields::LAST_ORDER_ID} : null,
            TargetEntity::LAST_ORDER_NAME => !empty($customer->{CustomerEtlFields::LAST_ORDER_NAME}) ? $customer->{CustomerEtlFields::LAST_ORDER_NAME} : null,
            TargetEntity::TAGS => !empty($customer->{CustomerEtlFields::TAGS}) ? $customer->{CustomerEtlFields::TAGS} : null,
            TargetEntity::CURRENCY => $customer->{CustomerEtlFields::CURRENCY},
            TargetEntity::PHONE => !empty($customer->{CustomerEtlFields::PHONE}) ? $customer->{CustomerEtlFields::PHONE} : null,
            TargetEntity::ADDRESSES => !empty($customer->{CustomerEtlFields::ADDRESSES}) ? json_encode($customer->{CustomerEtlFields::ADDRESSES}) : null,
        ];
    }
}

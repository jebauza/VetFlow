<?php

namespace App\Infrastructure\Integrations\MyLighthouse\Repositories;

use IteratorAggregate;
use Illuminate\Support\LazyCollection;
use App\Infrastructure\Integrations\MyLighthouse\Queries\MyLighthouseRateQuery;
use App\Infrastructure\Integrations\MyLighthouse\Connectors\MyLighthouseConnector;

class MyLighthouseRateRepo
{
    protected MyLighthouseConnector $connector;
    protected MyLighthouseRateQuery $query;

    public function __construct()
    {
        $this->connector = resolve(MyLighthouseConnector::class);
        $this->query = resolve(MyLighthouseRateQuery::class);
    }

    public function getAll(): IteratorAggregate
    {
        return LazyCollection::make(function () {
            $subscriptions = MyLighthouseHotelEtl::query()
                ->select(MyLighthouseHotelEtl::PARENT_ID, MyLighthouseHotelEtl::SUBSCRIPTION_ID)
                ->whereNotNull(MyLighthouseHotelEtl::SUBSCRIPTION_ID)
                ->get()
                ->toArray();

            foreach ($subscriptions as $subscription) {
                $this->query->setSubscriptionId($subscription[MyLighthouseHotelEtl::SUBSCRIPTION_ID]);

                $result = $this->connector->fetch($this->query)->rates;

                foreach ($result as $item) {
                    yield [
                        MyLighthouseRateEtl::HOTEL_ID => $item->hotelId,
                        MyLighthouseRateEtl::PARENT_ID => $subscription[MyLighthouseHotelEtl::PARENT_ID],
                        MyLighthouseRateEtl::ARRIVAL_DATE => $item->arrivalDate,
                        MyLighthouseRateEtl::VALUE => $item->value,
                    ];
                }
            }
        });
    }
}

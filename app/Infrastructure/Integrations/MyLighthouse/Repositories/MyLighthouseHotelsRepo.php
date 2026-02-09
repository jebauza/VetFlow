<?php

namespace App\ParqueSantiago\Syschronize\MyLighthouse\Etl\Repositories;

use IteratorAggregate;
use Illuminate\Support\LazyCollection;
use App\Infrastructure\Integrations\MyLighthouse\Queries\MyLighthouseHotelQuery;
use App\Infrastructure\Integrations\MyLighthouse\Connectors\MyLighthouseConnector;

class MyLighthouseHotelsRepo
{
    protected MyLighthouseConnector $connector;
    protected MyLighthouseHotelQuery $query;

    public function __construct()
    {
        $this->connector = resolve(MyLighthouseConnector::class);
        $this->query = resolve(MyLighthouseHotelQuery::class);
    }

    public function getAll(): IteratorAggregate
    {
        return LazyCollection::make(function () {
            $result = $this->connector->fetch($this->query);

            foreach ($result->hotels as $hotel) {
                yield [
                    MyLighthouseHotelEtl::ID => $hotel->id,
                    MyLighthouseHotelEtl::PARENT_ID => $hotel->id,
                    MyLighthouseHotelEtl::SUBSCRIPTION_ID => $hotel->subscription_id,
                    MyLighthouseHotelEtl::NAME => $hotel->name,
                ];

                foreach ($hotel->competitors as $competitor) {
                    yield [
                        MyLighthouseHotelEtl::ID => $competitor->id,
                        MyLighthouseHotelEtl::PARENT_ID => $hotel->id,
                        MyLighthouseHotelEtl::SUBSCRIPTION_ID => null,
                        MyLighthouseHotelEtl::NAME => $competitor->name,
                    ];
                }
            }
        });
    }
}

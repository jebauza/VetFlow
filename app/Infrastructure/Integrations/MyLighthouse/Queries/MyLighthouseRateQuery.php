<?php

namespace App\Infrastructure\Integrations\MyLighthouse\Queries;

use App\Infrastructure\Integrations\MyLighthouse\Queries\MyLighthouseQuery;

class MyLighthouseRateQuery extends MyLighthouseQuery
{
    const SUBSCRIPTION_ID = 'subscriptionId';
    const SHOP_LENGTH = 'shopLength';
    const PERSONS = 'persons';
    const OTA = 'ota';
    const LOS = 'los';
    const COMPSET_IDS = 'compsetIds';

    public function __construct()
    {
        $this->{self::PATH} = 'rates';
        $this->{self::PARAMETERS} = [
            self::SUBSCRIPTION_ID => null,
            self::SHOP_LENGTH => 365,
            self::PERSONS => 2,
            self::OTA => 'bookingdotcom',
            self::LOS => 3,
            self::COMPSET_IDS => '-1,1',
        ];
    }

    public function setSubscriptionId($subscriptionId): void
    {
        $this->{self::PARAMETERS}[self::SUBSCRIPTION_ID] = $subscriptionId;
    }
}

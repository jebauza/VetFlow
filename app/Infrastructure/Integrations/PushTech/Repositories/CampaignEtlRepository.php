<?php

namespace App\Infrastructure\Integrations\PushTech\Repositories;

use IteratorAggregate;
use Illuminate\Support\LazyCollection;
use App\Infrastructure\Integrations\PushTech\Fields\CampaignFields;
use App\Infrastructure\Integrations\PushTech\Queries\CampaignsEtlQuery;
use App\Infrastructure\Integrations\PushTech\Connectors\PushTechConnector;

class CampaignEtlRepository
{
    protected PushTechConnector $connector;
    protected CampaignsEtlQuery $campaignsEtlQuery;
    protected int $limit = 1000;

    public function __construct()
    {
        $this->connector = resolve(PushTechConnector::class);

        $this->campaignsEtlQuery = resolve(CampaignsEtlQuery::class);
    }

    public function getAll(): IteratorAggregate
    {
        return LazyCollection::make(function () {
            $data = $this->connector->fetch($this->campaignsEtlQuery->set_limit($this->limit)->set_order('desc'));

            if (!empty($data->{CampaignFields::CAMPAIGNS})) {
                foreach ($data->{CampaignFields::CAMPAIGNS} as $campaign) {
                    $transformer = $this->transformer($campaign);

                    yield $transformer;
                }
            }
        });
    }

    private function transformer(object $campaign): array
    {
        // return [
        //     TargetEntity::ID => $campaign->{CampaignFields::ID},

        //     TargetEntity::NAME => $campaign->{CampaignFields::NAME},
        //     TargetEntity::CREATED => $campaign->{CampaignFields::CREATED_AT},
        //     TargetEntity::UPDATED => $campaign->{CampaignFields::UPDATED_AT},
        //     TargetEntity::DESCRIPTION => $campaign->{CampaignFields::DESCRIPTION},
        //     TargetEntity::STATUS => $campaign->{CampaignFields::STATUS},
        //     TargetEntity::SCHEDULE_TYPE => $campaign->{CampaignFields::SCHEDULE_TYPE},
        //     TargetEntity::TIME_ZONE => $campaign->{CampaignFields::TIME_ZONE},
        //     TargetEntity::START_AT => !empty($campaign->{CampaignFields::START_AT}) ? $campaign->{CampaignFields::START_AT} : null,
        //     TargetEntity::NEXT_LAUNCH => !empty($campaign->{CampaignFields::NEXT_LAUNCH}) ? $campaign->{CampaignFields::NEXT_LAUNCH} : null,

        //     TargetEntity::FILTERS => json_encode($campaign->{CampaignFields::FILTERS}),
        //     TargetEntity::REPORTS => json_encode($campaign->{CampaignFields::REPORTS}),

        //     TargetEntity::UPDATED_AT => now(),
        //     TargetEntity::ACTIVE_ETL => true,
        // ];

        return (array) $campaign;
    }
}

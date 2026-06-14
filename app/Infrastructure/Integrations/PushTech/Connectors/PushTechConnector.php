<?php

namespace App\Infrastructure\Integrations\PushTech\Connectors;

use App\Common\Helpers\Utils;
use Illuminate\Support\Facades\Http;
use App\Infrastructure\Integrations\PushTech\Queries\PushTechQuery;

class PushTechConnector //implements ZeusConnectorInterface
{
    private string $api_url, $api_token, $account_id;

    public function __construct()
    {
        $this->api_url = 'https://www.pushtech.com/api';
        $this->api_token = config('services.push_tech.api_token');
        $this->account_id = config('services.push_tech.account_id');
    }

    public function fetch(PushTechQuery $query): object|array
    {
        try {
            $response = $this->runQuery($query->{PushTechQuery::PATH}, $query->{PushTechQuery::QUERY_PARAMETERS});
            // $data = json_decode($response->body(), true);

            if (!$response->successful()) {
                $response = $this->runQuery($query->{PushTechQuery::PATH}, $query->{PushTechQuery::QUERY_PARAMETERS});
            }

            $response->throwIf($response->successful());

            return $response->object();
        } catch (\Exception $e) {
            $info = !empty($response->transferStats) ? json_encode($response->transferStats->getHandlerStats()) : '';

            Utils::dumpLog("ReviewPro Api Error. Result: " . $e->getMessage() . " ($info)", 'error');
            Utils::dumpLog("ReviewPro Api Error. Result: " . $e->getMessage() . " ($info)");
        }

        return [];
    }

    public function runQuery(string $path, array $addParams)
    {
        $headers = [
            'Authorization' => "Token token=$this->api_token",
        ];
        $url = str_replace(':account_id', $this->account_id, $this->api_url . $path);
        $params = array_merge($addParams, []);
        sleep(1);

        // Maximum number of seconds
        return Http::connectTimeout(120)->timeout(120)->withHeaders($headers)->get($url, $params);
    }
}

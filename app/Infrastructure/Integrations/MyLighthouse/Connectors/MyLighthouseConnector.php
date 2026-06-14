<?php

namespace App\Infrastructure\Integrations\MyLighthouse\Connectors;

use App\Common\Helpers\Utils;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Promise\PromiseInterface;
use App\Infrastructure\Integrations\MyLighthouse\Queries\MyLighthouseQuery;

class MyLighthouseConnector
{
    protected string $url;
    protected string $header;
    protected string $token;

    public function __construct()
    {
        $this->url = config('services.my_lighthouse.url');
        $this->header = config('services.my_lighthouse.header');
        $this->token = config('services.my_lighthouse.token');
    }

    public function fetch(MyLighthouseQuery $query): object
    {
        try {
            $response = $this->runQuery(
                $query->{MyLighthouseQuery::PATH},
                $query->{MyLighthouseQuery::PARAMETERS}
            );

            //            $response->thowIf($response->successful());

            return $response->object();
        } catch (\Exception $e) {
            Utils::dumpLog("MyLightHouse Api Error. Result: " . $e->getMessage(), 'error');
            throw $e;
        }
    }

    public function runQuery(string $path, array $addParams): PromiseInterface|Response
    {
        $headers = [
            $this->header => $this->token,
        ];

        $params = array_merge($addParams, []);
        $url = Utils::urlMerge($this->url, $path);

        return Http::connectTimeout(60)
            ->timeout(60)
            ->withHeaders($headers)
            ->get($url, $params);
    }
}

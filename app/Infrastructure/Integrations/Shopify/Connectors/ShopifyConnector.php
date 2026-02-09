<?php

namespace App\Infrastructure\Integrations\Shopify\Connectors;

use Illuminate\Http\Request;
use App\Common\Helpers\Utils;
use Illuminate\Support\Facades\Http;
use App\Infrastructure\Integrations\Shopify\Queries\ShopifyQuery;

class ShopifyConnector
{
    const HEADERS = 'headers';
    const LINK = 'link';
    const BODY = 'body';

    public string $domain;
    private string $apiUrl, $accessToken;
    private array $servers;

    public function __construct()
    {
        $this->servers = config('services.shopify_servers');
        $firstServer = reset($this->servers);
        $this->domain = $firstServer['domain'];
        $apiVersion = $firstServer['api_version'];
        $this->accessToken = $firstServer['access_token'];
        $this->apiUrl = "https://$this->domain.myshopify.com/admin/api/$apiVersion";
    }

    public function set_domain(string $domain): self
    {
        foreach ($this->servers as $server) {
            if ($server['domain'] === $domain) {
                $this->domain = $server['domain'];
                $apiVersion = $server['api_version'];
                $this->accessToken = $server['access_token'];
                $this->apiUrl = "https://$this->domain.myshopify.com/admin/api/$apiVersion";
                break;
            }
        }

        return $this;
    }

    public function fetch(ShopifyQuery $query, $withHeaders = false): object|array
    {
        try {
            $response = $this->runQuery($query->{ShopifyQuery::PATH}, $query->{ShopifyQuery::QUERY_PARAMETERS});
            // $data = json_decode($response->body(), true);

            if (!$response->successful()) {
                $response = $this->runQuery($query->{ShopifyQuery::PATH}, $query->{ShopifyQuery::QUERY_PARAMETERS});
            }

            $response->throwIf($response->successful());

            if ($withHeaders) {
                return [
                    self::HEADERS => $response->headers(),
                    self::BODY => $response->object()
                ];
            }

            return $response->object();
        } catch (\Exception $e) {
            $info = !empty($response->transferStats) ? json_encode($response->transferStats->getHandlerStats()) : '';

            Utils::dumpLog("Shopify Api Error. Result: " . $e->getMessage() . " ($info)", 'error');
        }

        return [];
    }

    public function runQuery(string $path, array $addParams)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $this->accessToken,
        ];

        $url = $this->apiUrl . $path;
        $params = array_merge($addParams, []);
        sleep(1);

        // Maximum number of seconds
        return Http::connectTimeout(120)->timeout(120)->withHeaders($headers)->get($url, $params);
    }

    public function getNextPageInfoByLinks(array $links)
    {
        $nextPageInfo = null;

        foreach ($links as $link) {
            if (strripos($link, 'rel="next"')) {
                $startUrlPos = strripos($link, '<https://');
                $strripos = strripos($link, '>;');
                $endUrlPos = $strripos ?? strripos($link, '>');
                if ($startUrlPos !== false && $endUrlPos !== false) {
                    $url = substr($link, $startUrlPos + 1, $endUrlPos - ($startUrlPos + 1));
                    $request = Request::create($url);
                    $nextPageInfo = $request->page_info;
                    break;
                }
            }
        }

        return $nextPageInfo;
    }
}

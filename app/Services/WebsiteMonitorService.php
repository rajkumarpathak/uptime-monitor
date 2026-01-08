<?php

namespace App\Services;

use App\Models\Website;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class WebsiteMonitorService
{
    private Client $httpClient;
    private int $timeout;
    private int $concurrency;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'allow_redirects' => true,
            'headers' => [
                'User-Agent' => 'UptimeMonitor/1.0',
            ],
            'verify' => config('app.env') === 'production',
        ]);
        
        $this->timeout = 10;
        $this->concurrency = 10; // Check 10 websites simultaneously
    }

    public function checkWebsite(Website $website): bool
    {
        try {
            $url = $website->getNormalizedUrl();
            
            $response = $this->httpClient->get($url, [
                'timeout' => $this->timeout,
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            
            // Consider 2xx and 3xx status codes as "up"
            if ($statusCode >= 200 && $statusCode < 400) {
                $website->markAsUp();
                return true;
            } else {
                $website->markAsDown("HTTP Status: {$statusCode}");
                return false;
            }
        } catch (\Exception $e) {
            $website->markAsDown($e->getMessage());
            return false;
        }
    }

    public function checkMultipleWebsites(Collection $websites): array
    {
        $promises = [];
        $results = [];

        foreach ($websites as $website) {
            $promises[$website->id] = $this->httpClient->getAsync(
                $website->getNormalizedUrl(),
                [
                    'timeout' => $this->timeout,
                    'http_errors' => false,
                ]
            )->then(
                function (ResponseInterface $response) use ($website) {
                    $statusCode = $response->getStatusCode();
                    return [
                        'website' => $website,
                        'success' => $statusCode >= 200 && $statusCode < 400,
                        'status_code' => $statusCode,
                        'error' => null,
                    ];
                },
                function (RequestException $e) use ($website) {
                    return [
                        'website' => $website,
                        'success' => false,
                        'status_code' => 0,
                        'error' => $e->getMessage(),
                    ];
                }
            );
        }

        // Wait for all promises to complete
        $responses = Promise\Utils::settle($promises)->wait();

        foreach ($responses as $websiteId => $response) {
            if ($response['state'] === 'fulfilled') {
                $result = $response['value'];
                if ($result['success']) {
                    $result['website']->markAsUp();
                } else {
                    $result['website']->markAsDown(
                        "HTTP Status: {$result['status_code']}" . 
                        ($result['error'] ? " - {$result['error']}" : '')
                    );
                }
                $results[$websiteId] = $result['success'];
            } else {
                $website = $websites->firstWhere('id', $websiteId);
                $website->markAsDown('Connection failed');
                $results[$websiteId] = false;
            }
        }

        return $results;
    }
}
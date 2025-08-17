<?php

declare(strict_types=1);

namespace App\Services\Octo;

use App\Models\Octo\Connection;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class ApiClientService
{
    public function __construct(
        private Connection $connection
    ) {}

    public function get(string $url, array $params = []): Response
    {
        return $this->makeRequest('GET', $url, $params);
    }

    public function post(string $url, array $data = []): Response
    {
        return $this->makeRequest('POST', $url, $data);
    }

    private function makeRequest(string $method, string $url, array $data = []): Response
    {
        try {
            $client = Http::withToken(
                $this->connection->access_token
            )->timeout(30);

            $url = "https://api.github.com{$url}";

            $response = match (Str::upper($method)) {
                'GET' => $client->get($url, $data),
                'POST' => $client->post($url, $data),
                'PUT' => $client->put($url, $data),
                default => throw new InvalidArgumentException("Unsupported method: {$method}")
            };

            if ($response->failed()) {
                throw new Exception(
                    'GitHub API request failed: '.$response->status().' - '.$response->body()
                );
            }

            return $response;
        } catch (RequestException $e) {
            throw new Exception('GitHub API request failed: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
}

<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class FreeAgentApiClient
{
    private const API_BASE_URL = 'https://api.freeagent.com/v2';

    private const TOKEN_ENDPOINT = self::API_BASE_URL.'/token_endpoint';

    private const USER_AGENT = 'HMRC MTD Bridge Prototype';

    /**
     * @return array<string, array{name:string,direction:string}>
     */
    public function profitAndLossCategories(Business $business): array
    {
        $categories = [];

        for ($page = 1; $page <= 20; $page++) {
            $response = $this->get(
                $business,
                'categories',
                ['page' => $page, 'per_page' => 100, 'sub_accounts' => 'true'],
            );

            $payload = $response->json();

            foreach ([
                'income_categories' => 'income',
                'admin_expenses_categories' => 'expense',
                'cost_of_sales_categories' => 'expense',
            ] as $key => $direction) {
                foreach (($payload[$key] ?? []) as $category) {
                    if (! isset($category['url'], $category['description'])) {
                        continue;
                    }

                    $categories[$category['url']] = [
                        'name' => $category['description'],
                        'direction' => $direction,
                    ];
                }
            }

            $itemCount = collect([
                $payload['income_categories'] ?? [],
                $payload['admin_expenses_categories'] ?? [],
                $payload['cost_of_sales_categories'] ?? [],
                $payload['general_categories'] ?? [],
            ])->flatten(1)->count();

            if ($itemCount < 100) {
                break;
            }
        }

        return $categories;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function transactionsForPeriod(Business $business, string $fromDate, string $toDate): array
    {
        $transactions = [];

        for ($page = 1; $page <= 200; $page++) {
            $response = $this->get(
                $business,
                'accounting/transactions',
                [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'page' => $page,
                    'per_page' => 100,
                ],
            );

            $items = $response->json('transactions', []);

            if ($items === []) {
                break;
            }

            $transactions = [...$transactions, ...$items];

            if (count($items) < 100) {
                break;
            }
        }

        return $transactions;
    }

    private function get(Business $business, string $path, array $query = []): Response
    {
        $response = $this->request($business)->get($path, $query);

        if ($response->unauthorized() && filled($business->freeagent_refresh_token)) {
            $business = $this->refreshAccessToken($business->fresh());
            $response = $this->request($business)->get($path, $query);
        }

        return $response->throw();
    }

    private function request(Business $business): PendingRequest
    {
        return Http::baseUrl(self::API_BASE_URL)
            ->acceptJson()
            ->timeout(30)
            ->withToken($this->accessToken($business))
            ->withHeaders([
                'User-Agent' => self::USER_AGENT,
            ]);
    }

    private function accessToken(Business $business): string
    {
        if (! $business->hasFreeAgentCredentials()) {
            throw ValidationException::withMessages([
                'business_id' => sprintf('FreeAgent credentials are incomplete for %s.', $business->name),
            ]);
        }

        if ($business->freeAgentAccessTokenHasExpired()) {
            $business = $this->refreshAccessToken($business);
        }

        return (string) $business->freeagent_access_token;
    }

    private function refreshAccessToken(Business $business): Business
    {
        $response = Http::acceptJson()
            ->asForm()
            ->timeout(30)
            ->withBasicAuth((string) $business->freeagent_client_id, (string) $business->freeagent_client_secret)
            ->withHeaders([
                'User-Agent' => self::USER_AGENT,
            ])
            ->post(self::TOKEN_ENDPOINT, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $business->freeagent_refresh_token,
            ])
            ->throw();

        $payload = $response->json();

        $business->forceFill([
            'freeagent_access_token' => $payload['access_token'] ?? null,
            'freeagent_refresh_token' => $payload['refresh_token'] ?? $business->freeagent_refresh_token,
            'freeagent_access_token_expires_at' => now()->addSeconds(max(((int) ($payload['expires_in'] ?? 3600)) - 60, 60)),
        ])->save();

        return $business->fresh();
    }
}

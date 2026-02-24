<?php

declare(strict_types=1);

namespace HeartlandRetail;

use HeartlandRetail\Http\HttpClient;
use HeartlandRetail\Resource\ConfigResource;
use HeartlandRetail\Resource\CustomFieldsResource;
use HeartlandRetail\Resource\CustomersResource;
use HeartlandRetail\Resource\GiftCardsResource;
use HeartlandRetail\Resource\InventoryResource;
use HeartlandRetail\Resource\ItemsResource;
use HeartlandRetail\Resource\LocationsResource;
use HeartlandRetail\Resource\PromotionsResource;
use HeartlandRetail\Resource\PurchasingResource;
use HeartlandRetail\Resource\ReportsResource;
use HeartlandRetail\Resource\SalesResource;
use HeartlandRetail\Resource\SystemResource;
use HeartlandRetail\Resource\TaxResource;
use HeartlandRetail\Resource\UsersResource;
use HeartlandRetail\Resource\WebhooksResource;

/**
 * Heartland Retail API Client — main entry point for all API operations.
 *
 * Instantiate with an access token and account-specific base URL, then access
 * resource groups via named methods.
 *
 * @see https://dev.retail.heartland.us/
 */
class Client
{
    private readonly HttpClient $http;

    // ── Resource accessors (lazy-initialised) ─────────────────────────────────

    private ?ItemsResource $itemsResource = null;
    private ?CustomersResource $customersResource = null;
    private ?InventoryResource $inventoryResource = null;
    private ?SalesResource $salesResource = null;
    private ?PurchasingResource $purchasingResource = null;
    private ?PromotionsResource $promotionsResource = null;
    private ?GiftCardsResource $giftCardsResource = null;
    private ?UsersResource $usersResource = null;
    private ?WebhooksResource $webhooksResource = null;
    private ?TaxResource $taxResource = null;
    private ?LocationsResource $locationsResource = null;
    private ?ConfigResource $configResource = null;
    private ?CustomFieldsResource $customFieldsResource = null;
    private ?ReportsResource $reportsResource = null;
    private ?SystemResource $systemResource = null;

    /**
     * @param string $baseUrl           Full base URL, e.g. "https://mystore.retail.heartland.us/api"
     * @param string $accessToken       OAuth2 bearer token
     * @param int    $maxRetries        Max auto-retries on 429/5xx (default 3)
     * @param float  $requestsPerSecond Proactive rate cap (0 = unlimited)
     */
    public function __construct(
        string $baseUrl,
        #[\SensitiveParameter] string $accessToken,
        int $maxRetries = 3,
        float $requestsPerSecond = 0.0,
    ) {
        $this->http = new HttpClient($baseUrl, $accessToken, $maxRetries, $requestsPerSecond);
    }

    /**
     * Convenience factory: build a client from a subdomain and token.
     *
     * @param string $accessToken       OAuth2 bearer token
     * @param string $subdomain         e.g. "mystore" → https://mystore.retail.heartland.us/api
     * @param int    $maxRetries        Max auto-retries on 429/5xx
     * @param float  $requestsPerSecond Proactive rate cap (0 = unlimited)
     */
    public static function withToken(
        #[\SensitiveParameter] string $accessToken,
        string $subdomain,
        int $maxRetries = 3,
        float $requestsPerSecond = 0.0,
    ): static {
        $baseUrl = "https://{$subdomain}.retail.heartland.us/api";

        return new static($baseUrl, $accessToken, $maxRetries, $requestsPerSecond);
    }

    /**
     * Update the access token (e.g. after a token refresh).
     */
    public function setAccessToken(#[\SensitiveParameter] string $token): void
    {
        $this->http->setAccessToken($token);
    }

    // ── Resource accessors ────────────────────────────────────────────────────

    public function items(): ItemsResource
    {
        return $this->itemsResource ??= new ItemsResource($this->http);
    }

    public function customers(): CustomersResource
    {
        return $this->customersResource ??= new CustomersResource($this->http);
    }

    public function inventory(): InventoryResource
    {
        return $this->inventoryResource ??= new InventoryResource($this->http);
    }

    public function sales(): SalesResource
    {
        return $this->salesResource ??= new SalesResource($this->http);
    }

    public function purchasing(): PurchasingResource
    {
        return $this->purchasingResource ??= new PurchasingResource($this->http);
    }

    public function promotions(): PromotionsResource
    {
        return $this->promotionsResource ??= new PromotionsResource($this->http);
    }

    public function giftCards(): GiftCardsResource
    {
        return $this->giftCardsResource ??= new GiftCardsResource($this->http);
    }

    public function users(): UsersResource
    {
        return $this->usersResource ??= new UsersResource($this->http);
    }

    public function webhooks(): WebhooksResource
    {
        return $this->webhooksResource ??= new WebhooksResource($this->http);
    }

    public function tax(): TaxResource
    {
        return $this->taxResource ??= new TaxResource($this->http);
    }

    public function locations(): LocationsResource
    {
        return $this->locationsResource ??= new LocationsResource($this->http);
    }

    public function config(): ConfigResource
    {
        return $this->configResource ??= new ConfigResource($this->http);
    }

    public function customFields(): CustomFieldsResource
    {
        return $this->customFieldsResource ??= new CustomFieldsResource($this->http);
    }

    public function reports(): ReportsResource
    {
        return $this->reportsResource ??= new ReportsResource($this->http);
    }

    public function system(): SystemResource
    {
        return $this->systemResource ??= new SystemResource($this->http);
    }

    // ── Raw HTTP access ───────────────────────────────────────────────────────

    /**
     * Access the underlying HTTP client for custom / undocumented endpoints.
     */
    public function getHttpClient(): HttpClient
    {
        return $this->http;
    }
}

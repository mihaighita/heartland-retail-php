<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests\Unit;

use HeartlandRetail\Client;
use HeartlandRetail\Http\HttpClient;
use HeartlandRetail\Resource\ConfigResource;
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Client::class)]
final class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client('https://test.retail.heartland.us/api', 'test_token');
    }

    #[Test]
    public function with_token_factory_creates_client(): void
    {
        $client = Client::withToken('my_token', 'mystore');

        self::assertInstanceOf(Client::class, $client);
    }

    #[Test]
    public function resource_accessors_return_correct_types(): void
    {
        self::assertInstanceOf(ItemsResource::class, $this->client->items());
        self::assertInstanceOf(CustomersResource::class, $this->client->customers());
        self::assertInstanceOf(InventoryResource::class, $this->client->inventory());
        self::assertInstanceOf(SalesResource::class, $this->client->sales());
        self::assertInstanceOf(PurchasingResource::class, $this->client->purchasing());
        self::assertInstanceOf(PromotionsResource::class, $this->client->promotions());
        self::assertInstanceOf(GiftCardsResource::class, $this->client->giftCards());
        self::assertInstanceOf(UsersResource::class, $this->client->users());
        self::assertInstanceOf(WebhooksResource::class, $this->client->webhooks());
        self::assertInstanceOf(TaxResource::class, $this->client->tax());
        self::assertInstanceOf(LocationsResource::class, $this->client->locations());
        self::assertInstanceOf(ConfigResource::class, $this->client->config());
        self::assertInstanceOf(ReportsResource::class, $this->client->reports());
        self::assertInstanceOf(SystemResource::class, $this->client->system());
    }

    #[Test]
    public function resource_accessors_return_same_instance(): void
    {
        $items1 = $this->client->items();
        $items2 = $this->client->items();

        self::assertSame($items1, $items2);
    }

    #[Test]
    public function get_http_client_returns_http_client(): void
    {
        self::assertInstanceOf(HttpClient::class, $this->client->getHttpClient());
    }
}

<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests\Unit\Resource;

use HeartlandRetail\Http\Response;
use HeartlandRetail\Resource\CustomersResource;
use HeartlandRetail\Tests\MockHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CustomersResource::class)]
final class CustomersResourceTest extends TestCase
{
    private MockHttpClient $http;
    private CustomersResource $customers;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient(
            new Response(200, [], [
                'total' => 1,
                'pages' => 1,
                'per_page' => 50,
                'results' => [['id' => 1]],
            ], ''),
        );
        $this->customers = new CustomersResource($this->http);
    }

    #[Test]
    public function create_posts_to_customers(): void
    {
        $data = ['first_name' => 'Jane', 'last_name' => 'Doe'];
        $this->customers->create($data);

        $call = $this->http->lastCall();
        self::assertSame('POST', $call['method']);
        self::assertSame('customers', $call['path']);
    }

    #[Test]
    public function get_fetches_customer_by_id(): void
    {
        $this->customers->get(10);

        $call = $this->http->lastCall();
        self::assertSame('GET', $call['method']);
        self::assertSame('customers/10', $call['path']);
    }

    #[Test]
    public function merge_posts_correct_payload(): void
    {
        $this->customers->merge(1, 2);

        $call = $this->http->lastCall();
        self::assertSame('POST', $call['method']);
        self::assertSame('customers/1/merge', $call['path']);
        self::assertSame([['merge_customer_id' => 2]], $call['args']);
    }

    #[Test]
    public function create_address_posts_to_address_endpoint(): void
    {
        $data = ['address1' => '123 Main St', 'city' => 'Springfield'];
        $this->customers->createAddress(5, $data);

        $call = $this->http->lastCall();
        self::assertSame('POST', $call['method']);
        self::assertSame('customers/5/addresses', $call['path']);
        self::assertSame([$data], $call['args']);
    }

    #[Test]
    public function update_address_puts_to_correct_path(): void
    {
        $this->customers->updateAddress(5, 10, ['city' => 'Chicago']);

        $call = $this->http->lastCall();
        self::assertSame('PUT', $call['method']);
        self::assertSame('customers/5/addresses/10', $call['path']);
    }

    #[Test]
    public function list_addresses_gets_paginated(): void
    {
        $this->customers->listAddresses(5);

        $call = $this->http->lastCall();
        self::assertSame('GET', $call['method']);
        self::assertSame('customers/5/addresses', $call['path']);
    }
}

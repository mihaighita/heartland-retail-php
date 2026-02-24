<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests\Unit\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;
use HeartlandRetail\Resource\ItemsResource;
use HeartlandRetail\Tests\MockHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ItemsResource::class)]
final class ItemsResourceTest extends TestCase
{
    private MockHttpClient $http;
    private ItemsResource $items;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient(
            new Response(200, [], [
                'total' => 1,
                'pages' => 1,
                'per_page' => 50,
                'results' => [['id' => 1, 'description' => 'Test']],
            ], ''),
        );
        $this->items = new ItemsResource($this->http);
    }

    #[Test]
    public function create_posts_to_items(): void
    {
        $data = ['description' => 'Widget', 'price' => 9.99];
        $this->items->create($data);

        $call = $this->http->lastCall();
        self::assertSame('POST', $call['method']);
        self::assertSame('items', $call['path']);
        self::assertSame([$data], $call['args']);
    }

    #[Test]
    public function get_fetches_single_item(): void
    {
        $this->items->get(42);

        $call = $this->http->lastCall();
        self::assertSame('GET', $call['method']);
        self::assertSame('items/42', $call['path']);
    }

    #[Test]
    public function get_with_embed_passes_query_param(): void
    {
        $this->items->get(42, ['inventory_levels', 'custom_fields']);

        $call = $this->http->lastCall();
        self::assertSame('GET', $call['method']);
        self::assertSame('items/42', $call['path']);
        self::assertSame('inventory_levels,custom_fields', $call['args'][0]['embed']);
    }

    #[Test]
    public function update_puts_to_item_path(): void
    {
        $data = ['price' => 19.99];
        $this->items->update(42, $data);

        $call = $this->http->lastCall();
        self::assertSame('PUT', $call['method']);
        self::assertSame('items/42', $call['path']);
        self::assertSame([$data], $call['args']);
    }

    #[Test]
    public function search_returns_paginated_response(): void
    {
        $result = $this->items->search(['active' => true], page: 2, perPage: 25);

        self::assertInstanceOf(PaginatedResponse::class, $result);

        $call = $this->http->lastCall();
        self::assertSame('GET', $call['method']);
        self::assertSame('items', $call['path']);
        self::assertSame(2, $call['args'][0]['page']);
        self::assertSame(25, $call['args'][0]['per_page']);
        self::assertSame(true, $call['args'][0]['q[active]']);
    }

    #[Test]
    public function search_with_operator_filter(): void
    {
        $this->items->search(['description' => ['~', 'shirt']]);

        $call = $this->http->lastCall();
        self::assertSame('shirt', $call['args'][0]['q[description][~]']);
    }

    #[Test]
    public function merge_posts_to_merge_endpoint(): void
    {
        $this->items->merge(100, 200);

        $call = $this->http->lastCall();
        self::assertSame('POST', $call['method']);
        self::assertSame('items/100/merge', $call['path']);
        self::assertSame([['merge_item_id' => 200]], $call['args']);
    }

    #[Test]
    public function create_grid_posts_to_item_grids(): void
    {
        $this->items->createGrid(['name' => 'Sizes']);

        $call = $this->http->lastCall();
        self::assertSame('POST', $call['method']);
        self::assertSame('item_grids', $call['path']);
    }

    #[Test]
    public function all_returns_iterable(): void
    {
        $result = $this->items->all();

        self::assertIsIterable($result);
    }
}

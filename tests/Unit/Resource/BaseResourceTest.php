<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests\Unit\Resource;

use HeartlandRetail\Http\Response;
use HeartlandRetail\Resource\BaseResource;
use HeartlandRetail\Tests\MockHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BaseResource::class)]
final class BaseResourceTest extends TestCase
{
    private TestableResource $resource;
    private MockHttpClient $http;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient(
            new Response(200, [], [
                'total' => 2,
                'pages' => 1,
                'per_page' => 100,
                'results' => [['id' => 1], ['id' => 2]],
            ], ''),
        );
        $this->resource = new TestableResource($this->http);
    }

    #[Test]
    public function build_filter_converts_simple_equality(): void
    {
        $result = $this->resource->exposeBuildFilter(['active' => true, 'name' => 'test']);

        self::assertSame(true, $result['q[active]']);
        self::assertSame('test', $result['q[name]']);
    }

    #[Test]
    public function build_filter_converts_operator_pairs(): void
    {
        $result = $this->resource->exposeBuildFilter([
            'price' => ['>=', 10],
            'description' => ['~', 'shirt'],
        ]);

        self::assertSame(10, $result['q[price][>=]']);
        self::assertSame('shirt', $result['q[description][~]']);
    }

    #[Test]
    public function build_filter_handles_empty_array(): void
    {
        $result = $this->resource->exposeBuildFilter([]);

        self::assertSame([], $result);
    }

    #[Test]
    public function auto_paginate_yields_all_records(): void
    {
        $records = iterator_to_array($this->resource->exposeAutoPaginate('test'));

        self::assertCount(2, $records);
        self::assertSame(1, $records[0]['id']);
        self::assertSame(2, $records[1]['id']);
    }
}

/**
 * Concrete subclass to test BaseResource's protected methods.
 *
 * @internal
 */
class TestableResource extends BaseResource
{
    public function exposeBuildFilter(array $filters): array
    {
        return $this->buildFilter($filters);
    }

    public function exposeAutoPaginate(string $path, array $query = [], int $perPage = 100): iterable
    {
        return $this->autoPaginate($path, $query, $perPage);
    }
}

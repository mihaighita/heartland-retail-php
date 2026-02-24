<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests\Unit\Http;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PaginatedResponse::class)]
final class PaginatedResponseTest extends TestCase
{
    private function makePaginated(array $body): PaginatedResponse
    {
        $raw = new Response(200, [], $body, json_encode($body));

        return new PaginatedResponse($raw);
    }

    #[Test]
    public function it_exposes_pagination_metadata(): void
    {
        $paginated = $this->makePaginated([
            'total' => 150,
            'pages' => 3,
            'per_page' => 50,
            'results' => [['id' => 1], ['id' => 2]],
        ]);

        self::assertSame(150, $paginated->getTotal());
        self::assertSame(3, $paginated->getPageCount());
        self::assertSame(50, $paginated->getPerPage());
    }

    #[Test]
    public function it_is_countable(): void
    {
        $paginated = $this->makePaginated([
            'total' => 100,
            'pages' => 2,
            'per_page' => 50,
            'results' => [['id' => 1], ['id' => 2], ['id' => 3]],
        ]);

        self::assertCount(3, $paginated);
    }

    #[Test]
    public function it_is_iterable(): void
    {
        $results = [['id' => 10], ['id' => 20]];
        $paginated = $this->makePaginated([
            'total' => 2,
            'pages' => 1,
            'per_page' => 50,
            'results' => $results,
        ]);

        $collected = [];
        foreach ($paginated as $record) {
            $collected[] = $record;
        }

        self::assertSame($results, $collected);
    }

    #[Test]
    public function it_returns_results_array(): void
    {
        $results = [['id' => 5]];
        $paginated = $this->makePaginated(['results' => $results]);

        self::assertSame($results, $paginated->getResults());
    }

    #[Test]
    public function it_handles_empty_results(): void
    {
        $paginated = $this->makePaginated(['results' => [], 'total' => 0, 'pages' => 0]);

        self::assertSame(0, $paginated->getTotal());
        self::assertCount(0, $paginated);
        self::assertSame([], $paginated->getResults());
    }

    #[Test]
    public function it_handles_missing_pagination_keys_gracefully(): void
    {
        $paginated = $this->makePaginated([]);

        self::assertSame(0, $paginated->getTotal());
        self::assertSame(1, $paginated->getPageCount());
        self::assertSame([], $paginated->getResults());
    }

    #[Test]
    public function it_exposes_raw_response(): void
    {
        $paginated = $this->makePaginated(['results' => []]);

        self::assertSame(200, $paginated->getRawResponse()->getStatusCode());
    }
}

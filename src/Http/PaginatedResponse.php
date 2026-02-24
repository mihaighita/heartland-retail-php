<?php

declare(strict_types=1);

namespace HeartlandRetail\Http;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Wraps a paginated collection response from the Heartland Retail API.
 *
 * The API returns:
 *   {
 *     "total":    <int>,
 *     "pages":    <int>,
 *     "per_page": <int>,
 *     "results":  [ ... ]
 *   }
 *
 * This class is iterable — iterating it yields the records in the current page.
 * Use the Client auto-pagination helpers to walk across all pages automatically.
 *
 * @implements IteratorAggregate<int, array<string, mixed>>
 */
class PaginatedResponse implements IteratorAggregate, Countable
{
    /** @var array<int, array<string, mixed>> */
    private readonly array $results;

    public function __construct(private readonly Response $rawResponse)
    {
        $body = $rawResponse->getBody() ?? [];
        $this->results = $body['results'] ?? [];
    }

    public function getRawResponse(): Response
    {
        return $this->rawResponse;
    }

    /** Total number of matching records across ALL pages. */
    public function getTotal(): int
    {
        return (int) ($this->rawResponse->get('total') ?? 0);
    }

    /** Total number of available pages. */
    public function getPageCount(): int
    {
        return (int) ($this->rawResponse->get('pages') ?? 1);
    }

    public function getPerPage(): int
    {
        return (int) ($this->rawResponse->get('per_page') ?? count($this->results));
    }

    /** @return array<int, array<string, mixed>> Records in the current page. */
    public function getResults(): array
    {
        return $this->results;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }

    public function count(): int
    {
        return count($this->results);
    }
}

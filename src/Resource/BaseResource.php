<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\HttpClient;
use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Base class for all Heartland Retail API resource groups.
 *
 * Provides shared helpers: paginated GET, common CRUD wrappers,
 * and an auto-pager that transparently walks across all pages.
 */
abstract class BaseResource
{
    public function __construct(protected readonly HttpClient $http) {}

    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    /**
     * Issue a GET that returns a paginated collection.
     *
     * @param array<string, mixed> $query
     */
    protected function getPaginated(string $path, array $query = []): PaginatedResponse
    {
        $response = $this->http->get($path, $query);

        return new PaginatedResponse($response);
    }

    /**
     * Walk across all pages of a paginated endpoint and yield every record.
     *
     * @param array<string, mixed> $query Additional query params (page / per_page / filters).
     * @return iterable<int, array<string, mixed>>
     */
    protected function autoPaginate(
        string $path,
        array $query = [],
        int $perPage = 100,
    ): iterable {
        $page = 1;
        $pages = null;

        do {
            $params = array_merge($query, ['page' => $page, 'per_page' => $perPage]);
            $result = $this->getPaginated($path, $params);

            $pages ??= $result->getPageCount();

            foreach ($result as $record) {
                yield $record;
            }

            $page++;
        } while ($page <= $pages);
    }

    /**
     * Build a filter query string in Heartland Retail's advanced filter syntax.
     *
     * Example:
     *   buildFilter(['active' => true, 'description' => ['~', 'shirt']])
     *   produces:  ?q[active]=1&q[description][~]=shirt
     *
     * Simple values are passed as equality checks.
     * Arrays of the form [operator, value] use the given operator (e.g. "~" for LIKE).
     *
     * Supported operators: =, !=, <, >, <=, >=, ~ (contains), !~ (not contains)
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    protected function buildFilter(array $filters): array
    {
        $query = [];

        foreach ($filters as $field => $value) {
            if (is_array($value) && count($value) === 2) {
                [$operator, $operand] = $value;
                $query["q[{$field}][{$operator}]"] = $operand;
            } else {
                $query["q[{$field}]"] = $value;
            }
        }

        return $query;
    }
}

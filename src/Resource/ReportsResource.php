<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;

/**
 * Executes saved reports via the Heartland Retail Reporting API.
 *
 * @see https://support.heartlandretail.us/en/articles/94485-how-do-i-access-a-report-from-the-api
 */
class ReportsResource extends BaseResource
{
    /** @param array<string, mixed> $params */
    public function query(array $params = [], int $page = 1, int $perPage = 50): PaginatedResponse
    {
        $query = array_merge($params, ['page' => $page, 'per_page' => $perPage]);

        return $this->getPaginated('analyzer', $query);
    }

    /**
     * Iterate over ALL report rows automatically.
     *
     * @param array<string, mixed> $params
     * @return iterable<int, array<string, mixed>>
     */
    public function allRows(array $params = [], int $perPage = 100): iterable
    {
        return $this->autoPaginate('analyzer', $params, $perPage);
    }
}

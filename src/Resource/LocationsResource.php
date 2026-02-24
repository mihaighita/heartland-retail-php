<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;

/**
 * Read-only access to store locations.
 *
 * @see https://dev.retail.heartland.us/#resource-locations
 */
class LocationsResource extends BaseResource
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters = [], int $page = 1, int $perPage = 50): PaginatedResponse
    {
        return $this->getPaginated('locations', array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage],
        ));
    }

    /** @return iterable<int, array<string, mixed>> */
    public function all(): iterable
    {
        return $this->autoPaginate('locations', [], 100);
    }
}

<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages Heartland Retail *Items* (products / SKUs) and *Item Grids*.
 *
 * @see https://dev.retail.heartland.us/#resource-items
 * @see https://dev.retail.heartland.us/#resource-item_grids
 */
class ItemsResource extends BaseResource
{
    /**
     * Create a new item (product / SKU).
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Response
    {
        return $this->http->post('items', $data);
    }

    /**
     * Retrieve a single item by ID.
     *
     * @param string[] $embed Optional embed keys, e.g. ['inventory_levels', 'custom_fields']
     */
    public function get(int $id, array $embed = []): Response
    {
        $query = [];
        if ($embed !== []) {
            $query['embed'] = implode(',', $embed);
        }

        return $this->http->get("items/{$id}", $query);
    }

    /**
     * Update an existing item.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Response
    {
        return $this->http->put("items/{$id}", $data);
    }

    /**
     * Search / list items with optional filters and pagination.
     *
     * @param array<string, mixed> $filters
     * @param string[]             $embed
     */
    public function search(
        array $filters = [],
        int $page = 1,
        int $perPage = 50,
        array $embed = [],
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage],
        );
        if ($embed !== []) {
            $query['embed'] = implode(',', $embed);
        }

        return $this->getPaginated('items', $query);
    }

    /**
     * Iterate over ALL matching items across all pages automatically.
     *
     * @param array<string, mixed> $filters
     * @return iterable<int, array<string, mixed>>
     */
    public function all(array $filters = [], int $perPage = 100): iterable
    {
        return $this->autoPaginate('items', $this->buildFilter($filters), $perPage);
    }

    /**
     * Merge two items. The source item is merged into the target and deleted.
     */
    public function merge(int $targetId, int $sourceId): Response
    {
        return $this->http->post("items/{$targetId}/merge", ['merge_item_id' => $sourceId]);
    }

    // =========================================================================
    // Item Grids (size / colour matrices)
    // =========================================================================

    /** @param array<string, mixed> $data */
    public function createGrid(array $data): Response
    {
        return $this->http->post('item_grids', $data);
    }

    public function getGrid(int $id): Response
    {
        return $this->http->get("item_grids/{$id}");
    }

    /** @param array<string, mixed> $data */
    public function updateGrid(int $id, array $data): Response
    {
        return $this->http->put("item_grids/{$id}", $data);
    }

    /** @param array<string, mixed> $filters */
    public function searchGrids(array $filters = [], int $page = 1, int $perPage = 50): PaginatedResponse
    {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage],
        );

        return $this->getPaginated('item_grids', $query);
    }
}

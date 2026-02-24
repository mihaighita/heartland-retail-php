<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages custom field definitions for any entity type.
 *
 * @see https://dev.retail.heartland.us/#resource-custom_fields
 */
class CustomFieldsResource extends BaseResource
{
    /** @param array<string, mixed> $data Required: name, applies_to */
    public function create(array $data): Response
    {
        return $this->http->post('custom_fields', $data);
    }

    public function get(int $id): Response
    {
        return $this->http->get("custom_fields/{$id}");
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Response
    {
        return $this->http->put("custom_fields/{$id}", $data);
    }

    /** @param array<string, mixed> $filters */
    public function search(array $filters = [], int $page = 1, int $perPage = 50): PaginatedResponse
    {
        return $this->getPaginated('custom_fields', array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage],
        ));
    }
}

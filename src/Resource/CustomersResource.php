<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages Heartland Retail *Customers* and their *Addresses*.
 *
 * @see https://dev.retail.heartland.us/#resource-customers
 */
class CustomersResource extends BaseResource
{
    /**
     * Create a new customer.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Response
    {
        return $this->http->post('customers', $data);
    }

    /**
     * Retrieve a single customer by ID.
     *
     * @param string[] $embed e.g. ['addresses', 'custom_fields']
     */
    public function get(int $id, array $embed = []): Response
    {
        $query = [];
        if ($embed !== []) {
            $query['embed'] = implode(',', $embed);
        }

        return $this->http->get("customers/{$id}", $query);
    }

    /**
     * Update a customer.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Response
    {
        return $this->http->put("customers/{$id}", $data);
    }

    /**
     * Search customers.
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

        return $this->getPaginated('customers', $query);
    }

    /**
     * Iterate over ALL matching customers automatically.
     *
     * @param array<string, mixed> $filters
     * @return iterable<int, array<string, mixed>>
     */
    public function all(array $filters = [], int $perPage = 100): iterable
    {
        return $this->autoPaginate('customers', $this->buildFilter($filters), $perPage);
    }

    /**
     * Merge two customers. The source is merged into the target and deleted.
     */
    public function merge(int $targetId, int $sourceId): Response
    {
        return $this->http->post("customers/{$targetId}/merge", ['merge_customer_id' => $sourceId]);
    }

    // =========================================================================
    // Customer Addresses
    // =========================================================================

    public function listAddresses(int $customerId): PaginatedResponse
    {
        return $this->getPaginated("customers/{$customerId}/addresses");
    }

    public function getAddress(int $customerId, int $addressId): Response
    {
        return $this->http->get("customers/{$customerId}/addresses/{$addressId}");
    }

    /** @param array<string, mixed> $data */
    public function createAddress(int $customerId, array $data): Response
    {
        return $this->http->post("customers/{$customerId}/addresses", $data);
    }

    /** @param array<string, mixed> $data */
    public function updateAddress(int $customerId, int $addressId, array $data): Response
    {
        return $this->http->put("customers/{$customerId}/addresses/{$addressId}", $data);
    }
}

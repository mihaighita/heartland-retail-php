<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages shipping methods and payment types (configuration / reference data).
 *
 * @see https://dev.retail.heartland.us/#shipping
 * @see https://dev.retail.heartland.us/#payment_types
 */
class ConfigResource extends BaseResource
{
    /** @param array<string, mixed> $data */
    public function createShippingMethod(array $data): Response
    {
        return $this->http->post('shipping_methods', $data);
    }

    public function getShippingMethod(int $id): Response
    {
        return $this->http->get("shipping_methods/{$id}");
    }

    /** @param array<string, mixed> $data */
    public function updateShippingMethod(int $id, array $data): Response
    {
        return $this->http->put("shipping_methods/{$id}", $data);
    }

    /** @param array<string, mixed> $filters */
    public function searchShippingMethods(array $filters = [], int $page = 1, int $perPage = 50): PaginatedResponse
    {
        return $this->getPaginated('shipping_methods', array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage],
        ));
    }

    /** @param array<string, mixed> $data */
    public function createPaymentType(array $data): Response
    {
        return $this->http->post('payment_types', $data);
    }

    public function getPaymentType(int $id): Response
    {
        return $this->http->get("payment_types/{$id}");
    }

    /** @param array<string, mixed> $data */
    public function updatePaymentType(int $id, array $data): Response
    {
        return $this->http->put("payment_types/{$id}", $data);
    }

    /** @param array<string, mixed> $filters */
    public function searchPaymentTypes(array $filters = [], int $page = 1, int $perPage = 50): PaginatedResponse
    {
        return $this->getPaginated('payment_types', array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage],
        ));
    }
}

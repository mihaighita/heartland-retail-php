<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages Heartland Retail *Purchasing* workflows:
 *   - Purchase Orders
 *   - Purchase Receipts
 *   - Purchase Returns
 *   - Vendors
 *
 * @see https://dev.retail.heartland.us/#purchasing
 */
class PurchasingResource extends BaseResource
{
    // =========================================================================
    // Purchase Orders
    // =========================================================================

    /**
     * Create a purchase order.
     *
     * @param  array<string,mixed> $data  Required: vendor_id, location_id
     */
    public function createOrder(array $data): Response
    {
        return $this->http->post('purchasing/orders', $data);
    }

    /**
     * Retrieve a purchase order.
     */
    public function getOrder(int $id): Response
    {
        return $this->http->get("purchasing/orders/{$id}");
    }

    /**
     * Retrieve lines on a purchase order.
     */
    public function getOrderLines(int $id): PaginatedResponse
    {
        return $this->getPaginated("purchasing/orders/{$id}/lines");
    }

    /**
     * Add an item to a purchase order.
     *
     * @param  array<string,mixed> $data  Required: item_id, quantity
     */
    public function addOrderItem(int $orderId, array $data): Response
    {
        return $this->http->post("purchasing/orders/{$orderId}/lines", $data);
    }

    /**
     * Search purchase orders.
     *
     * @param  array<string,mixed> $filters  e.g. ['status' => 'open', 'vendor_id' => 3]
     */
    public function searchOrders(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('purchasing/orders', $query);
    }

    /**
     * Iterate over ALL purchase orders.
     *
     * @return iterable<array<string,mixed>>
     */
    public function allOrders(array $filters = [], int $perPage = 100): iterable
    {
        return $this->autoPaginate('purchasing/orders', $this->buildFilter($filters), $perPage);
    }

    // =========================================================================
    // Purchase Receipts
    // =========================================================================

    /**
     * Create a purchase receipt (receiving inventory against a PO).
     *
     * @param  array<string,mixed> $data  Required: order_id (or vendor_id), location_id
     */
    public function createReceipt(array $data): Response
    {
        return $this->http->post('purchasing/receipts', $data);
    }

    /**
     * Retrieve a purchase receipt.
     */
    public function getReceipt(int $id): Response
    {
        return $this->http->get("purchasing/receipts/{$id}");
    }

    /**
     * Retrieve lines on a purchase receipt.
     */
    public function getReceiptLines(int $id): PaginatedResponse
    {
        return $this->getPaginated("purchasing/receipts/{$id}/lines");
    }

    /**
     * Add an item to a receipt.
     *
     * @param  array<string,mixed> $data  Required: item_id, quantity_received
     */
    public function addReceiptItem(int $receiptId, array $data): Response
    {
        return $this->http->post("purchasing/receipts/{$receiptId}/lines", $data);
    }

    /**
     * Search purchase receipts.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchReceipts(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('purchasing/receipts', $query);
    }

    // =========================================================================
    // Purchase Returns
    // =========================================================================

    /**
     * Create a purchase return (return inventory to vendor).
     *
     * @param  array<string,mixed> $data  Required: vendor_id, location_id
     */
    public function createReturn(array $data): Response
    {
        return $this->http->post('purchasing/returns', $data);
    }

    /**
     * Retrieve a purchase return.
     */
    public function getReturn(int $id): Response
    {
        return $this->http->get("purchasing/returns/{$id}");
    }

    /**
     * Retrieve lines on a purchase return.
     */
    public function getReturnLines(int $id): PaginatedResponse
    {
        return $this->getPaginated("purchasing/returns/{$id}/lines");
    }

    /**
     * Add an item to a purchase return.
     *
     * @param  array<string,mixed> $data  Required: item_id, quantity
     */
    public function addReturnItem(int $returnId, array $data): Response
    {
        return $this->http->post("purchasing/returns/{$returnId}/lines", $data);
    }

    /**
     * Search purchase returns.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchReturns(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('purchasing/returns', $query);
    }

    // =========================================================================
    // Vendors
    // =========================================================================

    /**
     * Create a vendor.
     *
     * @param  array<string,mixed> $data  Required: name
     */
    public function createVendor(array $data): Response
    {
        return $this->http->post('purchasing/vendors', $data);
    }

    /**
     * Retrieve a vendor.
     */
    public function getVendor(int $id): Response
    {
        return $this->http->get("purchasing/vendors/{$id}");
    }

    /**
     * Update a vendor.
     *
     * @param  array<string,mixed> $data
     */
    public function updateVendor(int $id, array $data): Response
    {
        return $this->http->put("purchasing/vendors/{$id}", $data);
    }

    /**
     * Search vendors.
     *
     * @param  array<string,mixed> $filters  e.g. ['name' => ['~', 'Acme']]
     */
    public function searchVendors(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('purchasing/vendors', $query);
    }

    /**
     * Iterate over ALL vendors.
     *
     * @return iterable<array<string,mixed>>
     */
    public function allVendors(array $filters = [], int $perPage = 100): iterable
    {
        return $this->autoPaginate('purchasing/vendors', $this->buildFilter($filters), $perPage);
    }
}

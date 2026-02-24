<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages Heartland Retail Inventory operations:
 *   - Adjustment Sets (physical count / shrinkage adjustments)
 *   - Inventory Values (current stock-on-hand value)
 *   - Inventory Transactions (historical ledger)
 *   - Inventory Transfers (stock movements between locations)
 *
 * @see https://dev.retail.heartland.us/#inventory
 */
class InventoryResource extends BaseResource
{
    // =========================================================================
    // Adjustment Sets
    // =========================================================================

    /**
     * Create an adjustment set.
     *
     * @param  array<string,mixed> $data  e.g. ['reason_id' => 5, 'location_id' => 1]
     */
    public function createAdjustmentSet(array $data): Response
    {
        return $this->http->post('inventory/adjustment_sets', $data);
    }

    /**
     * Retrieve a single adjustment set.
     */
    public function getAdjustmentSet(int $id): Response
    {
        return $this->http->get("inventory/adjustment_sets/{$id}");
    }

    /**
     * Update an adjustment set.
     *
     * @param  array<string,mixed> $data
     */
    public function updateAdjustmentSet(int $id, array $data): Response
    {
        return $this->http->put("inventory/adjustment_sets/{$id}", $data);
    }

    /**
     * Retrieve lines within an adjustment set.
     */
    public function getAdjustmentSetLines(int $id): PaginatedResponse
    {
        return $this->getPaginated("inventory/adjustment_sets/{$id}/lines");
    }

    /**
     * Add an item to an adjustment set.
     *
     * @param  array<string,mixed> $data  Required: item_id, quantity
     */
    public function addItemToAdjustmentSet(int $adjustmentSetId, array $data): Response
    {
        return $this->http->post("inventory/adjustment_sets/{$adjustmentSetId}/lines", $data);
    }

    /**
     * Search adjustment sets.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchAdjustmentSets(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('inventory/adjustment_sets', $query);
    }

    // =========================================================================
    // Inventory Values
    // =========================================================================

    /**
     * Retrieve totals across all items (aggregate).
     *
     * @param  array<string,mixed> $query  Optional filters, e.g. ['location_id' => 2]
     */
    public function getInventoryValueTotals(array $query = []): Response
    {
        return $this->http->get('inventory/values/totals', $query);
    }

    /**
     * Retrieve inventory values broken down by item.
     *
     * @param  array<string,mixed> $query
     */
    public function getInventoryValuesByItem(array $query = [], int $page = 1, int $perPage = 50): PaginatedResponse
    {
        $params = array_merge($query, ['page' => $page, 'per_page' => $perPage]);
        return $this->getPaginated('inventory/values/by_item', $params);
    }

    /**
     * Search inventory values for a specific item.
     *
     * @param  array<string,mixed> $query  Optional filters (location_id, etc.)
     */
    public function getItemInventoryValues(int $itemId, array $query = []): PaginatedResponse
    {
        return $this->getPaginated("inventory/items/{$itemId}/values", $query);
    }

    // =========================================================================
    // Inventory Transactions (read-only ledger)
    // =========================================================================

    /**
     * Search the inventory transaction history.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchTransactions(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('inventory/transactions', $query);
    }

    /**
     * Iterate over ALL matching inventory transactions.
     *
     * @return iterable<array<string,mixed>>
     */
    public function allTransactions(array $filters = [], int $perPage = 100): iterable
    {
        return $this->autoPaginate('inventory/transactions', $this->buildFilter($filters), $perPage);
    }

    // =========================================================================
    // Inventory Transfers
    // =========================================================================

    /**
     * Search inventory transfers.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchTransfers(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('inventory/transfers', $query);
    }

    /**
     * Retrieve a single inventory transfer.
     */
    public function getTransfer(int $id): Response
    {
        return $this->http->get("inventory/transfers/{$id}");
    }

    /**
     * Create an inventory transfer.
     *
     * @param  array<string,mixed> $data  e.g. ['from_location_id' => 1, 'to_location_id' => 2]
     */
    public function createTransfer(array $data): Response
    {
        return $this->http->post('inventory/transfers', $data);
    }

    /**
     * Update an inventory transfer.
     *
     * @param  array<string,mixed> $data
     */
    public function updateTransfer(int $id, array $data): Response
    {
        return $this->http->put("inventory/transfers/{$id}", $data);
    }

    // -------------------------------------------------------------------------
    // Transfer Lines
    // -------------------------------------------------------------------------

    /**
     * Search lines of an inventory transfer.
     */
    public function searchTransferLines(int $transferId, array $query = []): PaginatedResponse
    {
        return $this->getPaginated("inventory/transfers/{$transferId}/lines", $query);
    }

    /**
     * Get a specific transfer line.
     */
    public function getTransferLine(int $transferId, int $lineId): Response
    {
        return $this->http->get("inventory/transfers/{$transferId}/lines/{$lineId}");
    }

    /**
     * Add an item line to a transfer.
     *
     * @param  array<string,mixed> $data  Required: item_id, quantity
     */
    public function createTransferLine(int $transferId, array $data): Response
    {
        return $this->http->post("inventory/transfers/{$transferId}/lines", $data);
    }

    /**
     * Update a transfer line.
     *
     * @param  array<string,mixed> $data
     */
    public function updateTransferLine(int $transferId, int $lineId, array $data): Response
    {
        return $this->http->put("inventory/transfers/{$transferId}/lines/{$lineId}", $data);
    }

    /**
     * Bulk-update transfer lines.
     *
     * @param  array<array<string,mixed>> $lines  Array of line update objects.
     */
    public function bulkUpdateTransferLines(int $transferId, array $lines): Response
    {
        return $this->http->put("inventory/transfers/{$transferId}/lines", ['lines' => $lines]);
    }

    /**
     * Delete a transfer line.
     */
    public function deleteTransferLine(int $transferId, int $lineId): Response
    {
        return $this->http->delete("inventory/transfers/{$transferId}/lines/{$lineId}");
    }

    /**
     * Bulk-delete transfer lines.
     *
     * @param  int[] $lineIds
     */
    public function bulkDeleteTransferLines(int $transferId, array $lineIds): Response
    {
        return $this->http->post("inventory/transfers/{$transferId}/lines/bulk_destroy", ['ids' => $lineIds]);
    }

    /**
     * Create a transfer shipment (mark as shipped).
     *
     * @param  array<string,mixed> $data
     */
    public function createTransferShipment(int $transferId, array $data = []): Response
    {
        return $this->http->post("inventory/transfers/{$transferId}/shipments", $data);
    }

    // -------------------------------------------------------------------------
    // Transfer Events
    // -------------------------------------------------------------------------

    /**
     * Search transfer events.
     */
    public function searchTransferEvents(int $transferId, array $query = []): PaginatedResponse
    {
        return $this->getPaginated("inventory/transfers/{$transferId}/events", $query);
    }

    /**
     * Get a specific transfer event.
     */
    public function getTransferEvent(int $transferId, int $eventId): Response
    {
        return $this->http->get("inventory/transfers/{$transferId}/events/{$eventId}");
    }

    /**
     * Search lines of a specific transfer event.
     */
    public function searchTransferEventLines(int $transferId, int $eventId, array $query = []): PaginatedResponse
    {
        return $this->getPaginated("inventory/transfers/{$transferId}/events/{$eventId}/lines", $query);
    }
}

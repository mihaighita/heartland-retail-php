<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages Heartland Retail *Sales* objects: Tickets, Orders, and Invoices.
 *
 * @see https://dev.retail.heartland.us/#sales
 */
class SalesResource extends BaseResource
{
    // =========================================================================
    // Tickets  (POS sales transactions)
    // =========================================================================

    /**
     * Create a new ticket (sales transaction).
     *
     * @param  array<string,mixed> $data  Required: location_id, station_id
     */
    public function createTicket(array $data): Response
    {
        return $this->http->post('tickets', $data);
    }

    /**
     * Retrieve a ticket by ID.
     *
     * @param  string[] $embed  e.g. ['lines', 'customer', 'payments']
     */
    public function getTicket(int $id, array $embed = []): Response
    {
        $query = [];
        if ($embed !== []) {
            $query['embed'] = implode(',', $embed);
        }
        return $this->http->get("tickets/{$id}", $query);
    }

    /**
     * Update a ticket.
     *
     * @param  array<string,mixed> $data
     */
    public function updateTicket(int $id, array $data): Response
    {
        return $this->http->put("tickets/{$id}", $data);
    }

    /**
     * Retrieve lines on a ticket.
     */
    public function getTicketLines(int $id): PaginatedResponse
    {
        return $this->getPaginated("tickets/{$id}/lines");
    }

    /**
     * Add an item line to a ticket.
     *
     * @param  array<string,mixed> $data  Required: item_id, quantity, price
     */
    public function addTicketItemLine(int $ticketId, array $data): Response
    {
        return $this->http->post("tickets/{$ticketId}/lines", $data);
    }

    /**
     * Add a payment to a ticket.
     *
     * @param  array<string,mixed> $data  Required: payment_type_id, amount
     */
    public function addTicketPayment(int $ticketId, array $data): Response
    {
        return $this->http->post("tickets/{$ticketId}/payments", $data);
    }

    /**
     * Add a coupon to a ticket.
     *
     * @param  array<string,mixed> $data  Required: coupon_code
     */
    public function addTicketCoupon(int $ticketId, array $data): Response
    {
        return $this->http->post("tickets/{$ticketId}/coupons", $data);
    }

    /**
     * Search tickets.
     *
     * Useful filter keys: location_id, customer_id, created_at, completed_at, status
     *
     * @param  array<string,mixed> $filters
     * @param  string[]            $embed
     */
    public function searchTickets(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50,
        array $embed   = []
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        if ($embed !== []) {
            $query['embed'] = implode(',', $embed);
        }
        return $this->getPaginated('tickets', $query);
    }

    /**
     * Iterate over ALL matching tickets across all pages.
     *
     * @return iterable<array<string,mixed>>
     */
    public function allTickets(array $filters = [], int $perPage = 100): iterable
    {
        return $this->autoPaginate('tickets', $this->buildFilter($filters), $perPage);
    }

    // =========================================================================
    // Orders  (web / special orders)
    // =========================================================================

    /**
     * Create a sales order.
     *
     * @param  array<string,mixed> $data  Required: location_id
     */
    public function createOrder(array $data): Response
    {
        return $this->http->post('orders', $data);
    }

    /**
     * Retrieve a sales order by ID.
     *
     * @param  string[] $embed
     */
    public function getOrder(int $id, array $embed = []): Response
    {
        $query = [];
        if ($embed !== []) {
            $query['embed'] = implode(',', $embed);
        }
        return $this->http->get("orders/{$id}", $query);
    }

    /**
     * Update a sales order.
     *
     * @param  array<string,mixed> $data
     */
    public function updateOrder(int $id, array $data): Response
    {
        return $this->http->put("orders/{$id}", $data);
    }

    /**
     * Retrieve lines on a sales order.
     */
    public function getOrderLines(int $id): PaginatedResponse
    {
        return $this->getPaginated("orders/{$id}/lines");
    }

    /**
     * Add an item to a sales order.
     *
     * @param  array<string,mixed> $data  Required: item_id, quantity
     */
    public function addOrderItem(int $orderId, array $data): Response
    {
        return $this->http->post("orders/{$orderId}/lines", $data);
    }

    /**
     * Add an item with discounts to a sales order.
     *
     * @param  array<string,mixed> $data  Fields: item_id, quantity, discounts[]
     */
    public function addOrderItemWithDiscounts(int $orderId, array $data): Response
    {
        return $this->http->post("orders/{$orderId}/lines_with_discounts", $data);
    }

    /**
     * Distribute a line item across fulfilment locations.
     *
     * @param  array<string,mixed> $data
     */
    public function distributeOrderLine(int $orderId, int $lineId, array $data): Response
    {
        return $this->http->put("orders/{$orderId}/lines/{$lineId}/distributions", $data);
    }

    /**
     * Add shipping to a sales order.
     *
     * @param  array<string,mixed> $data  Required: shipping_method_id, amount
     */
    public function addOrderShipping(int $orderId, array $data): Response
    {
        return $this->http->post("orders/{$orderId}/shipping", $data);
    }

    /**
     * Add a payment to a sales order.
     *
     * @param  array<string,mixed> $data  Required: payment_type_id, amount
     */
    public function addOrderPayment(int $orderId, array $data): Response
    {
        return $this->http->post("orders/{$orderId}/payments", $data);
    }

    /**
     * Search sales orders.
     *
     * @param  array<string,mixed> $filters
     * @param  string[]            $embed
     */
    public function searchOrders(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50,
        array $embed   = []
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        if ($embed !== []) {
            $query['embed'] = implode(',', $embed);
        }
        return $this->getPaginated('orders', $query);
    }

    /**
     * Iterate over ALL matching orders.
     *
     * @return iterable<array<string,mixed>>
     */
    public function allOrders(array $filters = [], int $perPage = 100): iterable
    {
        return $this->autoPaginate('orders', $this->buildFilter($filters), $perPage);
    }

    // =========================================================================
    // Invoices
    // =========================================================================

    /**
     * Create an invoice.
     *
     * @param  array<string,mixed> $data
     */
    public function createInvoice(array $data): Response
    {
        return $this->http->post('invoices', $data);
    }

    /**
     * Retrieve an invoice by ID.
     *
     * @param  string[] $embed
     */
    public function getInvoice(int $id, array $embed = []): Response
    {
        $query = [];
        if ($embed !== []) {
            $query['embed'] = implode(',', $embed);
        }
        return $this->http->get("invoices/{$id}", $query);
    }

    /**
     * Update an invoice.
     *
     * @param  array<string,mixed> $data
     */
    public function updateInvoice(int $id, array $data): Response
    {
        return $this->http->put("invoices/{$id}", $data);
    }

    /**
     * Retrieve invoice lines.
     */
    public function getInvoiceLines(int $id): PaginatedResponse
    {
        return $this->getPaginated("invoices/{$id}/lines");
    }

    /**
     * Search invoices.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchInvoices(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('invoices', $query);
    }
}

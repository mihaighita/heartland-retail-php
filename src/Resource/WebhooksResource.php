<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages Heartland Retail Webhooks.
 *
 * Webhooks allow you to subscribe to events in the system and receive HTTP
 * callbacks when those events occur.
 *
 * @see https://dev.retail.heartland.us/#webhooks
 */
class WebhooksResource extends BaseResource
{
    /**
     * Create a webhook subscription.
     *
     * @param  array<string,mixed> $data  Required: url, event_types[]
     *
     * Example event types: "ticket.completed", "item.updated", "customer.created"
     */
    public function create(array $data): Response
    {
        return $this->http->post('webhooks', $data);
    }

    /**
     * Retrieve a webhook by ID.
     */
    public function get(int $id): Response
    {
        return $this->http->get("webhooks/{$id}");
    }

    /**
     * Update a webhook.
     *
     * @param  array<string,mixed> $data
     */
    public function update(int $id, array $data): Response
    {
        return $this->http->put("webhooks/{$id}", $data);
    }

    /**
     * Delete a webhook subscription.
     */
    public function delete(int $id): Response
    {
        return $this->http->delete("webhooks/{$id}");
    }

    /**
     * Search webhooks.
     *
     * @param  array<string,mixed> $filters
     */
    public function search(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('webhooks', $query);
    }

    // =========================================================================
    // Webhook Events (incoming event log — read-only)
    // =========================================================================

    /**
     * List received webhook events.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchEvents(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('webhook_events', $query);
    }
}

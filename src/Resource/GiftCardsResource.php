<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages Heartland Retail Gift Cards and their balance adjustments.
 *
 * @see https://dev.retail.heartland.us/#gift_card
 */
class GiftCardsResource extends BaseResource
{
    // =========================================================================
    // Gift Cards
    // =========================================================================

    /**
     * Create a gift card.
     *
     * @param  array<string,mixed> $data  Required: number (card number), initial_balance
     */
    public function create(array $data): Response
    {
        return $this->http->post('gift_cards', $data);
    }

    /**
     * Retrieve a gift card by its card number.
     */
    public function getByNumber(string $cardNumber): Response
    {
        return $this->http->get("gift_cards/{$cardNumber}");
    }

    /**
     * Search gift cards.
     *
     * @param  array<string,mixed> $filters  e.g. ['active' => true]
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
        return $this->getPaginated('gift_cards', $query);
    }

    // =========================================================================
    // Gift Card Adjustments
    // =========================================================================

    /**
     * Create a balance adjustment for a gift card.
     *
     * @param  array<string,mixed> $data  Required: gift_card_number, amount, reason_id
     */
    public function createAdjustment(array $data): Response
    {
        return $this->http->post('gift_card_adjustments', $data);
    }

    /**
     * Retrieve an adjustment by ID.
     */
    public function getAdjustment(int $id): Response
    {
        return $this->http->get("gift_card_adjustments/{$id}");
    }

    /**
     * Search gift card adjustments.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchAdjustments(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('gift_card_adjustments', $query);
    }

    // =========================================================================
    // Adjustment Reason Codes (reference data)
    // =========================================================================

    /**
     * List available gift card adjustment reason codes.
     */
    public function getAdjustmentReasons(int $page = 1, int $perPage = 50): PaginatedResponse
    {
        return $this->getPaginated('gift_card_adjustment_reasons', [
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }
}

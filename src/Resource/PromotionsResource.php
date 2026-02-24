<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages Heartland Retail Promotions: Promotion Rules and Coupons.
 *
 * @see https://dev.retail.heartland.us/#promotions
 */
class PromotionsResource extends BaseResource
{
    // =========================================================================
    // Promotion Rules
    // =========================================================================

    /**
     * Create a promotion rule.
     *
     * @param  array<string,mixed> $data  Required: name, action_type_id
     */
    public function createRule(array $data): Response
    {
        return $this->http->post('promotion_rules', $data);
    }

    /**
     * Retrieve a promotion rule.
     */
    public function getRule(int $id): Response
    {
        return $this->http->get("promotion_rules/{$id}");
    }

    /**
     * Update a promotion rule.
     *
     * @param  array<string,mixed> $data
     */
    public function updateRule(int $id, array $data): Response
    {
        return $this->http->put("promotion_rules/{$id}", $data);
    }

    /**
     * Delete a promotion rule.
     */
    public function deleteRule(int $id): Response
    {
        return $this->http->delete("promotion_rules/{$id}");
    }

    /**
     * Search promotion rules.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchRules(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('promotion_rules', $query);
    }

    // =========================================================================
    // Coupons
    // =========================================================================

    /**
     * Create a coupon.
     *
     * @param  array<string,mixed> $data  Required: code, promotion_rule_id
     */
    public function createCoupon(array $data): Response
    {
        return $this->http->post('coupons', $data);
    }

    /**
     * Retrieve a coupon.
     */
    public function getCoupon(int $id): Response
    {
        return $this->http->get("coupons/{$id}");
    }

    /**
     * Update a coupon.
     *
     * @param  array<string,mixed> $data
     */
    public function updateCoupon(int $id, array $data): Response
    {
        return $this->http->put("coupons/{$id}", $data);
    }

    /**
     * Delete a coupon.
     */
    public function deleteCoupon(int $id): Response
    {
        return $this->http->delete("coupons/{$id}");
    }

    /**
     * Search coupons.
     *
     * @param  array<string,mixed> $filters  e.g. ['code' => 'SUMMER20']
     */
    public function searchCoupons(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('coupons', $query);
    }

    // =========================================================================
    // Action Types (read-only reference data)
    // =========================================================================

    /**
     * List available promotion action types.
     */
    public function getActionTypes(): Response
    {
        return $this->http->get('action_types');
    }
}

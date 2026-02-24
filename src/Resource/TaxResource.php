<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages tax jurisdictions and tax rules.
 *
 * @see https://dev.retail.heartland.us/#resource-tax_jurisdictions
 */
class TaxResource extends BaseResource
{
    /** @param array<string, mixed> $data */
    public function createJurisdiction(array $data): Response
    {
        return $this->http->post('tax_jurisdictions', $data);
    }

    public function getJurisdiction(int $id): Response
    {
        return $this->http->get("tax_jurisdictions/{$id}");
    }

    /** @param array<string, mixed> $data */
    public function updateJurisdiction(int $id, array $data): Response
    {
        return $this->http->put("tax_jurisdictions/{$id}", $data);
    }

    public function deleteJurisdiction(int $id): Response
    {
        return $this->http->delete("tax_jurisdictions/{$id}");
    }

    /** @param array<string, mixed> $filters */
    public function searchJurisdictions(array $filters = [], int $page = 1, int $perPage = 50): PaginatedResponse
    {
        return $this->getPaginated('tax_jurisdictions', array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage],
        ));
    }

    /** @param array<string, mixed> $data */
    public function createRule(array $data): Response
    {
        return $this->http->post('tax_rules', $data);
    }

    public function getRule(int $id): Response
    {
        return $this->http->get("tax_rules/{$id}");
    }

    /** @param array<string, mixed> $data */
    public function updateRule(int $id, array $data): Response
    {
        return $this->http->put("tax_rules/{$id}", $data);
    }

    /** @param array<array<string, mixed>> $rules */
    public function bulkUpdateRules(array $rules): Response
    {
        return $this->http->put('tax_rules', ['rules' => $rules]);
    }

    public function deleteRule(int $id): Response
    {
        return $this->http->delete("tax_rules/{$id}");
    }

    /** @param int[] $ids */
    public function bulkDeleteRules(array $ids): Response
    {
        return $this->http->post('tax_rules/bulk_destroy', ['ids' => $ids]);
    }

    /** @param array<string, mixed> $filters */
    public function searchRules(array $filters = [], int $page = 1, int $perPage = 50): PaginatedResponse
    {
        return $this->getPaginated('tax_rules', array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage],
        ));
    }
}

<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\PaginatedResponse;
use HeartlandRetail\Http\Response;

/**
 * Manages Heartland Retail Users, Roles, and Permissions.
 *
 * @see https://dev.retail.heartland.us/#users
 * @see https://dev.retail.heartland.us/#roles
 */
class UsersResource extends BaseResource
{
    // =========================================================================
    // Users
    // =========================================================================

    /**
     * Create a new user.
     *
     * @param  array<string,mixed> $data  Required: login, password, first_name, last_name
     */
    public function create(array $data): Response
    {
        return $this->http->post('users', $data);
    }

    /**
     * Retrieve a user by ID.
     */
    public function get(int $id): Response
    {
        return $this->http->get("users/{$id}");
    }

    /**
     * Update a user.
     *
     * @param  array<string,mixed> $data
     */
    public function update(int $id, array $data): Response
    {
        return $this->http->put("users/{$id}", $data);
    }

    /**
     * Update the currently authenticated user (self).
     *
     * @param  array<string,mixed> $data
     */
    public function updateCurrentUser(array $data): Response
    {
        return $this->http->put('users/me', $data);
    }

    /**
     * Search users.
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
        return $this->getPaginated('users', $query);
    }

    // -------------------------------------------------------------------------
    // User ↔ Role management
    // -------------------------------------------------------------------------

    /**
     * List roles assigned to a user.
     */
    public function getUserRoles(int $userId): PaginatedResponse
    {
        return $this->getPaginated("users/{$userId}/roles");
    }

    /**
     * Assign a role to a user.
     *
     * @param  array<string,mixed> $data  Required: role_id
     */
    public function addUserRole(int $userId, array $data): Response
    {
        return $this->http->post("users/{$userId}/roles", $data);
    }

    /**
     * Remove a role from a user.
     */
    public function removeUserRole(int $userId, int $roleId): Response
    {
        return $this->http->delete("users/{$userId}/roles/{$roleId}");
    }

    /**
     * List effective permissions for a user.
     */
    public function getUserPermissions(int $userId): PaginatedResponse
    {
        return $this->getPaginated("users/{$userId}/permissions");
    }

    // -------------------------------------------------------------------------
    // User ↔ Location management
    // -------------------------------------------------------------------------

    /**
     * List locations a user has access to.
     */
    public function getUserLocations(int $userId): PaginatedResponse
    {
        return $this->getPaginated("users/{$userId}/locations");
    }

    /**
     * Grant a user access to a location.
     *
     * @param  array<string,mixed> $data  Required: location_id
     */
    public function addUserLocation(int $userId, array $data): Response
    {
        return $this->http->post("users/{$userId}/locations", $data);
    }

    /**
     * Revoke a user's access to a location.
     */
    public function removeUserLocation(int $userId, int $locationId): Response
    {
        return $this->http->delete("users/{$userId}/locations/{$locationId}");
    }

    /**
     * Get user alerts.
     */
    public function getUserAlerts(int $userId): PaginatedResponse
    {
        return $this->getPaginated("users/{$userId}/alerts");
    }

    // =========================================================================
    // Roles
    // =========================================================================

    /**
     * Create a role.
     *
     * @param  array<string,mixed> $data  Required: name
     */
    public function createRole(array $data): Response
    {
        return $this->http->post('roles', $data);
    }

    /**
     * Retrieve a role.
     */
    public function getRole(int $id): Response
    {
        return $this->http->get("roles/{$id}");
    }

    /**
     * Update a role.
     *
     * @param  array<string,mixed> $data
     */
    public function updateRole(int $id, array $data): Response
    {
        return $this->http->put("roles/{$id}", $data);
    }

    /**
     * Delete a role.
     */
    public function deleteRole(int $id): Response
    {
        return $this->http->delete("roles/{$id}");
    }

    /**
     * Search roles.
     *
     * @param  array<string,mixed> $filters
     */
    public function searchRoles(
        array $filters = [],
        int   $page    = 1,
        int   $perPage = 50
    ): PaginatedResponse {
        $query = array_merge(
            $this->buildFilter($filters),
            ['page' => $page, 'per_page' => $perPage]
        );
        return $this->getPaginated('roles', $query);
    }

    // -------------------------------------------------------------------------
    // Role ↔ Permission management
    // -------------------------------------------------------------------------

    /**
     * List permissions assigned to a role.
     */
    public function getRolePermissions(int $roleId): PaginatedResponse
    {
        return $this->getPaginated("roles/{$roleId}/permissions");
    }

    /**
     * Add a permission to a role.
     *
     * @param  array<string,mixed> $data  Required: permission_id
     */
    public function addRolePermission(int $roleId, array $data): Response
    {
        return $this->http->post("roles/{$roleId}/permissions", $data);
    }

    /**
     * Remove a permission from a role.
     */
    public function removeRolePermission(int $roleId, int $permissionId): Response
    {
        return $this->http->delete("roles/{$roleId}/permissions/{$permissionId}");
    }

    // -------------------------------------------------------------------------
    // Role ↔ Users management
    // -------------------------------------------------------------------------

    /**
     * List users assigned to a role.
     */
    public function getRoleUsers(int $roleId): PaginatedResponse
    {
        return $this->getPaginated("roles/{$roleId}/users");
    }

    /**
     * Add users to a role.
     *
     * @param  int[] $userIds
     */
    public function addRoleUsers(int $roleId, array $userIds): Response
    {
        return $this->http->post("roles/{$roleId}/users", ['user_ids' => $userIds]);
    }

    /**
     * Remove users from a role.
     *
     * @param  int[] $userIds
     */
    public function removeRoleUsers(int $roleId, array $userIds): Response
    {
        return $this->http->post("roles/{$roleId}/users/bulk_destroy", ['user_ids' => $userIds]);
    }

    // =========================================================================
    // Permissions  (reference data)
    // =========================================================================

    /**
     * List all available system permissions.
     */
    public function searchPermissions(int $page = 1, int $perPage = 100): PaginatedResponse
    {
        return $this->getPaginated('permissions', ['page' => $page, 'per_page' => $perPage]);
    }
}

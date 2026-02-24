<?php

declare(strict_types=1);

namespace HeartlandRetail\Exception;

/**
 * Thrown when the API returns HTTP 403 (insufficient scope / permissions).
 */
class AuthorizationException extends HeartlandRetailException {}

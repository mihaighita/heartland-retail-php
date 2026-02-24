<?php

declare(strict_types=1);

namespace HeartlandRetail\Exception;

/**
 * Thrown when the API returns HTTP 401 (invalid / expired token).
 */
class AuthenticationException extends HeartlandRetailException {}

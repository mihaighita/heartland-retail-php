<?php

declare(strict_types=1);

namespace HeartlandRetail\Exception;

/**
 * Thrown when the HTTP transport itself fails (network error, timeout, etc.).
 */
class TransportException extends HeartlandRetailException {}

<?php

declare(strict_types=1);

namespace HeartlandRetail\Exception;

/**
 * Thrown when the API returns HTTP 422 (validation error).
 */
class ValidationException extends HeartlandRetailException
{
    /** @return array<string, string[]> */
    public function getErrors(): array
    {
        return $this->getResponseBody()['errors'] ?? [];
    }
}

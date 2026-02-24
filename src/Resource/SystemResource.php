<?php

declare(strict_types=1);

namespace HeartlandRetail\Resource;

use HeartlandRetail\Http\Response;

/**
 * System-level endpoints: identity check, host lookup, etc.
 */
class SystemResource extends BaseResource
{
    /**
     * Returns the currently authenticated user's profile.
     * Useful for verifying token validity and checking permissions.
     */
    public function whoAmI(): Response
    {
        return $this->http->get('system/whoami');
    }
}

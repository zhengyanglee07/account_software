<?php

namespace App\Traits;

Trait RefKeyRouteKeyNameTrait
{
    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'reference_key';
    }
}

<?php

namespace App\Interfaces;

interface IAutomationStepKind
{
    /**
     * Dynamically generate description for step kind. Please remember to include
     * protected $appends property with the value ['description'] as well at the
     * respective model.
     *
     * @return string
     */
    public function getDescriptionAttribute(): string;
}
<?php

namespace App\Utils\SegmentFormatters;

interface IFormatter
{
    /**
     * Main formatter function
     *
     * @param array $subCondition
     * @return string
     */
    public function format(array $subCondition): string;
}

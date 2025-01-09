<?php

namespace App\Utils\SegmentFormatters;

class TagsFormatter extends BaseFormatter implements IFormatter
{
    /**
     * @inheritDoc
     */
    public function format(array $subCondition): string
    {
        $tags = implode(', ', $subCondition['value']);
        return "{$subCondition['key']} $tags";
    }
}

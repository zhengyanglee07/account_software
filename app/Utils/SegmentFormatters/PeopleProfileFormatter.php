<?php

namespace App\Utils\SegmentFormatters;

class PeopleProfileFormatter extends BaseFormatter implements IFormatter
{
    /**
     * @inheritDoc
     */
    public function format(array $subCondition): string
    {
        $peopleProfileLabels = $this->getConditionsJson()['peopleProfileLabels'];
        $key = $subCondition['key'];
        $val = $subCondition['value'];

        if ($key === 'label') {
            $label = $peopleProfileLabels[$val] ?? 'unknown label';
            return "$label ";
        }

        return "$key $val ";
    }
}

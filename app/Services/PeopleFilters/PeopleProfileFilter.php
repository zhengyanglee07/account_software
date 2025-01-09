<?php


namespace App\Services\PeopleFilters;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PeopleProfileFilter extends BaseFilter implements IFilter
{

    /**
     * @inheritDoc
     */
    public function filter(Collection $contacts, $subConditions): Collection
    {
        $peopleProfileLabel = $subConditions[self::PEOPLE_PROFILE_LABEL]['value'];
        $peopleProfileSelectKey = $subConditions[self::PEOPLE_PROFILE_SELECT]['key'];
        $peopleProfileSelectVal = $subConditions[self::PEOPLE_PROFILE_SELECT]['value'];

        // TODO: move to centralized file in the future
        $searchableContactLabels = [
            'fname',
            'lname',
            'email',
            'phone_number',
            'gender',
            'birthday'
        ];

        $matchModelLabel = $this->matchLabel(
            $peopleProfileLabel,
            $peopleProfileSelectKey,
            $peopleProfileSelectVal
        );

        return $contacts->filter(function ($contact) use (
            $peopleProfileLabel,
            $searchableContactLabels,
            $matchModelLabel
        ) {
            // look up ProcessedContact model
            if (in_array($peopleProfileLabel, $searchableContactLabels, true)) {
                return $matchModelLabel($contact);
            }

            // look up ProcessedAddress model
            return $contact->addresses->contains(function ($address) use ($matchModelLabel) {
                return $matchModelLabel($address);
            });
        });
    }

    /**
     * Match label on contact/address depends on $op provided.
     * If $op === 'is'/'is not', case-insensitive cmp will be performed.
     * If $op === 'is set'/'is not set', content availability will be checked.
     *
     * A closure function will be returned with a $model instance as param.
     * The $model can be ProcessedContact or ProcessedAddress, depending on
     * where you want to match the $label.
     *
     * Note: $label must exist in model property, or model's table
     *
     * Special note:
     * For 'is' and 'is not', string comparison function down there only
     * supports single-byte string comparison, which means multibyte comparison
     * will probably fail, like utf-8. Use another solution if you feel like
     * multibyte case-insensitive comparison is needed
     *
     * @param $label string Label to match (must present in model)
     * @param $op string Comparison operator (is/is not/is set/is not set)
     * @param $val string|int|null User provided value to compare with, null on 'is set'/'is not set'
     * @return callable
     */
    private function matchLabel(string $label, string $op, $val): callable
    {
        return static function (Model $model) use ($label, $op, $val) {
            $modelLabel = $model->$label;

            if ($op === 'is set') return !is_null($modelLabel);
            if ($op === 'is not set') return is_null($modelLabel);

            // is and is not comparison
            $equals = $op === 'is';
            $res = strcasecmp($modelLabel, $val);
            return $equals ? $res === 0 : $res !== 0;
        };
    }
}

/**
 * Validate all AND conditions in condition filters.
 * False if ANY error found, even it's just 1 validation error, else true
 *
 * @param conditionFilters
 * @returns {boolean}
 */
// eslint-disable-next-line import/prefer-default-export
export const validateConditionFilters = (conditionFilters) =>
    !conditionFilters.some((ORCondition) =>
        ORCondition.some((ANDCondition) => ANDCondition.error)
    );

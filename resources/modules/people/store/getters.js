export default {
    getConditionTagSub: (state) => state.condition.tagSub,

    getConditionSiteActivitySub: (state) => state.condition.siteActivitySub,

    getConditionMarketingEmailStatusSub: (state) =>
        state.condition.marketingEmailStatusSub,

    getTagNames: (state) => state.tags.map((tag) => tag.tagName),

    // =======================================================
    // condition filters
    // =======================================================
    getConditionFilter: (state) => (id, orIdx) =>
        state.conditionFilters[orIdx].filter(
            (andCondition) => andCondition.id === id
        )[0],

    getConditionFilterSubConditions: (state, getters) => (id, orIdx) => {
        const andCondition = getters.getConditionFilter(id, orIdx);

        return andCondition ? andCondition.subConditions : [];
    },
};

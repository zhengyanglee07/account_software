import shortId from 'shortid';

export default {
    /**
     * Show "There's no data found here" desc or custom desc
     * True means show custom desc, e.g. "Haven't added any people..."
     *
     * Refer to Datatable.vue for more details
     *
     * @param state
     * @param {boolean} show
     */
    updateShowPeopleDesc(state, show) {
        state.showPeopleDesc = show;
    },

    /**
     * Update entire contacts
     *
     * @param state
     * @param contacts
     */
    updateContacts(state, { contacts }) {
        state.contacts = [...contacts];
    },

    /**
     * Update entire tags
     *
     * @param state
     * @param tags
     */
    updateTags(state, { tags }) {
        state.tags = [...tags];
    },

    /**
     * Update entire users products
     *
     * @param state
     * @param usersProducts
     */
    updateUsersProducts(state, { usersProducts }) {
        state.usersProducts = [...usersProducts];
    },

    /**
     * Update entire condition object
     *
     * @param state
     * @param condition
     */
    updateCondition(state, { condition }) {
        state.condition = { ...condition };
    },

    /**
     * Update entire customFieldNames array
     *
     * @param state
     * @param customFieldNames
     */
    updateCustomFieldNames(state, { customFieldNames }) {
        state.customFieldNames = [...customFieldNames];
    },

    /**
     * Update array of funnels in state
     *
     * @param state
     * @param allFunnels
     */
    updateFunnelLists(state, allFunnels) {
        state.funnels = allFunnels;
    },

    /**
     * Update array of ecommercePages in state
     *
     * @param state
     * @param allEcommercePages
     */
    updateEcommercePages(state, allEcommercePages) {
        state.ecommercePages = allEcommercePages;
    },

    /**
     * Update forms array in state
     *
     * @param state
     * @param forms
     */
    updateForms(state, forms) {
        state.forms = forms;
    },

    /**
     * Append(add) one tag to current tags
     *
     * @param state
     * @param tag
     */
    appendTag(state, { tag }) {
        state.tags = [...state.tags, tag];
    },

    /**
     * Update checkedContactIds
     *
     * @param state
     * @param contacts
     */
    updateCheckedContactIds(state, { contactIds }) {
        state.checkedContactIds = [...contactIds];
    },

    /**
     * Append(add) one contact id to checkedContactIds
     *
     * @param state
     * @param contactId
     */
    appendCheckedContactIds(state, { contactId }) {
        state.checkedContactIds = [...state.checkedContactIds, contactId];
    },

    /**
     * Remove contactId from checkedContactIds array
     *
     * @param state
     * @param contactId contactId to remove
     */
    removeCheckedContactId(state, { contactId }) {
        state.checkedContactIds = state.checkedContactIds.filter(
            (checkedContactId) => checkedContactId !== contactId
        );
    },

    /**
     * Clear checkedContactIds to empty array
     *
     * @param state
     */
    clearCheckedContactIds(state) {
        state.checkedContactIds = [];
    },

    // ==============================================================
    // Condition Filter
    // ==============================================================
    updateConditionFilters(state, { conditionFilters }) {
        state.conditionFilters = conditionFilters;
    },

    addNewANDConditionFilter(state, { orIdx }) {
        state.conditionFilters = state.conditionFilters.map(
            (ORCondition, idx) => {
                if (orIdx !== idx) return ORCondition;

                return [
                    ...ORCondition,
                    {
                        id: shortId.generate(),
                        name: '',
                        error: true, // to handle the extreme case of directly filter after adding new empty condition
                        subConditions: [],
                    },
                ];
            }
        );
    },

    addNewORConditionFilter(state) {
        state.conditionFilters.push([
            {
                id: shortId.generate(),
                name: '',
                error: true,
                subConditions: [],
            },
        ]);
    },

    /**
     * This mutation directly replace old AND condition with new one,
     * use with care
     *
     * @param state
     * @param id
     * @param orIdx
     * @param andConditionFilter
     */
    updateANDConditionFilter(state, { id, orIdx, newANDCondition }) {
        state.conditionFiltersShowErrors = false;

        state.conditionFilters = state.conditionFilters.map(
            (ORCondition, idx) => {
                if (orIdx !== idx) return ORCondition;

                return ORCondition.map((ANDCondition) => {
                    if (ANDCondition.id !== id) return ANDCondition;

                    return newANDCondition;
                });
            }
        );
    },

    /**
     * Update subCondition with the corresponding subConIdx in subConditions array
     *
     * Note: hold this mutation first. If you can't achieve desired result by
     *       using updateANDConditionFilter mutation method then only consider
     *       using this
     *
     * @param state
     * @param id
     * @param orIdx
     * @param subConIdx
     * @param subConProps
     */
    updateConditionFilterSubCondition(
        state,
        { id, orIdx, subConIdx, subConProps }
    ) {
        state.conditionFiltersShowErrors = false;

        state.conditionFilters = state.conditionFilters.map(
            (ORCondition, idx) => {
                if (idx !== orIdx) return ORCondition;

                return ORCondition.map((ANDCondition) => {
                    if (ANDCondition.id !== id) return ANDCondition;

                    return {
                        ...ANDCondition,
                        subConditions: ANDCondition.subConditions.map(
                            (subCon, subConIndex) => {
                                if (subConIndex !== subConIdx) return subCon;

                                return {
                                    ...subCon,
                                    ...subConProps,
                                };
                            }
                        ),
                    };
                });
            }
        );
    },

    updateConditionFiltersShowErrors(state, { show }) {
        state.conditionFiltersShowErrors = show;
    },

    deleteANDConditionFilter(state, { id, orIdx }) {
        state.conditionFilters = state.conditionFilters.map(
            (ORCondition, idx) => {
                if (idx !== orIdx) return ORCondition;

                return ORCondition.filter(
                    (ANDCondition) => ANDCondition.id !== id
                );
            }
        );
    },

    deleteORConditionFilter(state, { orIdx }) {
        state.conditionFilters = state.conditionFilters.filter(
            (ORCondition, idx) => idx !== orIdx
        );
    },

    /**
     * Clear all condition filters and reset error. Used in clear btn
     *
     * @param state
     */
    resetConditionFilters(state) {
        state.conditionFilters = [
            [
                {
                    id: 1,
                    name: '',
                    error: true,
                    subConditions: [],
                },
            ],
        ];
        state.conditionFiltersShowErrors = false;
    },
};

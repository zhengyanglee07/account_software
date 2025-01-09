// import Vue from 'vue';
import cloneDeep from 'lodash/cloneDeep';

/**
 *
 * @param {array} arr
 * @param {number} id
 * @returns {*}
 */
const findIndexWithId = (arr, id) => arr.findIndex((item) => item.id === id);

export default {
    updateAutomationRefKey(state, key) {
        state.automationRefKey = key;
    },

    updateStatus(state, status) {
        state.status = status;
    },

    updateName(state, name) {
        state.name = name;
    },

    updateRepeat(state, repeat) {
        state.repeat = repeat;
    },

    updateTestEmailRefKey(state, refKey) {
        state.testEmailRefKey = refKey;
    },

    updateStepsOrder(state, stepsOrder) {
        state.steps_order = stepsOrder;
    },

    updateAutomationTriggers(state, { automationTriggers }) {
        state.automationTriggers = automationTriggers;
    },

    updateAutomationTrigger(state, { id, automationTrigger }) {
        const index = findIndexWithId(state.automationTriggers, id);
        state.automationTriggers[index] = {
            ...state.automationTriggers[index],
            ...automationTrigger, // completely replace properties
        };
        // Vue.set(state.automationTriggers, index, {
        //     ...state.automationTriggers[index],
        //     ...automationTrigger, // completely replace properties
        // });
    },

    deleteAutomationTrigger(state, id) {
        const index = findIndexWithId(state.automationTriggers, id);
        state.automationTriggers.splice(index, 1);
    },

    updateSteps(state, { steps }) {
        state.steps = steps;
    },

    insertStep(state, { index, step }) {
        state.steps.splice(index, 0, step);
        state.steps_order.splice(index, 0, step.id);
    },

    updateStep(state, { id, step }) {
        const index = findIndexWithId(state.steps, id);
        state.steps[index] = {
            ...state.steps[index],
            ...step,
        };
        // Vue.set(state.steps, index, {
        //     ...state.steps[index],
        //     ...step,

        //     // properties: {
        //     //     ...state.steps[index].properties,
        //     //     ...step.properties,
        //     // },
        // });
    },

    deleteStep(state, id) {
        const index = findIndexWithId(state.steps, id);
        state.steps.splice(index, 1);

        state.steps_order = state.steps_order.filter((stepId) => stepId !== id);
    },

    updateSendEmailActionsEntities(state, { entities }) {
        state.sendEmailActionsEntities = entities;
    },

    insertOrUpdateSendEmailActionEntity(state, { stepId, entity }) {
        state.sendEmailActionsEntities[stepId] = entity;
    },

    updateTags(state, { tags }) {
        state.tags = tags;
    },

    updateTriggerOptions(state, { triggers }) {
        state.triggerOptions = triggers;
    },

    updateLandingPageForms(state, { forms }) {
        state.landingPageForms = forms;
    },

    updateUsersProducts(state, { usersProducts }) {
        state.usersProducts = usersProducts;
    },

    updateSenders(state, { senders }) {
        state.senders = senders;
    },

    insertOrUpdateSender(state, { sender }) {
        const idx = state.senders.findIndex(({ id }) => sender.id === id);

        if (idx !== -1) {
            state.senders.splice(idx, 1, sender);
            return;
        }

        state.senders.push(sender);
    },

    updateSegments(state, { segments }) {
        state.segments = segments;
    },

    updateModal(state, { modal }) {
        state.modal = cloneDeep(modal);
    },

    resetModal(state) {
        state.modal = {
            type: '',
            data: null,
        };
    },

    updateCustomFieldNames(state, customFieldNames) {
        state.customFieldNames = customFieldNames;
    },
    updateCompletedEmailIds(state, id) {
        if (state.completedEmailIds.includes(id)) return;
        state.completedEmailIds.push(id);
    },
    removeIncompletedEmail(state, id) {
        state.completedEmailIds = state.completedEmailIds.filter(
            (e) => e !== id
        );
    },
    updateStepBasedStatistics(state, statistics) {
        state.stepBasedStatistics = statistics;
    },
};

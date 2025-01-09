import {
    deepInsert,
    deepSearchAndModifyFirst,
    customDeepDelete,
} from '@shared/lib/deep.js';
import axios from 'axios';
import cloneDeep from 'lodash/cloneDeep';
import { nanoid } from 'nanoid';

export default {
    // ===================================================
    // Automation trigger
    // ===================================================
    async createAutomationTriggerAction(
        { commit, state: { automationRefKey } },
        payload
    ) {
        const { triggerId, properties, segmentId } = payload;

        try {
            const res = await axios.post(
                `/automations/${automationRefKey}/triggers`,
                {
                    triggerId,
                    properties,
                    segmentId,
                }
            );

            const { automationTriggers } = res.data;

            // directly update entire automationTriggers array
            commit('updateAutomationTriggers', { automationTriggers });
        } catch (err) {
            this.$toast.error('Error', 'Failed to update trigger.');
        }
    },

    async updateAutomationTrigger(
        { commit, state: { automationRefKey } },
        payload
    ) {
        const { id, triggerId, properties, segmentId } = payload;

        try {
            const res = await axios.put(
                `/automations/${automationRefKey}/triggers/${id}`,
                {
                    triggerId,
                    properties,
                    segmentId,
                }
            );

            const { automationTrigger } = res.data;
            commit('updateAutomationTrigger', { id, automationTrigger });
        } catch (err) {
            this.$toast.error('Error', 'Failed to create trigger.');
        }
    },

    async deleteAutomationTrigger(
        { commit, state: { automationRefKey } },
        payload
    ) {
        const { id } = payload;

        try {
            await axios.delete(
                `/automations/${automationRefKey}/triggers/${id}`
            );
            commit('deleteAutomationTrigger', id);
        } catch (err) {
            this.$toast.error('Error', 'Failed to delete trigger.');
        }
    },

    async createOrUpdateAutomationTrigger({ dispatch }, payload) {
        const {
            id, // id is null for trigger creation
            triggerId,
            properties,
            segmentId,
        } = payload;

        // create
        if (!id) {
            return dispatch('createAutomationTriggerAction', {
                triggerId,
                properties,
                segmentId,
            });
        }

        return dispatch('updateAutomationTrigger', {
            id,
            triggerId,
            properties,
            segmentId,
        });
    },
    // ===================================================
    // Automation trigger
    // ===================================================

    // ===================================================
    // Automation step (general)
    // ===================================================
    async fetchAutomationStep({ state: { automationRefKey } }, payload) {
        const { steps } = payload;

        const res = await axios.post(`/automations/${automationRefKey}/steps`, {
            steps,
        });
        return res.data;
    },

    async createAutomationStep({ state, commit, dispatch }, payload) {
        const {
            data,
            index,
            parent: { id: parentId } = null,
            config = {},
        } = payload;

        const steps = cloneDeep(state.steps);
        const newStepData = {
            ...data,
            id: data.id ? data.id : nanoid(),
        };
        deepInsert(steps, parentId, index, newStepData, config);

        try {
            await dispatch('fetchAutomationStep', { steps });
            commit('updateSteps', { steps });

            return newStepData;
        } catch (err) {
            console.error(err);
            this.$toast.error('Error', 'Failed to create step');
            return null;
        }
    },

    async updateAutomationStep(
        { commit, state: { steps, automationRefKey } },
        payload
    ) {
        const { id, data } = payload;

        const { ori: oriStep, obj: updatedSteps } = deepSearchAndModifyFirst(
            steps,
            {
                id,
            },
            data,
            {
                returnOri: true,
                replaceObj: true,
            }
        );

        try {
            const res = await axios.put(
                `/automations/${automationRefKey}/steps`,
                {
                    steps: updatedSteps,
                    prevStep: oriStep,
                    newStep: data,
                }
            );

            commit('updateSteps', {
                steps: updatedSteps,
            });
        } catch (err) {
            console.error(err);
            this.$toast.error('Error', 'Failed to update automation step');
        }
    },

    async deleteAutomationStep(
        { commit, state: { automationRefKey, steps: oriSteps } },
        payload
    ) {
        const { id } = payload;

        const steps = cloneDeep(oriSteps);
        const deletedStep = customDeepDelete(steps, id);

        if (!deletedStep) {
            throw new Error(
                'Something wrong in step deletion, nothing to delete. Please inform our support'
            );
        }

        try {
            await axios.post(`/automations/${automationRefKey}/steps/delete`, {
                steps, // updated steps
                step: deletedStep,
            });

            commit('updateSteps', { steps });
        } catch (err) {
            console.error(err);
            this.$toast.error('Error', 'Failed to delete step');
        }
    },

    async insertOrUpdateStep({ dispatch }, payload) {
        const { id } = payload;

        if (!id) {
            return dispatch('createAutomationStep', payload);
        }

        return dispatch('updateAutomationStep', {
            id,
            data: payload.data,
        });
    },
    // ===================================================
    // Automation step (general)
    // ===================================================

    // ===================================================
    // Automation step (special cases)
    // ===================================================
    async fetchAutomationEmail({ commit }, { emailEntity, sender }) {
        const res = await axios.post('/emails/automation', {
            emailEntity,
            sender,
        });

        const { email: newEmail, sender: newSender } = res.data;

        if (newSender) {
            commit('insertOrUpdateSender', {
                sender: {
                    id: newSender.id,
                    name: newSender.name,
                    email_address: newSender.email_address,
                },
            });
        }

        return newEmail;
    },

    async insertOrUpdateSendEmailActionStep({ commit, dispatch }, payload) {
        const { id, index, emailEntity, sender, parent, config } = payload;
        let stepId = id; // to be used in insert/update send email entity
        let newEmail;

        try {
            newEmail = await dispatch('fetchAutomationEmail', {
                emailEntity,
                sender,
            });
        } catch (err) {
            this.$toast.error(
                'Error',
                'Failed to create or update email. Please check your input'
            );
            return null;
        }

        const emailActionData = {
            type: 'action',
            kind: 'automationSendEmailAction',
            desc: `Send ${newEmail.name ?? '[empty name]'} email`,
            properties: {
                email_id: newEmail.id,
            },
        };

        if (!id) {
            let data;

            try {
                data = await dispatch('createAutomationStep', {
                    index,
                    data: emailActionData,
                    parent,
                    config,
                });
            } catch (err) {
                console.error(err);
                this.$toast.error(
                    'Error',
                    'Failed to create send email action'
                );
                return null;
            }

            // update stepId at earlier part
            stepId = data.id;
        } else {
            await dispatch('updateAutomationStep', {
                id,
                data: emailActionData,
            });
        }

        commit('insertOrUpdateSendEmailActionEntity', {
            stepId,
            entity: {
                emailId: newEmail.emailId,
                email_reference_key: newEmail.reference_key,
                name: newEmail.name,
                subject: newEmail.subject,
                preview: newEmail.preview_text,
                sender_id: newEmail.sender_id,
                email_design_reference_key: newEmail.email_design_reference_key,
                html: newEmail.email_design && newEmail.email_design.html,
                emailDesign: newEmail.emailDesign,
                hasRequiredMergeTags: newEmail.hasRequiredMergeTags,
                sender_name: newEmail.sender_name,
            },
        });

        return { newEmail };
    },
};

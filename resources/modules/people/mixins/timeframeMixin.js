/* eslint-disable indent */

import { required, requiredIf, minValue } from '@vuelidate/validators';
import { parseDate } from '@shared/lib/date.js';
import { mapState } from 'vuex';
import BetweenDateInput from '@people/components/BetweenDateInput.vue';
import RequiredErrMsg from '@people/components/RequiredErrMsg.vue';

export default {
    components: {
        BetweenDateInput,
        RequiredErrMsg,
    },
    data() {
        return {
            showInTheLastDurationInput: false,
            showBetweenDurationInput: false,

            timeFrameKey: '', // exists just for validation purpose, not used in subConditions
            durationKey: '',
            durationValue: '',
            durationBetweenFrom: '',
            durationBetweenTo: '',
        };
    },
    computed: {
        ...mapState('people', ['conditionFiltersShowErrors']),
        timeFrameValidation() {
            return {
                timeFrameKey: {
                    required,
                },
                durationKey: {
                    required: requiredIf(function () {
                        return this.timeFrameKey === 'in the last';
                    }),
                },
                durationValue: {
                    required: requiredIf(function () {
                        return this.timeFrameKey === 'in the last';
                    }),
                    minValue: minValue(1),
                },
                durationBetweenFrom: {
                    required: requiredIf(function () {
                        return this.isTimeFrameBetween();
                    }),
                },
                durationBetweenTo: {
                    required: requiredIf(function () {
                        return this.isTimeFrameBetween();
                    }),
                },
            };
        },
        timeFrame() {
            return this.condition.timeFrame;
        },
        durationArr() {
            return this.condition.duration;
        },
        showDurationFromRequiredErr() {
            return (
                this.conditionFiltersShowErrors &&
                this.v$.durationBetweenFrom.required.$invalid
            );
        },
        showDurationToRequiredErr() {
            return (
                this.conditionFiltersShowErrors &&
                this.v$.durationBetweenTo.required.$invalid
            );
        },

        timeframeSubCondition() {
            return {
                key: this.timeFrameKey,
                value: null, // this value is set to null purposely, just ignore
            };
        },

        // key & value are both null for 'over all time' timeframe option
        durationSubCondition() {
            return {
                key: !this.isTimeFrameBetween() ? this.durationKey : 'custom',
                value: !this.isTimeFrameBetween()
                    ? this.durationValue
                    : {
                          from: this.durationBetweenFrom,
                          to: this.durationBetweenTo,
                      },
            };
        },
    },
    methods: {
        isTimeFrameInTheLast() {
            return this.timeFrameKey === 'in the last';
        },
        isTimeFrameBetween() {
            return this.timeFrameKey === 'between';
        },
        toggleTimeFrameInput() {
            this.showInTheLastDurationInput = this.isTimeFrameInTheLast();
            this.showBetweenDurationInput = this.isTimeFrameBetween();

            this.durationKey = '';
            this.durationValue = '';
        },
        handleDurationFromInputChange(cb) {
            const fromDate = parseDate(this.durationBetweenFrom);

            // Note: Invalid Date will be obtained if "to" date is empty, this is perfectly ok
            const toDate = parseDate(this.durationBetweenTo);

            // reset "to" if "from" higher than or equal to it
            if (fromDate.getTime() >= toDate.getTime()) {
                this.durationBetweenTo = '';
            }

            cb(); // update condition filter
        },
        handleTimeFrameChange(cb) {
            this.toggleTimeFrameInput();
            cb(); // update condition filter
        },
    },
};

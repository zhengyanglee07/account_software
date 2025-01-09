import { mapMutations, mapState } from 'vuex';
import DatePicker from 'vue-datepicker-next';
import cloneDeep from 'lodash/cloneDeep';
import shippingAPI from '@setting/api/shippingAPI.js';

import 'vue-datepicker-next/index.css';

import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import customParseFormat from 'dayjs/plugin/customParseFormat';

dayjs.extend(customParseFormat);
dayjs.extend(utc);
dayjs.extend(timezone);

export default {
    props: {
        type: {
            type: String,
            default: 'delivery',
        },
        timeZone: {
            type: String,
            default: '',
        },
        onboarding: {
            type: Boolean,
            default: false,
        },
    },
    components: {
        DatePicker,
    },
    computed: {
        ...mapState('settings', ['delivery', 'pickup', 'isLoading']),
    },
    data() {
        return {
            isSaving: false,
        };
    },
    methods: {
        ...mapMutations('settings', [
            'initializeSetting',
            'updateSetting',
            'deleteSetting',
            'deleteNestedSetting',
            'updateNestedSetting',
            'alterAvailableSlot',
            'updateScheduleDay',
            'updateScheduleTimeslot',
            'updateDeliveryHour',
        ]),
        instantUpdateSetting(name, type, value, key, index) {
            // eslint-disable-next-line prefer-rest-params
            const valid = this.checkOpenDays(...arguments);
            if (valid) this.updateSetting({ name, type, value, key, index });
        },
        instantDeleteSetting(name, type, index1) {
            this.deleteSetting({ name, type, index1 });
        },
        instantDeleteNestedSetting(name, type, index1, key, index2) {
            this.deleteNestedSetting({ name, type, index1, key, index2 });
        },
        instantNestedSetting(name, type, value, key1, key2, index1, index2) {
            console.log(index1, index2, 'index1, index2');
            // eslint-disable-next-line prefer-rest-params
            const isValid = this.checkTimeslot(...arguments);
            if (!isValid) return;
            this.updateNestedSetting({
                name,
                type,
                value,
                key1,
                key2,
                index1,
                index2,
            });
        },
        notBeforeToday(date) {
            const datepicker = dayjs(date).format('YYYY-MM-DD h:mm a');
            const today = new Date(new Date().setHours(0, 0, 0, 0));
            const convertToday = dayjs(today)
                .tz(this.timezone)
                .format('YYYY-MM-DD h:mm a');
            return datepicker < convertToday;
        },
        validateInteger(event) {
            const code = event.keyCode;
            return (
                (code === 69 ||
                    code === 189 ||
                    code === 187 ||
                    code === 109 ||
                    code === 107 ||
                    code === 110 ||
                    code === 190) &&
                event.preventDefault()
            );
        },
        checkOpenDays(...args) {
            const [name, type, value, key, index] = args;
            if (key === 'isOpen' && !value) {
                const deliveryHour = cloneDeep(this[name][type]);
                deliveryHour[index][key] = value;
                return deliveryHour.filter((e) => e.isOpen).length > 0;
            }
            return true;
        },
        checkTimeslot(...args) {
            const [name, type, value, key1, key2, index1, index2] = args;
            const deliveryHours = cloneDeep(this[name].deliveryHours[index1]);
            let currentTimeslot = deliveryHours.availableSlots[index2];

            if (key2) currentTimeslot[key2] = value;
            else {
                deliveryHours.availableSlots.push(value);
                currentTimeslot = value;
            }

            const sameTimeslot = deliveryHours.availableSlots.filter((e) =>
                ['start', 'end'].every((key) => e[key] === currentTimeslot[key])
            );

            if (sameTimeslot.length > 1) {
                this.initializeSetting({
                    key: 'error',
                    value: `Duplicate time slot of ${sameTimeslot[0].start} to ${sameTimeslot[0].end} is not allowed within the same day`,
                });
                return false;
            }
            return true;
        },
        getScheduleHour() {
            const dayLbl = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            const dayStr = [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ];
            const scheduleHours = [];
            dayStr.forEach((day, index) => {
                const obj = {
                    name: day,
                    label: dayLbl[index],
                    isOpen: true,
                    availableSlots: [
                        {
                            start: '10:00 AM',
                            end: '12:00 PM',
                            limitValue: 0,
                        },
                    ],
                };
                scheduleHours.push(obj);
            });
            return scheduleHours;
        },
        updateShippingSetting(type, isOnboarding) {
            this.settingDetails = this[type];
            this.isSaving = true;
            let hasError = false;
            const isEnable =
                type === 'delivery'
                    ? this.settingDetails.deliveryHourType === 'custom'
                    : this.settingDetails.isEnableStorePickup;
            if (isEnable) {
                this.settingDetails.deliveryHours.forEach((data) => {
                    const isEmptyLimitValue = data.availableSlots.some(
                        (slot) =>
                            slot.limitValue === '' || slot.limitValue === null
                    );
                    if (this.settingDetails.isLimitOrder && isEmptyLimitValue) {
                        this.$toast.error(
                            'Error',
                            'Limit order do not leave blank'
                        );
                        this.isSaving = false;
                        hasError = true;
                    }
                });

                if (this.settingDetails.preOrderDay === '') {
                    this.$toast.error(
                        'Error',
                        'Number Of Day Slots Shown To Customers cannot be empty'
                    );
                    this.isSaving = false;
                    hasError = true;
                    return;
                }

                if (
                    this.settingDetails.isPreperationTime &&
                    this.settingDetails.preperationValue === ''
                ) {
                    this.$toast.error(
                        'Error',
                        'Preparation time do not leave blank'
                    );
                    this.updatingSetting = false;
                    hasError = true;
                    return;
                }
            }

            this.updateScheduleDay(type);
            this.updateScheduleTimeslot({ key: type });
            this.updateDeliveryHour(type);

            if (!hasError) {
                if (isOnboarding) {
                    const onboadingData = {
                        delivery: {
                            settingName: 'deliverySettings',
                            typeName: 'deliveryhour',
                            nextUrl: '/onboarding/shipping/pickup',
                        },
                        pickup: {
                            settingName: 'pickupSettings',
                            typeName: 'storepickup',
                            nextUrl: '/onboarding/save',
                        },
                    };
                    localStorage.setItem(
                        onboadingData[type].settingName,
                        JSON.stringify({
                            link: onboadingData[type].typeName,
                            ...this.settingDetails,
                        })
                    );
                    this.$inertia.visit(onboadingData[type].nextUrl, {
                        replace: true,
                    });
                    return;
                }
                const actionName =
                    type === 'delivery'
                        ? 'updateDeliveryHour'
                        : 'updateStorePickup';
                shippingAPI[actionName]({
                    ...this.settingDetails,
                })
                    .then((response) => {
                        const { status, message } = response.data;
                        this.$toast.success(status, message);
                    })
                    .catch((error) => {
                        this.$toast.error(
                            'Fail',
                            'Unexpected Error Occured. Please contact our support'
                        );
                    })
                    .finally(() => {
                        this.isSaving = false;
                    });
            }
        },
    },
};

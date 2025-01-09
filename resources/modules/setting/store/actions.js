export default {
    initializePickupDelivery({ state, commit, getters, dispatch }) {
        if (window.location.pathname.includes('onboarding')) {
            if (localStorage.getItem('deliverySettings')) {
                commit('initializeSetting', {
                    key: 'delivery',
                    value: JSON.parse(localStorage.deliverySettings),
                });
            }
            if (localStorage.getItem('pickupSettings')) {
                commit('initializeSetting', {
                    key: 'pickup',
                    value: JSON.parse(localStorage.pickupSettings),
                });
            }
            return;
        }

        const checkPreOrderDay = (preOrderDay) => {
            return preOrderDay === 0 ? 1 : preOrderDay;
        };

        const checkIsDaily = (type, preference) => {
            if (preference[`${type}_is_daily`]) {
                const deliveryHour = JSON.parse(
                    preference[`${type}_hour`] ?? '[]'
                );
                return deliveryHour.every((item) => item.isOpen);
            }
            return preference[`${type}_is_daily`];
        };

        const checkIsSameTimeSlot = (type, preference) => {
            if (preference[`${type}_is_same_time`]) {
                const deliveryHours = JSON.parse(
                    preference[`${type}_hour`] ?? '[]'
                );
                return deliveryHours
                    .filter((item) => item.isOpen)
                    .every(
                        (deliveryHour) =>
                            JSON.stringify(deliveryHour.availableSlots) ===
                            JSON.stringify(deliveryHours[0].availableSlots)
                    );
            }
            return preference[`${type}_is_same_time`];
        };

        window.axios
            .get('/shipping/setting/deliverypickup')
            .then((response) => {
                const { preferences } = response.data;
                const delivery = {
                    deliveryHourType: preferences.delivery_hour_type,
                    deliveryHours: preferences.delivery_hour
                        ? JSON.parse(preferences.delivery_hour)
                        : [],
                    disableDate: preferences.delivery_disabled_date
                        ? JSON.parse(preferences.delivery_disabled_date)
                        : [],
                    preOrderDay: checkPreOrderDay(
                        preferences.delivery_pre_order_from
                    ),
                    isPreperationTime: preferences.delivery_is_preperation_time,
                    isLimitOrder: preferences.delivery_is_limit_order,
                    preperationValue: preferences.delivery_preperation_value,
                    isDaily: checkIsDaily('delivery', preferences),
                    isSameTime: checkIsSameTimeSlot('delivery', preferences),
                };
                const pickup = {
                    isEnableStorePickup: preferences.is_enable_store_pickup,
                    deliveryHours: preferences.pickup_hour
                        ? JSON.parse(preferences.pickup_hour)
                        : [],
                    disableDate: preferences.pickup_disabled_date
                        ? JSON.parse(preferences.pickup_disabled_date)
                        : [],
                    preOrderDay: checkPreOrderDay(
                        preferences.pickup_pre_order_from
                    ),
                    isPreperationTime: preferences.pickup_is_preperation_time,
                    isLimitOrder: preferences.pickup_is_limit_order,
                    preperationValue: preferences.pickup_preperation_value,
                    isDaily: checkIsDaily('pickup', preferences),
                    isSameTime: checkIsSameTimeSlot('pickup', preferences),
                };
                // console.log(delivery,'here',pickup,'there');
                commit('initializeSetting', {
                    key: 'delivery',
                    value: delivery,
                });
                commit('initializeSetting', { key: 'pickup', value: pickup });
                commit('initializeSetting', { key: 'isLoading', value: false });
            })
            .catch((error) => {});
    },
};

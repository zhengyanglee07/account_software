export default {
    /**
     * Shipping Settings
     */
    updateSetting: (state, { name, type, value, key = null, index = null }) => {
        if (index || index === 0) state[name][type][index][key] = value;
        else state[name][type] = value;
    },
    deleteSetting: (state, { name, type, index1 = null, key = null }) => {
        if (key) state[name][type][key].splice(index1, 1);
        else state[name][type].splice(index1, 1);
    },
    deleteNestedSetting: (
        state,
        { name, type, index1 = null, key = null, index2 }
    ) => {
        state[name][type][index1][key].splice(index2, 1);
    },
    updateNestedSetting: (
        state,
        { name, type, value, key1, key2, index1, index2 }
    ) => {
        state[name][type][index1][key1][index2][key2] = value;
    },
    alterAvailableSlot: (state, { name, value, index1, index2 }) => {
        state[name].deliveryHours[index1].availableSlots.splice(
            index2 + 1,
            0,
            value
        );
    },
    initializeSetting: (state, { key, value }) => {
        state[key] = value;
    },

    updateScheduleDay: (state, key) => {
        if (state[key].isDaily) {
            state[key].deliveryHours = state[key].deliveryHours.map((m) => ({
                ...m,
                availableSlots: [...m.availableSlots],
                isOpen: true,
            }));
        }
    },
    updateScheduleTimeslot: (state, { key, isTemp = false }) => {
        if (
            (state[key].isSameTime && state[key].deliveryHours.length > 0) ||
            isTemp
        ) {
            const availableTimeslot = [];
            state[key].singleDeliveryHour[0].availableSlots.forEach((item) => {
                availableTimeslot.push(JSON.stringify(item));
            });

            state[key].deliveryHours = state[key].deliveryHours.map((m) => {
                if (m.isOpen)
                    m.availableSlots = availableTimeslot.map((mm) =>
                        JSON.parse(mm)
                    );
                return m;
            });
        }
    },
    updateDeliveryHour: (state, key) => {
        if (state[key].deliveryHours.length === 0) return;
        if (state[key].isDaily)
            state[key].deliveryHours = state[key].deliveryHours.map((m) => ({
                ...m,
                isOpen: true,
            }));
        if (state[key].isSameTime)
            state[key].singleDeliveryHour = [
                { ...state[key].deliveryHours.find((item) => item.isOpen) },
            ];
    },

    /**
     * Currency Settings
     */
    initialCurrencyDetails: (state, currencyDetails) => {
        state.currencyDetails = currencyDetails;
    },
};

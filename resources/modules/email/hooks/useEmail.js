import { ref } from 'vue';

const emails = ref([]);
const emailGroups = ref([]);
const selectedEmailIds = ref([]);

export default function useEmail() {
    const setEmails = (list) => {
        emails.value = list;
    };
    const setEmailGroups = (groups) => {
        emailGroups.value = groups;
    };
    const resetSelectedEmailIds = () => {
        selectedEmailIds.value = [];
    };

    return {
        emails,
        setEmails,
        emailGroups,
        setEmailGroups,
        selectedEmailIds,
        resetSelectedEmailIds,
    };
}

import referralsAPI from '@onlineStore/api/referralsAPI.js';
import eventBus from '@services/eventBus.js';
import { ref } from 'vue';
import { useStore } from 'vuex';

export default function referral() {
    const getReferralUser = async (refKey) => {
        const queryString = window?.location?.search;
        const parameters = new URLSearchParams(queryString);
        const value = parameters.get('is_referral');
        if (value && value !== '') {
            await referralsAPI
                .getReferralUser(value, refKey)
                .then(({ data: { referralUser } }) => {
                    localStorage.setItem(
                        'funnel#user',
                        JSON.stringify(referralUser)
                    );
                    eventBus.$emit('form-submitted', referralUser);
                });
        }
    };

    return {
        getReferralUser,
    };
}
export function referralPointsPlaceholder() {
    const text = ref('');
    const store = useStore();
    const getReferralPointsPlaceholder = async (elemId, points) => {
        const { isPublish, selectedReferralCampaign } = store.state.onlineStore;
        const elem = document.getElementById(elemId);
        if (elem.innerHTML.includes('{{REFERRAL_POINTS}}')) {
            text.value = elem.innerHTML;
        }
        if (
            elem &&
            isPublish &&
            selectedReferralCampaign &&
            text.value !== ''
        ) {
            let innerText = text.value;
            innerText = innerText.replaceAll(
                '{{REFERRAL_POINTS}}',
                points || 0
            );
            elem.innerHTML = innerText;
        }
    };

    return {
        text,
        getReferralPointsPlaceholder,
    };
}

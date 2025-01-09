import axios from 'axios';

export default {
    index() {
        return axios.get('/referral/fetch');
    },
    getReferralPoints(campaignId, code = null) {
        return axios.post('/referral/points', {
            campaignId,
            code,
        });
    },
    getReferralUser(referralCode, funnelId) {
        return axios.post(`/referral/user`, {
            referralCode,
            funnelId,
        });
    },
    clickSocialShare(referralCode, refKey, type) {
        return axios.post(`/referral/click`, {
            referralCode,
            refKey,
            type,
        });
    },
};

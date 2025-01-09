import axios from 'axios';

export default {
    applyPromotion(discountCode, isFunnel) {
        return axios.get(
            `/promotion/apply/${discountCode}${
                isFunnel ? '?isLanding=true' : ''
            }`
        );
    },
    deletePromotion(promotionId, isFunnel) {
        return axios.delete(
            `/promotion/delete/${promotionId}${
                isFunnel ? '?isLanding=true' : ''
            }`
        );
    },
    // TODO: DELETE
    getAllDiscount(promoInfo) {
        const appliedPromotionArray = localStorage.getItem('appliedPromotion')
            ? JSON.parse(localStorage.getItem('appliedPromotion'))
            : [];
        return axios.post('/promotion/info', {
            ...promoInfo,
            appliedPromotionArray,
        });
    },
    getDiscountCodeInfo(discountCode, promoInfo) {
        return axios.post('/promotion/discountCode', {
            discountCode,
            ...promoInfo,
            account_id: promoInfo.accountId,
        });
    },
    storeDiscount(appliedPromotion) {
        return axios.post('/promotion/store', {
            appliedPromotion,
        });
    },
};

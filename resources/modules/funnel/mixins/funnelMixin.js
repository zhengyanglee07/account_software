import ShareFunnelModal from '@funnel/components/ShareFunnelModal.vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        ShareFunnelModal,
    },

    data() {
        return {
            shareFunnelUrl: null,
        };
    },

    methods: {
        generateShareFunnelURL(refKey) {
            const { origin } = window.location;
            this.shareFunnelUrl = `${origin}/funnels?reference_key=${refKey}&&popOut=true`;
        },

        duplicateFunnel(funnelId) {
            axios
                .post(`/funnel/duplicate/${funnelId}`)
                .then(({ data: { status, message } }) => {
                    this.$toast.success(status, message);
                    router.visit('/funnels');
                })
                .catch((error) => {
                    if (
                        !error.response.data.message.includes(
                            "You've Reach The Limit"
                        )
                    )
                        this.triggerErrorToast(error);
                });
        },

        deleteFunnel(funnelId) {
            axios
                .delete(`/funnel/${funnelId}`)
                .then(({ data: { message } }) => {
                    this.$toast.success('Success', message);
                    if (this.funnels) {
                        const index = this.funnels.findIndex(
                            (e) => e.id === funnelId
                        );
                        this.funnels.splice(index, 1);
                    } else {
                        window.location.href = '/funnels';
                    }
                })
                .catch((error) => {
                    this.triggerErrorToast(error);
                });
        },

        triggerErrorToast(error) {
            this.$toast.error(
                'Error',
                'Something went wrong. Please contact out support.'
            );
            throw new Error(error);
        },
    },
};

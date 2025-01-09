/* eslint-disable consistent-return */
import {
    initializeToast,
    sendHyperSenderVerificationEmail,
    sendSESVerificationEmail,
} from '@setting/lib/verification.js';
import { delay } from '@shared/lib/delay.js';
import { validationFailedNotification } from '@shared/lib/validations.js';
import axios from 'axios';
import { inject } from 'vue';

export default {
    data() {
        return {
            showVerifyingSpinner: false,
        };
    },
    methods: {
        setToast() {
            this.$toast = inject('$toast');
            initializeToast();
        },
        hasError(promiseResults) {
            promiseResults.find((r) => r.status === 'rejected');
        },
        showErrors(promiseResults) {
            promiseResults.forEach((res) => {
                if (res.status === 'rejected') {
                    // for laravel validations
                    if (res.reason.response.status === 422) {
                        validationFailedNotification(res.reason);
                        return;
                    }

                    // generic error message
                    this.$toast.error('Error', 'Unexpected error occurs');
                }
            });
        },
        checkSenderDomain(email) {
            const dbReq = axios.get(`/senders/check?email=${email}`);
            const sesReq = axios.get(`/senders/check/ses?email=${email}`);

            return Promise.allSettled([dbReq, sesReq]);
        },

        async verifySenderDomain(senderEmail) {
            this.showVerifyingSpinner = true;

            // just a simple noti to inform what hyper is doing now
            // add a minimum delay (1s) in case the check below too fast
            this.$toast.info('Info', 'Checking your domain status...');
            await delay(1000);

            try {
                const results = await this.checkSenderDomain(senderEmail);

                // show errors if exist and cease all further actions
                if (this.hasError(results)) {
                    this.showErrors(results);
                    return;
                }

                const [dbRes, sesRes] = results;

                const { verified, sender } = dbRes.value.data;

                if (verified) {
                    this.$toast.warning(
                        'Warning',
                        'This email has been verified in your account'
                    );
                    return;
                }

                if (sender) {
                    this.$toast.warning(
                        'Warning',
                        'This email is pending for verification in your account. Please check your mailbox.'
                    );
                    return;
                }

                this.$toast.info(
                    'Info',
                    `Sending verification email to ${senderEmail}...`
                );

                // delay here serves the same purpose with check above, just longer
                await delay(2000);

                if (!sesRes.value) return;
                const { verified: sesVerified } = sesRes.value.data;

                if (!sesVerified) {
                    return await sendSESVerificationEmail(senderEmail);
                }

                return await sendHyperSenderVerificationEmail(senderEmail);
            } finally {
                this.showVerifyingSpinner = false;
            }
        },
    },
};

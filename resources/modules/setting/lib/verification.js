import axios from 'axios';
import { inject } from 'vue';

let $toast = null;

export const initializeToast = () => {
    $toast = inject('$toast');
};

export const sendHyperSenderVerificationEmail = async (email) => {
    const res = await axios.post(`/senders/verify`, {
        email,
    });

    const { message, sender } = res.data;

    $toast.success('Success', message);

    return sender; // this sender follows Laravel model struct
};

/**
 * Send a Amazon SES verification email to senderEmail
 *
 * @param senderEmail
 * @returns {Promise<*>}
 */
export const sendSESVerificationEmail = async (senderEmail) => {
    const res = await axios.post(`/emails/sender/verify`, {
        senderEmail,
    });

    const { message, sender } = res.data;

    // using globally assigned toast plugin from Vue
    $toast.success('Success', message);

    return sender; // this sender follows Laravel model struct
};

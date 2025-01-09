/* eslint-disable indent */

const AUTOMATION_IMAGE_DIR = '/images/automation';

/**
 *
 * @param {string} triggerType
 * @returns {string}
 */
export const getTriggerNodeImageSrc = (triggerType) => {
    const triggerTypeImageMap = {
        submit_form: `${AUTOMATION_IMAGE_DIR}/form-automation.png`,
        purchase_product: `${AUTOMATION_IMAGE_DIR}/purchase-automation.png`,
        date_based: `${AUTOMATION_IMAGE_DIR}/trigger-date-based.svg`,
        add_tag: `${AUTOMATION_IMAGE_DIR}/trigger-add-tag.svg`,
        remove_tag: `${AUTOMATION_IMAGE_DIR}/trigger-remove-tag.svg`,
        order_spent: `${AUTOMATION_IMAGE_DIR}/trigger-order-spent.svg`,
        abandon_cart: `${AUTOMATION_IMAGE_DIR}/trigger-abandoned-cart.svg`,
        place_order: `${AUTOMATION_IMAGE_DIR}/trigger-order-spent.svg`,
        enter_segment: `${AUTOMATION_IMAGE_DIR}/trigger-add-tag.svg`,
        exit_segment: `${AUTOMATION_IMAGE_DIR}/trigger-remove-tag.svg`,
    };

    return triggerTypeImageMap[triggerType] || '';
};

/**
 *
 * @param {string} stepType
 * @param {string} kind
 * @returns {string}
 */
export const getStepNodeImageSrc = (stepType, kind) => {
    switch (stepType) {
        case 'delay': {
            return `${AUTOMATION_IMAGE_DIR}/automation-delay-icon.svg`;
        }

        case 'action': {
            if (kind === 'automationSendEmailAction') {
                return `${AUTOMATION_IMAGE_DIR}/email-automation.png`;
            }

            if (kind === 'automationAddTagAction') {
                return `${AUTOMATION_IMAGE_DIR}/tag-automation.png`;
            }

            if (kind === 'automationRemoveTagAction') {
                return `${AUTOMATION_IMAGE_DIR}/remove-tag-automation.svg`;
            }
            return '';
        }
        case 'decision': {
            return `${AUTOMATION_IMAGE_DIR}/automation-decision-icon.svg`;
        }

        default: {
            return '';
        }
    }
};

export const transformTags = (tags) => [
    ...tags.map((tag) => ({
        ...tag,
        processed_tag_id: tag.id,
    })),
];

/**
 * Add landing_page_form_id to landingPageForms state, for
 * the ease to merge into trigger's properties
 *
 * In addition, "Any form" as default if user didn't select
 * any form in trigger modal
 *
 * @param {array} forms
 * @returns {array}
 */
export const transformLandingPageForms = (forms) => [
    // placeholder for any form
    {
        id: null,
        landing_page_form_id: null,
        title: 'Any Form',
    },
    ...forms.map((form) => ({
        ...form,
        landing_page_form_id: form.id,
    })),
];

/**
 * Add users_product_id to suersProducts state, for
 * the ease to merge into trigger's properties
 *
 * In addition, "Any product" as default if user didn't select
 * any product in trigger modal
 *
 * @param {array} usersProducts
 * @returns {array}
 */
export const transformUsersProducts = (usersProducts) => [
    // placeholder for any product
    {
        id: null,
        users_product_id: null,
        productTitle: 'Any Product',
        productDescription: 'Any Product',
    },
    ...usersProducts.map((usersProduct) => ({
        ...usersProduct,
        users_product_id: usersProduct.id,
    })),
];

export const transformSegments = (segments) => [
    // placeholder for any segment
    {
        id: null,
        segmentName: 'Any',
    },
    ...segments,
];

export const generateAddStepBtnId = (index, parent, config) => {
    const { id: parentId } = parent;
    const { route } = config;

    return `add-step-btn${parentId ? `-parent-${parentId}` : ''}${
        route ? `-${route}` : ''
    }-${index}`;
};

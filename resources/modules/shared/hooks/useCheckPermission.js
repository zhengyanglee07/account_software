import eventBus from '@services/eventBus.js';
import { usePage } from '@inertiajs/vue3';

let hasElement = false;

const replaceClickEvent = (id, callback) => {
    const elem = document.getElementById(id);
    hasElement = hasElement || !!elem;
    if (!elem) return;
    if (elem?.tagName === 'A') elem.removeAttribute('href');
    elem.removeAttribute('data-bs-target');
    elem.removeAttribute('data-bs-toggle');
    const { parentNode } = elem;
    const cloneElem = document.importNode(elem, true);
    cloneElem.onclick = () => {
        callback();
    };
    parentNode.replaceChild(cloneElem, elem);
};

const getQuotaLimit = (permissionDetail, slug) => {
    const quotaLimit = [];
    Object.keys(permissionDetail).forEach((key) => {
        const permission = Object.values(permissionDetail[key]).find(
            (e) => e.slug === slug
        );
        quotaLimit[key] = permission.max;
    });
    return quotaLimit;
};

export default function useCheckPermission() {
    if (!usePage().props.permissionData) return;
    const { currentPlan, permissionDetail, featureForPaidPlan } =
        usePage().props.permissionData;
    let timesRun = 0;
    const action = setInterval(() => {
        if (document.readyState !== 'complete') return;
        timesRun += 1;
        if (hasElement || timesRun >= 20) {
            clearInterval(action);
        }
        featureForPaidPlan.forEach((slug) => {
            if (slug === 'add-promotion') {
                [
                    'add-manual-promotion-button',
                    'add-automatic-promotion-button',
                ].forEach((id) => {
                    replaceClickEvent(id, () => {
                        eventBus.$emit('trigger-paid-plan-modal');
                    });
                });
            }
            replaceClickEvent(`${slug}-button`, () => {
                eventBus.$emit('trigger-paid-plan-modal');
            });
        });
        Object.entries(permissionDetail[currentPlan]).forEach(([key, item]) => {
            if (item.total >= item.max) {
                replaceClickEvent(`${item.slug}-button`, () => {
                    eventBus.$emit('open-limit-modal', {
                        showModal: true,
                        modalTitle: "You've Reach The Limit",
                        context: item.context ?? null,
                        subscriptionDetail: {
                            isInteger: true,
                            isUncontrollable: false,
                            planName: currentPlan,
                            quotaLimit: getQuotaLimit(
                                permissionDetail,
                                item.slug
                            ),
                        },
                    });
                });
            }
        });
    }, 150);
}

export default {
    getOrderedSteps(state) {
        const { steps_order: stepOrder, steps } = state;

        return stepOrder.map((stepId) =>
            steps.find((step) => step.id === stepId)
        );
    },
};

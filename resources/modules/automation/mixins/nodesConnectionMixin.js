import LeaderLine from 'vue3-leaderline';
import { generateAddStepBtnId } from '@automation/lib/automations.js';
import eventBus from '@services/eventBus.js';
import { mapState } from 'vuex';

export default {
    data() {
        return {
            lines: [],
        };
    },
    computed: {
        ...mapState('automations', ['automationTriggers', 'steps']),
    },
    methods: {
        /**
         * Clear lines to redraw new lines if new data is added,
         * to either automationTriggers/steps
         */
        clearLines() {
            this.lines.forEach((line) => line.remove());
            this.lines.length = 0; // empty the container
        },

        /**
         * Wrapper around connectNodeAndBtn to provide more comprehensive
         * method naming
         *
         * @param {*} nodeId
         * @param {*} btnId
         */
        connectNodeToBtn(nodeId, btnId, nodeToBtn = false) {
            this.connectNodeAndBtn(nodeId, btnId, nodeToBtn);
        },

        /**
         * The core function used to connect trigger/step node to add step btn/
         * nodeToBtn param is used to determine the start element of LeaderLine,
         * if nodeToBtn is true, then the start element is set to node.
         *
         * @param nodeId
         * @param btnId
         * @param nodeToBtn
         */
        connectNodeAndBtn(nodeId, btnId, nodeToBtn = true) {
            const node = document.querySelector(`#${nodeId}`);
            const btn = document.querySelector(`#${btnId}`);

            // can ignore this console.error with newly added Exit node
            // but return if node/btn not found is a must
            if (!node || !btn) {
                // console.error(
                //     `node: ${node} with nodeId=#${nodeId} or btn: ${btn} with btnId=#${btnId} not found`
                // );
                return;
            }

            const nodeRect = node.getBoundingClientRect();
            const btnRect = btn.getBoundingClientRect();

            /**
             * Edit Nov 2020: this offset err seems like fixed after implementing
             *                new UI, i.e. OFFSET_ERR=0.
             *                I will keep this as future reference.
             *
             * =====================================================================
             *
             * Note about this offset err, it's found manually with trial and error,
             * to align the midpoint of node & add step btn. Try to set it
             * to another num (such as 0) and you will get what I'm saying.
             *
             * The exact boundary of this err is somewhere around 3 ~ 4, unfortunately
             * I couldn't figure this out using the "math" way in a short period of
             * time. If you could in the future, feel free to replace this with
             * your solution
             *
             * Also note that the width of node and button might have some impact
             * on this err, although I seldom tested. If you notice the path line is
             * not aligned after you changed width, come here and adjust this err
             */
            const OFFSET_ERR = 0;

            const startElem = nodeToBtn ? node : btn;
            const startXCord = nodeToBtn
                ? nodeRect.width / 2 + OFFSET_ERR
                : btnRect.width / 2 + OFFSET_ERR;
            const endElem = nodeToBtn ? btn : node;
            const endXCord = nodeToBtn
                ? btnRect.width / 2 + OFFSET_ERR
                : nodeRect.width / 2 + OFFSET_ERR;
            const endYCord = nodeToBtn ? btnRect.height : nodeRect.height;

            // add line to lines, convenient to remove later
            this.lines.push(
                new LeaderLine(
                    // startElem,
                    LeaderLine.pointAnchor(startElem, { x: startXCord }),
                    // endElem,
                    LeaderLine.pointAnchor(endElem, { y: endYCord - 2 }),
                    {
                        color: 'black',
                        size: 2,
                        endPlug: 'behind',
                        startPlug: 'arrow1',
                    }
                ).setOptions({ startSocket: 'top', endSocket: 'bottom' })
            );
        },

        convergeDecisionStepBtns(step, nextStepBtnId) {
            /**
             * Refer to connectNodeAndBtn() if you bother to see long story
             * of this OFFSET_ERR
             */
            const OFFSET_ERR = 0;

            const nextStepBtn = document.querySelector(`#${nextStepBtnId}`);
            let nextStepBtnRect;
            if (nextStepBtn) {
                nextStepBtnRect = nextStepBtn.getBoundingClientRect();
            } else {
                nextStepBtnRect = 0;
            }

            const yesRouteLastStep = step.yes.slice(-1)[0];
            const noRouteLastStep = step.no.slice(-1)[0];

            if (yesRouteLastStep?.type !== 'exit') {
                const yesRouteLastAddStepBtnId = generateAddStepBtnId(
                    step.yes.length,
                    step,
                    {
                        route: 'yes',
                    }
                );
                const lastYesBtn = document.querySelector(
                    `#${yesRouteLastAddStepBtnId}`
                );
                let lastYesBtnRect;
                if (lastYesBtn) {
                    lastYesBtnRect = lastYesBtn.getBoundingClientRect();
                } else {
                    lastYesBtnRect = 0;
                }

                this.lines.push(
                    new LeaderLine(
                        LeaderLine.pointAnchor(lastYesBtn, {
                            x: lastYesBtnRect.width / 2 + OFFSET_ERR,
                        }),
                        LeaderLine.pointAnchor(nextStepBtn, {
                            y: nextStepBtnRect.height - 15,
                        }),
                        { color: 'black', size: 2, endPlug: 'behind' }
                    ).setOptions({ startSocket: 'bottom', endSocket: 'top' })
                );
            }

            if (noRouteLastStep?.type !== 'exit') {
                const noRouteLastAddStepBtnId = generateAddStepBtnId(
                    step.no.length,
                    step,
                    {
                        route: 'no',
                    }
                );
                const lastNoBtn = document.querySelector(
                    `#${noRouteLastAddStepBtnId}`
                );
                let lastNoBtnRect;
                if (lastNoBtn) {
                    lastNoBtnRect = lastNoBtn.getBoundingClientRect();
                } else {
                    lastNoBtnRect = 0;
                }

                // connect no route to next step btn
                this.lines.push(
                    new LeaderLine(
                        LeaderLine.pointAnchor(lastNoBtn, {
                            x: lastNoBtnRect.width / 2 + OFFSET_ERR,
                        }),
                        LeaderLine.pointAnchor(nextStepBtn, {
                            y: nextStepBtnRect.height - 15,
                        }),
                        { color: 'black', size: 2, endPlug: 'behind' }
                    ).setOptions({ startSocket: 'bottom', endSocket: 'top' })
                );
            }
        },

        /**
         * @param {array} automationTriggers
         */
        connectTriggerNodes(automationTriggers) {
            if (automationTriggers.length === 0) {
                this.connectNodeAndBtn(
                    'empty-trigger-node',
                    'add-step-btn-0',
                    false
                );
                return;
            }

            automationTriggers.forEach((automationTrigger) => {
                this.connectNodeAndBtn(
                    `trigger-node-${automationTrigger.id}`,
                    'add-step-btn-0',
                    false
                );
            });
        },

        /**
         * @param {array} steps
         */
        connectStepNodes(steps, parent = {}, config = {}) {
            if (steps.length === 0) return;

            steps.forEach((step, idx) => {
                const stepType = step.type;
                const btnId = generateAddStepBtnId(idx, parent, config);
                const stepNodeId = `step-node-${step.id}`;
                const nextBtnId = generateAddStepBtnId(idx + 1, parent, config);

                this.connectNodeAndBtn(stepNodeId, btnId);
                // this.connectNodeToBtn(stepNodeId, btnId, true);

                if (stepType !== 'decision') {
                    this.connectNodeAndBtn(stepNodeId, nextBtnId, false);
                } else {
                    this.convergeDecisionStepBtns(step, nextBtnId);
                }

                // connect decision step nodes if current step is 'decision'
                this.connectDecisionStepNodes(step);
            });
        },

        connectDecisionStepNodes(step) {
            if (step.type !== 'decision') return; // automatically ignore non-decision step type

            const stepNodeId = `step-node-${step.id}`;
            const yesRouteFirstAddStepBtnId = generateAddStepBtnId(0, step, {
                route: 'yes',
            });
            const noRouteFirstAddStepBtnId = generateAddStepBtnId(0, step, {
                route: 'no',
            });
            this.connectNodeToBtn(stepNodeId, yesRouteFirstAddStepBtnId);
            this.connectNodeToBtn(stepNodeId, noRouteFirstAddStepBtnId);

            this.connectStepNodes(step.yes, step, { route: 'yes' });
            this.connectStepNodes(step.no, step, { route: 'no' });
        },

        /**
         * @param {array} steps
         */
        connectLastExitNode(steps) {
            const lastAddBtnId = `add-step-btn-${steps.length}`;
            const exitNodeId = 'last-exit-node';

            this.connectNodeAndBtn(exitNodeId, lastAddBtnId, true);
        },
        async connectLine(automationTriggers, steps) {
            // Note: $nextTick is important, especially in first mount, if we
            //       want to access the updated DOM.
            //       See: https://vuejs.org/v2/guide/reactivity.html#Async-Update-Queue
            await this.$nextTick();

            this.clearLines(); // remove all lines first
            this.connectTriggerNodes(automationTriggers);
            this.connectStepNodes(steps);
            this.connectLastExitNode(steps);
        },
    },
    mounted() {
        eventBus.$on('connect-line', () => {
            this.connectLine(this.automationTriggers, this.steps);
        });
        this.unwatch = this.$store.watch(
            ({ automations: { automationTriggers, steps } }) => ({
                automationTriggers,
                steps,
            }),
            async (newVal) => {
                this.connectLine(newVal.automationTriggers, newVal.steps);
            },
            {
                // deep: true, // uncomment if you feel deep watch is needed
                // immediate: true,
            }
        );
    },
    beforeDestroy() {
        this.unwatch();
    },
};

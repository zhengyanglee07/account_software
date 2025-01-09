export default {
    props: {
        campaigns: {
            type: Array,
            required: true,
        },
    },
    methods: {
        generateCommissionRateDesc(conditionGroups) {
            return conditionGroups.reduce((acc, cg, cgIdx) => {
                const cgPrefix = `${acc}Condition group ${cgIdx + 1}: `;

                const levelDesc = cg.levels.reduce((lvlAcc, level, lIdx) => {
                    const lvlPrefix = `${lvlAcc}level ${lIdx + 1} - `;
                    const rate =
                        level.commission_type === 'percentage'
                            ? `${level.commission_rate}%`
                            : `$${level.commission_rate}`;

                    return (
                        lvlPrefix +
                        rate +
                        (lIdx !== cg.levels.length - 1 ? '\n' : '')
                    );
                }, '');

                return `${cgPrefix}\n${levelDesc}${
                    cgIdx !== conditionGroups.length - 1 ? '\n\n' : ''
                }`;
            }, '');
        },
    },
};

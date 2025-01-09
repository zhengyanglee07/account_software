export default {
    automationRefKey: '',
    name: '',
    status: '',
    repeat: 0,
    testEmailRefKey: '',
    completedEmailIds: [],

    /**
     * The automationTrigger @type below is a simplify version of data
     * coming from database, which includes only important info,
     * such as id, type, name, etc. Note that some of the info down
     * there are flattened from pivot table. For details please refer
     * to automation_trigger pivot table
     *
     * @type {[{
     *     id: number,
     *     triggerId: number,
     *     type: string,
     *     name: string,
     *     segmentId: number|null,
     *     properties: Object
     * }]}
     */
    automationTriggers: [],

    /**
     * The step @type below is a simplify version of data
     * coming from database, which includes only important
     * info, such as id, type, name, etc.
     *
     * @type {[{
     *     id: number,
     *     type: string,
     *     kind: string,
     *     desc: string,
     *     properties: Object
     * }]}
     */
    steps: [],

    /**
     * Ordering for steps
     *
     * @type {int[]}
     */
    steps_order: [],

    /**
     * Used to store email data/info referenced by send_email's email_id,
     * such as name, subject, preview and so on. To illustrate the
     * situation please continue to the following.
     *
     * By default, send_email action step properties only store email_id,
     * and it should not store any extra info related to email. Those info
     * can be retrieved in database. So how can frontend get and store those
     * info? In here.
     *
     * Email info can be processed in the controller, to the form of
     * ```
     * {
     *     stepId1: {
     *         name: emailName1,
     *         subject: ...,
     *         preview: ...,
     *         sender_id: ...,
     *         ...
     *     },
     *     stepId2: {
     *         name: emailName2,
     *         subject: ...,
     *         ...
     *     },
     *     ...
     * }
     * ```
     *
     * Technically I named this as action entity, or action properties entity
     * to be precise, but I want to make the name to be shorter :) This naming
     * is taking reference from normalized state, where data are normalized into
     * set of entities and an array of ids. I would not call this entity as a
     * fully normalized state, since it doesn't require an ids array. This is more
     * like a simple lookup table, or key-value store depends on how you name it.
     *
     * @type {{
     *     [stepId]: {
     *         email_reference_key: number,
     *         name: string,
     *         subject: string,
     *         preview: string,
     *         sender_id: number,
     *         email_design_reference_key: number,
     *         html: string,
     *     }
     * }}
     */
    sendEmailActionsEntities: {},

    /**
     * store default actions to take only, don't mutate this
     *
     * Note: type will be deprecated not far apart from now.
     *
     * @type {[{
     *     type: 'send_email' | 'add_tag' | 'remove_tag',
     *     kind: string,
     *     name: string,
     *     description: string
     * }]}
     */
    stepActions: [
        {
            type: 'send_email',
            kind: 'automationSendEmailAction',
            name: 'Send an email',
            description: 'Send a special email to the selected person.',
        },
        {
            type: 'add_tag',
            kind: 'automationAddTagAction',
            name: 'Add tag',
            description: 'Add a given tag to the selected person.',
        },
        {
            type: 'remove_tag',
            kind: 'automationRemoveTagAction',
            name: 'Remove tag',
            description: 'Remove a given tag from the selected person.',
        },
    ],

    /**
     * @type {{
     *     type: 'trigger' | 'step'
     *     data: null | {
     *         id: number | undefined,
     *         index: number,
     *     }
     * }}
     *
     */
    modal: {
        type: '',

        /**
         * Note on data:
         * - id is undefined if a new trigger/step is to be created
         * - index should ideally be used as the insertion of new (step) node, only.
         *   Id should be prioritized for the read/update/delete of existing node
         *
         * New in 2021
         * ===========
         * - 'parent' will be provided if the step is nested, e.g. in decision
         * - 'parent' is simply the data for the parent step that containing id, desc, etc
         *
         * - 'config' is an optional field to put some extra miscellaneous info,
         *   but it's required for decision to state which route
         */
        data: null,
    },

    /**
     * @type {{
     *     id: number|null,
     *     tagName: string
     *     processed_tag_id: number
     * }}
     */
    tags: [],

    /**
     * @type {[{
     *     id: number,
     *     type: string,
     *     name: string
     * }]}
     */
    triggerOptions: [],

    /**
     * Note: landing_page_form_id === id
     *
     * @type {[{
     *     id: number|null,
     *     landing_page_form_id: number,
     *     title: string
     * }]}
     */
    landingPageForms: [],

    /**
     * Note: users_product_id === id
     *
     * @type {[{
     *     id: number|null,
     *     users_product_id: number,
     *     productTitle: string,
     *     productDescription: string
     * }]}
     */
    usersProducts: [],

    /**
     * @type {[{
     *     id: number,
     *     name: string,
     *     email_address: string,
     *     status: string
     * }]}
     */
    senders: [],

    /**
     * @type {[{
     *     id: number,
     *     segmentName: string
     * }]}
     */
    segments: [],

    customFieldNames: [],

    stepBasedStatistics: {},
};

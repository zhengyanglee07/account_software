export default () => ({
    //* builder configuration
    builderSettings: {},

    //* for landing builder only
    funnelSettings: {},

    //* for popup builder only
    popupSettings: {},
    popupConfigurationsDefault: {
        width: [650, 650, 350],
        widthUnit: ['px', 'px', 'px'],
        minHeight: ['auto', 'auto', 'auto'],
        isBackgroundOverlay: true,
        isDismissOnOverlayClick: true,
        backgroundOverlayColor: '#232323',
        closeButtonColor: '#000',
        openAnimation: 'None',
        borderRadius: 6,
    },

    //* domain to publish current page
    isDefaultPage: false,
    publishDomain: null,

    //* trigger setting panel once value !== null
    onEditElement: {
        id: null,
        title: null,
        type: 'elements',
    },

    //* toggle leftbar visibility
    isLeftPanelCollapse: false,

    //* Builder, Published
    mode: 'Published',

    //* desktop, tablet, mobile
    responsiveMode: 'desktop',

    //* landing, page, header, footer, template, popup, theme
    pageType: 'page',

    //* drag and drop
    isOnDragging: false,

    dragSource: {
        group: null,
        elementName: null,
        elementId: null,
        fromSectionId: null,
        fromColumnId: null,
        fromIndex: null,
        isInner: null,
    },

    //* Builder design
    pageDesign: {
        sections: {
            allIds: [],
        },
        columns: {},
        elements: {},
    },

    headerDesign: {
        sections: {
            allIds: [],
        },
        columns: {},
        elements: {},
    },

    footerDesign: {
        sections: {
            allIds: [],
        },
        columns: {},
        elements: {},
    },

    popupDesign: {
        sections: {
            allIds: [],
        },
        columns: {},
        elements: {},
    },

    //* images
    images: [],

    previewImageSnapshot: {
        isTaking: false,
        queryString: '',
    },

    undoRedo: {
        done: [],
        current: {},
        undone: [],
    },

    //* template
    userTemplates: [],
    generalTemplates: [],

    //* contact form related
    formDetail: {},
    differentBillingAddress: {},

    //* Theme style
    themeStyles: {},

    //* shipping
    shippingFee: 0,

    //* Update people profile
    customFields: [],
    customFieldCountLimit: 3,

    //* Tags
    tags: {},

    //* Popups
    popups: [],
});

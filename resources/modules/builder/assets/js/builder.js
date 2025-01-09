const advanced = {
    cssId: '',
    paddingTop: [0, 0, 0],
    paddingRight: [0, 0, 0],
    paddingBottom: [0, 0, 0],
    paddingLeft: [0, 0, 0],
    marginTop: [0, 0, 0],
    marginRight: [0, 0, 0],
    marginBottom: [0, 0, 0],
    marginLeft: [0, 0, 0],
    borderType: 'none',
    borderWidthTop: 1,
    borderWidthRight: 1,
    borderWidthBottom: 1,
    borderWidthLeft: 1,
    borderColor: '#000',
    radiusTopLeft: [0, 0, 0],
    radiusTopRight: [0, 0, 0],
    radiusBottomRight: [0, 0, 0],
    radiusBottomLeft: [0, 0, 0],
    backgroundImage: [null, null, null],
    backgroundColor: '',
    isVisible: [false, false, false],
    entranceAnimation: ['None', 'None', 'None'],
    horizontalOffset: 0,
    verticalOffset: 0,
    shadowBlur: 0,
    shadowSpread: 0,
    shadowColor: null,
};

const section = {
    name: 'Section',
    img: 'gap.svg',
    type: 'section',
    component: 'SectionContainerBuilder',

    isInner: false,

    columns: [],

    settings: {
        contentWidth: 1140,
        minHeight: 50,
        verticalAlign: 'center',
        horizontalAlign: 'center',
        showContainer: false,
        isReverse: [false, false, false],
    },

    styles: {
        backgroundOverlayColor: '',
        backgroundOverlayOpacity: 0.5,
        hoverBackgroundOverlayColor: null,
        hoverBackgroundOverlayOpacity: 0.5,

        topDividerType: 'none',
        topDividerColor: '#7239EA',
        topDividerWidth: [120, 120, 120],
        topDividerHeight: [20, 20, 20],
        topDividerFlip: false,
        topDividerBringToFront: false,
        topDividerInverted: false,
        bottomDividerType: 'none',
        bottomDividerColor: '#7239EA',
        bottomDividerWidth: [120, 120, 120],
        bottomDividerHeight: [20, 20, 20],
        bottomDividerFlip: false,
        bottomDividerBringToFront: false,
        bottomDividerInverted: false,
    },

    advanced: {
        ...advanced,
        paddingTop: [80, 80, 80],
        paddingRight: [10, 10, 10],
        paddingBottom: [80, 80, 80],
        paddingLeft: [10, 10, 10],
        hasParallaxEffect: false,
    },
};

const innerSection = {
    ...section,

    name: 'Inner Section',
    type: 'element',
    img: 'inner-section.svg',
    isInner: true,

    advanced: {
        ...section.advanced,
        backgroundColor: '#FFFFFF00',
        paddingTop: [20, 20, 20],
        paddingBottom: [20, 20, 20],
    },
};

const column = {
    name: 'Column',
    img: 'gap.svg',
    type: 'column',

    isInner: false,

    elements: [],

    settings: {
        col: [30, 30, 30],
        alignItems: ['flex-start', 'flex-start', 'flex-start'],
        elementGap: [20, 20, 20],
    },

    advanced: {
        ...advanced,
        paddingTop: [10, 10, 10],
        paddingRight: [10, 10, 10],
        paddingBottom: [10, 10, 10],
        paddingLeft: [10, 10, 10],
    },
};

const gap = {
    name: 'Gap',
    img: 'gap.svg',
    component: 'GapElement',
    type: 'element',

    settings: {
        height: [30, 30, 30],
    },

    advanced: {
        ...advanced,
    },
};

const product = {
    name: 'Product',
    img: 'products.svg',
    component: 'ProductsElement',
    type: 'element',

    settings: {
        //* refer theme product settings
        addProductMethod: 'all',
        alignment: ['flex-start', 'flex-start', 'flex-start'],
        selectedCategory: '0',
        orderSequence: 'None',
        orderBy: 'None',
        columnCount: [4, 4, 2],
        rowCount: [1, 1, 1],
        isShowInSlider: false,
        isPaginated: false,
        listItems: [
            {
                id: null,
                productImagePath:
                    'https://cdn.hypershapes.com/assets/product-default-image.png',
                productTitle: 'Product',
                productComparePrice: null,
                productPrice: '0.00',
                categories: [],
                saleChannels: ['funnel', 'online-store', 'mini-store'],
            },
            {
                id: null,
                productImagePath:
                    'https://cdn.hypershapes.com/assets/product-default-image.png',
                productTitle: 'Product',
                productComparePrice: null,
                productPrice: '0.00',
                categories: [],
                saleChannels: ['funnel', 'online-store', 'mini-store'],
            },
            {
                id: null,
                productImagePath:
                    'https://cdn.hypershapes.com/assets/product-default-image.png',
                productTitle: 'Product',
                productComparePrice: null,
                productPrice: '0.00',
                categories: [],
                saleChannels: ['funnel', 'online-store', 'mini-store'],
            },
            {
                id: null,
                productImagePath:
                    'https://cdn.hypershapes.com/assets/product-default-image.png',
                productTitle: 'Product',
                productComparePrice: null,
                productPrice: '0.00',
                categories: [],
                saleChannels: ['funnel', 'online-store', 'mini-store'],
            },
        ],
    },

    styles: {
        //* refer theme product styles
    },

    advanced: {
        ...advanced,
    },
};

const heading = {
    name: 'Heading',
    img: 'heading.svg',
    component: 'HeadingElement',
    type: 'element',

    settings: {
        textContent: 'Here Is Your Headline',
        headingType: 'h1',
    },

    styles: {
        textColor: '#000000',
        lineHeight: [1, 1, 1],
        fontSize: [32, 32, 28],
        fontFamily: 'Lato',
        alignment: ['left', 'left', 'left'],
        fontStyle: 'normal',
        fontWeight: 'bold',
        textDecoration: 'none',
    },

    advanced: {
        ...advanced,
    },
};

const paragraph = {
    name: 'Paragraph',
    img: 'paragraph.svg',
    component: 'ParagraphElement',
    type: 'element',

    settings: {
        textContent:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
    },
    styles: {
        alignment: ['left', 'left', 'left'],
        textColor: '#000000',
        selectedFont: 'Lato',
        fontSize: [16, 16, 16],
        fontWeight: 'normal',
        fontStyle: 'normal',
        textDecoration: 'none',
        lineHeight: [1.6, 1.6, 1.6],
    },

    advanced: {
        ...advanced,
    },
};

const imageSlider = {
    name: 'Image Slider',
    img: 'image-slider.svg',
    component: 'ImageSliderElement',
    type: 'element',

    settings: {
        listItems: [
            {
                id: 0,
                slideText: 'Slide #1',
                image: 'https://cdn.hypershapes.com/assets/placeholder.png',
                link: '',
                openInNewWindow: false,
            },
            {
                id: 1,
                slideText: 'Slide #2',
                image: 'https://cdn.hypershapes.com/assets/placeholder.png',
                link: '',
                openInNewWindow: false,
            },
            {
                id: 2,
                slideText: 'Slide #3',
                image: 'https://cdn.hypershapes.com/assets/placeholder.png',
                link: '',
                openInNewWindow: false,
            },
            {
                id: 3,
                slideText: 'Slide #4',
                image: 'https://cdn.hypershapes.com/assets/placeholder.png',
                link: '',
                openInNewWindow: false,
            },
        ],
        height: [400, 400, 400],
        heightType: 'custom height',
        heightUnit: ['px', 'px', 'px'],
    },

    advanced: {
        ...advanced,
    },
};

const faq = {
    name: 'FAQ',
    img: 'faq.svg',
    component: 'FaqElement',
    type: 'element',

    settings: {
        questionContent: 'Put a Frequently Asked Question here',
        answerContent:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
    },

    styles: {
        alignment: ['left', 'left', 'left'],

        questionFontSize: [24, 24, 20],
        questionTextColor: '#000000',
        questionFontFamily: 'Lato',
        questionFontStyle: 'normal',
        questionFontWeight: 'bold',
        questionTextDecoration: 'none',
        questionLineHeight: [1.6, 1.6, 1.6],
        questionMarginTop: [0, 0, 0],
        questionTextIndent: [10, 10, 10],

        answerFontSize: [16, 16, 16],
        answerTextColor: '#000000',
        answerFontFamily: 'Lato',
        answerFontStyle: 'normal',
        answerFontWeight: 'normal',
        answerTextDecoration: 'none',
        answerLineHeight: [1.6, 1.6, 1.6],
        answerMarginTop: [0, 0, 0],

        iconColor: '',
        iconSize: [24, 24, 24],
        priceColor: '',
        priceSize: 12,
        selectedPriceFont: 'Lato',
    },

    advanced: {
        ...advanced,
    },
};

const testimonial = {
    name: 'Testimonial',
    img: 'testimonial.svg',
    component: 'TestimonialElement',
    type: 'element',

    settings: {
        nameContent: 'John Doe',
        titleContent: 'Entrepreneur',
        textContent:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        profileLink: '',
        alignment: 'center',
        isShowImage: true,
        image: 'https://cdn.hypershapes.com/assets/placeholder.png',
        imagePosition: 'row',
    },

    styles: {
        imageSize: 60,
        borderRadius: 50,
        borderType: '',

        textColor: '#000000',
        textFontSize: [16, 16, 16],
        textLineHeight: [1.6, 1.6, 1.6],
        textFontFamily: 'Lato',
        textFontStyle: 'normal',
        textFontWeight: 'normal',
        textTextDecoration: 'none',

        nameColor: '#000000',
        nameFontSize: [16, 16, 16],
        nameLineHeight: [1.6, 1.6, 1.6],
        nameFontFamily: 'Lato',
        nameFontStyle: 'normal',
        nameFontWeight: 'normal',
        nameTextDecoration: 'none',

        titleColor: '#000000',
        titleFontSize: [16, 16, 16],
        titleLineHeight: [1.6, 1.6, 1.6],
        titleFontFamily: 'Lato',
        titleFontStyle: 'normal',
        titleFontWeight: 'normal',
        titleTextDecoration: 'none',
    },

    advanced: {
        ...advanced,
    },
};

const imageBox = {
    name: 'Image Box',
    img: 'image-box.svg',
    component: 'ImageBoxElement',
    type: 'element',

    settings: {
        image: 'https://cdn.hypershapes.com/assets/placeholder.png',
        imagePosition: ['top', 'top', 'top'],
        imageVerticalAlignment: 'center',
        imageWidth: [35, 25, 20],
        imageSpacing: [15, 15, 15],
        headingContent: 'This is the heading',
        descriptionContent:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
    },

    styles: {
        alignment: ['center', 'center', 'center'],
        headingColor: '#000000',
        headingFontFamily: 'Lato',
        headingFontSize: [25, 25, 21],
        headingLineHeight: [1.6, 1.6, 1.6],
        headingFontStyle: 'normal',
        headingFontWeight: 'normal',
        headingTextDecoration: 'none',

        descriptionFontFamily: 'Lato',
        descriptionColor: '#000000',
        descriptionFontSize: [14, 14, 14],
        descriptionLineHeight: [1.6, 1.6, 1.6],
        descriptionFontStyle: 'normal',
        descriptionFontWeight: 'normal',
        descriptionTextDecoration: 'none',

        hoverAnimation: 'None',
    },

    advanced: {
        ...advanced,
    },
};

const button = {
    name: 'Button',
    img: 'button.svg',
    component: 'ButtonElement',
    type: 'element',

    settings: {
        mainText: 'Click Here',
        subText: '',
        alignment: ['left', 'left', 'left'],
        linkType: 'anchor',
        anchorLink: '',
        urlLink: '',
        isOpenInNewWindow: false,
        upsellProductRefKey: null,
        // selectedLandingPageId: [null],
        hasIcon: false,
        iconType: null,
        iconPosition: 'before',
        iconSpacing: 5,
        targetPopup: null,
    },

    styles: {
        backgroundType: 'normal',
        gradientSecondaryBackground: '#679EFC',
        gradientType: 'Linear',
        gradientLinearAngle: 90,
        gradientPosition: 'Center Center',

        hoverBackgroundType: 'normal',
        hoverElemBackgroundColor: '',
        hoverGradientSecondaryBackground: null,
        hoverGradientType: null,
        hoverGradientLinearAngle: null,
        hoverGradientPosition: null,

        borderRadius: 5,

        elemPaddingTop: [6, 6, 6],
        elemPaddingRight: [24, 24, 24],
        elemPaddingBottom: [6, 6, 6],
        elemPaddingLeft: [24, 24, 24],

        buttonShadowColor: '#ccc',
        buttonHorizontalOffset: 0,
        buttonVerticalOffset: 0,
        buttonShadowBlur: 0,
        buttonShadowSpread: 0,

        mainTextFontSize: [16, 16, 16],
        mainTextLineHeight: [1.6, 1.6, 1.6],
        mainTextFontStyle: 'normal',
        mainTextFontWeight: 'normal',
        mainTextDecoration: 'None',

        subTextFontSize: [12, 12, 12],
        subTextLineHeight: [1.6, 1.6, 1.6],
        subTextFontFamily: 'Lato',
        subTextFontStyle: 'normal',
        subTextFontWeight: 'normal',
        subTextDecoration: 'None',
        subTextColor: '#fff',

        hoverSubTextColor: null,

        ongoingAnimation: 'None',
        ongoingAnimationSpeed: 'Normal',
        ongoingAnimationIcon: 'None',
        ongoingAnimationIconSpeed: 'Normal',
        hoverAnimation: 'None',
        hoverAnimationSpeed: 'Normal',
        hoverAnimationIcon: 'None',
        hoverAnimationIconSpeed: 'Normal',
    },

    advanced: {
        ...advanced,
    },
};

const progressBar = {
    name: 'Progress Bar',
    img: 'progress-bar.svg',
    component: 'ProgressBarElement',
    type: 'element',

    settings: {
        innerText: '',
        progressPercentage: [70, 70, 70],
    },

    styles: {
        progressColor: '',
        progressBackgroundColor: '',
        heightSliderValue: 20,
        radiusSliderValue: 0,

        innerTextColor: '',
        innerTextFontFamily: 'Lato',
        innerTextFontSize: [16, 16, 16],
        innerTextFontWeight: 'normal',
        innerTextFontStyle: 'normal',
        innerTextDecoration: 'None',
        innerTextLineHeight: [1.6, 1.6, 1.6],
    },

    advanced: {
        ...advanced,
    },
};

const siteLogo = {
    name: 'Site Logo',
    img: 'site-logo.svg',
    component: 'SiteLogoElement',
    type: 'element',
    settings: {
        alignment: ['center', 'center', 'center'],
        imageUrl: 'https://cdn.hypershapes.com/assets/placeholder.png',
    },

    styles: {
        width: [100, 100, 100],
        widthUnit: ['px', 'px', 'px'],
        hoverAnimation: 'None',
    },

    advanced: {
        ...advanced,
    },
};

const form = {
    name: 'Form',
    img: 'form.svg',
    component: 'FormElement',
    type: 'element',

    settings: {
        name: 'New Form',
        inputSize: 'input_size_sm',
        hasLabel: true,
        labelDesign: 'normal',
        hasRequiredMark: true,
        isInlineList: false,
        buttonText: 'Send',
        buttonSize: 'button_size_sm',
        buttonColumnWidth: ['100%', '100%', '100%'],
        buttonAlignment: ['justify', 'justify', 'justify'],
        redirectURL: '',
        actionAfterSubmit: ['Update People Profile'],
        redirectAfterSubmit: 'Redirect URL',
        hiddenInput: ['none', 'none', 'none'],
        successMessage: 'The form was submitted successfully',
        tags: [],
        updatePeopleProfileOptions: [
            {
                text: 'Email Address',
                value: 'email',
                index: 1,
            },
            {
                text: 'First Name',
                value: 'fname',
                index: -1,
            },
            {
                text: 'Last Name',
                value: 'lname',
                index: -1,
            },
            {
                text: 'Address - line 1',
                value: 'address1',
                index: -1,
            },
            {
                text: 'Address - line 2',
                value: 'address2',
                index: -1,
            },
            {
                text: 'Address - postcode',
                value: 'zip',
                index: -1,
            },
            {
                text: 'Address - city',
                value: 'city',
                index: -1,
            },
            {
                text: 'Address - state',
                value: 'state',
                index: -1,
            },
            {
                text: 'Address - country',
                value: 'country',
                index: -1,
            },
            {
                text: 'Gender',
                value: 'gender',
                index: -1,
            },
            {
                text: 'Birthday',
                value: 'birthday',
                index: -1,
            },
            {
                text: 'Mobile Number',
                value: 'phone_number',
                index: -1,
            },
            {
                text: 'Create New Custom Fields',
                value: 'NewCustomField',
                index: -1,
            },
            {
                text: 'Custom Fields List',
                value: 'customFieldList',
                index: null,
            },
        ],
        listItems: [
            {
                id: 0,
                label: 'Name',
                title: 'Name',
                placeholder: '',
                type: 'Text',
                required: true,
                columnWidth: 'Default',
                rows: 2,
                options: [],
                savedInputValues: '',
                inline: false,
                minDate: '',
                maxDate: '',
                minValue: '',
                maxValue: '',
            },
            {
                id: 1,
                label: 'Email',
                title: 'Email',
                placeholder: '',
                type: 'Email',
                required: true,
                columnWidth: 'Default',
                rows: 2,
                options: [],
                savedInputValues: '',
                inline: false,
                minDate: '',
                maxDate: '',
                minValue: '',
                maxValue: '',
            },
            {
                id: 2,
                label: 'Message',
                title: 'Message',
                placeholder: '',
                type: 'Textarea',
                required: true,
                columnWidth: 'Default',
                rows: 5,
                inline: false,
                minDate: '',
                options: [''],
                savedInputValues: '',
                maxDate: '',
                minValue: '',
                maxValue: '',
            },
        ],
        emailSettings: {
            receiverEmail: '',
            subject: 'Someone has opted into your form!',
            senderEmail: 'mail@myhypershapes.com',
            senderName: 'Hypershapes',
            message: 'all-fields',
        },
    },

    styles: {
        labelTextColor: null,
        labelFontFamily: 'Lato',
        labelFontSize: [14, 14, 14],
        labelFontWeight: 'normal',
        labelFontStyle: 'normal',
        labelFontDecoration: 'none',
        labelLineHeight: [1.0, 1.0, 1.0],

        fieldTextColor: null,
        fieldFontFamily: 'Lato',
        fieldFontSize: [16, 16, 16],
        fieldFontWeight: 'normal',
        fieldFontStyle: 'normal',
        fieldFontDecoration: 'none',
        fieldLineHeight: [1.6, 1.6, 1.6],
        fieldBackgroundColor: null,
        fieldBorderColor: null,
        fieldBorderWidth: 1,
        fieldBorderRadius: 5,
        fieldSpaceBetween: 10,

        buttonTextColor: null,
        buttonFontFamily: 'Lato',
        buttonFontSize: [16, 16, 16],
        buttonFontWeight: 'normal',
        buttonFontStyle: 'normal',
        buttonFontDecoration: 'none',
        buttonLineHeight: [1.6, 1.6, 1.6],
        buttonBackgroundColor: null,
        buttonPaddingTop: [12, 12, 12],
        buttonPaddingRight: [24, 24, 24],
        buttonPaddingBottom: [12, 12, 12],
        buttonPaddingLeft: [24, 24, 24],
        buttonBorderRadius: 5,
    },

    advanced: {
        ...advanced,
    },
};

const menuCart = {
    name: 'Menu Cart',
    img: 'menu-cart.svg',
    component: 'MenuCartElement',
    type: 'element',

    settings: {
        iconType: 'bi-bag',
        counterPosition: 'right',
    },

    styles: {
        alignment: ['center', 'center', 'center'],
        iconColor: '#000000',
        iconHoverColor: '#000000',
        iconSize: [20, 20, 20],

        textColor: '#000000',
        textHoverColor: '#000000',
        textIndent: [0, 0, 0],

        textFontFamily: 'Lato',
        textFontSize: [18, 18, 18],
        textFontWeight: 'normal',
        textFontStyle: 'normal',
        textFontDecoration: 'none',
        textLineHeight: [1, 1, 1],
    },

    advanced: {
        ...advanced,
    },
};

const video = {
    name: 'Video',
    img: 'video.svg',
    component: 'VideoElement',
    type: 'element',

    settings: {
        videoType: 'youtube',
        videoLink:
            'https://www.youtube.com/watch?v=0eKVizvYSUQ&ab_channel=Google',
        videoEmbed:
            '<iframe width="506" height="378" src="https://www.youtube.com/watch?v=0eKVizvYSUQ&ab_channel=Google" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
        videoAutoplay: false,
        videoMute: false,
        videoLoop: true,
        videoControl: true,
        videoBranding: false,
    },

    styles: {
        videoSticky: false,
        videoStickyPosition: 'bottom right',
        videoStickyCloseIcon: true,
    },

    advanced: {
        ...advanced,
    },
};

const list = {
    name: 'List',
    img: 'list.svg',
    component: 'ListElement',
    type: 'element',

    settings: {
        layout: ['default', 'default', 'default'],
        listItems: [
            {
                id: 0,
                text: 'List Item #1',
                icon: 'fas fa-check-circle',
                link: '',
            },
            {
                id: 1,
                text: 'List Item #2',
                icon: 'fas fa-check-circle',
                link: '',
            },
            {
                id: 2,
                text: 'List Item #3',
                icon: 'fas fa-check-circle',
                link: '',
            },
        ],
    },

    styles: {
        spaceBetween: [10, 10, 10],
        listAlignment: ['flex-start', 'flex-start', 'flex-start'],

        iconColor: '#000',
        iconHoverColor: '#000',
        iconSize: [16, 16, 16],
        iconPosition: 'initial',

        textColor: null,
        textHoverColor: null,
        textIndent: [0, 0, 0],

        textFontSize: [16, 16, 16],
        textLineHeight: [1.6, 1.6, 1.6],
        textFontFamily: 'Lato',
        textFontStyle: 'normal',
        textFontWeight: 'normal',
        textDecoration: 'None',
    },

    advanced: {
        ...advanced,
    },
};

const socialIcon = {
    name: 'Social Icon',
    img: 'social-icon.svg',
    component: 'SocialIconElement',
    type: 'element',

    settings: {
        listItems: [
            {
                id: 0,
                icon: 'fab fa-facebook',
                text: 'Facebook',
                link: '',
            },
            {
                id: 1,
                icon: 'fab fa-twitter',
                text: 'Twitter',
                link: '',
            },
            {
                id: 2,
                icon: 'fab fa-youtube',
                text: 'Youtube',
                link: '',
            },
            {
                id: 3,
                icon: 'fab fa-instagram',
                text: 'Instagram',
                link: '',
            },
        ],
        borderRadius: '100%',
        alignment: ['flex-start', 'flex-start', 'flex-start'],
    },

    styles: {
        buttonSize: [50, 50, 50],
        iconFontSize: [2, 2, 2],
        spacing: [10, 10, 10],
    },

    advanced: {
        ...advanced,
    },
};

const image = {
    name: 'Image',
    img: 'image.svg',
    component: 'ImageElement',
    type: 'element',

    settings: {
        link: '',
        isOpenInNewWindow: false,
        clickActionType: 'redirect-url',
        targetPopup: null,
        alignment: ['center', 'center', 'center'],
        imageUrl: 'https://cdn.hypershapes.com/assets/placeholder.png',
    },

    styles: {
        width: [100, 100, 100],
        widthUnit: ['%', '%', '%'],
        borderRadius: 0,
        horizontalOffset: 0,
        verticalOffset: 0,
        shadowBlur: 0,
        shadowSpread: 0,
        shadowColor: null,

        ongoingAnimation: 'None',
        ongoingAnimationSpeed: 'Normal',
        hoverAnimation: 'None',
        hoverAnimationSpeed: 'Normal',
    },

    advanced: {
        ...advanced,
    },
};

const twoStepForm = {
    name: 'Two Step Form',
    img: 'order-form.svg',
    component: 'TwoStepFormElement',
    type: 'element',

    settings: {
        selectedProduct: null,
        headline1: 'personal detail',
        subHeadline1: 'Where To Ship',
        buttonFooterText1: 'We Respect Your Privacy & Information',
        hasFullName: true,
        hasPhoneNumber: true,
        hasCompanyName: false,
        hasBillingAddress: true,

        headline2: 'your order summary',
        subHeadline2: 'Your Order Info',
        buttonFooterText2: '100% Secure & Safe Payments',

        redirectURL: '',
        actionAfterSubmit: 'Next Step In Funnel',
        redirectAfterSubmit: 'Redirect URL',
        step: 'step1',
        buttonText1: 'Go To Step #2',
        buttonText2: 'checkout now',
    },

    styles: {
        borderColor: '',

        buttonColor1: '',
        buttonTextColor1: '',
        buttonFontFamily1: 'Lato',
        buttonFontSize1: [14, 14, 14],
        buttonLineHeight1: ['1.6', '1.6', '1.6'],
        buttonFontWeight1: 'normal',
        buttonFontStyle1: 'normal',
        buttonFontDecoration1: 'none',

        buttonColor2: '',
        buttonTextColor2: '',
        buttonFontFamily2: 'Lato',
        buttonFontSize2: [14, 14, 14],
        buttonLineHeight2: ['1.6', '1.6', '1.6'],
        buttonFontWeight2: 'normal',
        buttonFontStyle2: 'normal',
        buttonFontDecoration2: 'none',
    },

    advanced: {
        ...advanced,
    },
};

const orderSummary = {
    name: 'Order Summary',
    img: 'order-summary.svg',
    component: 'OrderSummaryElement',
    type: 'element',

    settings: {
        productImagePath: '',
        productPrice: '',
    },
    advanced: {
        ...advanced,
    },
};

const shareButton = {
    name: 'Share Button',
    img: 'share-button.svg',
    component: 'ShareButtonElement',
    type: 'element',

    settings: {
        listItems: [
            {
                id: 0,
                icon: 'fab fa-facebook',
                text: 'Facebook',
            },
            {
                id: 1,
                icon: 'fab fa-twitter',
                text: 'Twitter',
            },
            {
                id: 2,
                icon: 'fab fa-whatsapp',
                text: 'Whatsapp',
            },
            {
                id: 3,
                icon: 'fab fa-envelope',
                text: 'Email',
            },
        ],
        buttonShape: '0px',
        gridColumns: ['Auto', 'Auto', 'Auto'],
        alignment: ['flex-start', 'flex-start', 'flex-start'],
        targetUrlMode: 'custom',
        customLink: '',
    },

    styles: {
        buttonSize: [50, 50, 50],
        iconFontSize: [1.9, 1.9, 1.9],
        spacing: [2, 2, 2],
        verticalSpacing: [0, 0, 0],
    },

    advanced: {
        ...advanced,
    },
};

const customerAccount = {
    name: 'Customer Account',
    img: 'image-slider.svg',
    component: 'CustomerAccountElement',
    type: 'element',

    settings: {
        layout: 'icon',
        icon: 'fa-user',
        alignment: ['center', 'center', 'center'],
    },

    styles: {
        iconSize: [20, 20, 20],
        textFontFamily: 'Lato',
        textFontSize: [16, 16, 16],
        textFontWeight: 'normal',
        textFontStyle: 'normal',
        textFontDecoration: 'none',
        textLineHeight: [1, 1, 1],
        textColor: '',
    },

    advanced: {
        ...advanced,
    },
};

const searchForm = {
    name: 'Search Form',
    img: 'search-form.svg',
    component: 'SearchFormElement',
    type: 'element',

    settings: {
        placeholder: 'Search',
        layout: 'horizontal',
        breakpoint: 'all',
        align: ['center', 'center', 'center'],
    },

    styles: {
        iconColor: '',
        iconSize: [20, 20, 20],

        textColor: '',
        textIndent: [10, 10, 10],

        inputFontFamily: 'Lato',
        inputFontSize: [16, 16, 16],
        inputFontWeight: 'normal',
        inputFontStyle: 'normal',
        inputFontDecoration: 'none',
        inputLineHeight: [1.6, 1.6, 1.6],

        width: [100, 100, 100],

        inputBackgroundColor: '',
        borderColor: '#E0E0E0',
        borderRadius: 30,
        borderWidth: 1,
    },

    advanced: {
        ...advanced,
    },
};

const currencyDropdown = {
    name: 'Currency Dropdown',
    img: 'currency-dropdown.svg',
    component: 'CurrencyDropdownElement',
    type: 'element',

    settings: {
        showFlag: false,
    },

    styles: {
        fontSize: [16, 16, 16],
    },

    advanced: {
        ...advanced,
    },
};

const navMenu = {
    name: 'Nav Menu',
    img: 'nav-menu.svg',
    component: 'NavMenuElement',
    type: 'element',

    settings: {
        menuId: '',
        layout: 'horizontal',
        alignment: 'center',
        pointer: 'none',
        breakpoint: 'mobile',
        sideBarAlign: 'left',
        toggleAlign: ['flex-start 1', 'center 1', 'flex-end 1'],
    },

    styles: {
        mainMenuFontFamily: 'Lato',
        mainMenuFontSize: [16, 16, 16],
        mainMenuFontWeight: 'normal',
        mainMenuFontStyle: 'normal',
        mainMenuFontDecoration: 'none',
        mainMenuLineHeight: [1.6, 1.6, 1.6],

        textColor: '',
        textHoverColor: '',
        pointerHoverColor: '',

        horizontalPadding: [10, 10, 10],
        verticalPadding: [5, 5, 5],

        dropdownTextColor: '',
        dropdownTextHoverColor: '',
        dropdownBackgroundColor: '',
        dropdownBackgroundHoverColor: '',

        dropdownFontFamily: 'Inter',
        dropdownFontSize: [16, 16, 16],
        dropdownFontStyle: 'normal',
        dropdownFontWeight: 'normal',
        dropdownFontDecoration: 'none',
        dropdownLineHeight: [1.6, 1.6, 1.6],

        toggleButtonColor: '',
        toggleButtonHoverColor: '',
        toggleButtonBackgroundColor: '',
        toggleButtonBackgroundHoverColor: '',
        toggleButtonSize: [15, 15, 15],
    },

    advanced: {
        ...advanced,
    },
};

const countdown = {
    name: 'Countdown',
    img: 'countdown.svg',
    component: 'CountdownElement',
    type: 'element',

    settings: {
        isEvergreen: true,
        dueAt: null,
        durationHours: 0,
        durationMinutes: 1,
        isDayVisible: true,
        isHourVisible: true,
        isMinuteVisible: true,
        isSecondVisible: true,

        actionType: 'None',
        redirectLink: '',
        isOpenInNewTab: false,

        revisitAction: 'auto-reset-timer',
        cookiesExpirationDays: 1,
    },

    styles: {
        containerWidth: [100, 100, 100],
        backgroundColor: null,
        spaceBetween: [10, 10, 10],

        digitColor: null,
        digitFontFamily: 'Lato',
        digitFontWeight: 'normal',
        digitFontSize: [70, 30, 20],
        digitFontStyle: 'normal',
        digitFontDecoration: 'none',
        digitLineHeight: [1.6, 1.6, 1.6],

        labelColor: null,
        labelFontFamily: 'Lato',
        labelFontSize: [25, 20, 15],
        labelFontWeight: 'normal',
        labelFontStyle: 'normal',
        labelFontDecoration: 'none',
        labelLineHeight: [1.6, 1.6, 1.6],
    },

    advanced: {
        ...advanced,
    },
};

// const oneStepForm = {
//     name: 'One Step Form',
//     img: '1-step-form.svg',
//     component: 'OneStepFormElement',
//     type: 'element',

//     settings: {
//         buttonFooterText: 'We Respect Your Privacy & Information',
//         fullName: true,
//         companyName: false,
//         phoneNumber: true,
//         billingAddress: true,
//         selectedProduct: null,
//         redirectURL: '',
//         actionAfterSubmit: 'Next Step In Funnel',
//     },

//     styles: {
//         buttonText: 'Complete Order',
//         buttonColor: null,
//         buttonTextColor: null,
//         fontFamily: 'Default',
//         fontSize: [14, 14, 14],
//         fontWeight: 'normal',
//         fontStyle: 'normal',
//         fontDecoration: 'none',
//         lineHeight: [1.6, 1.6, 1.6],
//     },

//     advanced: {
//         ...advanced,
//     },
// };

/**
 * Mini Store elements
 */
// const miniStoreName = {
//     name: 'Mini Store Name',
//     img: 'heading.svg',
//     component: 'MiniStoreNameElement',
//     type: 'element',

//     styles: {
//         cssId: '',
//         paddingTop: 0,
//         paddingRight: 0,
//         paddingBottom: 0,
//         paddingLeft: 0,
//         marginTop: 0,
//         marginRight: 0,
//         marginBottom: 0,
//         marginLeft: 0,
//         radiusTopLeft: 0,
//         radiusTopRight: 0,
//         radiusBottomRight: 0,
//         radiusBottomLeft: 0,
//         backgroundImage: null,
//         backgroundColor: '',
//         isVisible: false,
//         entranceAnimation: 'None',
//     },

//     advanced: {
//         ...advanced,
//     },
// };

// const miniStoreDescription = {
//     name: 'Mini Store Description',
//     img: 'paragraph.svg',
//     component: 'MiniStoreDescriptionElement',
//     type: 'element',

//     styles: {
//         cssId: '',
//         paddingTop: 0,
//         paddingRight: 0,
//         paddingBottom: 0,
//         paddingLeft: 0,
//         marginTop: 0,
//         marginRight: 0,
//         marginBottom: 0,
//         marginLeft: 0,
//         radiusTopLeft: 0,
//         radiusTopRight: 0,
//         radiusBottomRight: 0,
//         radiusBottomLeft: 0,
//         backgroundImage: null,
//         backgroundColor: '',
//         isVisible: false,
//         entranceAnimation: 'None',
//     },

//     advanced: {
//         ...advanced,
//     },
// };

// const miniStoreSiteLogo = {
//     name: 'Mini Store Site Logo',
//     img: 'site-logo.svg',
//     component: 'MiniStoreSiteLogoElement',
//     type: 'element',

//     styles: {
//         cssId: '',
//         paddingTop: 0,
//         paddingRight: 0,
//         paddingBottom: 0,
//         paddingLeft: 0,
//         marginTop: 0,
//         marginRight: 0,
//         marginBottom: 0,
//         marginLeft: 0,
//         radiusTopLeft: 0,
//         radiusTopRight: 0,
//         radiusBottomRight: 0,
//         radiusBottomLeft: 0,
//         backgroundImage: null,
//         backgroundColor: '',
//         isVisible: false,
//         entranceAnimation: 'None',
//     },

//     advanced: {
//         ...advanced,
//     },
// };

// const miniStorePreviousIcon = {
//     name: 'Previous Icon',
//     img: 'site-logo.svg',
//     component: 'MiniStorePreviousIconElement',
//     type: 'element',

//     styles: {
//         cssId: '',
//         paddingTop: 0,
//         paddingRight: 0,
//         paddingBottom: 0,
//         paddingLeft: 0,
//         marginTop: 0,
//         marginRight: 0,
//         marginBottom: 0,
//         marginLeft: 0,
//         radiusTopLeft: 0,
//         radiusTopRight: 0,
//         radiusBottomRight: 0,
//         radiusBottomLeft: 0,
//         backgroundImage: null,
//         backgroundColor: '',
//         isVisible: false,
//         entranceAnimation: 'None',

//         advanced: {
//             ...advanced,
//         },
//     }
// };

// const categoryProductList = {
//     name: 'Category Product List',
//     img: 'products.svg',
//     component: 'categoryProductListElement',
//     type: 'element',
//     settings: {
//         orderSequence: 'None',
//         orderBy: 'None',
//         columnCount: 4,
//         rowCount: 1,
//         priceHide: true,

//         listItems: [
//             {
//                 id: null,
//                 productImagePath:
//                     '/src/shared/assets/product-default-image.png',
//                 productTitle: 'Product',
//                 productComparePrice: '0.00',
//                 productPrice: '0.00',
//                 categories: [],
//             },
//             {
//                 id: null,
//                 productImagePath:
//                     '/src/shared/assets/product-default-image.png',
//                 productTitle: 'Product',
//                 productComparePrice: '0.00',
//                 productPrice: '0.00',
//                 categories: [],
//             },
//             {
//                 id: null,
//                 productImagePath:
//                     '/src/shared/assets/product-default-image.png',
//                 productTitle: 'Product',
//                 productComparePrice: '0.00',
//                 productPrice: '0.00',
//                 categories: [],
//             },
//             {
//                 id: null,
//                 productImagePath:
//                     '/src/shared/assets/product-default-image.png',
//                 productTitle: 'Product',
//                 productComparePrice: '0.00',
//                 productPrice: '0.00',
//                 categories: [],
//             },
//         ],
//     },
//     style: {
//         titleColor: '',
//         titleFontFamily: 'Default',
//         titleFontSize: 16,
//         titleFontStyle: 'normal',
//         titleFontWeight: 'normal',
//         titleFontDecoration: 'None',
//         titleLineHeight: 1.6,

//         priceColor: '',
//         priceFontFamily: 'Default',
//         priceFontSize: 16,
//         priceFontStyle: 'normal',
//         priceFontWeight: 'normal',
//         priceFontDecoration: 'None',
//         priceLineHeight: 1.6,

//         regPriceColor: '',
//         regPriceFontFamily: 'Default',
//         regPriceFontSize: 16,
//         regPriceFontStyle: 'normal',
//         regPriceFontWeight: 'normal',
//         regPriceFontDecoration: 'line through',
//         regPriceLineHeight: 1.6,

//         hoverEffect: 'None',
//     },

//     advanced: {
//         ...advanced,
//     },
// };

// const locationSummary = {
//     name: 'Location Summary',
//     img: 'gap.svg',
//     component: 'MiniStoreLocationSummaryElement',
//     type: 'element',

//     advanced: {
//         ...advanced,
//     },
// };

/**
 * Webinar elements
 */
// const webinarCountdown = {
//     name: 'Webinar Countdown',
//     img: 'countdown.svg',
//     component: 'CountdownElement',
//     type: 'element',

//     settings: {
//         isEvergreen: true,
//         dueAt: null,
//         durationHours: 0,
//         durationMinutes: 1,
//         isDayVisible: true,
//         isHourVisible: true,
//         isMinuteVisible: true,
//         isSecondVisible: true,

//         actionType: 'None',
//         redirectLink: '',
//         isOpenInNewTab: false,

//         revisitAction: 'Auto Reset Timer',
//         cookiesExpirationDays: 1,
//     },

//     styles: {
//         containerWidth: [100, 100, 100],
//         backgroundColor: null,
//         spaceBetween: [10, 10, 10],

//         digitColor: null,
//         digitFontFamily: 'Default',
//         digitFontWeight: 'normal',
//         digitFontSize: [70, 30, 20],
//         digitFontStyle: 'normal',
//         digitFontDecoration: 'none',
//         digitLineHeight: [1.6, 1.6, 1.6],

//         labelColor: null,
//         labelFontFamily: 'Default',
//         labelFontSize: [25, 20, 15],
//         labelFontWeight: 'normal',
//         labelFontStyle: 'normal',
//         labelFontDecoration: 'none',
//         labelLineHeight: [1.6, 1.6, 1.6],
//     },

//     advanced: {
//         ...advanced,
//     },
// };

// const webinarRegistrationForm = {
//     name: 'Webinar Registration Form',
//     img: 'form.svg',
//     component: 'FormElement',
//     type: 'element',

//     settings: {
//         name: 'New Form',
//         inputSize: 'input_size_sm',
//         hasLabel: true,
//         labelDesign: 'normal',
//         hasRequiredMark: true,
//         isInlineList: false,
//         buttonText: 'Send',
//         buttonSize: 'button_size_sm',
//         buttonColumnWidth: ['100%', '100%', '100%'],
//         buttonAlignment: ['justify', 'justify', 'justify'],
//         redirectURL: '',
//         actionAfterSubmit: ['Update People Profile'],
//         redirectAfterSubmit: 'Redirect URL',
//         hiddenInput: ['none', 'none', 'none'],
//         updatePeopleProfileOptions: [
//             {
//                 text: 'Email Address',
//                 value: 'email',
//                 selected: true,
//                 index: 1,
//             },
//             {
//                 text: 'First Name',
//                 value: 'fname',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Last Name',
//                 value: 'lname',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Address - line 1',
//                 value: 'address1',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Address - line 2',
//                 value: 'address2',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Address - postcode',
//                 value: 'zip',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Address - city',
//                 value: 'city',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Address - state',
//                 value: 'state',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Address - country',
//                 value: 'country',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Gender',
//                 value: 'gender',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Birthday',
//                 value: 'birthday',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Mobile Number',
//                 value: 'phone_number',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Create New Custom Fields',
//                 value: 'NewCustomField',
//                 selected: false,
//                 index: -1,
//             },
//             {
//                 text: 'Custom Fields List',
//                 value: 'customFieldList',
//                 selected: true,
//                 index: -1,
//             },
//         ],
//         listItems: [
//             {
//                 id: 0,
//                 label: 'Name',
//                 title: 'Name',
//                 placeholder: '',
//                 type: 'Text',
//                 required: true,
//                 columnWidth: 'Default',
//                 rows: 2,
//                 options: [],
//                 savedInputValues: '',
//                 inline: false,
//                 minDate: '',
//                 maxDate: '',
//                 minValue: '',
//                 maxValue: '',
//             },
//             {
//                 id: 1,
//                 label: 'Email',
//                 title: 'Email',
//                 placeholder: '',
//                 type: 'Email',
//                 required: true,
//                 columnWidth: 'Default',
//                 rows: 2,
//                 options: [],
//                 savedInputValues: '',
//                 inline: false,
//                 minDate: '',
//                 maxDate: '',
//                 minValue: '',
//                 maxValue: '',
//             },
//             {
//                 id: 2,
//                 label: 'Message',
//                 title: 'Message',
//                 placeholder: '',
//                 type: 'Textarea',
//                 required: true,
//                 columnWidth: 'Default',
//                 rows: 5,
//                 inline: false,
//                 minDate: '',
//                 options: [''],
//                 savedInputValues: '',
//                 maxDate: '',
//                 minValue: '',
//                 maxValue: '',
//             },
//         ],
//         emailSettings: {
//             receiverEmail: '',
//             subject: 'Someone has opted into your form!',
//             senderEmail: 'mail@myhypershapes.com',
//             senderName: 'Hypershapes',
//             message: 'all-fields',
//         },
//     },

//     styles: {
//         labelTextColor: null,
//         labelFontFamily: 'Default',
//         labelFontSize: [16, 16, 16],
//         labelFontWeight: 'normal',
//         labelFontStyle: 'normal',
//         labelFontDecoration: 'none',
//         labelLineHeight: [1.0, 1.0, 1.0],

//         fieldTextColor: null,
//         fieldFontFamily: 'Default',
//         fieldFontSize: [16, 16, 16],
//         fieldFontWeight: 'normal',
//         fieldFontStyle: 'normal',
//         fieldFontDecoration: 'none',
//         fieldLineHeight: [1.6, 1.6, 1.6],
//         fieldBackgroundColor: null,
//         fieldBorderColor: null,
//         fieldBorderWidth: 1,

//         buttonTextColor: null,
//         buttonFontFamily: 'Default',
//         buttonFontSize: [16, 16, 16],
//         buttonFontWeight: 'normal',
//         buttonFontStyle: 'normal',
//         buttonFontDecoration: 'none',
//         buttonLineHeight: [1.6, 1.6, 1.6],
//         buttonBackgroundColor: null,
//         buttonPaddingTop: [12, 12, 12],
//         buttonPaddingRight: [24, 24, 24],
//         buttonPaddingBottom: [12, 12, 12],
//         buttonPaddingLeft: [24, 24, 24],
//     },

//     advanced: {
//         ...advanced,
//     },
// };

// const webinarSummary = {
//     name: 'Webinar Summary',
//     img: 'order-summary.svg',
//     component: 'OrderSummaryElement',
//     type: 'element',

//     advanced: {
//         ...advanced,
//     },
// }

const referralLink = {
    name: 'Referral Link',
    img: 'referral-link-icon.svg',
    component: 'ReferralLinkElement',
    type: 'element',

    advanced: {
        ...advanced,
    },
};
const reward = {
    name: 'Reward',
    img: 'referral-reward-icon.svg',
    component: 'rewardElement',
    type: 'element',

    settings: {
        rewards: [
            {
                id: 0,
                label: 'Promo Code',
                type: 'unlock',
                imagePath: '',
                rewardType: '',
            },
            {
                id: 1,
                label: 'Custom Link',
                type: 'unlock',
                imagePath: '',
                rewardType: '',
            },
        ],
    },

    styles: {
        backgroundType: 'normal',
        elemBackgroundColor: '',
        gradientSecondaryBackground: '#679EFC',
        gradientType: 'Linear',
        gradientLinearAngle: 90,
        gradientPosition: 'Center Center',

        hoverBackgroundType: 'normal',
        hoverElemBackgroundColor: '',
        hoverGradientSecondaryBackground: null,
        hoverGradientType: null,
        hoverGradientLinearAngle: null,
        hoverGradientPosition: null,

        borderRadius: 5,

        elemPaddingTop: [8, 8, 8],
        elemPaddingRight: [16, 16, 16],
        elemPaddingBottom: [8, 8, 8],
        elemPaddingLeft: [16, 16, 16],

        buttonShadowColor: '#ccc',
        buttonHorizontalOffset: 0,
        buttonVerticalOffset: 0,
        buttonShadowBlur: 0,
        buttonShadowSpread: 0,

        mainTextFontSize: [12, 12, 12],
        mainTextLineHeight: [1.6, 1.6, 1.6],
        mainTextFontFamily: 'Lato',
        mainTextFontStyle: 'normal',
        mainTextFontWeight: 'normal',
        mainTextDecoration: 'None',
        mainTextColor: '#fff',

        hoverMainTextColor: null,

        ongoingAnimation: 'None',
        ongoingAnimationSpeed: 'Normal',
        hoverAnimation: 'None',
        hoverAnimationSpeed: 'Normal',
    },

    advanced: {
        ...advanced,
    },
};

const action = {
    name: 'Action',
    img: 'referral-action-icon.svg',
    component: 'ActionElement',
    type: 'element',

    settings: {
        isInviterActionHidden: false,
        isInviteeActionHidden: false,

        actions: [
            {
                id: 0,
                label: 'Join this referral campaign',
                type: 'join',
                imagePath: '',
                imgType: 'sign-up',
                referralType: 'inviter',
            },
            {
                id: 1,
                label: 'Refer a new sign up',
                type: 'sign-up',
                imagePath: '',
                imgType: 'sign-up',
                referralType: 'inviter',
            },
            {
                id: 2,
                label: 'Refer a new customer',
                type: 'purchase',
                imagePath: '',
                imgType: 'customer',
                referralType: 'inviter',
            },
            {
                id: 3,
                label: 'Become a new sign up',
                type: 'sign-up',
                imagePath: '',
                imgType: 'sign-up',
                referralType: 'invitee',
            },
            {
                id: 4,
                label: 'Become a new customer',
                type: 'purchase',
                imagePath: '',
                imgType: 'customer',
                referralType: 'invitee',
            },
            {
                id: 5,
                label: 'Custom Actions',
                type: 'custom',
                imagePath: '',
                imgType: 'go',
                referralType: 'inviter',
            },
        ],
    },

    styles: {
        backgroundType: 'normal',
        elemBackgroundColor: '',
        gradientSecondaryBackground: '#679EFC',
        gradientType: 'Linear',
        gradientLinearAngle: 90,
        gradientPosition: 'Center Center',

        hoverBackgroundType: 'normal',
        hoverElemBackgroundColor: '',
        hoverGradientSecondaryBackground: null,
        hoverGradientType: null,
        hoverGradientLinearAngle: null,
        hoverGradientPosition: null,

        borderRadius: 5,

        elemPaddingTop: [8, 8, 8],
        elemPaddingRight: [16, 16, 16],
        elemPaddingBottom: [8, 8, 8],
        elemPaddingLeft: [16, 16, 16],

        buttonShadowColor: '#ccc',
        buttonHorizontalOffset: 0,
        buttonVerticalOffset: 0,
        buttonShadowBlur: 0,
        buttonShadowSpread: 0,

        mainTextFontSize: [12, 12, 12],
        mainTextLineHeight: [1.6, 1.6, 1.6],
        mainTextFontFamily: 'Lato',
        mainTextFontStyle: 'normal',
        mainTextFontWeight: 'normal',
        mainTextDecoration: 'None',
        mainTextColor: '#fff',

        hoverMainTextColor: null,

        ongoingAnimation: 'None',
        ongoingAnimationSpeed: 'Normal',
        hoverAnimation: 'None',
        hoverAnimationSpeed: 'Normal',
    },

    advanced: {
        ...advanced,
    },
};

export {
    siteLogo,
    navMenu,
    menuCart,
    searchForm,
    customerAccount,
    currencyDropdown,
    section,
    innerSection,
    column,
    heading,
    paragraph,
    button,
    image,
    form,
    imageBox,
    imageSlider,
    video,
    gap,
    countdown,
    list,
    socialIcon,
    shareButton,
    faq,
    testimonial,
    progressBar,
    product,
    twoStepForm,
    // oneStepForm,
    orderSummary,
    // locationSummary,
    referralLink,
    reward,
    action,
};

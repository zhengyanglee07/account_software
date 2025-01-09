import { mapGetters, mapState } from 'vuex';
import { camelizeString } from '@builder/store/helpers.js';

export default {
    inheritAttrs: false,
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
        name: {
            type: String,
            required: true,
        },
        settings: {
            type: Object,
            default: () => ({}),
        },
        styles: {
            type: Object,
            default: () => ({}),
        },
        advanced: {
            type: Object,
            required: true,
        },
        elementWidth: {
            type: Number,
            default: 760,
        },
        sectionType: {
            type: String,
            default: 'page',
        },
    },

    data() {
        return {
            DOMUpdate: true,
        };
    },

    // updated() {
    //     // avoid entrance animation are paused when new elements added
    //     this.DOMUpdate = true;
    // },

    mounted() {
        if (this.mode === 'Builder') this.DOMUpdate = true;
    },

    computed: {
        ...mapState('builder', ['mode']),

        ...mapGetters('builder', ['settingIndex']),

        isInBuilderMode() {
            return this.mode === 'Builder';
        },

        // * Refer https://animate.style/ for respective class names
        entranceAnimation() {
            let animation =
                this.advanced.entranceAnimation?.[this.settingIndex];
            if (animation === 'None') return '';
            switch (animation) {
                case 'RubberBand':
                    animation = 'rubberBand';
                    break;
                case 'Shake':
                    animation = 'shakeX';
                    break;
                case 'Light Speed In':
                    animation = 'lightSpeedInRight';
                    break;
                default:
                    animation = camelizeString(animation);
                    break;
            }
            return `animate__animated ${
                this.DOMUpdate ? '' : 'animate__paused'
            } animate__${animation}`;
        },

        generalAttributes() {
            const index = this.settingIndex;
            const {
                cssId,
                paddingTop = [0, 0, 0],
                paddingRight = [0, 0, 0],
                paddingBottom = [0, 0, 0],
                paddingLeft = [0, 0, 0],
                marginTop = [0, 0, 0],
                marginRight = [0, 0, 0],
                marginBottom = [0, 0, 0],
                marginLeft = [0, 0, 0],
                backgroundImage,
                backgroundColor,
                isVisible,
                radiusTopLeft = [0, 0, 0],
                radiusTopRight = [0, 0, 0],
                radiusBottomRight = [0, 0, 0],
                radiusBottomLeft = [0, 0, 0],
                horizontalOffset = 0,
                verticalOffset = 0,
                shadowBlur = 0,
                shadowSpread = 0,
                shadowColor = 'white',
                borderType,
                borderWidthTop = 1,
                borderWidthRight = 1,
                borderWidthBottom = 1,
                borderWidthLeft = 1,
                borderColor,
            } = this.advanced;
            return {
                id: cssId,
                class: [
                    'elem-container',
                    this.entranceAnimation,
                    {
                        'd-none': !this.isInBuilderMode
                            ? isVisible[index]
                            : false,
                    },
                ],
                style: {
                    backgroundColor,
                    backgroundImage: `url('${backgroundImage?.[index]}')`,
                    backgroundPosition: 'center',
                    backgroundSize: 'cover',
                    borderRadius: `
                        ${radiusTopLeft[index]}px
                        ${radiusTopRight[index]}px
                        ${radiusBottomRight[index]}px
                        ${radiusBottomLeft[index]}px
                    `,
                    margin: `
                        ${marginTop[index]}px
                        ${marginRight[index]}px
                        ${marginBottom[index]}px
                        ${marginLeft[index]}px
                    `,
                    padding: `
                        ${paddingTop[index]}px
                        ${paddingRight[index]}px
                        ${paddingBottom[index]}px
                        ${paddingLeft[index]}px
                    `,
                    boxShadow: `
                        ${horizontalOffset}px 
                        ${verticalOffset}px 
                        ${shadowBlur}px 
                        ${shadowSpread}px 
                        ${shadowColor ?? '#fff'}
                     `,
                    borderWidth: `
                        ${borderWidthTop}px
                        ${borderWidthRight}px
                        ${borderWidthBottom}px
                        ${borderWidthLeft}px
                    `,
                    borderColor,
                    borderStyle: `
                        ${borderType}
                    `,
                },
            };
        },
    },
};

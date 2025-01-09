<template>
  <Component
    :is="element"
    class="btn primary-button hover-animation"
    :class="[
      `hover__${kebabCaseLize(
        buttonSettings?.styles?.hoverAnimation ?? 'None'
      )}`,
      `ongoing__${kebabCaseLize(
        buttonSettings?.styles?.ongoingAnimation ?? 'None'
      )}`,
    ]"
    :style="{ ...buttonStyles, ...hoverButtonStyles }"
    :disabled="disableBtn"
    :href="mode === 'Builder' ? 'javascript:void(0)' : href"
    :target="href && isOpenInNewTab && mode !== 'Builder' ? '_blank' : null"
    @mouseenter="hoverButton = true"
    @mouseleave="hoverButton = false"
  >
    <span :style="mainTextStyles">
      <slot name="btn-text"> Earn points now </slot>
    </span>
  </Component>
</template>

<script>
import { computed, ref } from 'vue';
import { useStore } from 'vuex';
import { kebabCaseLize } from '@builder/store/helpers.js';
import { action } from '@builder/assets/js/builder.js';

export default {
  props: {
    buttonSettings: {
      type: Object,
      default: null,
    },
    buttonIndex: {
      type: Number,
      default: 0,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    href: {
      type: String,
      default: null,
    },
    isOpenInNewTab: {
      type: Boolean,
      default: true,
    },
  },

  setup(props) {
    const { styles: defaultStyles } = action;

    const disableBtn = ref(false);

    const store = useStore();

    const settingIndex = computed(() => store.getters['builder/settingIndex']);

    const mode = computed(() => store.state.builder.mode);

    const hoverButton = ref(false);
    const element = computed(() => {
      if (!props.href) return 'button';
      return 'a';
    });

    const buttonBackground = computed(() => {
      const s = props.buttonSettings?.styles ?? defaultStyles;
      const {
        backgroundType = 'normal',
        elemBackgroundColor,
        gradientSecondaryBackground,
        gradientLinearAngle,
        gradientPosition,
        gradientType,
      } = defaultStyles;

      if ((s.backgroundType || backgroundType) === 'normal')
        return s.elemBackgroundColor || elemBackgroundColor;

      const colorGradient = `
        ${s.elemBackgroundColor || elemBackgroundColor} 0%,
        ${s.gradientSecondaryBackground || gradientSecondaryBackground} 100%
      `;

      if ((s.gradientType || gradientType) === 'Linear')
        return `linear-gradient(${
          s.gradientLinearAngle || gradientLinearAngle
        }deg, ${colorGradient})`;

      return `radial-gradient(at ${(
        s.gradientPosition || gradientPosition
      )?.toLowerCase()}, ${colorGradient})`;
    });

    const hoverButtonBackground = computed(() => {
      const s = props.buttonSettings?.styles ?? defaultStyles;
      const {
        hoverBackgroundType,
        hoverElemBackgroundColor,
        hoverGradientSecondaryBackground,
        hoverGradientLinearAngle,
        hoverGradientPosition,
        hoverGradientType,
      } = defaultStyles;

      if ((s.hoverBackgroundType || hoverBackgroundType) === 'normal')
        return s.hoverElemBackgroundColor;

      const colorGradient = `
        ${s.hoverElemBackgroundColor || hoverElemBackgroundColor} 0%,
        ${
          s.hoverGradientSecondaryBackground || hoverGradientSecondaryBackground
        } 100%
      `;

      if ((s.hoverGradientType || hoverGradientType) === 'Linear')
        return `linear-gradient(${
          s.hoverGradientLinearAngle || hoverGradientLinearAngle
        }deg, ${colorGradient})`;

      return `radial-gradient(at ${(
        s.hoverGradientPosition || hoverGradientPosition
      )?.toLowerCase()}, ${colorGradient})`;
    });

    const hoverButtonStyles = computed(() => {
      if (hoverButton.value) {
        return {
          transition: '0.2s linear',
          background: `${hoverButtonBackground.value ?? null}`,
        };
      }
      return false;
    });

    const getAnimationSpeed = (string) => {
      switch (string) {
        case 'Fast':
          return `${0.6}`;
        case 'Slow':
          return `${1.6}`;
        default:
          return `${1}`;
      }
    };

    const buttonStyles = computed(() => {
      const s = props.buttonSettings?.styles ?? defaultStyles;
      return {
        borderRadius: `${s.borderRadius}px`,
        paddingTop: `${s.elemPaddingTop[settingIndex.value]}px`,
        paddingRight: `${s.elemPaddingRight[settingIndex.value]}px`,
        paddingBottom: `${s.elemPaddingBottom[settingIndex.value]}px`,
        paddingLeft: `${s.elemPaddingLeft[settingIndex.value]}px`,
        background:
          s.elemBackgroundColor !== null ? buttonBackground.value : null,
        boxShadow: `
          ${s.buttonHorizontalOffset}px
          ${s.buttonVerticalOffset}px
          ${s.buttonShadowBlur}px
          ${s.buttonShadowSpread}px
          ${s.buttonShadowColor ?? '#ccc'}
        `,
        '--ongoing-speed': getAnimationSpeed(
          s.ongoingAnimationSpeed ?? defaultStyles.ongoingAnimationSpeed
        ),
        '--hover-speed': getAnimationSpeed(
          s.hoverAnimationSpeed ?? defaultStyles.hoverAnimationSpeed
        ),
      };
    });

    const mainTextStyles = computed(() => {
      const s = props.buttonSettings?.styles ?? defaultStyles;
      return {
        fontFamily:
          s.mainTextFontFamily.toLowerCase() === 'default'
            ? null
            : s.mainTextFontFamily,
        fontSize: `${s.mainTextFontSize[settingIndex.value]}px`,
        lineHeight: s.mainTextLineHeight[settingIndex.value],
        fontStyle: s.mainTextFontStyle,
        fontWeight: s.mainTextFontWeight,
        textDecoration: s.mainTextDecoration,
        color: hoverButton.value ? s.hoverMainTextColor : s.mainTextColor,
        transition: '0.3s',
      };
    });

    return {
      element,
      buttonStyles,
      kebabCaseLize,
      mainTextStyles,
      hoverButton,
      hoverButtonStyles,
      disableBtn,
      mode,
    };
  },
};
</script>

<style lang="scss" scoped>
.primary-button {
  color: $h-primary-text;
  background: $h-primary;
  border: none;
  transition-duration: 0.3s;
  text-overflow: ellipsis;
  overflow: hidden;
  max-width: 100%;
  text-transform: none;
  line-height: 1;

  &:hover {
    color: $h-primary-text;
    background: $h-primary;
  }

  &:focus {
    box-shadow: none;
  }
}
</style>

<template>
  <Component
    :is="mode !== 'Builder' ? 'ShareNetwork' : 'div'"
    :network="socialNetworkName.toLowerCase()"
    :url="pageURL"
    :title="document?.title ?? title"
    :description="document?.description ?? description"
    class="share-button-wrapper"
    :style="shareButtonStyle"
    @click="$emit('click')"
  >
    <i
      :class="iconContainer"
      class="m-0"
      :style="[iconShapeStyles('Left'), iconStyles]"
    />
    <span
      class="icon-text m-0"
      :class="iconContainer + '-text'"
      :style="[iconShapeStyles('Right'), textStyles]"
    >
      {{ socialNetworkName }}
    </span>
  </Component>
</template>
<script>
import elementMixin from '@builder/mixins/elementMixin.js';
import { toRefs, computed } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'ShareButtonElementComponent',

  mixins: [elementMixin],

  props: {
    listItem: {
      type: Object,
      required: true,
    },
    settings: {
      type: Object,
      required: true,
    },
    styles: {
      type: Object,
      required: true,
    },
    title: {
      type: String,
      default: () => 'Hypershapes',
    },
    description: {
      type: String,
      default: () => '',
    },
  },

  setup(props) {
    const { styles, settings, listItem } = toRefs(props);
    const store = useStore();
    const mode = computed(() => store.state.builder.mode);
    const list = listItem.value;
    const setting = settings.value;
    const style = styles.value;

    const settingIndex = computed(() => store.getters['builder/settingIndex']);

    const pageURL = computed(() => {
      return settings.value.targetUrlMode === 'currentpage'
        ? window.location.href
        : settings.value.customLink;
    });

    const iconContainer = computed(() => {
      if (list.icon === 'fa fa-envelope' || list.icon === 'fab fa-envelope') {
        return list.icon.replace('fab ', 'fa ');
      }

      return list.icon;
    });

    const iconShapeStyles = (side) => {
      let { buttonShape } = setting;
      if (buttonShape === '100%') buttonShape = '25px';
      return {
        [`borderTop${side}Radius`]: buttonShape,
        [`borderBottom${side}Radius`]: buttonShape,
      };
    };

    const buttonSizeValue = computed(() => (style.buttonSize * 180) / 100);

    const shareButtonStyle = computed(() => ({
      minWidth:
        // eslint-disable-next-line no-nested-ternary
        setting.gridColumns[settingIndex.value] === 'Auto'
          ? store.state.builder.responsiveMode === 'mobile'
            ? '148px'
            : '155px'
          : '',
      width:
        setting.gridColumns[settingIndex.value] === 'Auto'
          ? `${buttonSizeValue.value}px`
          : '100%',
      textDecoration: 'none',
      cursor: mode.value !== 'Builder' ? 'pointer' : 'default',
      margin: `0 ${style.spacing[settingIndex.value]}px ${
        style.spacing[settingIndex.value]
      }px 0`,
    }));

    const iconStyles = computed(() => ({
      width: `${style.buttonSize[settingIndex.value]}px`,
      height: `${style.buttonSize[settingIndex.value]}px`,
      fontSize: `${style.iconFontSize[settingIndex.value]}em`,
      padding: '6px 24px',
      minHeight: '16px',
    }));

    const textStyles = computed(() => ({
      height: `${style.buttonSize[settingIndex.value]}px`,
    }));

    const socialNetworkName = computed(() => {
      const iconName = list.icon.replace('fa fa-', '').replace('fab fa-', '');
      switch (iconName) {
        case 'envelope':
          return 'email';
        case 'facebook-messenger':
          return 'messenger';
        default:
          return iconName;
      }
    });

    return {
      iconContainer,
      pageURL,
      iconShapeStyles,
      shareButtonStyle,
      iconStyles,
      textStyles,
      socialNetworkName,
    };
  },
};
</script>

<style lang="scss" scoped>
.share-button-wrapper {
  display: flex;
  overflow: hidden;
  // min-width: 160px;
}

.fab,
.fa-envelope,
.fa-facebook,
.fa-facebook-messenger,
.fa-twitter,
.fa-instagram,
.fa-linkedin,
.fa-telegram,
.fa-whatsapp,
.fa-weibo,
.fa-envelope-text {
  font-size: 30px;
  width: 80px;
  height: 80px;
  text-decoration: none;
  margin: 5px 0px 5px 0px;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

.fa-facebook-text {
  background-color: #355089;
  color: white;
}

.fa-facebook-messenger-text {
  background-color: rgb(0, 132, 255);
  color: white;
}

.fa-twitter-text {
  background: #1c9be8;
  color: white;
}

.fa-instagram-text {
  background-color: #3f729b;
  background-image: linear-gradient(
    200deg,
    #515bd4,
    #8134af,
    #dd2a7b,
    #feda77,
    #f58529
  );
  color: white;
}

.fa-linkedin-text {
  background-color: #005e8f;
  color: white;
}

.fa-telegram-text {
  background-color: #0088cc;
  color: white;
}

.fa-whatsapp-text {
  background-color: #1eba58;
  color: white;
}

.fa-weibo-text {
  background-color: #b51b22;
  color: white;
}

.fa-envelope {
  background: linear-gradient(
    113deg,
    rgba(234, 67, 53, 1) 100%,
    rgba(187, 0, 27, 1) 0%
  );
  color: white;
}

.fa-facebook {
  background-color: #3b5998;
  /* #385793 */
  color: white;
}

.fa-facebook-messenger {
  background-color: rgb(0, 106, 204);
  /* #385793 */
  color: white;
}

.fa-twitter {
  background: #55acee;
  color: white;
}

.fa-linkedin {
  background-color: #0e76a8;
  color: white;
}

.fa-telegram {
  background-color: #2ca4df;
  color: white;
}

.fa-whatsapp {
  background-color: #25d366;
  color: white;
}

.fa-weibo {
  background-color: #df2029;
  color: white;
}

.icon-text {
  font-family: $base-font-family;
  font-size: 1em;
  width: 100%;
  margin-left: 0;
  padding: 0.5rem 1rem 0.5rem 0.5rem;
  text-transform: capitalize;
  font-weight: 700;
}
.fa,
.fab,
.fas {
  color: white;
}

.fa {
  &-facebook-text {
    background-color: #355089;
    color: white;
  }

  &-twitter-text {
    background: #1c9be8;
    color: white;
  }

  &-instagram-text {
    background-color: #3f729b;
    background-image: linear-gradient(
      200deg,
      #515bd4,
      #8134af,
      #dd2a7b,
      #feda77,
      #f58529
    );
    color: white;
  }

  &-linkedin-text {
    background-color: #005e8f;
    color: white;
  }

  &-telegram-text {
    background-color: #0088cc;
    color: white;
  }

  &-whatsapp-text {
    background-color: #1eba58;
    color: white;
  }

  &-weibo-text {
    background-color: #b51b22;
    color: white;
  }

  &-envelope-text {
    background: #c71c24;
    color: white;
  }
}
</style>

<template>
  <div
    v-show="loaded"
    :class="position"
    :style="{ '--attrsVal': '0px' }"
  >
    <span
      :class="badgeClass"
      :style="{
        'background-color': backgroundColor,
        color: backgroundColor,
        'font-size': fontSize + 'px',
        margin:
          position === 'top-right'
            ? `-${marginSize}px -${marginSize}px ${marginSize}px ${marginSize}px`
            : position === 'top-left'
              ? `-${marginSize}px ${marginSize}px ${marginSize}px -${marginSize}px`
              : position === 'bottom-left'
                ? `${marginSize}px ${marginSize}px -${marginSize}px -${marginSize}px`
                : position === 'bottom-right'
                  ? `${marginSize}px -${marginSize}px -${marginSize}px ${marginSize}px`
                  : `${marginSize}px`,
        top:
          isMiniStore &&
          marginSize > 10 &&
          !position.includes('middle') &&
          position.includes('top')
            ? `-10px !important`
            : 0,
        bottom:
          isMiniStore &&
          marginSize > 10 &&
          !position.includes('middle') &&
          position.includes('bottom')
            ? `-10px !important`
            : 0,
        left:
          isMiniStore && marginSize > 10 && !position.includes('middle')
            ? `${
              position.includes('left') ? '-7px !important' : '7px !important'
            }`
            : 0,
      }"
    >
      <span
        :class="textClass"
        :style="{ color: textColor }"
      >{{ text }}</span>
    </span>
  </div>
</template>

<script>
/* eslint no-unsafe-optional-chaining: 1 */
/* eslint no-unused-expressions: 1 */
/* eslint no-useless-concat: 1 */
export default {
  props: [
    'textColor',
    'text',
    'fontFamily',
    'backgroundColor',
    'badgeDesign',
    'fontSize',
    'position',
    'marginSize',
    'isMiniStore',
  ],

  data() {
    return {
      minBottom: null,
      loaded: false,
    };
  },

  computed: {
    badgeClass() {
      const badgeClass =
        this.badgeDesign === 'circle'
          ? 'product-badge text-break badge-circle'
          : 'product-badge text-break badge-rectangular';
      return badgeClass;
    },
    textClass() {
      const textClass = `product-badge-text` + ` ${  this.fontFamily}`;
      return textClass;
    },
  },

  mounted() {
    const setBadgeBottom = new Promise((resolve, reject) => {
      setTimeout(() => {
        this.setMinHeight();
        resolve();
      }, 1000);
    });
    setBadgeBottom;
  },

  methods: {
    setMinHeight() {
      const sliderList = document.getElementsByClassName('splide__slide')[0];
      if (!sliderList) {
        this.loaded = true;
      }
      this.minBottom =
        sliderList?.clientHeight + (sliderList?.style.marginTop * 2 || 32);
      const container = document.getElementsByClassName('bigImgContainer')[0];
      if (container) container.style.maxHeight = `${this.minBottom - 27}px`;
      this.loaded = true;
    },
  },
};
</script>

<style lang="scss" scoped>

.product-badge {
  position: relative;
  margin: 0;
}

.product-badge.badge-rectangular {
  border-radius: 0px;
  width: auto;
  height: auto;
  padding: 0.5em 1.5em;
  margin: 0;
}

.product-badge.badge-circle {
  border-radius: 50%;
  width: 4.5em;
  height: 4.5em;
}

.product-badge-text {
  background-color: transparent !important;
}

.product-badge {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
  text-align: center;
  font-size: 10px;
  line-height: 1.1;
  z-index: 9;
  max-width: 100%;
  top: initial !important;
  right: initial !important;
  // @media(max-width: 768px) {
  //   top: 0;
  //   right: 0;
  // }
}

.bottom-left {
  position: absolute;
  bottom: 0;
  left: var(--attrsVal);
}

.bottom-middle {
  position: absolute;
  top: initial;
  bottom: 0;
  right: initial;
  left: 50%;
  transform: translate(-50%, 0);
  -webkit-transform: translate(-50%, 0);
  margin: 0;
}

.bottom-right {
  position: absolute;
  bottom: 0;
  right: var(--attrsVal);
}

.top-left {
  position: absolute;
  top: 0;
  left: var(--attrsVal);
}

.top-middle {
  position: absolute;
  top: 0;
  right: initial;
  left: 50%;
  transform: translate(-50%, 0);
  -webkit-transform: translate(-50%, 0);
  margin: 0;
}

.top-right {
  position: absolute;
  top: 0;
  right: var(--attrsVal);
}

.middle {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  -webkit-transform: translate(-50%, -50%);
}

.middle-left {
  position: absolute;
  top: 50%;
  transform: translate(0, -50%);
  -webkit-transform: translate(0, -50%);
  right: initial;
  left: var(--attrsVal);
  margin: 0;
}

.middle-right {
  position: absolute;
  top: 50%;
  transform: translate(0, -50%);
  -webkit-transform: translate(0, -50%);
  bottom: initial;
  right: var(--attrsVal);
  margin: 0;
}
</style>

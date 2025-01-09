<template>
  <Component
    :is="element"
    :href="href"
    :target="href && isOpenInNewTab ? '_blank' : null"
    :disabled="disabled"
    :class="[
      `${
        ['theme-primary', 'theme-secondary'].includes(type) ? '' : 'btn'
      } btn-${type} btn-${size} ${color ? `btn-color-${color}` : ''} ${
        active ? 'active' : ''
      }`,
      {
        'p-0': type === 'link',
      },
    ]"
    :type="buttonType"
    @click="$emit('click')"
  >
    <i
      v-if="hasAddIcon || hasEditIcon"
      class="fa-solid me-2"
      :class="{
        'fa-plus': hasAddIcon,
        'fa-pen': hasEditIcon,
      }"
    />
    <!-- @slot The content of the button (text, icon, etc) -->
    <slot />
    <i
      v-if="hasDropdownIcon"
      class="fa-solid fa-caret-down ms-2"
    />
  </Component>
</template>

<script>
export default {
  props: {
    /**
     * The type of button
     */
    type: {
      type: String,
      default: 'primary',
      validator: (value) => {
        return [
          'primary',
          'secondary',
          'info',
          'success',
          'warning',
          'danger',
          'close',
          'light',
          'light-primary',
          'link',
          'dark',
        ].includes(value);
      },
    },
    /**
     * The href to redirect the user when he clicks the button.
     * This component will automatically become an anchor tag when href is set
     */
    href: {
      type: String,
      default: null,
    },
    /**
     * To determine whether open the href in new tab or not
     */
    isOpenInNewTab: {
      type: Boolean,
      default: false,
    },
    /**
     * To determine if the button is disabled
     */
    disabled: {
      type: Boolean,
      default: false,
    },
    /**
     * To determine the size of the button
     */
    size: {
      type: String,
      default: 'md',
      validator: (value) => {
        return ['sm', 'md', 'lg'].includes(value);
      },
    },
    /**
     * To determine if the button is activated
     */
    active: {
      type: Boolean,
      default: false,
    },
    /**
     * To determine button is submit button
     */
    isSubmit: {
      type: Boolean,
      default: false,
    },
    /**
     * To customize the color of button text
     */
    color: {
      type: String,
      default: null,
    },
    /**
     * If true, will prepend a plus icon to the button text
     */
    hasAddIcon: {
      type: Boolean,
      default: false,
    },
    /**
     * If true, will prepend a pen icon to the button text
     */
    hasEditIcon: {
      type: Boolean,
      default: false,
    },
    /**
     * If true, will append a down icon to the button text
     */
    hasDropdownIcon: {
      type: Boolean,
      default: false,
    },
    isInStore: {
      type: Boolean,
      default: false,
    },

    hex: {
      type: String,
      default: null,
    },
    /**
     * Force the usage of window.location.replace for redirection
     */
    isWindowRedirection: {
      type: Boolean,
      default: false,
    },
  },

  emits: [
    /**
     * Triggered on button click
     */
    'click',
  ],

  computed: {
    element() {
      if (!this.href) return 'button';
      return this.isOpenInNewTab || this.isInStore || this.isWindowRedirection ? 'a' : 'Link';
    },

    buttonType() {
      if (this.type === 'link' || this.href) return null;
      return this.isSubmit ? 'submit' : 'button';
    },
  },
};
</script>

<style scoped>
button,
a {
  width: fit-content;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn:deep(i) {
  line-height: 1.5;
  padding-right: 0;
}
</style>

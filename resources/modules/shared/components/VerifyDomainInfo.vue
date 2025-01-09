<template>
  <div>
    <i
      class="fa fa-question-circle ms-2 verify-domain-info"
      data-bs-toggle="tooltip"
      :data-bs-placement="placement"
      title="Please verify your email in Settings > Email Settings"
      style="font-size: 1.1rem"
    />
  </div>
</template>

<script>
const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  name: 'VerifyDomainInfo',
  props: {
    placement: {
      type: String,
      default: 'right',
    },
  },
  data() {
    return {
      tooltipClass: 'vdi__tooltip', // remember to use this class also in style below
    };
  },
  created() {
    this.$nextTick(function () {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
      );
      bootstrap?.then(({ Tooltip }) => {
        tooltipTriggerList.map((tooltipTriggerEl) => {
          return new Tooltip(tooltipTriggerEl, {
            template: `<div class="${this.tooltipClass}" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>`,
          });
        });
      });
    });
  },
};
</script>

<style lang="scss">
$tooltip-class: 'vdi__tooltip'; // keep this the same with tooltipClass in data() above

/*
* Temp solution for the extremely annoying positioning problem in
* automation modal. Remove this if no longer needed
*/
.#{$tooltip-class} {
  z-index: 1000000 !important;
}

@media (max-width: 422px) {
  .#{$tooltip-class} {
    top: -20px !important;
    left: 5px !important;

    .arrow {
      top: 50px !important;
    }

    .tooltip-inner {
      width: 120px !important;
    }
  }
}
</style>

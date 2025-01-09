<template>
  <div>
    <div class="row justify-content-md-center">
      <div class="col-md-12">
        <Link
          :href="`/orders/details/${order.reference_key}`"
          class="back-to-previous"
        >
          <i
            class="fa fa-angle-left back-btn-icon"
            style="font-size: 16px"
            aria-hidden="true"
          />
          <span
            style="margin-right: 0.5rem; color: #c2c9ce"
          >Back to
            <span
              style="
                color: #43bdef;
                margin: 0;
                margin-right: 10px;
                font-size: 16px !important;
              "
            >#{{ order.order_number }}</span></span>
        </Link>
      </div>
    </div>
    <div class="row row-header">
      <div
        class="col-md-12"
        style="padding: 0 !important"
      >
        <h1 class="headerText h-three">
          {{ secondaryHeader }}
        </h1>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['secondaryHeader', 'order'],

  methods: {},
};
</script>

<style scoped lang="scss">
</style>

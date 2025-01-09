<template>
  <div
    class="container-header mb-3"
    style="padding: 0"
  >
    <Link
      :href="
        order !== undefined
          ? `/orders/details/${order.reference_key}`
          : '/orders'
      "
      class="back-to-previous"
    >
      <i
        class="fa fa-angle-left back-btn-icon"
        aria-hidden="true"
      />
      <span
        style="margin-right: 0.5rem; color: #c2c9ce"
      >Back to
        <span
          v-if="order !== undefined"
          style="
            color: #43bdef;
            margin: 0;
            margin-right: 10px;
            font-size: 16px !important;
          "
        >
          #{{ order.order_number }}
        </span>
        <span v-else> Orders </span>
      </span>
    </Link>
    <div
      v-if="mainTitle !== undefined"
      class="headerText h-three"
    >
      {{ mainTitle }}
    </div>
    <slot name="content" />
  </div>
</template>

<script>
export default {
  name: 'OrderHeader',
  props: ['mainTitle', 'order'],
};
</script>

<style lang="scss" scoped>
</style>

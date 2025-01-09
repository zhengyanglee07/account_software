<template>
  <div class="row">
    <div
      v-for="detail in details"
      :key="detail.title"
      class="col-md-3 affiliate-individual-card"
    >
      <div class="card p-3 h-100 affiliate-card text-center">
        <span
          class="affiliate_text_title"
          style="margin-bottom: 10px"
        >{{
          detail.title
        }}</span>
        <h2
          class="affiliate_card-title h-two"
          style="color: #7766f7"
        >
          <span>$</span>{{ detail.value }}
        </h2>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CommissionCard',
  props: {
    datas: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      details: this.datas,
    };
  },
  mounted() {
    this.details = [...this.datas];
  },
};
</script>

<style lang="scss" scoped>
.affiliate-individual-card {
  padding: 0 5px !important;
}

@media (max-width: 768px) {
  .affiliate-individual-card {
    flex: 0 0 auto;
    width: 50%;
    margin-bottom: 20px;
  }

  .row .affiliate-individual-card:nth-child(odd) {
    padding-right: 10px !important;
  }

  .row .affiliate-individual-card:nth-child(even) {
    padding-left: 10px !important;
  }
}
</style>

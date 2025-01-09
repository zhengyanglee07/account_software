<template>
  <div :class="`col-xl-${col} my-0 mb-4`">
    <!--begin::Statistics Widget 5-->
    <div class="card statistic-card hoverable card-xl-stretch">
      <!--begin::Body-->
      <div class="card-body">
        <div :class="titleClass">
          {{ title }} <br>
        </div>
        <div class="fw-bold text-gray-800 fs-title">
          {{ data }}
          <span
            v-if="append"
            class="text-gray-800 fs-append"
          >
            ({{ append }})
          </span>
        </div>
      </div>
      <!--end::Body-->
    </div>
    <!--end::Statistics Widget 5-->
  </div>
</template>

<script>
export default {
  name: 'ReportStatisticCard',
  props: {
    title: {
      type: String,
      default: '',
    },
    data: {
      type: String,
      default: '',
    },
    append: {
      type: String,
      default: '',
    },
    col: {
      type: Number,
      default: 4,
    },
    titleClass: {
      type: String,
      default: 'text-gray-800 fw-bolder fs-title mb-2',
    },
  },
};
</script>

<style>
.statistic-card {
  color: #009ef7;
  border-color: #009ef7;
  background-color: #f1faff !important;
  border-width: 1px;
  border-style: dashed;
  cursor: default;
}

.report-card-container.row > * {
  padding-right: 5px;
}

.fs-title {
  font-size: 16px;
}

.fs-append {
  font-size: 12px;
}
</style>

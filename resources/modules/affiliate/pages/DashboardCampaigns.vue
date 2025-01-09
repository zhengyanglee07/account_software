<template>
  <BaseDatatable
    no-action
    :table-headers="tableHeaders"
    :table-datas="
      $page.props.member?.participant?.status === 'approved' ? tableData : []
    "
    :title="
      $page.props.member?.participant?.status !== 'approved'
        ? 'Be Patient'
        : 'campaign'
    "
    :custom-description="
      $page.props.member?.participant?.status !== 'approved'
        ? 'Store owner is reviewing your application. You will see the details after approved.'
        : 'No campaigns avlailable now'
    "
  >
    <template
      v-if="$page.props.member?.participant?.status !== 'approved'"
      #action-button
    >
      <p class="text-gray-400 fs-5 fw-bold mb-13">
        * We will notify you by email when you are approved.
      </p>
    </template>
    <template #cell-title="{ row: item }">
      {{ item.title }}
      <i
        :id="`ref-link-btn-${item.id}`"
        class="copy-btn far fa-copy hoverable"
        :data-clipboard-text="item.refLink"
        data-bs-toggle="custom-tooltip"
        data-bs-placement="bottom"
        title="Copied to Clipboard!"
      />
    </template>
    <template #cell-levels="{ row: { levels } }">
      <div
        v-for="(e, i) in levels"
        :key="i"
        class="mb-1"
      >
        <span class="text-muted fw-bold fs-6">Level {{ e.level }}</span>{{ ' - ' }}
        <span
          v-if="e.commission_type === 'fixed'"
          class="text-muted fw-bold fs-6"
        >
          $
        </span>
        <span class="text-muted fw-bold fs-6"> {{ e.commission_rate }} </span>
        <span
          v-if="e.commission_type === 'percentage'"
          class="text-muted fw-bold fs-6"
        >
          %
        </span>
      </div>
    </template>
  </BaseDatatable>
</template>

<script>
import Clipboard from 'clipboard';
import campaignsDT from '@affiliate/mixins/campaignsDT';
import AffiliateLayout from '@shared/layout/AffiliateLayout.vue';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  mixins: [campaignsDT],
  layout: AffiliateLayout,
  props: {
    campaigns: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      tableHeaders: [
        {
          name: `Title`,
          key: 'title',
          custom: true,
        },
        {
          name: `Commission`,
          key: 'levels',
          width: '150px',
          custom: true,
        },
        {
          name: `Products`,
          key: 'products',
        },
      ],
    };
  },
  computed: {
    tableData() {
      return this.campaigns?.map((c) => ({
        id: c?.id,
        title: c?.title,
        products:
          c?.productOrCategoryNames.length === 0
            ? 'All products'
            : c?.productOrCategoryNames.join(', '),
        levels: c?.levels,
        refLink: c?.refLink,
      }));
    },
  },
  mounted() {
    setTimeout(() => {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('.fa-copy')
      );
      bootstrap?.then(({ Tooltip }) => {
        const tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => {
          const tooltip = new Tooltip(tooltipTriggerEl, {
            trigger: 'click',
          });
          tooltipTriggerEl.onmouseleave = () => {
            tooltip.hide();
          };
          return tooltip;
        });
      });
    }, 1000);
  },
};
</script>

<style scoped lang="scss"></style>

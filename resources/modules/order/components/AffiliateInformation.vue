<!-- eslint-disable vue/no-parsing-error -->
<template>
  <BaseCard
    has-header
    has-toolbar
    title="Affiliate"
  >
    <!-- <template #toolbar>
      <BaseButton
        type="link"
        href="/affiliate/members/commissions"
      >
        Manage
      </BaseButton>
    </template> -->

    <div
      v-for="(aff, index) in affiliateInfo"
      :key="index"
    >
      <p v-if="index === 0">
        Affiliate campaign:
        <BaseButton
          type="link"
          :href="`/affiliate/members/campaigns/${aff.campaign.reference_key}?edit=1`"
          is-open-in-new-tab
        >
          {{ aff.campaign.title }}
        </BaseButton>
      </p>
      <p class="fw-bold fs-5">
        Level {{ aff.level }}:
      </p>
      <p>
        Refer by:
        <BaseButton
          type="link"
          :href="`/affiliate/members/${aff.affRefKey}`"
          is-open-in-new-tab
        >
          {{ aff.affiliate_email }}
        </BaseButton>
      </p>

      <p>
        Commission:
        {{ aff.currency === 'MYR' ? 'RM' : aff.currency }} {{ aff.commission }}
        <BaseBadge
          class="text-capitalize"
          :text="aff.status"
          :type="getBadgeType(aff.status)"
        />
      </p>
    </div>
    <div class="d-flex flex-center">
      <BaseButton
        class="w-100"
        type="light-primary"
        :disabled="loading"
        @click="bulkUpdateCommissions"
      >
        <span class="text-capitalize">{{ status }}&nbsp</span> All Commissions
      </BaseButton>
    </div>
  </BaseCard>
</template>

<script setup>
import { ref, onMounted, computed, inject } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const $toast = inject('$toast');
const { page } = router;
const props = defineProps({
  affiliateInfo: {
    type: Array,
    default: null,
  },
});
const getBadgeType = (status) => {
  switch (status) {
    case 'approved':
      return 'success';
    case 'pending':
      return 'warning';
    default:
      return 'secondary';
  }
};

const ids = computed(() => props.affiliateInfo.map((e) => e.id));
const status = computed(() => {
  const arr = [];
  props.affiliateInfo.map((e) => {
    arr.push(e.status);
    return e;
  });

  return arr.includes('pending') || arr.includes('disapproved')
    ? 'approve'
    : 'disapprove';
});
const loading = ref(false);
const bulkUpdateCommissions = async () => {
  // eslint-disable-next-line no-restricted-globals
  const res = confirm(
    `Are you sure want to ${status.value} all these commissions?`
  );
  if (res) {
    try {
      loading.value = true;
      await axios
        .put('/affiliate/members/bulk/commissions', {
          ids: ids.value,
          status: `${status.value}d`,
        })
        .then(() => {
          loading.value = false;
          $toast.success(
            'Success',
            `Successfully ${status.value}d affiliate commissions`
          );
          const { url } = page;
          router.visit(url);
        });
    } catch (err) {
      console.log(err);
      $toast.error('Error', `Failed to ${status.value} affiliate commissions.`);
    }
  }
};
</script>

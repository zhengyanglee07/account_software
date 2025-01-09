<template>
  <BaseModal
    :modal-id="modalId"
    title="Assign Promo Code"
  >
    <BaseFormGroup
      label="Unique Promo Code"
      description="One affiliate member can only assign one unique promo code"
    >
      <BaseFormSelect v-model="selectedPromotionId">
        <option value="none">
          None
        </option>
        <option
          v-for="(p, index) in availablePromotions.filter((el) => el)"
          :key="index"
          class="code-option"
          :value="p?.id"
        >
          {{ p?.discount_code }}
        </option>
      </BaseFormSelect>
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        :disabled="assigning"
        class="primary-small-square-button"
        @click="save"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
/* eslint-disable indent */
import { Modal } from 'bootstrap';

export default {
  props: {
    modalId: {
      type: String,
      default: '',
    },
    availablePromotions: {
      type: Array,
      default: () => [],
    },
    participant: {
      type: Object,
      default: () => ({}),
    },
    selectedPromo: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      selectedPromotionId: 'none',
      assigning: false,
    };
  },
  watch: {
    selectedPromo: {
      handler(newVal) {
        if (newVal?.id) {
          this.selectedPromotionId = newVal?.id ?? 'none';
        } else this.selectedPromotionId = 'none';
      },
      deep: true,
    },
  },
  mounted() {
    setTimeout(() => {
      const modalEl = document.getElementById(this.modalId);

      modalEl.addEventListener('hidden.bs.modal', () => {
        this.selectedPromotionId = this.selectedPromo?.id ?? 'none';
      });
    }, 1000);
  },
  methods: {
    async save() {
      // if (
      //   this.selectedPromotionId !== 'none' &&
      //   this.participant?.discountCodes.length > 0
      // ) {
      //   this.$toast.warning(
      //     'Warning',
      //     'You have already assigned a promotion code to this affiliate'
      //   );
      //   return;
      // }

      this.assigning = true;
      try {
        await this.$axios.put('/affiliate/members/participant/promo', {
          promotionId: this.selectedPromotionId,
          participantId: this.participant.id,
        });
        this.$toast.success(
          'Success',
          'Promo code has been assigned successfully'
        );
        this.$inertia.visit('/affiliate/members', { replace: true });
        // console.log('er', this.selectedPromotionId);
        Modal.getInstance(document.getElementById(this.modalId)).hide();
        this.$emit('update-participants', this.participant.id, {
          discount_codes:
            this.selectedPromotionId === 'none'
              ? []
              : [
                  this.availablePromotions
                    ?.filter((el) => el)
                    ?.filter((p) => p?.id === this.selectedPromotionId)
                    ?.map((e) => e?.discount_code),
                ],
        });
        this.selectedPromotionId = 'none';
      } catch (err) {
        console.error(err);
        this.$toast.error('Error', 'Something went wrong');
      } finally {
        this.assigning = false;
      }
    },
  },
};
</script>

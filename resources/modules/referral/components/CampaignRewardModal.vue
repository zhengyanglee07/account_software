<template>
  <BaseModal
    :modal-id="modalId"
    :title="Object.keys(selectedReward).length ? 'Edit Reward' : 'Add Reward'"
  >
    <BaseFormGroup
      label="Title"
      :error-message="
        err === true && v$.rewardTitle.required.$invalid
          ? 'Title is required'
          : ''
      "
      required
    >
      <BaseFormInput
        id="reward-title"
        v-model="rewardTitle"
        type="text"
        placeholder="Enter reward title"
      />
    </BaseFormGroup>
    <BaseFormGroup label="Reward Type">
      <BaseFormSelect
        id="reward-type"
        v-model="rewardType"
        label-key="name"
        value-key="value"
        :options="[
          {
            name: 'Promotion Code',
            value: 'promo-code',
          },
          {
            name: 'Custom Link',
            value: 'downloadable-content',
          },
          {
            name: 'Custom Message',
            value: 'custom-message',
          },
        ]"
        @change="
          rewardValue =
            rewardType === 'downloadable-content' ? '' : rewardValue;
          rewardValueText = rewardValueText.trim() || 'Click here to view';
        "
      />
    </BaseFormGroup>
    <BaseFormGroup
      v-if="rewardType !== 'custom-message'"
      :label="
        rewardType === 'downloadable-content' ? 'URL' : 'Which Promo Code'
      "
      required
      :error-message="err ? getRewardValueErrorMessage() : ''"
    >
      <BaseFormInput
        v-if="rewardType === 'downloadable-content'"
        id="reward-value"
        v-model="rewardValue"
        type="url"
        placeholder="exp: https://example.com"
        @input="err = false"
      />
      <BaseFormSelect
        v-else
        id="reward-value"
        v-model="rewardValue"
        @change="err = false"
      >
        <option
          value=""
          disabled
        >
          Select promo code
        </option>
        <option
          v-for="(promo, index) in promotions"
          :key="index"
          :value="promo.id"
        >
          {{ promo.discount_code }}
        </option>
      </BaseFormSelect>
    </BaseFormGroup>
    <BaseFormGroup
      v-if="rewardType === 'downloadable-content'"
      label="Custom link text"
      required
      :error-message="
        err && v$.rewardValueText.required.$invalid
          ? 'This field is required'
          : ''
      "
    >
      <BaseFormInput
        id="reward-value"
        v-model="rewardValueText"
        type="text"
        @input="err = false"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Points to unlock"
      required
    >
      <BaseFormInput
        v-model="point"
        type="number"
        :min="1"
      />
    </BaseFormGroup>
    <BaseFormGroup
      v-if="rewardType !== 'promo-code'"
      label="Redemption Instruction"
      description="Will only be shown after the participant unlocked this reward"
      :required="rewardType === 'custom-message'"
      :error-message="
        err && v$.redemptionInstruction.required.$invalid
          ? 'This field is required'
          : ''
      "
    >
      <BaseFormTextarea
        id="instruction"
        v-model="redemptionInstruction"
        placeholder="Provide instructions to your participant on how to redeem this reward"
        :rows="3"
      />
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        id="add-reward"
        @click="addReward"
      >
        {{ Object.keys(selectedReward).length ? 'Update' : 'Add' }}
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { nanoid } from 'nanoid';
import { Modal } from 'bootstrap';
import { required, requiredIf, url } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';

const mustBeHttps = (value) => value.startsWith('https://');

export default {
  props: {
    modalId: {
      type: String,
      default: '',
    },
    promotions: {
      type: Array,
      default: () => [],
    },
    selectedReward: {
      type: Object,
      default: () => {},
    },
  },

  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      rewardTitle: '',
      point: 10,
      rewardType: 'promo-code',
      rewardValue: '',
      rewardValueText: 'Click here to view',
      redemptionInstruction: '',
      err: false,
    };
  },
  validations() {
    return {
      rewardTitle: {
        required,
      },
      rewardValue: {
        required: requiredIf(this.rewardType !== 'custom-message'),
        url: this.rewardType === 'downloadable-content' ? url : true,
        mustBeHttps:
          this.rewardType === 'downloadable-content' ? mustBeHttps : true,
      },
      rewardValueText: {
        required,
      },
      redemptionInstruction: {
        required: requiredIf(this.rewardType === 'custom-message'),
      },
    };
  },
  watch: {
    selectedReward: {
      deep: true,
      handler(newValue) {
        if (newValue) {
          this.rewardTitle = newValue?.rewardTitle ?? '';
          this.rewardType = newValue?.rewardType ?? 'promo-code';
          this.rewardValue = newValue?.rewardValue ?? '';
          this.rewardValueText =
            newValue?.rewardValueText?.trim() || 'Click here to view';
          this.redemptionInstruction = newValue?.redemptionInstruction
            ? newValue?.redemptionInstruction
            : '';
          this.point = newValue?.pointToUnlock ?? 10;
        }
      },
    },
  },
  methods: {
    getRewardValueErrorMessage() {
      if (this.v$.rewardValue.$invalid) {
        if (this.v$.rewardValue.required.$invalid) {
          return 'This field is required';
        }
        if (this.v$.rewardValue.url.$invalid) {
          return 'URL is invalid';
        }
        if (this.v$.rewardValue.mustBeHttps.$invalid) {
          return 'URL must starts with https://';
        }
      }
      return '';
    },
    typeTitle(value) {
      switch (value) {
        case 'promo-code':
          return 'Promotion code';
        case 'custom-message':
          return 'Custom Message';
        default:
          return 'Custom Link';
      }
    },
    addReward() {
      if (this.point <= 0) {
        this.$toast.warning(
          'Warning',
          'Point to unlock should be greater than 0'
        );
        return;
      }
      this.err = this.v$.$invalid;
      if (!this.err) {
        this.$emit('edit-reward', {
          id: this.selectedReward?.id ?? nanoid(),
          rewardTitle: this.rewardTitle,
          pointToUnlock: this.point,
          rewardType: this.rewardType,
          rewardValue: this.rewardValue,
          rewardValueText: this.rewardValueText,
          typeTitle: this.typeTitle(this.rewardType),
          redemptionInstruction: this.redemptionInstruction?.trim(),
          promoCode:
            this.promotions.find((el) => el.id === +this.rewardValue)
              ?.discount_code ?? null,
        });

        // reset local data
        Modal.getInstance(document.getElementById(`${this.modalId}`)).hide();
        this.point = 10;
        this.rewardValue = '';
        this.rewardType = 'promo-code';
        this.rewardValueText = 'Click here to view';
      }
    },
  },
};
</script>

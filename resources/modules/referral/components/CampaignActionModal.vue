<template>
  <BaseModal
    :modal-id="modalId"
    :title="Object.keys(selectedAction).length ? 'Edit Action' : 'Add Action'"
  >
    <template #footer>
      <BaseButton
        id="add-action"
        :disabled="!actionOptions.length"
        @click="addAction"
      >
        {{ Object.keys(selectedAction).length ? 'Update' : 'Add' }}
      </BaseButton>
    </template>
    <BaseFormGroup
      v-if="actionOptions.length"
      :col="8"
    >
      <BaseFormSelect
        v-model="actionType"
        :options="actionOptions"
        label-key="type"
        value-key="value"
        @change="err = false"
      />
    </BaseFormGroup>
    <BaseFormGroup
      v-if="actionOptions.length"
      :col="4"
    >
      <BaseFormInput
        id="point"
        v-model="point"
        type="number"
        :min="1"
      >
        <template #append>
          point(s)
        </template>
      </BaseFormInput>
    </BaseFormGroup>
    <section v-if="actionType === 'custom'">
      <BaseFormGroup
        label="Title"
        description="Will be displayed to participant in reward element"
        required
        :error-message="
          err && v$.title.required.$invalid ? 'Title is required' : ''
        "
      >
        <BaseFormInput
          id="title"
          v-model="title"
          type="text"
          placeholder="e.g. Like our Facebook page"
          @input="err = false"
        />
      </BaseFormGroup>
      <BaseFormGroup
        label="URL"
        description="A participant will earn points whenever he visits this URL"
        required
        :error-message="err ? getUrlErrorMessage() : ''"
      >
        <BaseFormInput
          id="url"
          v-model="url"
          type="text"
          placeholder="e.g. https://www.facebook.com/hypershapes"
          @input="err = false"
        />
      </BaseFormGroup>
    </section>
    <BaseEmptyData
      v-if="!actionOptions.length"
      custom-description="No more actions available"
    />
  </BaseModal>
</template>

<script>
import BaseEmptyData from '@shared/components/BaseEmptyData.vue';
import { nanoid } from 'nanoid';
import { Modal } from 'bootstrap';
import { required, requiredIf, url } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';

const mustBeHttps = (value) => value.startsWith('https://');

export default {
  components: {
    BaseEmptyData,
  },
  props: {
    modalId: {
      type: String,
      default: '',
    },
    actions: {
      type: Array,
      default: () => [],
    },
    selectedAction: {
      type: Object,
      default: () => {},
    },
    selectedChannel: {
      type: String,
      default: () => 'funnel',
    },
    type: {
      type: String,
      default: () => 'inviter',
    },
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      point: 10,
      title: '',
      url: '',
      err: false,
      actionType: 'purchase',
      actionOptions: [
        {
          type: `${
            this.type === 'inviter' ? 'Refer' : 'Become'
          } a new customer`,
          value: 'purchase',
        },
        {
          type: `${this.type === 'inviter' ? 'Refer' : 'Become'} a new sign up`,
          value: 'sign-up',
        },
        { type: 'Join this referal campaign', value: 'join' },
        { type: 'Custom Action', value: 'custom' },
      ],
    };
  },
  validations() {
    return {
      title: {
        required: requiredIf(this.actionType === 'custom'),
      },
      url: {
        required: requiredIf(this.actionType === 'custom'),
        url: this.actionType === 'custom' ? url : true,
        mustBeHttps: this.actionType === 'custom' ? mustBeHttps : true,
      },
    };
  },
  watch: {
    selectedAction: {
      deep: true,
      handler(newValue) {
        const options = this.initialize();
        if (Object.keys(newValue).length) {
          this.actionOptions = [
            {
              type: `${this.actionTitle(newValue.actionType)}`,
              value: `${newValue.actionType}`,
            },
          ];
          if (this.actions.length === 1) {
            this.actionOptions = options;
          }
          this.actionType = newValue.actionType;
          this.point = newValue.point;
          this.title = newValue.title;
          this.url = newValue.url;
        }
      },
    },
    type(newValue) {
      if (newValue) {
        this.initialize();
      }
    },
  },
  mounted() {
    setTimeout(() => {
      const modalEl = document.getElementById(this.modalId);
      modalEl.addEventListener('hidden.bs.modal', () => {
        if (!Object.keys(this.selectedAction).length) {
          this.point = 10;
          this.actionOptions = this.actionOptions.filter(
            (el) => !(el.value === this.actionType) && el.value === 'custom'
          );
          this.actionType = this.actionOptions[0]
            ? this.actionOptions[0]?.value
            : null;
        }
      });
    }, 1000);
  },
  methods: {
    getUrlErrorMessage() {
      if (this.v$.url.$invalid) {
        if (this.v$.url.required.$invalid) {
          return 'URL is required';
        }
        if (this.v$.url.url.$invalid) {
          return 'URL is invalid';
        }
        if (this.v$.url.mustBeHttps.$invalid) {
          return 'URL must starts with https://';
        }
      }
      return '';
    },
    initialize() {
      let options = [
        {
          type: `${
            this.type === 'inviter' ? 'Refer' : 'Become'
          } a new customer`,
          value: 'purchase',
        },
        {
          type: `${this.type === 'inviter' ? 'Refer' : 'Become'} a new sign up`,
          value: 'sign-up',
        },
        { type: 'Join this referal campaign', value: 'join' },
        { type: 'Custom Action', value: 'custom' },
      ];
      if (this.selectedChannel !== 'funnel')
        options = options.filter((e) => e.value !== 'sign-up');
      if (this.type !== 'inviter')
        options = options.filter(
          (e) => e.value !== 'join' && e.value !== 'custom'
        );
      this.actionOptions = this.actions.length
        ? options.filter(
            (el) => !this.actions.map((nv) => nv.actionType).includes(el.value)
          )
        : options;
      if (
        !this.actionOptions.map((a) => a.value).includes('custom') &&
        this.type === 'inviter'
      )
        this.actionOptions.push({ type: 'Custom Action', value: 'custom' });
      this.actionType = this.actionOptions[0]
        ? this.actionOptions[0]?.value
        : null;
      this.title = '';
      this.url = '';
      return options;
    },
    actionTitle(value) {
      switch (value) {
        case 'sign-up':
          return `${
            this.type === 'inviter' ? 'Refer' : 'Become'
          } a new sign up`;
        case 'purchase':
          return `${
            this.type === 'inviter' ? 'Refer' : 'Become'
          } a new customer`;
        case 'join':
          return 'Join this referal campaign';
        default:
          return 'Custom Action';
      }
    },
    addAction() {
      this.err = this.v$.$invalid;
      if (this.err) return;
      if (!this.actionOptions.length) return;
      if (this.point <= 0) {
        this.$toast.warning('Warning', 'Point should be greater than 0');
        return;
      }
      const actionType =
        this.type === 'inviter' ? 'edit-action' : 'edit-invitee-action';
      this.$emit(actionType, {
        id: this.selectedAction?.id ?? nanoid(),
        point: this.point,
        actionType: this.actionType,
        title:
          this.actionType !== 'custom'
            ? this.actionTitle(this.actionType)
            : this.title,
        url: this.url,
      });

      // reset local data
      Modal.getInstance(document.getElementById(`${this.modalId}`)).hide();
    },
  },
};
</script>

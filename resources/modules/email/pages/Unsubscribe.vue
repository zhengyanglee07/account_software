<template>
  <div
    class="container mt-5 d-flex flex-column align-items-center"
    style="text-align: center"
  >
    <img
      :src="companyLogo ?? hypershapesLogo"
      width="400"
      class="m-10"
    >
    <div
      v-if="!isUnsubcibed"
      class="mt-5 d-flex flex-column align-items-center"
    >
      <h1 class="font-weight-light">
        Unsubscribe
      </h1>
      <p class="my-5">
        Are you sure you want to unsubscribe? You can no longer receive emails
        from us after performing this action.
      </p>
      <BaseButton
        class="mb-5"
        @click="unsubscribe"
      >
        Unsubscribe
      </BaseButton>
    </div>
    <div
      v-else
      class="mt-5 d-flex flex-column align-items-center"
    >
      <p class="text-muted">
        If you wish to resubscribe to our email list, you can perform it via the
        following link:
      </p>
      <BaseButton
        type="link"
        :href="subscribeLink"
      >
        Subscribe
      </BaseButton>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UnsubscribeEmail',
  layout: '',
};
</script>

<script setup>
/* eslint-disable */
import axios from 'axios';
import hypershapesLogo from '@shared/assets/media/hypershapes-logo.png';

const props = defineProps({
  unsubLink: String,
  subscribeLink: String,
  isUnsubcibed: Boolean,
  companyLogo: String,
});

const unsubscribe = () => {
  axios.post(props.unsubLink).then(({ data }) => (window.location.href = data));
};
</script>

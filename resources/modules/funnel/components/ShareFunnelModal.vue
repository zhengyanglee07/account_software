<template>
  <BaseModal
    title="Share this Funnel"
    modal-id="share-funnel-modal"
    no-footer
  >
    <BaseFormGroup
      label="
        Copy this URL and share with other Hypershapes users to give them a
        cloned copy of your amazing funnel !
      "
    >
      <BaseFormInput
        id="readonly-funnel-url"
        :model-value="shareFunnelUrl"
        readonly
        type="text"
      >
        <template #append>
          <BaseButton
            id="copy-button"
            type="link"
            data-clipboard-target="#readonly-funnel-url"
            title="Copy to clipboard"
          >
            <i class="fa-solid fa-clipboard" />
          </BaseButton>
        </template>
      </BaseFormInput>
      <p
        v-if="showSuccessMessage"
        class="text-center text-success"
      >
        Copied to clipboard!
      </p>
    </BaseFormGroup>
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue';
import ClipboardJS from 'clipboard';

const props = defineProps({
  shareFunnelUrl: {
    type: String,
    required: true,
  },
});

const showSuccessMessage = ref(false);
const clipboard = new ClipboardJS('#copy-button');

clipboard.on('success', () => {
  showSuccessMessage.value = true;
  setTimeout(() => {
    showSuccessMessage.value = false;
  }, 2000);
});
</script>

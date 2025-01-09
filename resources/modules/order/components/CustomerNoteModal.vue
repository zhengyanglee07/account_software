<template>
  <BaseModal
    title="Edit Note"
    modal-id="customerNoteModal"
  >
    <template #footer>
      <BaseButton @click="emits('submit', customerNote)">
        Save
      </BaseButton>
    </template>

    <BaseFormGroup label="Notes">
      <textarea
        id="notes"
        v-model="customerNote"
        class="form-control"
        style="height: 100px"
      />
    </BaseFormGroup>
  </BaseModal>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
  order: { type: Object, default: () => {} },
});
const emits = defineEmits(['submit']);

const customerNote = ref(null);

onMounted(() => {
  customerNote.value = props.order?.notes;
});
</script>

<style></style>

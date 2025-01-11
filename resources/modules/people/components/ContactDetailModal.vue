<template>
  <BaseModal :modal-id="modalId" no-dismiss title="Add People Profile" no-padding size="lg">
    <div class="alert alert-warning d-flex align-items-center p-5 mb-10">
      Sample Error
    </div>

    <ContactDetail :countries="countries" :states="states" />
    <template #footer>
      <BaseButton type="light" data-bs-dismiss="modal" @click="cancelAddPeopleProfile">
        Dismiss
      </BaseButton>
      <BaseButton id="add-people-button" :disabled="saving" @click="addPeopleProfile">
        <div v-if="saving">
          <i class="fas fa-circle-notch fa-spin pe-0" />
        </div>
        <span v-else>Save</span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import ContactDetail from '@people/components/ContactDetail.vue';

const props = defineProps({
  modalId: {
    type: String,
    required: true,
  },
  countries: {
    type: Array,
    required: true,
  },
  states: {
    type: Array,
    required: true,
  },
});
</script>

<style scoped lang="scss">
.modal__dividor {
  width: 100%;
  height: 0.4px;
  background-color: #ced4da;
  margin: 20px 0;
}
</style>

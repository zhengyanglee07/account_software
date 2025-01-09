<template>
  <BaseModal
    :title="isSaveTemplate ? 'Save as Template' : `Create new ${type}`"
    modal-id="template-name-modal"
  >
    <BaseFormGroup
      :label="
        isSaveTemplate
          ? `Save your ${pageType} design into template library`
          : `Enter a name for your ${type}`
      "
      :description="
        isSaveTemplate
          ? 'Your design will be available for reuse in any builder.'
          : ''
      "
    >
      <BaseFormInput
        id="template-name"
        v-model="templateName"
        type="text"
        max="191"
        :placeholder="`${isSaveTemplate ? 'Enter template name' : ''}`"
      />
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        data-bs-dismiss="modal"
        :disabled="templateName.trim() === ''"
        @click="isSaveTemplate ? saveTemplate() : $emit('create', templateName)"
      >
        Create
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useStore } from 'vuex';
import { nanoid } from 'nanoid';
import templateAPI from '@builder/api/templateAPI.js';
import eventBus from '@services/eventBus.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const props = defineProps({
  isSaveTemplate: {
    type: Boolean,
    default: true,
  },
  type: {
    type: String,
    default: 'page',
  },
});

const emit = defineEmits(['create']);

const store = useStore();
const sectionId = ref(null);
const templateName = ref('');
const pageType = computed(() => store.state.builder.pageType);

const templateObject = computed(
  () =>
    store.state.builder[store.getters['builder/pageBuilderEditableDesign']()]
);
const accountId = computed(
  () => store.state.builder.builderSettings.account_id
);

const sectionTemplateDesign = () => {
  const sectionDesign = {
    sections: {
      allIds: [],
    },
    columns: {},
    elements: {},
  };
  const newColumnIdsArray = [];
  const { sections, columns, elements } = templateObject.value;

  sections[sectionId.value].columns.forEach((columnId) => {
    const newElementArray = [];
    columns[columnId].elements.forEach((elementId) => {
      const newElementId = nanoid();
      sectionDesign.elements[newElementId] = {
        ...elements[elementId],
        id: newElementId,
      };
      newElementArray.push(newElementId);
    });
    const newColumnId = nanoid();
    sectionDesign.columns[newColumnId] = {
      ...columns[columnId],
      id: newColumnId,
      elements: newElementArray,
    };
    newColumnIdsArray.push(newColumnId);
  });

  const newSectionId = nanoid();
  sectionDesign.sections[newSectionId] = {
    ...sections[sectionId.value],
    id: newSectionId,
    columns: newColumnIdsArray,
  };
  sectionDesign.sections.allIds = [newSectionId];

  return sectionDesign;
};

const saveTemplate = () => {
  templateAPI
    .create(
      templateName.value,
      sectionId.value ? 'section' : pageType.value,
      accountId.value,
      sectionId.value ? sectionTemplateDesign() : templateObject.value
    )
    .then(({ data: { updatedTemplates } }) => {
      eventBus.$emit('userTemplateSaved');
      store.commit('builder/setUserTemplates', updatedTemplates);
      bootstrap?.then(({ Modal }) => {
        new Modal(document.getElementById('template-modal')).show();
      });
      templateName.value = '';
    })
    .catch((error) => {
      console.error(error);
    });
};

eventBus.$on('triggerTemplateNameModal', (id = null) => {
  sectionId.value = id;
});
</script>

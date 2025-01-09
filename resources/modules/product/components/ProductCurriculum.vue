<template>
  <div>
    <BaseCard has-header has-toolbar title="Curriculum">
      <template #toolbar>
        <BaseButton size="sm" has-edit-icon @click="triggerAddModuleModal">
          Add Modules
        </BaseButton>
      </template>
      <div>
        <NestedDraggable v-model="elements">
          <template #toolbar-0="{ element }">
            <BaseBadge
              :text="element.isPublished ? 'Published' : 'Draft'"
              :type="element.isPublished ? 'success' : 'secondary'"
            />
            <BaseButton
              size="sm"
              type="light-primary"
              class="me-2"
              has-add-icon
              @click="
                () => {
                  targetModuleId = element.id;
                  triggerAddLessonModal();
                }
              "
            >
              Add Lesson
            </BaseButton>
            <BaseButton
              class="me-2"
              size="sm"
              @click="triggerEditModuleModal(element)"
            >
              Edit
            </BaseButton>
            <BaseButton
              :id="`${element.id}-dropdown`"
              size="sm"
              type="light"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="fa-solid fa-ellipsis" />
            </BaseButton>

            <BaseDropdown :id="`${element.id}-dropdown`">
              <BaseDropdownOption
                text="Duplicate"
                @click="
                  () => {
                    duplicateModule(element);
                  }
                "
              />
              <BaseDropdownOption
                text="Delete"
                @click="triggerDeleteModuleModal(element)"
              />
            </BaseDropdown>
          </template>
          <template #toolbar-1="{ element }">
            <BaseBadge
              :text="element.isPublished ? 'Published' : 'Draft'"
              :type="element.isPublished ? 'success' : 'secondary'"
            />
            <BaseButton
              class="me-2"
              size="sm"
              @click="triggerEditLessonModal(element)"
            >
              Edit
            </BaseButton>

            <BaseButton
              :id="`${element.id}-dropdown`"
              size="sm"
              type="light"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="fa-solid fa-ellipsis" />
            </BaseButton>

            <BaseDropdown :id="`${element.id}-dropdown`">
              <BaseDropdownOption
                text="Duplicate"
                @click="duplicateLesson(element)"
              />
              <BaseDropdownOption
                text="Delete"
                @click="triggerDeleteLessonModal(element)"
              />
            </BaseDropdown>
          </template>
        </NestedDraggable>
      </div>
    </BaseCard>

    <form @submit.prevent="updateModule">
      <BaseModal
        :small="true"
        modal-id="course-module-modal"
        :title="`${isEditModule ? 'Edit' : 'Add'} Module`"
      >
        <BaseFormGroup label="Title" required>
          <BaseFormInput v-model="moduleData.name" required />
        </BaseFormGroup>
        <BaseFormGroup label="Status">
          <BaseFormRadio
            :model-value="moduleData.isPublished ? 'true' : 'false'"
            value="false"
            @input="
              (val) => {
                moduleData.isPublished = val === 'true';
              }
            "
          >
            Draft
          </BaseFormRadio>
          <BaseFormRadio
            :model-value="moduleData.isPublished ? 'true' : 'false'"
            :disabled="moduleData.elements.every((e) => !e.isPublished)"
            value="true"
            @input="
              (val) => {
                moduleData.isPublished = val === 'true';
              }
            "
          >
            Publish
          </BaseFormRadio>
        </BaseFormGroup>
        <template #footer>
          <BaseButton is-submit>
            {{ isEditModule ? 'Update' : 'Add' }}
          </BaseButton>
        </template>
      </BaseModal>
    </form>

    <form @submit.prevent="submitLesson">
      <BaseModal
        :small="true"
        modal-id="course-lesson-modal"
        :title="`${isEditLesson ? 'Edit' : 'Add'} Lesson`"
      >
        <BaseFormGroup label="Title" required>
          <BaseFormInput v-model="lessonData.name" required />
        </BaseFormGroup>
        <BaseFormGroup label="Module" required>
          <BaseFormSelect v-model="targetModuleId" required>
            <option value="none" disabled>Please select module</option>
            <option
              v-for="(element, index) in elements"
              :key="index"
              :value="element?.id"
            >
              {{ element.name }}
            </option>
          </BaseFormSelect>
        </BaseFormGroup>
        <BaseFormGroup label="Video Source" required>
          <BaseFormSelect v-model="selectedVideoSource" required>
            <option value="Youtube">Youtube</option>
            <option value="Vimeo">Vimeo</option>
          </BaseFormSelect>
        </BaseFormGroup>
        <BaseFormGroup
          label="Video URL"
          :description="`Paste a URL from ${selectedVideoSource}`"
          required
        >
          <BaseFormInput v-model="lessonData.videoUrl" type="url" required />
        </BaseFormGroup>
        <BaseFormGroup v-if="selectedVideoSource === 'Youtube'" required>
          <BaseFormCheckBox
            v-model="lessonData.parameter.isHideBranding"
            :value="true"
          >
            Hide Youtube Branding
          </BaseFormCheckBox>
        </BaseFormGroup>
        <BaseFormGroup label="Description">
          <TextEditor
            ref="lessonEditor"
            v-model="lessonData.description"
            editor-id="lesson-description"
          />
        </BaseFormGroup>

        <BaseFormGroup label="Status">
          <BaseFormRadio
            :model-value="lessonData.isPublished ? 'true' : 'false'"
            value="false"
            @input="
              (val) => {
                lessonData.isPublished = val === 'true';
              }
            "
          >
            Draft
          </BaseFormRadio>
          <BaseFormRadio
            :model-value="lessonData.isPublished ? 'true' : 'false'"
            value="true"
            @input="
              (val) => {
                lessonData.isPublished = val === 'true';
              }
            "
          >
            Publish
          </BaseFormRadio>
        </BaseFormGroup>
        <template #footer>
          <BaseButton is-submit>
            {{ isEditLesson ? 'Edit' : 'Add' }}
          </BaseButton>
        </template>
      </BaseModal>
    </form>

    <BaseModal
      :small="true"
      modal-id="delete-module-modal"
      title="Delete Confirmation"
    >
      Delete this module will also delete all the lessons inside it, and the
      deleted content cannot be restored. Are you sure want to proceed?
      <template #footer>
        <BaseButton @click="deleteModule"> Confirm </BaseButton>
      </template>
    </BaseModal>
    <BaseModal
      :small="true"
      modal-id="delete-lesson-modal"
      title="Delete Confirmation"
    >
      Are you sure want to delete this lesson?
      <template #footer>
        <BaseButton @click="deleteLesson"> Confirm </BaseButton>
      </template>
    </BaseModal>

    <ImageUploader type="description" @update-value="chooseImage" />
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Modal } from 'bootstrap';
import Draggable from 'vuedraggable';
import axios from 'axios';
import clone from 'clone';
import cloneDeep from 'lodash/cloneDeep';

import TextEditor from '@shared/components/TextEditor.vue';
import ImageUploader from '@shared/components/ImageUploader.vue';
import eventBus from '@services/eventBus.js';

const props = defineProps({
  modelValue: { type: [Object], default: () => {} },
});

const emit = defineEmits(['update:modelValue']);

const elements = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});

const selectedVideoSource = ref('Youtube');

const targetModuleId = ref('');
const targetLessonId = ref('');
const isEditModule = ref(false);
const isEditLesson = ref(false);

const moduleData = reactive({});
const setDefaultModule = () => {
  Object.entries({
    id: '',
    name: '',
    isPublished: false,
    hasCollapse: true,
    elements: [],
  }).forEach(([key, value]) => {
    moduleData[key] = value;
  });
};

const lessonData = reactive({});
const setDefaultLesson = () => {
  Object.entries({
    id: '',
    moduleId: '',
    name: '',
    isPublished: true,
    videoUrl: '',
    description: '<p></p>',
    parameter: {
      isHideBranding: false,
    },
    elements: [],
  }).forEach(([key, value]) => {
    lessonData[key] = value;
  });
};
setDefaultModule();
setDefaultLesson();

watch(lessonData, () => {
  const url = lessonData.videoUrl;
  if (url.includes('youtube.com') || url.includes('youtu.be')) {
    const youtubeRegex =
      /(youtube.com\/watch\?v=|youtu.be\/)([a-zA-Z0-9_-]{11})/;
    const youtubeMatch = url.match(youtubeRegex);
    if (youtubeMatch) {
      selectedVideoSource.value = 'Youtube';
    }
  } else if (url.includes('vimeo.com')) {
    const vimeoRegex = /vimeo.com\/(\d+)/;
    const vimeoMatch = url.match(vimeoRegex);
    if (vimeoMatch) {
      selectedVideoSource.value = 'Vimeo';
    }
  }
});

const modal = reactive({
  module: null,
  lesson: null,
  alert: null,
});
const triggerAddModuleModal = () => {
  isEditModule.value = false;
  setDefaultModule();
  modal.module = new Modal(document.getElementById('course-module-modal'));
  modal.module.show();
};
const triggerAddLessonModal = () => {
  isEditLesson.value = false;
  setDefaultLesson();
  modal.lesson = new Modal(document.getElementById('course-lesson-modal'));
  modal.lesson.show();
};
const triggerDeleteModuleModal = (element) => {
  targetModuleId.value = element.id;
  modal.alert = new Modal(document.getElementById('delete-module-modal'));
  modal.alert.show();
};
const triggerEditModuleModal = (element) => {
  isEditModule.value = true;
  targetModuleId.value = element.id;
  Object.entries(element).forEach(([key, value]) => {
    moduleData[key] = value;
  });
  modal.module = new Modal(document.getElementById('course-module-modal'));
  modal.module.show();
};
const triggerEditLessonModal = (element) => {
  isEditLesson.value = true;
  targetLessonId.value = element.id;

  targetModuleId.value = elements.value.find((e) =>
    e.elements.some((ee) => ee.id === targetLessonId.value)
  )?.id;
  Object.entries(element).forEach(([key, value]) => {
    lessonData[key] = value;
  });
  modal.lesson = new Modal(document.getElementById('course-lesson-modal'));
  modal.lesson.show();
};
const triggerDeleteLessonModal = (element) => {
  targetLessonId.value = element.id;
  modal.alert = new Modal(document.getElementById('delete-lesson-modal'));
  modal.alert.show();
};

const getModuleById = () =>
  elements.value.find((e) => e.id === targetModuleId.value);

const uuidv4 = () => {
  return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (c) =>
    (c ^ (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))) // eslint-disable-line
      .toString(16)
  );
};

const updateModule = () => {
  if (isEditModule.value) {
    const selectedModule = elements.value.find(
      (e) => e.id === targetModuleId.value
    );
    Object.entries(moduleData).forEach(([key, value]) => {
      selectedModule[key] = value;
    });
  } else {
    moduleData.id = uuidv4();
    elements.value.push({ ...moduleData });
  }
  modal.module.hide();
};
const duplicateModule = (element) => {
  const newModule = clone(element);
  newModule.id = uuidv4();
  newModule.name += ' - copy';
  newModule.elements.forEach((e) => {
    e.id = uuidv4();
    e.name += ' - copy';
    e.moduleId = newModule.id;
  });
  elements.value.push({ ...newModule });
};
const deleteModule = () => {
  const index = elements.value.findIndex((e) => e.id === targetModuleId.value);
  elements.value.splice(index, 1);
  modal.alert.hide();
};

const addLesson = () => {
  lessonData.id = uuidv4();
  lessonData.moduleId = targetModuleId.value;
  getModuleById().elements.push({ ...lessonData });
  modal.lesson.hide();
};
const editLesson = () => {
  let selectedLesson = null;

  elements.value.forEach((e) => {
    const lessonIndex = e.elements.findIndex(
      (ee) => ee.id === targetLessonId.value
    );
    if (lessonIndex < 0) return;
    selectedLesson = e.elements[lessonIndex];

    Object.entries(lessonData).forEach(([key, value]) => {
      selectedLesson[key] = value;
    });
    if (selectedLesson.moduleId === targetModuleId.value) return;
    e.elements.splice(lessonIndex, 1);
    getModuleById().elements.push({ ...selectedLesson });
  });

  modal.lesson.hide();
};
const duplicateLesson = (element) => {
  const newLesson = clone(element);
  newLesson.id = uuidv4();
  newLesson.name += ' - copy';
  const targetModule = elements.value.find((e) => e.id === newLesson.moduleId);
  targetModule.elements.push({ ...newLesson });
};
const deleteLesson = () => {
  elements.value.forEach((e) => {
    const lessonIndex = e.elements.findIndex(
      (ee) => ee.id === targetLessonId.value
    );
    if (lessonIndex < 0) return;
    e.elements.splice(lessonIndex, 1);
  });
  modal.alert.hide();
};
const submitLesson = () => {
  if (isEditLesson.value) editLesson();
  else addLesson();
};

watch(
  () => elements.value,
  () => {
    elements.value.forEach((module, moduleIndex) => {
      module.order = moduleIndex;
      module.elements.forEach((lesson, lessonIndex) => {
        lesson.moduleId = module.id;
        lesson.order = lessonIndex;
      });
      if (!module.isPublished) return;
      module.isPublished =
        module.elements.length > 0 &&
        module.elements.some((ee) => ee.isPublished);
    });
  },
  { deep: true }
);

const lessonEditor = ref(null);
const chooseImage = (e) => {
  eventBus.$emit('lesson-description-editor-insert-image', e);
};
</script>

<style></style>

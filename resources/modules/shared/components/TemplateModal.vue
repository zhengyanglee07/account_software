<template>
  <BaseModal
    modal-id="template-modal"
    title="Template Library"
    size="xl"
    no-footer
    manual
    scrollable
    is-image-modal
  >
    <template #tabs>
      <template v-if="isPreview">
        <BaseButton
          type="secondary"
          class="text-gray-600"
          @click="isPreview = false"
        >
          <i class="fa fa-chevron-left text-gray-600 me-2" />
          Back to Library
        </BaseButton>
        <BaseButton
          v-if="isPreview"
          class="text-white"
          data-bs-dismiss="modal"
          @click="storeTemplateObject(selectedTemplate.element)"
        >
          Insert
        </BaseButton>
      </template>
      <template v-else>
        <BaseButton
          v-for="(tab, index) in tabs"
          :key="index"
          type="link"
          class="text-capitalize"
          :active="selectedTab === tab"
          @click="selectedTab = tab"
        >
          {{ tab }}
        </BaseButton>
      </template>
    </template>

    <div
      v-if="isPreview"
      class="preview-container"
    >
      <iframe
        class="w-100 border-0"
        :src="
          selectedTemplate.type === 'theme'
            ? `/builder/theme/${selectedTemplate.id}/preview`
            : `/builder/template/${selectedTemplate.id}/preview`
        "
      />
    </div>
    <div
      v-else
      class="d-flex"
      style="background: #eff2f5"
    >
      <div
        v-if="selectedTab !== 'template'"
        class="left-panel w-250px bg-white d-flex flex-column px-3 py-4"
        :class="isInMobile ? 'd-none' : 'd-flex'"
      >
        <BaseDropdownOption
          v-for="(tag, index) in templateTags"
          :key="index"
          :text="tag"
          :class="{
            active: selectedTag === tag,
          }"
          @click="selectedTag = tag"
        />
      </div>

      <div class="right-panel p-4">
        <div
          v-show="selectedTab === 'template'"
          class="user-template-container"
        >
          <BaseDatatable
            title="template"
            no-edit-action
            no-hover
            :table-headers="tableHeaders"
            :table-datas="userTemplates"
            @delete="deleteTemplate"
          >
            <template
              #action-options="{
                row: { viewLink, element: templateObject },
              }"
            >
              <BaseDropdownOption
                text="View"
                :link="viewLink"
                is-open-in-new-tab
              />
              <BaseDropdownOption
                text="Insert"
                data-bs-dismiss="modal"
                @click="storeTemplateObject(templateObject)"
              />
            </template>
          </BaseDatatable>
        </div>

        <div
          v-show="selectedTab !== 'template'"
          :class="
            isResponsiveHeight ? 'mansory-container' : 'template-container'
          "
          :style="isInMobile ? { 'column-count': 2 } : { 'column-count': 3 }"
        >
          <div
            v-for="template in [...blankTemplate, ...generalTemplates]"
            :key="template.id"
            :class="
              isResponsiveHeight
                ? 'mansory-container__item'
                : 'template-container__item'
            "
            :style="
              filterByTag(selectedTag, { template })
                ? null
                : { display: 'none' }
            "
          >
            <div
              :class="
                isResponsiveHeight
                  ? 'mansory-container__preview'
                  : 'template-container__preview'
              "
              :style="{
                backgroundImage: isResponsiveHeight
                  ? ''
                  : `url(${template.image_path})`,
              }"
            >
              <img
                v-if="isResponsiveHeight && template?.image_path"
                :src="template?.image_path"
                :alt="template.title"
              >
              <div
                :class="
                  isResponsiveHeight
                    ? 'mansory-container__overlay'
                    : 'template-container__overlay'
                "
              >
                <BaseButton
                  v-if="template.id"
                  class="template-button"
                  :style="{
                    width: isResponsiveHeight ? 'auto' : '60%',
                  }"
                  @click="triggerPreviewPanel(template)"
                >
                  <i class="fas fa-plus" />
                  &nbsp; Preview
                </BaseButton>
                <BaseButton
                  class="template-button"
                  data-bs-dismiss="modal"
                  :class="isResponsiveHeight ? 'ms-3' : 'mt-3'"
                  :style="{
                    width: isResponsiveHeight ? 'auto' : '60%',
                  }"
                  @click="storeTemplateObject(template.element)"
                >
                  <i class="fas fa-plus" />
                  &nbsp; Insert
                </BaseButton>
              </div>
            </div>
            <p class="template-title">
              {{ template.title }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </BaseModal>

  <TemplateNameModal
    :type="sectionType"
    :is-save-template="false"
    @create="insertTemplate"
  />
</template>

<script setup>
import { computed, ref, inject, onBeforeMount, watch } from 'vue';
import { useStore } from 'vuex';
import templateAPI from '@builder/api/templateAPI.js';
import eventBus from '@services/eventBus.js';
import BaseModal from '@shared/components/BaseModal.vue';
import axios from 'axios';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const props = defineProps({
  isInBuilder: {
    type: Boolean,
    default: false,
  },
  sectionType: {
    type: String,
    default: undefined,
  },
  funnelId: {
    type: Number,
    default: null,
  },
  landingpageId: {
    type: Number,
    default: null,
  },
  landingIndex: {
    type: Number,
    default: null,
  },
});

const store = useStore();
const isInMobile = computed(() =>
  typeof window !== `undefined` ? window?.screen?.width <= 480 : false
);

const tableHeaders = [
  {
    name: 'Name',
    key: 'name',
  },
  {
    name: 'Type',
    key: 'type',
  },
  {
    name: 'Creation Date Time',
    key: 'created_at',
    isDateTime: true,
  },
];

const isPreview = ref(false);
const selectedTemplate = ref(null);
const selectedTag = ref('All');
const sectionId = ref(null);
const dbGeneralTemplates = ref([]);
const $toast = inject('$toast');
const selectedTab = ref('section');

//* 'page', 'theme', 'header', 'footer'
const tabs = computed(() => {
  if (props.sectionType === 'theme') return ['theme'];
  return [
    'section',
    props.sectionType === 'landing' || props.sectionType === undefined
      ? 'page'
      : props.sectionType,
    props.sectionType === 'template' ? null : 'template',
  ];
});

const blankTemplate = computed(() =>
  props.isInBuilder || props.sectionType === 'theme'
    ? []
    : [
        {
          id: null,
          title: 'Start from scratch',
          image_path: null,
          element: '[]',
        },
      ]
);

const fetchTemplates = () => {
  return templateAPI
    .index()
    .then(({ data: { userTemplates, generalTemplates } }) => {
      store.commit('builder/setUserTemplates', userTemplates);
      dbGeneralTemplates.value = generalTemplates;
    })
    .catch((error) => {
      console.error(error);
    });
};

onBeforeMount(() => {
  fetchTemplates();
});

const userTemplates = computed(() => {
  return store.state.builder.userTemplates.map((template) => {
    return {
      ...template,
      viewLink: `/builder/userTemplate/${template.id}/preview`,
    };
  });
});

const isResponsiveHeight = computed(() =>
  ['section', 'header', 'footer'].includes(selectedTab.value)
);

const templateTags = computed(() => {
  let tags = [];
  switch (selectedTab.value) {
    case 'section':
      tags = [
        'All',
        'Achievement / Statistic',
        'About / Story',
        'Brands / Partners',
        'Countdown',
        'Contact',
        'Call To Action',
        'FAQ',
        'Feature',
        'Pricing',
        'Professional Profile',
        'Services',
        'Testimonial',
      ];
      break;
    case 'page':
      tags = [
        'All',
        'Arts & Design',
        'Automative & Cars',
        'Beauty & Wellness',
        'Consulting & Coaching',
        'Education',
        'Fashion & Accessories',
        'Farming & Gardening',
        'Finance & Law',
        'Food & Beverage',
        'Home & Decor',
        'Property & Real Estate',
        'Service & Maintenance',
        'Sports & Ourdoors',
        'Society',
      ];
      break;
    default:
      tags = ['All'];
  }
  return tags;
});

const generalTemplates = computed(() =>
  dbGeneralTemplates.value.filter(
    (template) => template.type === selectedTab.value
  )
);

const triggerPreviewPanel = (template) => {
  selectedTemplate.value = template;
  isPreview.value = true;
};

const createFromTemplate = (templateName) => {
  let url;
  if (props.sectionType === 'page' || props.sectionType === undefined) {
    url = '/landing/create';
  } else if (props.sectionType === 'theme') {
    url = '/online-store/theme';
  } else {
    url = '/online-store/createNewSection';
  }

  axios
    .post(url, {
      pageName: templateName,
      funnelId: props.funnelId,
      pageIndex: props.landingIndex,
      sectionType: props.sectionType,
      newObject: selectedTemplate.value,
    })
    .then((response) => {
      const {type: sectionType, reference_key: refKey } = response.data;
      if (props.sectionType === 'theme') {
        window.location.reload();
      } else {
        window.location.replace(`/builder/${sectionType === 'landing' ? 'landing' : props.sectionType}/${refKey}/editor`);
      }
    })
    .catch((error) => {
      $toast.error('Error', error);
      console.error(error);
    });
};

const insertTemplateToNewLanding = (templateName) => {
  const templateObject = selectedTemplate.value;
  const filteredTemplateObject = store.getters['builder/filterElements']({
    type: props.sectionType,
    templateObject,
  });
  const newObject =
    templateObject === undefined ? '[]' : filteredTemplateObject;
  axios
    .put('/landing/insert/template', {
      id: props.landingpageId,
      templateObject: newObject,
      pageName: templateName,
      sectionType: props.sectionType,
    })
    .then((response) => {
      window.location.replace(`/builder/landing/${response.data}/editor`);
    })
    .catch((error) => {
      console.error(error);
    });
};

const insertTemplate = (templateName) => {
  if (props.isInBuilder) {
    store.commit('builder/insertTemplate', {
      template: selectedTemplate.value,
      parentSectionId: sectionId.value,
    });
    eventBus.$emit('templateAdded');
  } else if (props.landingpageId) {
    insertTemplateToNewLanding(templateName);
  } else {
    createFromTemplate(templateName);
  }
  selectedTemplate.value = null;
  isPreview.value = false;
};

const storeTemplateObject = (templateObject) => {
  selectedTemplate.value = templateObject;
  if (props.isInBuilder) {
    insertTemplate();
    return;
  }
  bootstrap?.then(({ Modal }) => {
    new Modal(document.getElementById('template-name-modal')).show();
  });
};

const deleteTemplate = (id) => {
  templateAPI
    .delete(id)
    .then(() => {
      store.commit('builder/deleteUserTemplate', id);
      $toast.success("Success", "Successfully deleted template");
    })
    .catch((error) => {
      console.log(error);
      $toast.error("Error", 'Failed to delete template. Contact our support for more details');
    });
};

const filterByTag = (tag, templateAssignedTags) => {
  let assignedTags = JSON.parse(JSON.stringify(templateAssignedTags)).template
    .tags;
  // for start from stratch
  if (assignedTags === undefined && tag === 'All') {
    return true;
  }

  // for other template tags
  if (assignedTags !== undefined) {
    assignedTags = JSON.parse(assignedTags);
    for (let i = 0; i < assignedTags.length; i++) {
      if (tag === assignedTags[i]) {
        return true;
      }
    }
    return false;
  }
  return false;
};

watch(
  () => props.sectionType,
  (newValue) => {
    if (newValue === 'theme') {
      selectedTab.value = newValue;
    } else {
      selectedTab.value = 'section';
    }
  }
);

eventBus.$on('userTemplateSaved', () => {
  selectedTab.value = 'template';
  isPreview.value = false;
});

eventBus.$on('addTopSection', (id) => {
  sectionId.value = id;
});
</script>

<style lang="scss" scoped>
.left-panel {
  box-shadow: 0 0 28px 0 rgb(82 63 105 / 5%);
  position: sticky;
  top: 0;
}

.right-panel {
  width: 100%;
  height: calc(80vh - 55px);
  overflow-y: auto;
}

.mansory-container {
  column-count: 3;
  column-gap: 10px;

  &__item {
    width: 100%;
    padding: 0.5rem;
    background: #fff;
    margin-bottom: 1rem;
    display: inline-block;
    border-radius: 0.475rem;
    box-shadow: 0px 0px 20px 0px rgb(76 87 125 / 2%);
  }

  &__overlay {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    top: 0;
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: rgba(#202930, 0.8);
    opacity: 0;

    &:hover {
      opacity: 1;
      cursor: pointer;
    }
  }
}

.template-container {
  display: flex;
  flex-wrap: wrap;
  width: 100%;
  height: 100%;
  justify-content: flex-start;
  align-items: flex-start;

  &__item {
    justify-content: space-between;
    width: 190px;
    height: 240px;
    padding: 8px;
    margin: 0 12px 12px 0;
    background: #fff;
    border-radius: 0.475rem;
  }

  &__preview {
    height: 190px;
    background-size: cover;
  }

  &__overlay {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    width: 100%;
    height: 100%;
    background-color: rgba(32, 41, 48, 0.8);
    opacity: 0;

    &:hover {
      opacity: 1;
      cursor: pointer;
    }
  }
}

.mansory-container__preview {
  height: auto;
  min-height: 45px;
  position: relative;

  img {
    width: 100%;
    height: auto;
  }
}

.template-title {
  margin: 0;
  margin-top: 5px;
  font-size: 14px;
}

.template-button {
  display: flex;
  justify-content: center;
  align-items: center;
  color: #fff;
  font-size: 12px;
  margin: 2px;
  border-radius: 20px;

  &__icon {
    color: white;
    padding: 0px;
  }
}

.preview-container {
  display: flex;
  height: 100%;
  width: 100%;
  overflow-y: hidden;
  min-height: 550px;

  iframe {
    width: 100%;
    border: 0;
  }
}
</style>

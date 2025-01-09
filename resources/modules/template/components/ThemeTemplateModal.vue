<template>
  <BaseModal
    no-dismiss
    modal-id="theme-builder"
    manual
    title="Theme Template"
  >
    <div class="modal-body">
      <div
        v-show="step <= 2"
        class="template-selection"
      >
        <h2>
          {{ capitalize(activeType) }}
        </h2>
        <div class="row">
          <div
            v-for="template in filteredTemplatesByType"
            :key="template.id"
            class="template-container"
            :class="{
              active: selectedTemplates[activeType] === template,
            }"
            @click="selectedTemplates[activeType] = template"
          >
            <div class="template-container__preview">
              <img
                :src="template.image_path"
                height="100%"
                width="100%"
              >
            </div>
            <!-- Template Name -->
            <p class="template-container__title">
              {{ template.title }}
            </p>
          </div>
        </div>
      </div>

      <div
        v-show="step === 3"
        class="theme-template-preview"
      >
        <div class="template-details mb-4">
          <BaseFormGroup
            for="add-new-tag-input"
            label="Template name"
          >
            <BaseFormInput
              id="add-new-tag-input"
              v-model="templateName"
              type="text"
            />
          </BaseFormGroup>
        </div>
        <div
          v-for="(template, type) in selectedTemplates"
          :key="type"
          class="mb-4"
        >
          <template v-if="template">
            <h2>{{ capitalize(type) }} Template : {{ template.title }}</h2>
            <div class="template-container--inline w-100">
              <div
                v-if="template"
                class="template-container__preview"
              >
                <img
                  :src="template.image_path"
                  height="100%"
                  width="100%"
                >
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
    <template #footer>
      <BaseButton
        type="light"
        data-bs-dismiss="modal"
      >
        Dismiss
      </BaseButton>
      <BaseButton
        type="secondary"
        :disabled="step === 0"
        @click="decrementStep"
      >
        Previous
      </BaseButton>
      <BaseButton
        id="goToNextStep"
        @click="incrementStep"
      >
        {{ step > 2 ? 'Generate' : 'Next' }}
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import templateAPI from '@template/api/templateAPI.js';

export default {
  name: 'ThemeTemplateModal',

  props: { allTemplates: { type: Array, default: () => [] } },

  data() {
    return {
      step: 0,
      templateName: null,
      selectedTemplates: {
        header: null,
        page: null,
        footer: null,
      },
      tags: ['All'],
    };
  },

  computed: {
    activeType() {
      switch (this.step) {
        case 0:
          return 'header';
        case 1:
          return 'page';
        case 2:
          return 'footer';
        default:
          return '';
      }
    },

    filteredTemplatesByType() {
      return this.allTemplates.filter((e) => e.type === this.activeType);
    },

    isTemplatesEmpty() {
      return Object.values(this.selectedTemplates).some((e) => e === null);
    },
  },

  methods: {
    decrementStep() {
      this.step -= 1;
    },

    incrementStep() {
      if (this.step <= 2) {
        this.step += 1;
        return;
      }
      this.validateIsEmpty();
    },

    capitalize(s) {
      if (typeof s !== 'string') return '';
      return s.charAt(0).toUpperCase() + s.slice(1);
    },

    validateIsEmpty() {
      if (this.isTemplatesEmpty) {
        this.$toast.error(
          'Error',
          'Must select one header, one page and one footer template to generate theme builder'
        );
      } else if (
        this.templateName === null ||
        this.templateName.trim() === ''
      ) {
        this.$toast.error('Error', 'Template name cannot be empty');
      } else {
        this.saveThemeTemplate();
      }
    },

    saveThemeTemplate() {
      const { header, page, footer } = this.selectedTemplates;

      templateAPI
        .updateOrCreate('/template/create', {
          title: this.templateName,
          type: 'theme',
          tags: JSON.stringify(this.tags),
          template_objects: JSON.stringify({
            header: header.id,
            page: page.id,
            footer: footer.id,
          }),
        })
        .then((response) => {
          this.$inertia.visit(
            `/template/theme/${response.data.id}?source=preview&snapshot=true`
          );
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },
};
</script>

<style scoped lang="scss">
.primary-square-button--grey {
  background: #545b62;
  //margin-left: 10px;
  margin-right: 20px;
}

.template-container {
  cursor: pointer;
  width: 49%;
  display: inline-block;
  margin-right: 2%;
  margin-bottom: 20px;
  background: #fff;
  // border: 1px solid transparent;
  // box-shadow: 0 0.75rem 1.5rem rgb(18 38 63 / 4%);
  border-radius: 2.5px;
  border: 1px solid #ced4da;

  &:nth-child(even) {
    margin-right: 0;
  }

  &__title {
    text-align: center;
    font-size: 13px;
  }
}

.modal-body {
  height: 100%;
  max-height: 100%;
  background-color: #f8f8fb;
}

.active {
  border: 1px solid red;
}

h2,
label.m-container__text {
  padding: 0;
  margin-bottom: 10px;
}
</style>

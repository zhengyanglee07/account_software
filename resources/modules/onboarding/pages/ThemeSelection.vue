<template>
  <BasePageLayout is-setting>
    <BaseSettingLayout
      title="Step 1: Choose a theme for your online store homepage"
      is-onboarding
    >
      <template #description>
        <p style="padding-left: 10px">
          You can easily customize the design of the theme via our builder
        </p>
      </template>

      <template #content>
        <div class="row template-container">
          <p
            v-if="generalTemplates?.length === 0"
            class="fs-3 text-danger"
          >
            No template found
          </p>
          <div
            v-for="template in generalTemplates"
            v-else
            :key="template.id"
            class="col-4 template-container__item"
          >
            <div
              class="template-container__preview"
              :style="`background-image: url(${template.image_path});`"
            >
              <div class="template-container__overlay">
                <BaseButton
                  v-if="template.id"
                  class="template-button"
                  :href="`/builder/theme/${template.id}/preview`"
                  is-open-in-new-tab
                >
                  <i class="me-2 fas fa-plus" />
                  Preview
                </BaseButton>
                <BaseButton
                  class="template-button mt-3"
                  @click="storeTemplateObject(template)"
                >
                  <i class="me-2 fas fa-plus" />
                  Insert
                </BaseButton>
              </div>
            </div>
            <p class="template-title">
              {{ template.title }}
            </p>
          </div>
        </div>
      </template>

      <template #footer>
        <BaseButton
          v-if="generalTemplates?.length === 0"
          type="link"
          :href="nextUrl"
          class="me-3"
        >
          Skip
          <i class="ms-2 fa-solid fa-arrow-right" />
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script>
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';
import axios from 'axios';

export default {
  layout: OnboardingLayout,
  props: {
    generalTemplates: { type: Array, default: () => [] },
  },
  data() {
    return {
      isResponsiveHeight: false,
      nextUrl: '/onboarding/save',
    };
  },

  mounted() {},

  methods: {
    storeTemplateObject(template) {
      localStorage.setItem(
        'onlineStoreThemes',
        JSON.stringify({
          pageName: template.title,
          newObject: template.template_objects,
        })
      );
      this.$inertia.visit(this.nextUrl);
    },
  },
};
</script>

<style lang="scss" scoped>
.template-container {
  display: flex;
  flex-wrap: wrap;
  width: 100%;
  height: 100%;
  justify-content: flex-start;
  align-items: flex-start;

  &__item {
    justify-content: space-between;
    height: 500px;
    padding: 8px;
    background: #fff;
    border-radius: 0.475rem;
    margin-bottom: 100px;
  }

  &__preview {
    height: 500px;
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
.template-button {
  display: flex;
  justify-content: center;
  align-items: center;
  color: #fff;
  margin: 2px;
  border-radius: 20px;
  width: 60% !important;
}
.template-title {
  margin: 5px 0 0;
  font-size: 1.5rem;
}

@media screen and (max-width: 415px) {
  .template-container {
    &__item {
      width: 50% !important;
      height: 200px;
      margin-bottom: 50px;
    }
    &__preview {
      height: 200px;
    }
  }
  .template-title {
    font-size: 1rem;
  }
}
</style>

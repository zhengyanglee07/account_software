<template>
  <div>
    <BaseEmailSetupSection
      title="Content"
      subtitle="Design the content for your email"
      :complete="!!email['email_design_id']"
      :show-collapse="showPreviewCollapse"
    >
      <template #custom-main-btn />

      <template #collapse>
        <!-- temporarily comment out due to html2canvas CORS issue -->
        <!-- <preview-html
          v-if="email['email_design_id']"
          class="mt-5 mb-5"
          style="cursor: pointer; width: 275px;"
          @click.native="navigateToEditEmail"
          :html="html"
          :width="275"
          :height="360"
        /> -->
      </template>
    </BaseEmailSetupSection>
  </div>
</template>

<script>
import BaseEmailSetupSection from '@email/components/BaseEmailSetupSection.vue';
// import PreviewHtml from '@components/canvas/PreviewHtml';
import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
  name: 'EmailSetupContentSection',
  components: {
    BaseEmailSetupSection,
    // PreviewHtml,
  },
  props: {
    email: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      html: null,
      showPreviewCollapse: true,
    };
  },
  computed: {
    emailRefKey() {
      return this.email.reference_key;
    },
    emailDesignRefKey() {
      return this.email.email_design_reference_key;
    },
  },
  // display email design preview
  mounted() {
    if (this.emailDesignRefKey) {
      axios
        .get(
          `/emails/${this.emailRefKey}/design/${this.emailDesignRefKey}?field=html`
        )
        .then(({ data }) => {
          this.html = data.emailDesign.html;
        });
    }
  },
  methods: {
    togglePreview() {
      this.showPreviewCollapse = !this.showPreviewCollapse;
    },
    getEmailDesignURL() {
      return `/emails/${this.emailRefKey}/design/${this.emailDesignRefKey}/edit?source=standard&key=${this.emailRefKey}`;
    },
    navigateToEditEmail() {
      this.$inertia.visit(this.getEmailDesignURL());
    },
    createEmailDesign() {
      new Modal(document.getElementById('eb-display-templates-modal')).show();
      // this.$modal.show('eb-display-templates-modal', {
      //   create: true,
      //   emailRefKey: this.emailRefKey,
      // });
    },
  },
};
</script>

<style scoped lang="scss">
.secondary-button {
  font-family: $base-font-family;
  font-size: 14px;
  font-weight: 500;
  color: #1a73e8;
  width: 7.5rem;
  padding: 5px 0;
  border-radius: 10rem;
  border: 1px solid #1a73e8;
  background-color: #fff;
  text-transform: uppercase;

  &--a {
    text-align: center;

    &:hover {
      text-decoration: none;
    }
  }

  &:focus {
    outline: none;
  }

  &:hover {
    color: #1a65c8;
    border-color: #1a65c8;
  }
}
</style>

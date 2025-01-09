<template>
  <BaseModal
    :modal-id="modalId"
    :title="`Edit email of ${selectedType.label.toLowerCase()}`"
  >
    <BaseFormGroup label="Subject">
      <BaseFormInput
        id="subject-input"
        v-model="emailType[selectedType.value].subject"
        type="text"
        @click="cursorAt = 'subject-input'"
      />
      <BaseButton
        id="merge-tag-selection"
        class="mt-2"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        has-dropdown-icon
      >
        Insert value
      </BaseButton>

      <BaseDropdown
        id="merge-tag-selection"
        size="md"
      >
        <BaseDropdownOption
          v-for="(option, index) in inputOptions"
          :key="index"
          :text="option.name"
          @click="inputMergeTag(option.value)"
        />
      </BaseDropdown>
    </BaseFormGroup>
    <BaseFormGroup label="Content">
      <div
        v-for="(et, index) in Object.values(emailType)"
        :key="index"
      >
        <ProductDescriptionEditor
          v-if="selectedType.value === et.type"
          ref="editor"
          :editor-id="et.type + '-template'"
          :data="emailType[selectedType.value]"
          :property="'template'"
          class="general-card-section w-100"
          @input="emailType[selectedType.value].template = value"
          @click="cursorAt = selectedType.value + '-template'"
          @selection-change="cursorIndex = $event"
        />
      </div>

      <!-- <ProductDescriptionEditor
        :ref="
          (el) => {
            editor[selectedType.value] = el;
          }
        "
        :editor-id="selectedType.value + '-template'"
        :data="tempEmailContent"
        :property="'template'"
        class="general-card-section w-100"
        @input="tempEmailContent.template = value"
        @click="cursorAt = selectedType.value + '-template'"
        @selectionChange="theQuill = $event"
      /> -->

      <!-- <ProductDescriptionEditor
        ref="quillEditor"
        :editor-id="selectedType.value + '-template'"
        :data="emailType[selectedType.value]"
        :property="'template'"
        class="general-card-section w-100"
        @input="emailType[selectedType.value].template = value"
        @click="cursorAt = selectedType.value + '-template'"
        @selectionChange="theQuill = $event"
      /> -->
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        class="me-3"
        type="secondary"
        @click="$emit('openTestEmailModal', emailType[selectedType.value])"
      >
        Test Email
      </BaseButton>
      <BaseButton @click="saveTemplates">
        Update
      </BaseButton>
    </template>
  </BaseModal>
  <ImageUploader
    type="description"
    @update-value="chooseImage"
  />
</template>

<script>
import ProductDescriptionEditor from '@product/components/ProductDescriptionEditor.vue';
import { Modal } from 'bootstrap';
import ImageUploader from '@shared/components/ImageUploader.vue';

export default {
  components: {
    ProductDescriptionEditor,
    ImageUploader,
  },
  props: {
    modalId: {
      type: String,
      default: '',
    },
    selectedType: {
      type: String,
      default: '',
    },
    emailContent: {
      type: Object,
      default: () => {},
    },
    templates: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      editor: [],
      emailType: {
        'register-success': {
          template: `<p>Hi {{name}},</p><p><br></p><p>You've successfully joined our referral campaign!</p><p><br></p><p>You can unlock exciting rewards with the points that you earn.</p><p><br></p><p>Earning points is easy — you earn points when someone clicks through your referral link and performs the action set out for the campaign.</p><p><br></p><p>Here's your unique referral link: {{referral_url}}</p><p><br></p><p>Just grab your link and start inviting all your friends and family.</p><p><br></p><p>You can check your points, rewards and more info anytime here: {{share_page_url}}</p><p><br></p><p>Start unlocking rewards with more points.</p><p><br></p><p>Here’s to a rewarding journey! :)</p>`,
          type: 'register-success',
          subject: `You're in {{name}}!`,
        },
        'referral-success': {
          template: `<p>Hi {{name}},</p><p><br></p><p>Great news! Someone just performed an action through your referral link.</p><p><br></p><p>You now have more points. Here you can see your latest points: {{share_page_url}}.</p><p><br></p><p>People seem to love what you’ve shared with them.</p><p><br></p><p>So, keep sharing your unique referral URL: {{referral_url}}</p><p><br></p><p>Share this link with your friends and family whom you think will benefit.</p><p><br></p><p>Happy Sharing :)</p>`,
          type: 'referral-success',
          subject: `{{name}}, someone just performed an action through your referral link!`,
        },
        'reward-unlocked': {
          template: `<p>Nicely done, {{name}}!</p><p><br></p><p>You’ve earned enough points to unlock a well-deserved reward.</p><p><br></p><p>Redeem your reward here: {{share_page_url}}</p><p><br></p><p>Continue to unlock more rewards — your unique referral URL:</p><p>{{referral_url}}</p><p><br></p><p>Keep up the great work :)</p>`,
          type: 'reward-unlocked',
          subject: `{{name}}, you just unlocked a reward!`,
        },
        'grand-prize': {
          template: `<p>Hi {{name}},</p><p><br></p><p>I have some amazing news for you...</p><p><br></p><p>The campaign has come to an end... and you won!</p><p><br></p><p>Congratulations on winning the grand prize!</p><p><br></p><p>We'll reach out to you in the next 48 hours with all the details. For now, we just wanted to congratulate you, and thank you for being so active throughout the campaign.</p><p><br></p><p>Talk soon!</p>`,
          type: 'grand-prize',
          subject: `{{name}}, congratulations! You’re the winner!`,
        },
      },
      inputOptions: [
        {
          name: 'Name',
          value: 'name',
        },
        {
          name: 'Referral URL',
          value: 'referral_url',
        },
        {
          name: 'Share Page URL',
          value: 'share_page_url',
        },
      ],
      selectedInput: null,
      cursorAt: '',
      theQuill: null,
      cursorIndex: 0,
    };
  },

  mounted() {
    this.$nextTick(() => {
      this.emailType = {
        'register-success': this.emailContent.find(
          (email) => email.type === 'register-success'
        ),
        'referral-success': this.emailContent.find(
          (email) => email.type === 'referral-success'
        ),
        'reward-unlocked': this.emailContent.find(
          (email) => email.type === 'reward-unlocked'
        ),
        'grand-prize': this.emailContent.find(
          (email) => email.type === 'grand-prize'
        ),
      };
      // this.editor.setHTML(this.emailType[this.selectedType.value].template)
    });
    // setTimeout(() => {

    //   const modalEl = document.getElementById(this.modalId);
    //   modalEl.addEventListener('hidden.bs.modal', () => {});
    // }, 1000);
  },

  methods: {
    saveTemplates() {
      this.$emit('email-template', {
        emailType: { ...this.emailType },
      });
      Modal.getInstance(document.getElementById(`${this.modalId}`)).hide();
    },
    inputMergeTag(val) {
      if (this.cursorAt === 'subject-input') {
        const subjectInputPosition =
          document.getElementById('subject-input')?.selectionStart;
        const selectedTypeSubject =
          this.emailType[this.selectedType.value].subject;
        this.emailType[
          this.selectedType.value
        ].subject = `${selectedTypeSubject.slice(
          0,
          subjectInputPosition
        )}{{${val}}}${selectedTypeSubject.slice(subjectInputPosition)}`;
      } else {
        const theQuill = this.$refs.editor[0].editor.getQuill();
        console.log(this.$refs.editor[0].editor);
        theQuill.insertText(this.cursorIndex, `{{${val}}}`);
      }
    },
    openTestEmailModal(val) {
      this.$emit('showTestEmailModal', val);
    },
    chooseImage(e) {
      const theQuill = this.$refs.editor[0].editor.getQuill();
      theQuill.insertEmbed(this.cursorIndex, 'image', e);
      this.$refs.editor[0].assign();
    },
  },
};
</script>

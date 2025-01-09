<template>
  <BasePopover
    :id="popoverId"
    :trigger="trigger"
    placement="right"
  >
    <span
      v-if="!isBuilder"
      class="pe-none"
    >
      <img
        src="@shared/assets/media/placeholder-indicator.svg"
        alt="{...}"
      >
    </span>
    <template #title>
      <h3 class="m-0">
        Add a variable:
      </h3>
    </template>
    <template #content>
      <div
        v-for="({ value, description }, index) in availablePlaceholder"
        :key="index"
        class="mb-5"
      >
        <p class="border bg-light p-2 mb-2 clickable cursor-pointer">
          {{ value }}
        </p>
        <span class="pe-none">{{ description }}</span>
      </div>
    </template>
  </BasePopover>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import eventBus from '@shared/services/eventBus.js';

const props = defineProps({
  popoverId: { type: String, default: 'emailPlaceholderPopover' },
  isBuilder: { type: Boolean, default: false },
  type: { type: String, default: 'emailSetupName' },
  inputId: { type: String, default: '' },
  inputValue: { type: String, default: '' },
  trigger: { type: String, default: 'hover' },
});

const emits = defineEmits(['popover', 'insert']);

const placeholder = [
  {
    value: '{{FIRST_NAME}}',
    description: 'Adds the first name of the recipient.',
    availableFor: [
      'emailSetupName',
      'emailSetupSubject',
      'emailBuilderHeading',
      'emailBuilderParagraph',
      'emailBuilderFooter',
    ],
  },
  {
    value: '{{COMPANY_NAME}}',
    description: 'Adds the company name of your account',
    availableFor: [
      'emailSetupName',
      'emailSetupSubject',
      'emailBuilderHeading',
      'emailBuilderParagraph',
      'emailBuilderFooter',
    ],
  },
  {
    value: '{{COMPANY_ADDRESS}}',
    description: 'Adds the full address of your company',
    availableFor: [
      'emailSetupName',
      'emailSetupSubject',
      'emailBuilderHeading',
      'emailBuilderParagraph',
      'emailBuilderFooter',
    ],
  },
  {
    value: '{{CURRENT_YEAR}}',
    description: 'Adds the current year. Exp: 2022',
    availableFor: [
      'emailSetupName',
      'emailSetupSubject',
      'emailBuilderHeading',
      'emailBuilderParagraph',
      'emailBuilderFooter',
    ],
  },
  {
    value: '{{UNSUBSCRIBE}}',
    description: 'Adds the link to unsubscribe from the mailing list',
    availableFor: [
      'emailBuilderHeading',
      'emailBuilderFooter',
      'emailBuilderParagraph',
    ],
  },
  {
    value: '{{REFERRAL_POINTS}}',
    description:
      'Participant will see the points he/she earned through the referral campaign',
    availableFor: ['paragraphElement', 'headingElement'],
  },
];

const availablePlaceholder = computed(() =>
  placeholder.filter((e) => e.availableFor.includes(props.type))
);

const popoverElem = ref(null);
const isFocusPopover = ref(false);

const closePopoverOnMouseLeave = (triggerEvent, popoverTriggerEl) => {
  if (!props.popoverId.includes('emailBuilder')) return;
  popoverTriggerEl.addEventListener(triggerEvent, () => {
    if (triggerEvent === 'click') return;
    popoverElem.value.show();
  });
  popoverTriggerEl.addEventListener('mouseleave', () => {
    setTimeout(() => {
      if (!isFocusPopover.value) popoverElem.value.hide();
      isFocusPopover.value = false;
    }, 100);
  });
  popoverTriggerEl.addEventListener(triggerEvent, () => {
    setTimeout(() => {
      const popoverEl = document.querySelector('.popover');
      if (!popoverEl) return;
      popoverEl.addEventListener('mouseover', () => {
        isFocusPopover.value = true;
      });
      popoverEl.addEventListener('mouseleave', () => {
        isFocusPopover.value = false;
        popoverElem.value.hide();
      });
    }, 100);
  });
};

onMounted(() => {
  eventBus.$on(`insertPlaceholder-${props.popoverId}`, (content) => {
    if (!props.inputId) return;
    const originalText = props.inputValue ?? '';
    const inputElem = document.getElementById(props.inputId);
    const position = inputElem.selectionStart;
    const insertedText =
      originalText.substring(0, position) +
      content +
      originalText.substring(position);
    emits('insert', insertedText);
  });

  eventBus.$on('tinymceInitialized', () => {
    const popoverTriggerEls = document.getElementsByClassName(
      'tox-tbtn tox-tbtn--select'
    );
    Object.values(popoverTriggerEls).forEach((popoverTriggerEl) => {
      closePopoverOnMouseLeave('click', popoverTriggerEl);
    });
  });

  eventBus.$on(props.popoverId, (popover) => {
    popoverElem.value = popover;
    emits('popover', popover);

    const popoverEl = document.getElementById(props.popoverId);
    closePopoverOnMouseLeave('mouseover', popoverEl);
  });
});
</script>

<style scoped></style>

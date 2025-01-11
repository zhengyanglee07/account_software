<template>
  <BaseModal
    :modal-id="modalId"
    no-dismiss
    title="Select MSIC Code"
    no-padding
    size="lg"
  >
    <BaseFormGroup>
      <BaseSearchSelect
        v-model="selectedMsicCode"
        placeholder="Type in 5-digit MSIC code or describe your business activity here..."
        :options="groupedOptions[4]"
        label="code"
        track-by="code"
        :custom-label="({ code, description }) => `${code}-${description}`"
        open-direction="bottom"
      />
    </BaseFormGroup>

    <BaseFormGroup label="Selected MSIC Code" class="pt-5">
      <BaseSearchSelect
        v-for="i in 5"
        :key="i"
        :class="`msic-code-split-${i} mb-5`"
        v-model="selectedMsicCodeSplit[i - 1]"
        @change="handleMsicCodeSplitChange(i - 1)"
        :options="
          groupedOptions[i - 1]?.filter((item) =>
            i > 1
              ? item.parent_code === selectedMsicCodeSplit[i - 2]?.code
              : !item.parent_code
          )
        "
        label="code"
        track-by="code"
        :custom-label="({ code, description }) => `${code}-${description}`"
        open-direction="bottom"
      />
    </BaseFormGroup>
    <template #footer>
      <BaseButton type="light" data-bs-dismiss="modal"> Cancel </BaseButton>
      <BaseButton data-bs-dismiss="modal" @click="save">
        <span>Select</span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { reactive, computed, ref, watch } from 'vue';

const props = defineProps({
  modalId: {
    type: String,
    required: true,
  },
  msicCodes: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['change']);

const groupedOptions = computed(() =>
  props.msicCodes.reduce((acc, curr) => {
    const key = curr.parent_code ? curr.parent_code.length : 0;
    if (!acc[key]) {
      acc[key] = [];
    }
    acc[key].push(curr);
    return acc;
  }, {})
);
const selectedMsicCode = ref(null);
const selectedMsicCodeSplit = reactive({
  0: [],
  1: [],
  2: [],
  3: [],
  4: [],
});

watch(selectedMsicCode, (newVal, oldVal) => {
  const splitCode = newVal.code.split('');
  if (splitCode.length < 5) {
    return;
  }
  selectedMsicCodeSplit[4] = newVal;
  [3, 2, 1, 0].forEach((i) => {
    selectedMsicCodeSplit[i] = groupedOptions.value[i].find(
      (item) => item.code === selectedMsicCodeSplit[i + 1].parent_code
    );
  });
});

const handleMsicCodeSplitChange = (index) => {
  Object.keys(selectedMsicCodeSplit).forEach((key) => {
    if (parseInt(key) > index) {
      selectedMsicCodeSplit[key] = [];
    }
  });

  if (index === 4) {
    selectedMsicCode.value = selectedMsicCodeSplit[4];
  }
};

const save = () => {
  emit('change', selectedMsicCode.value);
};
</script>

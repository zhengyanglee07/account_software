<template>
  <div>
    <BaseFormGroup label="Which segment?">
      <BaseFormSelect
        v-model="proxyValue"
        :options="options"
        label-key="segmentName"
      />
    </BaseFormGroup>
  </div>
</template>

<script setup>
import { useStore } from 'vuex';
import { defineEmits, onMounted, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ id: null, segmentName: 'Any' }),
  },
});

const emits = defineEmits(['update:modelValue']);
const store = useStore();
const options = computed(() => store.state.automations.segments);
const proxyValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emits('update:modelValue', val);
  },
});

onMounted(() => {
  if (props.modelValue?.segment_id === undefined) return;
  emits(
    'update:modelValue',
    options.value.find((e) => e.id === props.modelValue?.segment_id)
  );
});
</script>

<style scoped></style>

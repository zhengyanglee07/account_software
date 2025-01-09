<template>
  <BaseCard has-header title="Course Access Period">
    <BaseFormGroup
      label="Each student can access to the course content for how long"
    >
      <BaseFormRadio value="true" v-model="isLifeTime">
        Lifetime
      </BaseFormRadio>
      <BaseFormRadio value="false" v-model="isLifeTime">
        Specific amount of time after purchase
        <div v-if="isLifeTime === 'false'" class="d-flex mt-2">
          <BaseFormGroup required style="width: 40%">
            <BaseFormInput
              v-model="totalPeriod"
              type="number"
              min="1"
              required
            />
          </BaseFormGroup>
          <BaseFormGroup required style="padding-left: 1rem; width: 40%">
            <BaseFormSelect v-model="selectedDuration" required>
              <option
                v-for="(duration, index) in durationList"
                :value="duration"
                :key="index"
              >
                {{ duration }}
              </option>
            </BaseFormSelect>
          </BaseFormGroup>
        </div>
      </BaseFormRadio>
    </BaseFormGroup>
  </BaseCard>
</template>

<script setup>
import { ref, computed, inject, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Object, default: () => {} },
});

const emit = defineEmits(['update:modelValue']);

const val = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});

const isLifeTime = ref('true');

const durationList = ['Days', 'Weeks', 'Months', 'Years'];
const totalPeriod = ref(90);
const selectedDuration = ref('Days');

watch(
  [isLifeTime, totalPeriod, selectedDuration],
  ([lifeTime, period, duration]) => {
    const isLifeTime = lifeTime === 'true';
    val.value = {
      period: isLifeTime ? 0 : period,
      duration: isLifeTime ? 'lifetime' : duration,
    };
  }
);
</script>

<style scoped lang="scss"></style>

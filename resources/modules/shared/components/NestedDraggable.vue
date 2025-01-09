<template>
  <Draggable
    v-bind="dragOptions"
    tag="div"
    class="item-container accordion"
    ghost-class="ghost"
    :list="modelValue"
    :move="onMoveCallback"
    :role="level"
    @input="emitter"
  >
    <template #item="{ element, index }">
      <div :id="`accordion-${element.id}`">
        <div class="d-flex item">
          <i class="drag-icon fa-solid fa-ellipsis-vertical" />
          <div
            class="w-100 d-flex justify-content-between align-items-center me-4"
          >
            {{ element.name }}
            <div class="d-flex">
              <div>
                <slot :name="getSlotName(level)" :element="element" />
              </div>
              <button
                :class="`btn btn-sm ms-2 accordion-button w-auto ${
                  index > 0 && 'collapsed'
                } ${element.hasCollapse ? 'visible' : 'invisible'}`"
                style="background-color: #f5f8fa"
                data-bs-toggle="collapse"
                aria-expanded="false"
                :data-bs-target="`#collapse-${element.id}`"
                :aria-controls="`collapse-${element.id}`"
              />
            </div>
          </div>
        </div>
        <div
          :id="`collapse-${element.id}`"
          :data-bs-parent:id="`#accordion-${element.id}`"
          :class="`accordion-collapse collapse ${
            index > 0 ? 'collapse' : 'show'
          }`"
        >
          <NestedDraggable
            :id="element.id"
            v-model="element.elements"
            class="child-element item-sub"
            :level="level + 1"
          >
            <template #[`${getSlotName(level+1)}`]="{ element: subElement }">
              <slot :name="getSlotName(level + 1)" :element="subElement" />
            </template>
          </NestedDraggable>
        </div>
      </div>
    </template>
  </Draggable>
</template>

<script setup>
import Draggable from 'vuedraggable';
import { computed } from 'vue';

const props = defineProps({
  id: {
    type: String,
    default: 'draggable',
  },
  modelValue: {
    required: false,
    type: Array,
    default: null,
  },
  list: {
    required: false,
    type: Array,
    default: null,
  },
  level: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['update:modelValue']);

const dragOptions = computed(() => ({
  animation: 0,
  group: 'description',
  disabled: false,
  ghostClass: 'ghost',
}));

const emitter = (value) => {
  emit('update:modelValue', value);
};

const onMoveCallback = (value) => value.from.role === value.to.role;

const getSlotName = (level) => `toolbar-${level}`;
</script>

<style scoped lang="scss">
.item-container {
  width: 100%;
}
.item {
  padding: 1rem 0rem;
  border: 1px solid #ced4da;
}
.drag-icon {
  font-size: 1.5rem;
  padding: 0 1rem;
  display: flex;
  align-items: center;
}
.item-sub {
  padding-left: 20px;
}
.ghost {
  border-left: 2px solid;
  border-color: $h-primary;
}
.accordion-button:not(.collapsed)::after {
  transform: rotate(-90deg);
}
</style>

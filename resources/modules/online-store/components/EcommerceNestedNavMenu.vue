<template>
  <DragComponent
    class="nested-container"
    animation="150"
    group="description"
    tag="div"
    ghost-class="ghost"
    handle=".nav-items-container__icon"
    :swap-threshold="0.5"
    :move="checkMove"
    :list="menuList"
    :disabled="false"
    @input="emitter"
  >
    <template #item="{ element, index }">
      <div :key="element.refKey">
        <div
          class="nav-items-container"
          :class="level >= 3 ? 'invalid-nav-menu-container' : 'null'"
        >
          <div class="flex-horizontal center-items">
            <div class="nav-items-container__icon">
              <i class="fa-solid fa-ellipsis-vertical" />
            </div>

            <div>
              {{ element.name }}
            </div>

            <div class="nav-items-container__flex-right">
              <BaseButton
                size="sm"
                class="nav-items-container__button"
                @click="editMenuItem(element, index)"
              >
                Edit
              </BaseButton>
              <BaseButton
                type="secondary"
                size="sm"
                @click="deleteMenuItem(element, index)"
              >
                Delete
              </BaseButton>
            </div>
          </div>
        </div>

        <!-- nestsed -->
        <NestedMenuList
          :menu-list="element.nested"
          :level="level + 1"
          @set-reorder-menu-list="reorderMenuItem"
          @set-add-menu-list="addMenuItem"
          @set-del-menu-list="deleteMenuItem"
          @set-edit-menu-list="editMenuItem"
        />
      </div>
    </template>
  </DragComponent>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  menuList: {
    type: Array,
    required: true,
  },
  level: {
    type: Number,
    required: true,
  },
  isLastElement: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  'setReorderElements',
  'setAddMenuList',
  'setDelMenuList',
  'setEditMenuList',
  'input',
]);

const checkMove = (e) => {};

const reorderMenuItem = (data) => {
  emit('setReorderElements', data);
};

const checkInvalidNested = (to, from) => {
  return false;
};

const addMenuItem = () => {
  emit('setAddMenuList');
};

const deleteMenuItem = (e, index) => {
  // prevent the data getting nested
  if (e[2]) emit('setDelMenuList', e);
  else emit('setDelMenuList', [e, index, props.level]);
};

const editMenuItem = (e, index) => {
  // prevent the data getting nested
  if (e[2]) emit('setEditMenuList', e);
  else emit('setEditMenuList', [e, index, props.level]);
};

const emitter = (value) => {
  emit('input', value);
};
</script>

<script>
import draggable from 'vuedraggable'; // eslint-disable-line import/first

export default {
  name: 'NestedMenuList',
  components: {
    DragComponent: draggable,
  },
};
</script>

<style lang="scss" scoped>
.center-container {
  display: flex;
  align-items: center;
  justify-content: center;
}

.center-items {
  display: flex;
  align-items: center;
}

.empty-container {
  padding: 5rem 0rem;
}

.flex-horizontal {
  display: flex;
  flex-direction: row;
}

.flex-vertical {
  display: flex;
  flex-direction: column;
}

.nav-items-container {
  padding: 0.7rem 0rem;
  border-width: 1px;
  border-color: $table-border-color;
  border-style: solid;
  align-items: center;

  &__icon {
    cursor: pointer;
    padding: 0 1rem;

    & i {
      font-size: 1.5rem;
    }
  }

  &__flex-right {
    right: 0;
    float: right;
    margin-right: 0.5rem;
    margin-left: auto;
  }

  &__button {
    margin-right: 0.7rem;
  }
}

.add-new-menu-container {
  border-width: 2px;
  height: 50px;
  border-color: $h-primary;
  border-style: solid;
  align-items: center;
  cursor: default;
  user-select: none;

  & .text {
    color: $h-primary;
    padding: 0.4rem 0.5rem;
  }
}

.nested-container {
  width: 100%;
  padding-left: 20px;
  display: flex;
  flex-direction: column;
}

.ghost {
  border-left: 2px solid;
  border-color: $h-primary;
}

.invalid-nav-menu-container {
  background-color: red;
}
</style>

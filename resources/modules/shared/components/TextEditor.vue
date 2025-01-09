<template>
  <QuillEditor
    :id="editorId"
    ref="editor"
    :options="options"
    content-type="html"
    :content="modelValue"
    style="height: 250px"
    @update:content="updateContent"
    @selectionChange="onSelectionChange"
  />
</template>

<script setup>
import { ref, defineEmits, onMounted } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { Modal } from 'bootstrap';
import eventBus from '@services/eventBus.js';

const props = defineProps({
  editorId: { type: String, default: '' },
  modelValue: { type: String, default: '' },
});

const emits = defineEmits(['update:modelValue']);

const uploadImage = () => {
  const imageUploader = new Modal(document.getElementById('description-modal'));
  imageUploader.show();
};

const editor = ref(null);
const quill = ref(null);

const contentHTML = ref('');
const options = {
  modules: {
    toolbar: {
      container: [
        [{ header: [1, 2, 3, 4, 5, 6, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ color: [] }, { background: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['image'],
        ['clean'],
      ],
      handlers: {
        image: uploadImage,
      },
    },
  },
  theme: 'snow',
};

const rangeIndex = ref(0);
const onSelectionChange = (e) => {
  rangeIndex.value = quill.value?.selection?.lastRange?.index ?? 0;
};

const updateContent = (value) => {
  emits('update:modelValue', value);
};

onMounted(() => {
  quill.value = editor.value.getQuill();
  eventBus.$on(`${props.editorId}-editor-insert-image`, (e) => {
    quill.value.insertEmbed(rangeIndex.value, 'image', e);
  });
});
</script>

<style scoped lang="scss">
#product-description-editor {
  height: 250px;
  overflow: auto;
  border-bottom-left-radius: 2.5px;
  border-bottom-right-radius: 2.5px;
}

:deep .ql-toolbar.ql-snow {
  border-top-left-radius: 2.5px;
  border-top-right-radius: 2.5px;
}

:deep .ql-editor {
  min-height: 250px;
  img,
  iframe {
    max-height: 600px !important;
    max-width: 600px !important;
    object-fit: contain !important;
    left: 50%;
    position: relative;
    transform: translateX(-50%);
    padding: 15px 0;
  }

  img {
    width: auto !important;
  }
}

:deep .ql-tooltip {
  bottom: 0px !important;
  left: 0px !important;
  top: auto !important;
}

:deep .ql-video {
  width: 100%;
  height: 100%;
}
</style>

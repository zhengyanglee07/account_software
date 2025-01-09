<template>
  <div>
    <QuillEditor
      :id="editorId"
      ref="editor"
      v-model:content="contentHTML"
      :options="options"
      content-type="html"
      :style="{ height: resized ? 'auto' : '250px' }"
      @keyup="assign"
      @focus="assign"
      @textChange="assign"
      @selectionChange="rangeSelection"
    />
  </div>
</template>

<script>
import { ref, toRefs, watch, nextTick, onMounted } from 'vue';
import { QuillEditor, Quill } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { Modal } from 'bootstrap';

export default {
  components: {
    QuillEditor,
  },
  props: {
    editorId: {
      type: String,
      default: 'editor',
    },
    data: {
      type: [Array, Object],
      required: false,
    },
    property: {
      type: String,
      required: false,
    },
  },
  emits: ['inputImage', 'selectionChange'],
  setup(props, { emit }) {
    const { data, property, editorId } = toRefs(props);
    const rangeIndex = ref(0);
    const editor = ref('');
    const uploadImage = () => {
      emit('inputImage');
      nextTick(() => {
        const imageUploader = new Modal(
          document.getElementById('description-modal')
        );
        imageUploader.show();
      });
    };
    const rangeSelection = (e) => {
      if (e?.range?.index) {
        rangeIndex.value = e?.range?.index;
        emit('selectionChange', rangeIndex.value);
      }
    };

    const options = {
      modules: {
        toolbar: {
          container: [
            [{ header: [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ color: [] }, { background: [] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['image', 'video'],
            ['clean'],
          ],
          handlers: {
            image: uploadImage,
          },
        },
      },
      theme: 'snow',
    };
    const contentHTML = ref('');
    const resized = ref(false);

    const assign = (e) => {
      const [contentHtml] = document
        .getElementById(editorId.value)
        .getElementsByClassName('ql-editor');
      const notEmpty =
        contentHtml.outerHTML.includes('<img') ||
        contentHtml.outerHTML.includes('<iframe') ||
        contentHtml.textContent !== '';
      data.value[property.value] = notEmpty ? contentHtml.innerHTML : '';
    };

    const watched = ref(false);

    const chooseImage = (e) => {
      const container = document.getElementById(editorId.value);
      const quill = new Quill(container);
      quill.insertEmbed(rangeIndex.value, 'image', e);
      assign();
    };

    watch(
      () => data.value[property.value],
      (newValue) => {
        if (!watched.value && newValue) {
          editor.value.setContents(newValue);
          watched.value = true;
        } else watched.value = true;
      }
    );

    onMounted(() => {
      editor.value.setContents(data.value[property.value]);
      const editorWrapper = document.getElementById(editorId.value);
      const editorElem = document
        .getElementById(editorId.value)
        .getElementsByClassName('ql-editor')[0];
      const ro = new ResizeObserver((entries) => {
        // eslint-disable-next-line no-restricted-syntax
        for (const entry of entries) {
          const cr = entry.contentRect;
          // 226px is exact height of editor without toolbar header
          if (cr.height > 226) {
            resized.value = true;
            editorWrapper.style.height = `auto`;
          }
        }
      });
      ro.observe(editorElem);
    });

    return {
      editor,
      options,
      contentHTML,
      assign,
      chooseImage,
      rangeSelection,
      resized,
    };
  },
};
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
  resize: vertical;
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

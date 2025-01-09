<template>
  <div
    :id="editorId"
    class="legal-template-editor"
  />
</template>

<script>
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import 'quill/dist/quill.core.css';
import eventBus from '@services/eventBus.js';

export default {
  props: ['value', 'editorId'],

  data() {
    return {
      productEditor: null,
      range: null,
      isUpdated: false,
    };
  },

  watch: {
    value: {
      handler(newVal) {
        if (!this.isUpdated) {
          this.productEditor.root.innerHTML =
            this.value === undefined ? '' : this.value;
        }
      },
    },
  },

  mounted() {
    this.productEditor = new Quill(`#${this.editorId}`, {
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
            image: this.uploadImage,
          },
        },
      },
      theme: 'snow',
    });

    this.productEditor.on('text-change', () => {
      this.isUpdated = true;
      this.updateValue();
    });

    eventBus.$on('productDescriptionImageUploadEvent', (data) =>
      this.insertImage(data)
    );
  },

  methods: {
    uploadImage(value) {
      this.$modal.show('product-upload');
      eventBus.$emit('editType', 'productDescriptionImageUpload');
      this.range = this.productEditor.getSelection();
    },

    insertImage(url) {
      this.productEditor.insertEmbed(this.range.index, 'image', url);
    },

    updateValue() {
      this.$emit(
        'input',
        this.productEditor.getText() ? this.productEditor.root.innerHTML : ''
      );
    },
  },
};
</script>

<style scoped lang="scss">
.legal-template-editor {
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

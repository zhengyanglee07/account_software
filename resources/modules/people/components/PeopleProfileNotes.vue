<template>
  <BaseFormGroup description="Only you and other staff can see notes.">
    <div class="d-flex">
      <BaseFormInput
        v-model="newNote"
        type="text"
        placeholder="Leave a comment"
        @input="handleOnInput"
      />
      <BaseButton
        :disabled="showPostBtnSpinner"
        @click="postNote"
      >
        Post
      </BaseButton>
    </div>
  </BaseFormGroup>

  <div class="timeline">
    <div
      v-for="(innerNotes, idx) in notesArray"
      :key="idx"
      class="timeline-outer-items"
    >
      <div class="mt-3">
        <h6 class="timeline-inner-item-title-date p-two grey-text">
          {{ innerNotes[0].date }}
        </h6>
        <div
          v-for="note in innerNotes"
          :key="note.id"
          class="timeline-inner-item pt-2"
        >
          <div class="timeline-inner-item-title w-100">
            <span class="bullet" />
            <div class="row w-100">
              <div class="col-sm-3">
                <div>
                  <span class="time-content p-two">{{
                    note['created_at_time']
                  }}</span>
                </div>
              </div>
              <div class="col-sm-9">
                <span class="title-content p-two">{{ note.content }}</span>
              </div>
            </div>

            <!-- Note; why note content put in title? Because in the future
                        maybe your boss want to add note item (see below), so
                        now the note content is treated as note title instead
                        in frontend
              -->
          </div>

          <!-- You can add timeline item content here in the future if your boss
                call you to do. Remember to adjust the styling accordingly :)
          -->
        </div>
      </div>
    </div>
  </div>

  <!-- Future use to transform notes to BaseDatatable -->
  <!-- <BaseDatatable
    no-header
    no-action
    no-search
    :table-headers="tableHeaders"
    :table-datas="notesArray"
  /> -->
</template>

<script>
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { validationFailedNotification } from '@shared/lib/validations.js';
import eventBus from '@services/eventBus.js';
import { UPDATE_NOTES } from '@people/lib/peopleProfileBus.js';

export default {
  name: 'PeopleProfileNotes',
  props: {
    notes: [Array, Object],
    contactId: [Number, String],
  },
  setup() {
    return { v$: useVuelidate() };
  },

  data() {
    return {
      newNote: '',
      showPostBtnSpinner: false,
      showError: false,
      // Future use to transform notes to BaseDatatable
      tableHeaders: [
        /**
         * @param name : column header title
         * @param key : datas column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         * @param textalign : text align, default => left
         */
        { name: 'Date', key: 'date' },
        { name: 'Time', key: 'time' },
        { name: 'Note', key: 'note' },
      ],
    };
  },
  validations: {
    newNote: {
      required,
    },
  },
  computed: {
    notesArray() {
      return Object.keys(this.notes).map((date) =>
        this.notes[date].map((note) => ({
          ...note,
          date,
        }))
      );
    },
  },
  methods: {
    handleOnInput() {
      this.showError = false;
      this.v$.newNote.$touch();
    },
    postNote() {
      this.showError = false;

      if (this.v$.newNote.$invalid) {
        this.showError = true;
        return;
      }

      this.showPostBtnSpinner = true;

      this.$axios
        .post(`/people/profile/${this.contactId}/notes`, {
          content: this.newNote,
        })
        .then(({ data: { notes } }) => {
          this.$toast.success('Success', 'Successfully added new note.');

          // update view timeline + clear input
          eventBus.$emit(UPDATE_NOTES, notes);
          this.newNote = '';
        })
        .catch((error) => {
          console.error(error);

          validationFailedNotification(error);
        })
        .finally(() => {
          this.showPostBtnSpinner = false;
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

<template>
  <BaseModal
    :modal-id="modalId"
    is-overflow-visible
    data_backdrop="static"
    :title="isRemove ? 'Remove Affiliate Group' : 'Add Affiliate Group'"
  >
    <BaseFormGroup
      :error-message="
        showEmptyCheckedError ? 'Please select at least one member' : ''
      "
    >
      <BaseMultiSelect
        :options="availableGroups"
        :model-value="null"
        placeholder="Select Group"
        label="title"
        multiple
        @input="appendGroup"
      >
        <template #no-options="{ search, searching }">
          <template v-if="searching">
            <div
              type="button"
              @click="createNewGroup(search)"
            >
              Add {{ search }}
            </div>
          </template>
        </template>
      </BaseMultiSelect>

      <BaseBadge
        v-for="(g, i) in selectedGroups"
        :key="i"
        :text="g.title"
        has-delete-button
        @click="removeGroup(g.id)"
      />
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        :disabled="saving"
        @click="save"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { validationFailedNotification } from '@shared/lib/validations.js';

export default {
  props: {
    modalId: {
      type: String,
      required: true,
    },
    groups: {
      type: Array,
      required: true,
    },
    checkedParticipantIds: {
      type: Array,
      required: true,
    },
    isRemove: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      selectedGroups: [],
      saving: false,
      showEmptyCheckedError: false,
    };
  },
  validations: {},
  computed: {
    availableGroups() {
      return this.groups.filter(
        (g) => !this.selectedGroups.map((sg) => sg.id).includes(g.id)
      );
    },
  },
  watch: {
    selectedGroups() {
      this.showEmptyCheckedError = false;
    },
  },
  mounted() {
    setTimeout(() => {
      const modalEl = document.getElementById(this.modalId);

      modalEl.addEventListener('show.bs.modal', () => {
        this.showEmptyCheckedError = false;
      });
    }, 1000);
  },
  methods: {
    async createNewGroup(title) {
      try {
        const res = await this.$axios.post('/affiliate/members/groups', {
          title,
        });

        this.$emit('push-group', res.data.group);
        this.$toast.success('Success', 'Successfully created new group');
      } catch (err) {
        if (err.response.status !== 422) {
          this.$toast.error('Error', 'Failed to create new group');
          return;
        }

        validationFailedNotification(err);
      }
    },

    async appendGroup(g) {
      this.selectedGroups = [...this.selectedGroups, g.pop()];
    },

    async removeGroup(id) {
      this.selectedGroups = this.selectedGroups.filter((g) => g.id !== id);
    },

    async save() {
      this.showEmptyCheckedError = !this.checkedParticipantIds.length;

      if (this.showEmptyCheckedError) {
        return;
      }

      if (this.selectedGroups.length === 0) {
        this.$toast.error('Error', 'Please select at least one group to add');
        return;
      }

      this.saving = true;

      try {
        await this.$axios({
          method: this.isRemove ? 'delete' : 'put',
          url: '/affiliate/members/participant/groups',
          data: {
            participantIds: this.checkedParticipantIds,
            groupIds: this.selectedGroups.map((sg) => sg.id),
          },
        });

        this.$toast.success(
          'Success',
          `Successfully ${this.isRemove ? 'removed' : 'added'} groups ${
            this.isRemove ? 'from' : 'to'
          } selected members`
        );
        this.selectedGroups = [];
        this.$emit('update-checked', []); // to clear all checked
        this.$emit('hide');
      } catch (err) {
        console.log(err);
        this.$toast.error('Error', 'Failed to add groups to selected members');
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped lang="scss"></style>

<template>
  <BasePageLayout
    page-name="User Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseDatatable
      title="user"
      no-edit-action
      no-delete-action
      no-hover
      :table-headers="tableHeaders"
      :table-datas="userDatas"
    >
      <template #cell-joined_date="{ row: { joined_date, joinedDate } }">
        <span v-if="joinedDate !== 'pending'">
          {{ joined_date }}
        </span>
        <span
          v-else
          class="badge badge-warning"
        > pending </span>
      </template>
      <template #action-button>
        <BaseButton
          has-add-icon
          href="/settings/role"
          @click="toggleCreateAutomation"
        >
          Invite User
        </BaseButton>
      </template>
      <template #action-options="{ row: { userId, email } }">
        <BaseDropdownOption
          text="Delete"
          @click="removeUser({ userId, email })"
        />
      </template>
    </BaseDatatable>
  </BasePageLayout>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';

export default {
  props: {
    users: Array,
  },

  data() {
    return {
      tableHeaders: [
        /**
         * @param text : column header title
         * @param value : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         * @param textalign : justify-content, default => left
         */
        { name: 'Email', key: 'email' },
        { name: 'Role', key: 'role' },
        {
          name: 'Joined at',
          key: 'joined_date',
          isDateTime: true,
          custom: true,
        },
      ],
      selectedId: null,
      selectedEmail: '',
      parsedUsers: [],
      emptyState: {
        title: 'user',
        description: 'invited users',
        action: 'invited',
      },
    };
  },
  computed: {
    userDatas() {
      return this.parsedUsers.map((item) => ({
        id: item.userId,
        userId: item.userId,
        email: item.email,
        role: item.role,
        joined_date: item.convertedTime,
        joinedDate: item.joinedDate,
      }));
    },
  },
  mounted() {
    const tempUsers = cloneDeep(this.users);
    this.parsedUsers = tempUsers.sort(function (a, b) {
      return new Date(a.convertedTime) - new Date(b.convertedTime);
    });
  },
  methods: {
    deleteConfirmation(id, email) {
      this.selectedId = id;
      this.selectedEmail = email;
      $('#selected-user-delete-modal').modal('show');
    },

    closeDeleteModal() {
      this.selectedId = null;
      $('#selected-user-delete-modal').modal('hide');
    },

    removeUser(user) {
      this.selectedId = user.id;
      this.selectedEmail = user.email;
      axios
        .delete(`/account/removeUser/${this.selectedId}/${this.selectedEmail}`)
        .then(({ data }) => {
          this.$toast.success('Success', 'User removed.');
          this.parsedUsers = this.parsedUsers.filter(
            (people) => people.email !== this.selectedEmail
          );
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>

<style lang="scss" scoped></style>

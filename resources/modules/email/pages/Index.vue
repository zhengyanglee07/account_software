<template>
  <div class="mb-5 d-flex">
    <div>
      <BaseButton
        id="email-status-dropdown-button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        type="white"
        has-edit-icon
        has-dropdown-icon
        class="me-2 mt-2"
      >
        Status
      </BaseButton>
      <BaseDropdown id="email-status-dropdown-list">
        <BaseDropdownOption
          v-for="status in ['Draft', 'Scheduled', 'Sent']"
          :key="status"
        >
          <BaseFormRadio
            :id="`email-status-${status}`"
            :model-value="emailFilter.status"
            :value="status"
            @input="addStatusFilter(status)"
          >
            {{ status }}
          </BaseFormRadio>
        </BaseDropdownOption>
        <BaseDropdownOption>
          <BaseButton
            type="link"
            @click="clearFilter('status')"
          >
            Clear
          </BaseButton>
        </BaseDropdownOption>
      </BaseDropdown>
    </div>
    <div>
      <BaseButton
        id="email-group-dropdown-button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        type="white"
        has-edit-icon
        has-dropdown-icon
        class="me-2 mt-2"
      >
        Group
      </BaseButton>
      <BaseDropdown id="email-group-dropdown-list">
        <BaseDropdownOption
          v-for="group in emailGroups"
          :key="group"
        >
          <BaseFormCheckBox
            :id="`email-group-${group}`"
            :model-value="emailFilter.group"
            :value="group"
            @input="addGroupFilter(group)"
          >
            {{ group }}
          </BaseFormCheckBox>
        </BaseDropdownOption>
        <BaseDropdownOption>
          <BaseButton
            type="link"
            @click="clearFilter('group')"
          >
            Clear
          </BaseButton>
        </BaseDropdownOption>
      </BaseDropdown>
    </div>
  </div>

  <div class="d-flex pb-5">
    <BaseBadge
      v-for="{ key, value } in allFilters"
      :key="key"
      :text="value"
      has-delete-button
      @click="removeFilter(key, value)"
    />
  </div>

  <EmailHomeDatatable :db-emails="filteredEmails" />
</template>

<script setup>
import {
  computed,
  onBeforeMount,
  reactive,
  registerRuntimeCompiler,
  watch,
} from 'vue';
import EmailHomeDatatable from '@email/components/EmailHomeDatatable.vue';
import useEmail from '@email/hooks/useEmail.js';

const props = defineProps({
  dbEmails: { type: Array, default: () => [] },
  emailGroups: { type: Array, default: () => [] },
});

const {
  emails,
  setEmails,
  emailGroups,
  setEmailGroups,
  resetSelectedEmailIds,
} = useEmail();

const emailFilter = reactive({
  status: [],
  group: [],
});

const removeFilter = (filter, value) => {
  emailFilter[filter] = emailFilter[filter].filter((e) => e !== value);
};
const addStatusFilter = (value) => {
  emailFilter.status = [value];
};
const addGroupFilter = (value) => {
  const addedValue = emailFilter.group.find((e) => e === value);
  if (addedValue === value) {
    removeFilter('group', value);
    return;
  }
  emailFilter.group.push(value);
};
const clearFilter = (filter) => {
  emailFilter[filter] = [];
};

const allFilters = computed(() =>
  Object.entries(emailFilter)
    .map(([key, value]) => value.map((m) => ({ key, value: m })))
    .flat()
);

watch(allFilters, () => {
  resetSelectedEmailIds();
});

const filteredEmails = computed(() =>
  emails.value.filter((email) => {
    const isStatusMatched =
      emailFilter.status?.length === 0 ||
      emailFilter.status.includes(email.email_status?.status);
    const isGroupMatched =
      emailFilter.group?.length === 0 ||
      emailFilter.group.some((groupName) => email.group.includes(groupName));
    return isStatusMatched && isGroupMatched;
  })
);

onBeforeMount(() => {
  setEmails(props.dbEmails);
  setEmailGroups(props.emailGroups);
});
</script>

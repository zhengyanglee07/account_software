<template>
  <BasePageLayout
    is-setting
    page-name="Domain Settings"
    back-to="/settings/all"
  >
    <template #action-button>
      <BaseButton
        id="add-domain-button"
        has-add-icon
        @click="triggerAddModal"
      >
        Add Domain
      </BaseButton>
    </template>
    <BaseSettingLayout
      title="Manage Domain"
      is-datatable-only-in-content
    >
      <template #description>
        <p>
          View and manage your domain. You can enter up to
          {{ planDomainQuota }} custom domains.
        </p>

        <p>
          All the connected domains will be protected by free SSL certificates
          provided by Hypershapes.
        </p>

        <p>
          For the complete step-by-step guide on how to connect your custom
          domain to Hypershapes, refer to our
          <BaseButton
            type="link"
            is-open-in-new-tab
            href="https://hypershapes.freshdesk.com/support/solutions/articles/72000531766-connect-your-domain-to-hypershapes"
          >
            documentation
          </BaseButton>
          here
        </p>
        <small>
          *Only one domain for online store or mini store can be connected at a
          same time
        </small>
      </template>

      <template #content>
        <BaseDatatable
          title="domain"
          :table-headers="tableHeaders"
          :table-datas="allDomains"
          no-hover
          no-header
          no-sorting
          no-edit-action
          @delete="deleteDomain"
        >
          <template #cell-status="{ row: { is_verified: isVerified } }">
            <span
              class="badge"
              :class="`badge-${isVerified === 1 ? 'success' : 'warning'}`"
            >
              {{ isVerified === 1 ? 'Connected' : 'Pending for Connection' }}
            </span>
          </template>
          <template #action-options="{ row }">
            <BaseDropdownOption
              v-if="!row.is_verified"
              text="Verify"
              @click="verifyDomain(row.id)"
            />
            <BaseDropdownOption
              v-else
              text="Edit"
              @click="triggerEditModal(row)"
            />
          </template>
        </BaseDatatable>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>

  <BaseModal
    modal-id="domain-modal"
    :title="`${selectedDomain === null ? 'Add' : 'Edit'} Domain`"
  >
    <BaseFormGroup
      label="Domain Name"
      required
      description="Enter the name of the domain you want to connect to Hypershapes"
      :error-message="errorMsg"
    >
      <BaseFormInput
        id="domain-name"
        v-model="newDomainName"
        type="text"
        placeholder="Exp: example.com"
        :disabled="!isSelectedDomainSubdomain && isSelectedDomainVerified"
      >
        <template #prepend>
          https://
        </template>
        <template
          v-if="isSelectedDomainSubdomain"
          #append
        >
          {{ rootDomain }}
        </template>
      </BaseFormInput>
    </BaseFormGroup>

    <template v-if="selectedDomain">
      <BaseFormGroup label="Assign this domain as root URL for">
        <template
          v-for="{ label, value } in salesChannels"
          :key="value"
        >
          <BaseFormRadio
            :id="value"
            v-model="selectedDomain.type"
            :value="value"
          >
            <span class="text-capitalize">{{ label }}</span>
          </BaseFormRadio>
        </template>
        <BaseFormSelect
          v-if="selectedDomain.type === 'funnel'"
          v-model="selectedDomain.type_id"
        >
          <option
            disabled
            :value="null"
          >
            Choose a Funnel
          </option>
          <option
            v-for="funnel in allFunnels"
            :key="funnel.id"
            :value="funnel.id"
          >
            {{ funnel.funnel_name }}
          </option>
        </BaseFormSelect>
      </BaseFormGroup>
      <BaseFormGroup>
        <BaseFormCheckBox
          id="is-affiliate-member-domain"
          v-model="selectedDomain.is_affiliate_member_dashboard_domain"
          :value="1"
        >
          Use this domain for affiliate member system account
        </BaseFormCheckBox>
      </BaseFormGroup>
    </template>

    <template #footer>
      <BaseButton @click="validateDomain">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import axios from 'axios';
import { ref, computed, inject, onMounted } from 'vue';
import { Modal } from 'bootstrap';
import BaseModal from '@shared/components/BaseModal.vue';
import cloneDeep from 'lodash/cloneDeep';

const props = defineProps({
  domains: {
    type: Array,
    required: true,
  },
  subdomainId: {
    type: Number,
    required: true,
  },
  allFunnels: {
    type: Array,
    default: () => [],
  },
  planDomainQuota: {
    type: Number,
    required: true,
  },
  enabledSalesChannels: {
    type: Array,
    default: () => ['funnel', 'online-store', 'mini-store'],
  },
});

const tableHeaders = [
  {
    name: 'Domain',
    key: 'domain',
  },
  {
    name: 'Status',
    key: 'status',
    custom: true,
  },
];

const $toast = inject('$toast');
const allDomains = ref([...props.domains]);
const selectedDomain = ref(null);
const newDomainName = ref('');
const rootDomain = ref(null);
const domainModal = ref(null);
const errorMsg = ref(null);

onMounted(() => {
  domainModal.value = new Modal(document.getElementById('domain-modal'));
});

const isSelectedDomainSubdomain = computed(
  () => selectedDomain.value?.id === props.subdomainId
);

const isSelectedDomainVerified = computed(() => {
  return selectedDomain.value?.is_verified ?? false;
});

const domainName = computed(() => {
  const domain = isSelectedDomainSubdomain.value
    ? `${newDomainName.value}${rootDomain.value}`
    : newDomainName.value;
  return domain.trim();
});

const salesChannels = computed(() => {
  const salesChannelsInDescendingOrder = [...props.enabledSalesChannels]
    .sort()
    .reverse();
  return salesChannelsInDescendingOrder.map((channel) => {
    return {
      label: channel.split('-').join(' '),
      value: channel,
    };
  });
});

const toggleDomainModal = (isShow = true) => {
  if (isShow) {
    domainModal.value.show();
  } else {
    domainModal.value.hide();
  }
};

const resetToInitialValues = () => {
  errorMsg.value = null;
  newDomainName.value = null;
  selectedDomain.value = null;
};

const triggerAddModal = () => {
  resetToInitialValues();
  toggleDomainModal();
};

const triggerEditModal = (domain) => {
  resetToInitialValues();
  selectedDomain.value = cloneDeep(domain);
  const { domain: selectedDomainName } = domain;
  newDomainName.value = selectedDomainName;

  if (isSelectedDomainSubdomain.value) {
    const [subdomain, ...hostname] = domain.domain.split('.');
    rootDomain.value = `.${hostname.join('.')}`;
    newDomainName.value = subdomain;
  }

  toggleDomainModal();
};

const isValidDomain = () => {
  return /^(https?:\/\/)?([a-z\d]([a-z\d-.]?[a-z\d])*\.[a-z]([a-z.]?[a-z])*){1,255}$/i.test(
    domainName.value
  );
};

const updateOrCreateDomain = () => {
  const isAddDomain = selectedDomain.value === null;
  const method = isAddDomain ? 'post' : 'put';
  const url = isAddDomain
    ? '/domain/save'
    : `/domain/${selectedDomain.value?.id}`;
  axios({
    method,
    url,
    data: {
      ...selectedDomain.value,
      newDomainName: domainName.value,
    },
  })
    .then(({ data: { message, updatedRecords } }) => {
      allDomains.value = updatedRecords;
      $toast.success('Success', message);
      resetToInitialValues();
      toggleDomainModal(false);
    })
    .catch((err) => {
      $toast.error(
        'Failed to save domain',
        'Please contact our support for help'
      );
      throw new Error(err);
    });
};

const checkDomainAvailability = () => {
  axios
    .post('/domain/check', {
      domainName: domainName.value,
      isSkipPermissionChecker: isSelectedDomainSubdomain.value,
    })
    .then((response) => {
      if (response.data.isExisted) {
        errorMsg.value =
          'Domain was connected to Hypershapes. Try another domain instead';
        return;
      }
      updateOrCreateDomain();
    })
    .catch((error) => {
      $toast.error(
        'Something went wrong',
        'Please contact our support for help'
      );
      throw new Error(error);
    });
};

const validateDomain = () => {
  if (!newDomainName.value) {
    errorMsg.value = 'Domain name is required';
    return;
  }
  if (
    isSelectedDomainSubdomain.value &&
    !/^[a-z0-9-]*$/.test(newDomainName.value)
  ) {
    errorMsg.value = 'Only a-z 0-9 and - is accepted';
    return;
  }
  if (!isValidDomain()) {
    errorMsg.value = 'Incorrect domain format';
    return;
  }

  if (selectedDomain.value !== null && !isSelectedDomainSubdomain.value) {
    updateOrCreateDomain();
  } else {
    checkDomainAvailability();
  }
};

const verifyDomain = (id) => {
  $toast.info('Verifying domain', 'Please wait a while...');
  axios
    .put(`/domain/verify/${id}`)
    .then(({ data: { domain, updatedRecords } }) => {
      allDomains.value = updatedRecords;
      $toast.success(
        'Success',
        `https://${domain} is now connected and protected by SSL. Assign it to your ${props.enabledSalesChannels.join(
          ', '
        )} now`
      );
    })
    .catch((err) => {
      $toast.error(
        'Something went wrong',
        'Please contact our support for help'
      );
      throw new Error(err);
    });
};

const deleteDomain = (id) => {
  axios
    .delete(`/domain/${id}`)
    .then(({ data: { status, message, records } }) => {
      if (status !== 'Success') {
        $toast.error(status, message);
        return;
      }
      $toast.success(status, message);
      allDomains.value = records;
      resetToInitialValues();
      toggleDomainModal(false);
    })
    .catch((err) => {
      $toast.error(
        'Something went wrong',
        'Please contact our support for help'
      );
      throw new Error(err);
    });
};
</script>

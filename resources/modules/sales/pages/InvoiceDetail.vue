<template>
  <BasePageLayout :page-name="`#1111`" back-to="/sales/invoices" is-setting>
    <BaseOrderPageLayout>
      <template #header> </template>

      <template #left>
        <BaseCard has-header has-footer :title="`General Info`">
          <BaseFormGroup col="6" for="f_number" label="No." required>
            <BaseFormSelect v-model="form.number" id="f_number"
              :options="numberFormats.filter((e) => e.type === 'sale_invoice')" label-key="format" value-key="id" />
          </BaseFormGroup>
          <BaseFormGroup col="6" for="f_reference_no" label="Reference No." required>
            <BaseFormInput v-model="form.reference_no" id="f_reference_no" type="text" />
          </BaseFormGroup>

          <BaseFormGroup col="6" for="f_date" label="Date" required>
            <BaseFormInput v-model="form.date" id="f_date" type="text" />
          </BaseFormGroup>
          <BaseFormGroup col="3" for="f_currencyCode" label="Curreny" required>
            <BaseFormInput v-model="form.currency_code" id="f_currencyCode" type="text" />
          </BaseFormGroup>
          <BaseFormGroup col="3" for="f_exchangeRate" label="Rate" required>
            <BaseFormInput v-model="form.exchange_rate" id="f_exchangeRate" type="text" />
          </BaseFormGroup>
        </BaseCard>

        <BaseCard has-header has-footer :title="`Items`">
          <BaseDatatable title="items" :table-headers="ITEMS_TABLE_HEADERS" :table-datas="form.form_items" no-search
            no-action no-responsive>
            <template #action-button>
              <BaseButton @click="openModal(ADD_ITEMS_MODAL_ID)">Add Items
              </BaseButton>
            </template>
            <template #cell-classification_code="{ index }">
              <BaseMultiSelect v-model="form.form_items[index].classification_code" :options="classificationCodes"
                label="Description" :reduce="(option) => option.Code" />
            </template>
            <template #cell-quantity="{ index }">
              <div class="d-flex">
                <BaseFormInput v-model="form.form_items[index].quantity" type="number" style="width: 100px" />
                <BaseMultiSelect v-model="form.form_items[index].unit_type" :options="unitTypes" label="Name"
                  :reduce="(option) => option.Code" style="width: 100%" />
              </div>
            </template>
            <template #cell-unit_price="{ index }">
              <BaseFormInput v-model="form.form_items[index].unit_price" type="number" />
            </template>
            <template #cell-amount="{ row }">
              {{ (row.unit_price * row.quantity).toFixed(2) }}
            </template>
            <template #cell-discount="{ index }">
              <div class="d-flex align-items-center">
                <BaseFormInput v-model="form.form_items[index].discount" type="number" />%
              </div>
            </template>
            <template #cell-tax_code="{ row }">
              <BaseFormInput @click="
                openModal(ADD_TAX_MODAL_ID, () => {
                  activeItemId = row.product_id;
                  setItemTaxes(row.taxes);
                })
                " :model-value="row.taxes.reduce((acc, tax) => acc + tax.taxAmount, 0)
    " type="number" />
            </template>
          </BaseDatatable>
        </BaseCard>

        <BaseCard has-header :title="`Summary`">
          <BaseDatatable title="summary" no-header no-table-header :table-headers="SUMMARY_TABLE_HEADERS"
            :table-datas="summaries" no-search no-action no-responsive>
            <template #cell-value="{ row }">
              <div class="d-flex justify-content-between align-items-end" style="width: 20%; margin-left: auto">
                <p>{{ form.currency_code }}</p>
                <p>{{ row.value }}</p>
              </div>
            </template>
          </BaseDatatable>

          <!-- <BaseFormGroup label="Rounding On"> -->
          <!--   <BaseFormSelect v-model="form.rounding_on" :options="[{ name: 'None' }]" label="name" value="name" /> -->
          <!-- </BaseFormGroup> -->
        </BaseCard>
      </template>
      <template #right>
        <BaseCard>
          <BaseFormGroup label=" Customer">
            <BaseMultiSelect v-model="form.contact_id" placeholder="Search customers" :filter="fuseSearch" label="fname"
              :options="contactOptions" :reduce="(option) => option.id" :value="0" @option:selected="addContact">
              <template #no-options="{ search, searching }">
                <template v-if="searching">
                  No results found for
                  <em>{{ search }}</em>
                  .
                </template>
              </template>
              <template #option="{ option }">
                <div v-if="option.name !== 'Create Customer'" class="d-flex">
                  <div class="py-2">
                    <img src="@order/assets/media/processed_contact.png" style="width: 30px; height: 30px" />
                  </div>
                  <div class="ps-5">
                    <div>{{ option.fname }}</div>
                    <div>{{ option.email }}</div>
                  </div>
                </div>
                <div v-else class="d-flex ms-3 me-3">
                  <div class="py-2">
                    <i class="pe-6 fas fa-plus w-auto" />
                    {{ option.name }}
                  </div>
                </div>
              </template>
            </BaseMultiSelect>
          </BaseFormGroup>
        </BaseCard>
      </template>

      <template #footer>
        <BaseButton type="primary" @click="submitDocument" :disabled="isLoading">
          <i v-if="isLoading" class="fas fa-spinner fa-pulse" />
          <span v-else>Submit</span>
        </BaseButton>
      </template>
    </BaseOrderPageLayout>
  </BasePageLayout>

  <ContactDetailModal :modal-id="ADD_CONTACT_MODAL_ID" :countries="countries" :states="states" />
  <ItemTaxModal :modal-id="ADD_TAX_MODAL_ID" :tax-types="taxTypes" :item-taxes="itemTaxes" @save="saveTax" />
  <BrowseProductModal :modal-id="ADD_ITEMS_MODAL_ID" :modal-title="'Select Items'" :all-products="items"
    :selected-product="form.form_items.map((m) => m.product_id)" @save="selectItems" />
</template>

<script setup>
import { ref, reactive, watch, computed, inject } from 'vue';
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import ContactDetailModal from '@people/components/ContactDetailModal.vue';
import ItemTaxModal from '@product/components/ItemTaxModal.vue';
import BrowseProductModal from '@product/components/BrowseProductModal.vue';
import { Modal } from 'bootstrap';
import axios from 'axios';

const $toast = inject('$toast');

const props = defineProps({
  countries: {
    type: Array,
    required: true,
  },
  states: {
    type: Array,
    required: true,
  },
  numberFormats: {
    type: Array,
    default: () => [],
  },
  classificationCodes: {
    type: Array,
    default: () => [],
  },
  unitTypes: {
    type: Array,
    default: () => [],
  },
  taxTypes: {
    type: Array,
    default: () => [],
  },
  items: {
    type: Array,
    default: () => [],
  },
  contacts: {
    type: Array,
    default: () => [],
  },
});

const ADD_CONTACT_MODAL_ID = 'AddContanctModal';
const ADD_TAX_MODAL_ID = 'AddTaxModal';
const ADD_ITEMS_MODAL_ID = 'AddItemsModal';
const ITEMS_TABLE_HEADERS = [
  { name: 'Item', key: 'title' },
  { name: 'Classification Code', key: 'classification_code', custom: true },
  { name: 'Quantity', key: 'quantity', custom: true },
  { name: 'Unit Price', key: 'unit_price', custom: true },
  { name: 'Amount', key: 'amount', custom: true },
  { name: 'Discount', key: 'discount', custom: true },
  { name: 'Tax', key: 'tax_code', custom: true },
];
const SUMMARY_TABLE_HEADERS = [
  { name: '', key: 'key' },
  { name: '', key: 'value', custom: true },
];

function generateInvoiceReference() {
  const prefix = 'INV'; // You can use any prefix you like
  const timestamp = Date.now().toString(36); // Convert timestamp to base-36 for compactness
  const randomPart = Math.random().toString(36).substring(2, 8).toUpperCase(); // Random alphanumeric string

  return `${prefix}-${timestamp}-${randomPart}`;
}

const val = ref('');
const form = reactive({
  payment_mode: 'credit',
  contact_id: props.contacts[0].id,
  // billing_party: 'aaaaaaa',
  // billing_contact_person_id: null,
  // billing_contact_person: 'aaaaa',
  // show_shipping: null,
  // shipping_info: null,
  // shipping_party: null,
  // shipping_contact_person_id: null,
  // shipping_contact_person: null,
  number: 4,
  number2: '',
  reference_no: generateInvoiceReference(),
  date: '2025-01-09',
  currency_code: 'MYR',
  exchange_rate: 1,
  form_items: [
    // {
    //   id: 1,
    //   parent_id: null,
    //   type: null,
    //   product_id: 1,
    //   description: 'Sales',
    //   service_date: null,
    //   account_id: 20,
    //   quantity: 1,
    //   location_id: null,
    //   product_unit_id: 1,
    //   unit_price: 100,
    //   discount: null,
    //   tax_code_id: 1,
    //   classification_code: null,
    //   taxes: [
    //     {
    //       taxType: '',
    //       numberOfUnits: 0,
    //       ratePerUnit: 0,
    //       taxRate: 0,
    //       taxAmount: 0,
    //     },
    //   ],
    // },
  ],
  rounding_on: null,
  // term_items: [
  //   {
  //     id: 1,
  //     term_id: 3,
  //     description: null,
  //     payment_due: '100%',
  //   },
  // ],
  // description: null,
  // internal_note: null,
  // remarks: null,
  // tag_ids: [],
  // files: [],
  // linked_transaction_ids: [],
  // status: 'ready',
  // myinvois_action: 'NORMAL',
  // customs_form_no: null,
  // customs_k2_form_no: null,
  // incoterms: null,
  // allow_intercept: true,
});

const activeItemId = ref(null);
const itemTaxes = ref([]);

const setItemTaxes = (taxes) => {
  itemTaxes.value = taxes;
};
const openModal = (modalId, callback = () => { }) => {
  callback();
  let modalInstance = Modal.getInstance(document.getElementById(modalId));

  if (!modalInstance) {
    modalInstance = new Modal(document.getElementById(modalId));
  }
  modalInstance.show();
};

const contactList = ref(props.contacts);
const contactOptions = computed(() => [
  { id: 0, name: 'Create Customer' },
  ...contactList.value,
]);
const addContact = (data) => {
  if (data.id > 0) return;
  openModal(ADD_CONTACT_MODAL_ID);
};
const fuseSearch = (options, search) =>
  search.length
    ? options.filter(
      (e) => e.fname?.includes(search) || e.email?.includes(search)
    )
    : options;

const selectItems = (items) => {
  let itemId = 0;
  items.forEach((itemId) => {
    if (
      form.form_items.findIndex((item) => item.product_id === itemId) === -1
    ) {
      const selectedItem = props.items.find((item) => item.id === itemId);
      form.form_items.push({
        id: itemId++,
        title: selectedItem.title,
        product_id: itemId,
        description: selectedItem.description,
        quantity: 1,
        origin_country: null,
        unit_type: selectedItem.unit_type,
        unit_price: 100,
        discount: 0,
        classification_code: selectedItem.classification_code,
        taxes: [],
        tax_exempt_amount: 0,
        tax_exempt_reason: '',
      });
    }
  });
};

const saveTax = (data) => {
  form.form_items.forEach((item) => {
    if (item.product_id === activeItemId.value) {
      item.taxes = data.taxes;
      item.tax_exempt_amount = data.taxExempt.amount;
      item.tax_exempt_reason = data.taxExempt.reason;
    }
  });
};

const getItemAmount = (item) => {
  const discount = item.discount || 0;
  const taxAmount = item.taxes.reduce((acc, tax) => acc + tax.taxAmount, 0);
  return (
    item.quantity * item.unit_price -
    (item.quantity * item.unit_price * discount) / 100 +
    taxAmount
  );
};

const summaries = computed(() => [
  {
    key: 'Subtotal',
    value: form.form_items.reduce((acc, item) => acc + getItemAmount(item), 0),
  },
  {
    key: 'Discount',
    value: form.form_items.reduce(
      (acc, item) =>
        acc + (item.quantity * item.unit_price * item.discount) / 100,
      0
    ),
  },
  {
    key: 'Tax',
    value: form.form_items.reduce(
      (acc, item) =>
        acc + item.taxes.reduce((acc, tax) => acc + tax.taxAmount, 0),
      0
    ),
  },
  {
    key: 'Total',
    value: form.form_items.reduce((acc, item) => acc + getItemAmount(item), 0),
  },
]);

const isLoading = ref(false);
const submitDocument = () => {
  isLoading.value = true;
  axios
    .post('/sales/invoices/submit', form)
    .then((res) => {
      console.log(res);
      const data = res.data;
      if (data.rejectedDocuments.length > 0) {
        $toast.error('Error', `${data.rejectedDocuments[0].error.message}`);
      }
      if (data.acceptedDocuments.length > 0) {
        $toast.success('Success', 'Document submitted successfully');
      }
    })
    .catch((err) => {
      console.log(err);
      $toast.error('Error', 'Failed to submit document');
    })
    .finally(() => {
      isLoading.value = false;
    });
};

// temp for init
selectItems([props.items[0].id]);
</script>

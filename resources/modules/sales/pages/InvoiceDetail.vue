<template>
  <BasePageLayout :page-name="`#1111`" back-to="/sales/invoices" is-setting>
    <BaseOrderPageLayout>
      <template #header> Header</template>

      <template #left>
        <BaseCard has-header has-footer :title="`General Info`">
          <BaseFormGroup col="6" for="f_number" label="No." required>
            <BaseFormSelect
              v-model="form.number"
              id="f_number"
              :options="numberFormats.filter((e) => e.type === 'sale_invoice')"
              label-key="format"
              value-key="type"
            />
          </BaseFormGroup>
          <BaseFormGroup
            col="6"
            for="f_reference_no"
            label="Reference No."
            required
          >
            <BaseFormInput
              v-model="form.number2"
              id="f_reference_no"
              type="text"
            />
          </BaseFormGroup>

          <BaseFormGroup col="6" for="f_date" label="Date" required>
            <BaseFormInput v-model="form.date" id="f_date" type="text" />
          </BaseFormGroup>
          <BaseFormGroup col="3" for="f_currencyCode" label="Curreny" required>
            <BaseFormInput
              v-model="form.currency_code"
              id="f_currencyCode"
              type="text"
            />
          </BaseFormGroup>
          <BaseFormGroup col="3" for="f_exchangeRate" label="Rate" required>
            <BaseFormInput
              v-model="form.exchange_rate"
              id="f_exchangeRate"
              type="text"
            />
          </BaseFormGroup>
        </BaseCard>

        <BaseCard has-header has-footer :title="`Items`">
          <BaseDatatable
            title="items"
            :table-headers="ITEMS_TABLE_HEADERS"
            :table-datas="[
              {
                title: 'Item 1',
                classification_code: '004',
                quantity: 1,
                unit_price: 100,
                amount: 100,
                discount: 0,
                taxes: [
                  {
                    taxType: '',
                    numberOfUnits: 0,
                    ratePerUnit: 0,
                    taxRate: 0,
                    taxAmount: 0,
                  }
                ]
              },
            ]"
            no-header
            no-action
            no-responsive
          >
            <template #cell-classification_code="{ row }">
              <BaseMultiSelect
                v-model="row.classification_code"
                :options="classificationCodes"
                label="Description"
                :reduce="(option) => option.Code"
              />
            </template>
            <template #cell-quantity="{ row }">
              <div class="d-flex">
                <BaseFormInput
                  v-model="row.quantity"
                  type="number"
                  style="width: 20%"
                />
                <BaseMultiSelect
                  v-model="row.unit_type"
                  :options="unitTypes"
                  label="Name"
                  :reduce="(option) => option.Code"
                  style="width: 100%"
                />
              </div>
            </template>
            <template #cell-unit_price="{ row }">
              <BaseFormInput v-model="row.unit_price" type="number" />
            </template>
            <template #cell-discount="{ row }">
              <div class="d-flex align-items-center">
                <BaseFormInput v-model="row.discount" type="number" />%
              </div>
            </template>
            <template #cell-tax_code="{ row }">
              <BaseFormInput
                @click="
                  openModal(ADD_TAX_MODAL_ID, () => {
                    setItemTaxes(row.taxes);
                  })
                "
                type="number"
              />
            </template>
          </BaseDatatable>
        </BaseCard>
      </template>
      <template #right>
        <BaseCard>
          <BaseFormGroup label="Add Customer">
            }
            <BaseMultiSelect
              placeholder="Search customers"
              :filter="fuseSearch"
              label="fname"
              :options="contactOptions"
              :value="0"
              @option:selected="addContact"
            >
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
                    <img
                      src="@order/assets/media/processed_contact.png"
                      style="width: 30px; height: 30px"
                    />
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

      <template #footer>Footer</template>
    </BaseOrderPageLayout>
  </BasePageLayout>

  <ContactDetailModal
    :modal-id="ADD_CONTACT_MODAL_ID"
    :countries="countries"
    :states="states"
  />
  <ItemTaxModal
    :modal-id="ADD_TAX_MODAL_ID"
    :tax-types="taxTypes"
    :item-taxes="itemTaxes"
  />
</template>

<script setup>
import { ref, reactive } from 'vue';
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import ContactDetailModal from '@people/components/ContactDetailModal.vue';
import ItemTaxModal from '@product/components/ItemTaxModal.vue';
import { Modal } from 'bootstrap';

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
});

const ADD_CONTACT_MODAL_ID = 'AddContanctModal';
const ADD_TAX_MODAL_ID = 'AddTaxModal';
const ITEMS_TABLE_HEADERS = [
  { name: 'Item', key: 'title' },
  { name: 'Classification Code', key: 'classification_code', custom: true },
  { name: 'Quantity', key: 'quantity', custom: true },
  { name: 'Unit Price', key: 'unit_price', custom: true },
  { name: 'Amount', key: 'amount' },
  { name: 'Discount', key: 'discount', custom: true },
  { name: 'Tax', key: 'tax_code', custom: true },
];

const val = ref('');
const form = reactive({
  payment_mode: 'credit',
  contact_id: 2,
  group_id: null,
  title: null,
  billing_party: 'aaaaaaa',
  billing_contact_person_id: null,
  billing_contact_person: 'aaaaa',
  show_shipping: null,
  shipping_info: null,
  shipping_party: null,
  shipping_contact_person_id: null,
  shipping_contact_person: null,
  number: 'IV-00001',
  number2: '123123123',
  date: '2025-01-09',
  currency_code: 'MYR',
  exchange_rate: 1,
  form_items: [
    {
      id: 1,
      parent_id: null,
      type: null,
      product_id: 1,
      description: 'Sales',
      service_date: null,
      account_id: 20,
      quantity: 1,
      location_id: null,
      product_unit_id: 1,
      unit_price: 100,
      discount: null,
      tax_code_id: 1,
      classification_code: null,
      taxes: [
        {
          taxType: '',
          numberOfUnits: 0,
          ratePerUnit: 0,
          taxRate: 0,
          taxAmount: 0,
        },
      ],
    },
  ],
  rounding_on: null,
  term_items: [
    {
      id: 1,
      term_id: 3,
      description: null,
      payment_due: '100%',
    },
  ],
  description: null,
  internal_note: null,
  remarks: null,
  tag_ids: [],
  files: [],
  linked_transaction_ids: [],
  status: 'ready',
  myinvois_action: 'NORMAL',
  customs_form_no: null,
  customs_k2_form_no: null,
  incoterms: null,
  allow_intercept: true,
});
const itemTaxes = ref([]);

const setItemTaxes = (taxes) => {
  itemTaxes.value = taxes;
};
const openModal = (modalId, callback = () => {}) => {
  callback();
  const modalInstance = Modal.getInstance(document.getElementById(modalId));
  modalInstance.show();
};

const contactOptions = ref([{ name: 'Create Customer' }]);
const addContact = () => {
  openModal(ADD_CONTACT_MODAL_ID);
};
const fuseSearch = (options, search) =>
  search.length
    ? options.filter(
        (e) => e.fname?.includes(search) || e.email?.includes(search)
      )
    : options;
</script>

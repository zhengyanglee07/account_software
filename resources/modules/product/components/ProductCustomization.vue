<template>
  <template
    v-for="(inputOption, index) in inputOptionArray"
    :key="index"
  >
    <BaseFormGroup
      label="Display Name"
      required
      :error-message="errorDisplayName[index] ? 'Display name is required' : ''"
      :description="
        optionType === 'shared' ? 'Visible to customers on storefront' : ''
      "
    >
      <template #label-row-end>
        <BaseButton
          v-if="optionType !== 'shared'"
          :id="'delete-customization-' + index"
          size="sm"
          type="link"
          color="danger"
          @click="
            deleteInputOption(inputOption.is_shared, index, inputOption.id)
          "
        >
          Delete Customization
        </BaseButton>
      </template>
      <BaseFormInput
        :id="`display-name-${index}`"
        v-model="inputOption.display_name"
        placeholder="Labeling, Engraving, etc."
        type="text"
        :disabled="optionType !== 'shared' && inputOption.is_shared"
        @input="inputValue('display_name', index, inputOption.display_name)"
      />
    </BaseFormGroup>
    <!-- <BaseFormGroup
      v-if="optionType === 'shared'"
      label="Unique Name"
      required
      :error-message="
        errorName[index] ? 'Non duplicate unique name is required' : ''
      "
      description="Unique identifier for managing customizations"
    >
      <BaseFormInput
        :id="`name-${index}`"
        v-model="inputOption.name"
        type="text"
        placeholder="Custom 1, Custom 2, etc."
        @input="inputValue('name', index, inputOption.name)"
      />
    </BaseFormGroup> -->
    <BaseFormGroup
      label="Type"
      :col="8"
    >
      <BaseFormSelect
        :id="`type-${index}`"
        v-model="inputOption.type"
        :disabled="optionType !== 'shared' && inputOption.is_shared"
        @change="inputValue('type', index, inputOption.type)"
      >
        <option
          v-for="(input, typeIndex) in typeOfInput"
          :key="typeIndex"
          :value="input"
        >
          {{ input }}
        </option>
      </BaseFormSelect>
    </BaseFormGroup>
    <BaseFormGroup
      :col="2"
      label="Required"
      class="row"
    >
      <BaseFormCheckBox
        :id="`checkbox-${index}`"
        v-model="inputOption.is_required"
        :value="true"
        class="justify-content-center align-items-center ps-6"
        :disabled="optionType !== 'shared' && inputOption.is_shared"
        @change="inputValue('is_required', index, inputOption.is_required)"
      />
    </BaseFormGroup>
    <template v-if="inputOption.type === 'Checkbox'">
      <BaseFormGroup>
        <BaseFormCheckBox
          :id="`set-a-range-${index}`"
          v-model="inputOption.is_range"
          :value="true"
          :disabled="optionType !== 'shared' && inputOption.is_shared"
          @change="inputValue('is_range', index, inputOption.is_range)"
        >
          Set a range
        </BaseFormCheckBox>
      </BaseFormGroup>
      <BaseFormGroup
        v-if="inputOption.is_range"
        :col="6"
        label="At Least"
        :error-message="errorAtLeast[index] ? 'At Least is required' : ''"
        required
      >
        <BaseFormInput
          :id="`at-least-${index}`"
          v-model="inputOption.at_least"
          type="number"
          step="1"
          min="0"
          :disabled="optionType !== 'shared' && inputOption.is_shared"
          @input="
            inputValue(
              'at_least',
              index,
              ('' + inputOption.at_least).replace('-', '')
            )
          "
        />
      </BaseFormGroup>
      <BaseFormGroup
        v-if="inputOption.is_range"
        :col="6"
        label="Up To"
        :error-message="errorUpTo[index] ? 'Requires larger than At Least' : ''"
      >
        <BaseFormInput
          :id="`up-to-${index}`"
          v-model="inputOption.up_to"
          type="number"
          step="1"
          min="0"
          :disabled="optionType !== 'shared' && inputOption.is_shared"
          @input="
            inputValue(
              'up_to',
              index,
              ('' + inputOption.up_to).replace('-', '')
            )
          "
        />
      </BaseFormGroup>
      <BaseFormGroup>
        <BaseFormCheckBox
          :id="`total-price-${index}`"
          v-model="inputOption.is_total_Charge"
          :value="true"
          :disabled="optionType !== 'shared' && inputOption.is_shared"
          @change="
            inputValue('is_total_Charge', index, inputOption.is_total_Charge)
          "
        >
          Total Price
        </BaseFormCheckBox>
      </BaseFormGroup>
      <BaseFormGroup
        v-if="inputOption.is_total_Charge"
        :label="`Total Price ( ${currency === 'MYR' ? 'RM' : currency} )`"
        required
        :error-message="errorTotalPrice[index] ? 'Total price is requried' : ''"
      >
        <BaseFormInput
          :id="`total-charge-${index}`"
          v-model="inputOption.total_charge_amount"
          type="number"
          step="1"
          min="0.00"
          :disabled="optionType !== 'shared' && inputOption.is_shared"
          @input="
            inputValue(
              'total_charge_amount',
              index,
              ('' + inputOption.total_charge_amount).replace('-', '')
            )
          "
        />
      </BaseFormGroup>
    </template>

    <template
      v-for="(input, i) in inputOption.inputs"
      :key="i"
    >
      <InputField
        :id="`input-${index}-${i}`"
        :input="input"
        :option-type="optionType"
        :selected-index="i"
        :option-index="index"
        :type="inputOption.type"
        :currency="currency"
        :total-customs="inputOption?.inputs?.length"
        :drag-option="optionType === 'shared' ? true : !inputOption.is_shared"
        @addInputOptionArray="addInput(inputOption.type, index)"
      />
    </template>
    <BaseFormGroup
      v-if="
        (inputOption.type !== 'Text Field' &&
          inputOption.type !== 'Number Field' &&
          inputOption.type !== 'Text Area' &&
          inputOption.is_shared !== 1) ||
          (inputOption.type !== 'Text Field' &&
            inputOption.type !== 'Number Field' &&
            inputOption.type !== 'Text Area' &&
            optionType === 'shared')
      "
    >
      <BaseButton
        :id="`add-input-${index}`"
        size="sm"
        type="link"
        @click="addInput(inputOption.type, index)"
      >
        + Add Value
      </BaseButton>
    </BaseFormGroup>
    <div
      v-if="!noSeperator"
      class="separator mb-10"
    />
  </template>
  <template v-if="!hideAddGlobalButton">
    <BaseFormGroup :col="6">
      <BaseButton
        :id="`add-global-cutomization-${index}`"
        size="sm"
        @click="addGlobalCustomization()"
      >
        Add Global Customization
      </BaseButton>
    </BaseFormGroup>
  </template>
  <Teleport to="body">
    <BaseModal
      ref="customizations"
      title="Configure Customizations"
      modal-id="shared-option-modal"
      z-index="1056"
      size="lg"
    >
      <BaseDatatable
        no-search
        :no-header="datas.length > 0"
        title="customization"
        no-action
        :table-headers="headers"
        :table-datas="datas"
        checbox-id="add-global-customization-checkbox"
        @selectAll="handleSelectAll"
      >
        <template #action-button>
          <BaseButton
            v-if="!datas.length"
            id="add-customization"
            has-add-icon
            data-bs-dismiss="modal"
            @click="redirect"
          >
            Add Customization
          </BaseButton>
        </template>
        <template #cell-checkbox="{ row: { id } }">
          <BaseFormCheckBox
            :id="`add-global-customization-checkbox-${id}`"
            v-model="sharedOptionArray"
            :value="id"
            @change="handleSelect"
          />
        </template>
        <template #cell-values="{ index }">
          <span v-if="optionValues[index] !== ''">
            {{ optionValues[index] }}
          </span>
          <BaseBadge
            v-else
            text="Empty"
            type="warning"
          />
        </template>
      </BaseDatatable>
      <template #footer>
        <BaseButton
          id="save-shared-option"
          :disabled="!datas.length"
          @click="addSharedOption"
        >
          Save
        </BaseButton>
      </template>
    </BaseModal>
  </Teleport>
</template>

<script>
/* eslint-disable no-unused-expressions */
// import draggable from 'vuedraggable';
import { mapState, mapMutations } from 'vuex';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import InputField from '@product/components/InputField.vue';
import cloneDeep from 'lodash/cloneDeep';
import { Modal } from 'bootstrap';

export default {
  components: {
    InputField,
    // Draggable: draggable,
  },
  mixins: [specialCurrencyCalculationMixin],
  props: ['optionType', 'currency', 'hideAddGlobalButton', 'noSeperator'],

  data() {
    return {
      typeOfInput: [
        'Color',
        'Image',
        'Button',
        'Checkbox',
        'Dropdown',
        'Number Field',
        'Text Field',
        'Text Area',
      ],
      // tableHeaders: [
      //   '',
      //   'Unique Name',
      //   'Display Name',
      //   'Type',
      //   'Values',
      //   'Products',
      // ],
      headers: [
        { key: 'checkbox', sortable: false },
        { name: 'Custom', key: 'display_name', sortable: false },
        {
          name: 'Type',
          key: 'type',
          sortable: false,
        },
        { name: 'Values', key: 'values', custom: true, sortable: false },
      ],
      datas: [],
      sharedOptionArray: [],
      displayName: [],
      openRange: false,
      inputOptionArray: [],
    };
  },

  computed: {
    ...mapState('product', [
      'inputOptions',
      'errorOptions',
      'errorDisplayName',
      'errorName',
      'errorUpTo',
      'errorAtLeast',
      'errorTotalPrice',
    ]),
    // inputOptionId() {
    //   const ids = [];
    //   this.inputOptionArray.forEach((option) => {
    //     ids.push(option.id);
    //   });
    //   return ids;
    // },
    optionValues() {
      const optionDatas = cloneDeep(this.datas);
      const optionArray = [];
      optionDatas
        .sort((a, b) => {
          if (a?.id < b?.id) {
            return 1;
          }
          if (a?.id > b.id) {
            return -1;
          }
          return 0;
        })
        .forEach((option) => {
          const inputArray = [];
          option.inputs.forEach((input) => {
            inputArray.push(input.label);
          });
          optionArray.push(inputArray.join(', '));
        });
      return optionArray;
    },
  },
  watch: {
    // inputOptionId(newValue) {
    //   let values = '';
    //   const [first] = newValue;
    //   first === undefined ? (values = 'create') : (values = first);
    //   axios.get(`/get/exists/${values}`).then((response) => {
    //     this.$store.dispatch('product/fetchName', {
    //       nameArray: response.data,
    //     });
    //   });
    // },
    inputOptions: {
      deep: true,
      handler(newValue) {
        this.inputOptionArray = cloneDeep(newValue);
        this.sharedOptionArray = cloneDeep(
          newValue?.filter((item) => item.is_shared)
        )?.map((e) => e.id);
      },
    },
  },
  mounted() {
    axios.get('/get/product/option').then((response) => {
      this.datas = response.data;
    });

    if (this.optionType === 'shared') {
      axios.get(`/get/exists/create`).then((response) => {
        this.$store.dispatch('product/fetchName', {
          nameArray: response.data,
        });
      });
    }
    setTimeout(() => {
      const modalEl = document.getElementById('shared-option-modal');
      modalEl.addEventListener('hide.bs.modal', () => {
        document.getElementById(
          'add-global-customization-checkbox'
        ).checked = false;
        this.sharedOptionArray = cloneDeep(
          this.inputOptions.filter((item) => item.is_shared)
        ).map((e) => e.id);
      });
    }, 1000);
  },
  methods: {
    ...mapMutations('product', [
      'pushOptionArray',
      'deleteOptionArray',
      'inputOptionValue',
      'pushSharedOptionArray',
      'resetInputOptionValues',
      'checkError',
      'resetOptionArray',
      'resetDeletedArray',
      'reorderInputOptionValues',
    ]),
    nextInput(index, latestIndex) {
      document.getElementById(`label-${index}-${latestIndex - 1}`).focus();
    },
    handleSelectAll(e) {
      this.sharedOptionArray = [];
      if (e) {
        this.datas.forEach((option) => {
          this.sharedOptionArray.push(option.id);
        });
      }
    },
    handleSelect(e) {
      const { checked } = e.target;
      if (!checked)
        document.getElementById(
          'add-global-customization-checkbox'
        ).checked = false;
    },
    addGlobalCustomization() {
      new Modal(document.getElementById('shared-option-modal')).show();
    },
    addInputOption() {
      this.resetOptionArray();
      this.resetDeletedArray();
      this.pushOptionArray({ type: 'inputOptions' });
    },
    async addInput(type, index) {
      await this.pushOptionArray({
        type: 'inputs',
        optionIndex: index,
        valueType: type,
      });
      this.$nextTick(() =>
        this.nextInput(index, this.inputOptionArray[index].inputs.length)
      );
    },
    deleteInputOption(isShared, index, id) {
      this.deleteOptionArray({
        type: 'inputOptions',
        isShared,
        index,
        id,
      });
    },
    inputValue(object, index, value) {
      if (object === 'type') {
        this.resetInputOptionValues({ index, value });
      }
      this.inputOptionValue({ type: 'inputOptions', object, index, value });
    },
    async addSharedOption() {
      const compare = (a, b) => {
        const x = a.global_priority ?? 0;
        const y = b.global_priority ?? 0;
        if (x < y) {
          return -1;
        }
        if (x > y) {
          return 1;
        }
        return 0;
      };
      console.log(this.sharedOptionArray);
      await this.pushSharedOptionArray(
        this.datas
          .filter((el) => this.sharedOptionArray.includes(el.id))
          .sort(compare)
      );
      this.$emit('save');
      this.$nextTick(() => {
        Modal.getInstance(
          document.getElementById('shared-option-modal')
        ).hide();
      });
    },

    reorderValues(i) {
      const data = { index: i, values: this.inputOptionArray[i].inputs };
      this.reorderInputOptionValues(data);
    },

    redirect() {
      this.$inertia.visit('/product/customizations');
    },
  },
};
</script>

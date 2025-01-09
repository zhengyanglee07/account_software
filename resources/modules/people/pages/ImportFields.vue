<template>
  <BasePageLayout
    is-setting
    page-name="Back to People"
    back-to="/people"
  >
    <BaseCard
      title="Column Matching"
      has-header
    >
      <p>Match the columns in your uploaded file to a field our system.</p>
      <p
        class="mb-3 mt-3"
        style="font-weight: bold"
      >
        {{ csvData.length }} people to be imported from this file
      </p>
      <form class="form-horizontal">
        <div class="table-responsive-md">
          <table
            class="table table-content-border table-hover match_column_tables"
            style="margin-left: -10px !important"
          >
            <thead style="align-items: center">
              <tr>
                <td
                  v-for="(_, index) in csvHeaderFields"
                  :key="`rootCols-${index}`"
                  class="py-4 px-5"
                  style="padding: 50px"
                  :style="{
                    'background-color':
                      selectedValue[index] === 'none' ? 'orange' : '#e6e9ec',
                  }"
                >
                  <div
                    v-if="isHidden[index]"
                    class="d-flex align-items-center"
                  >
                    <BaseFormSelect
                      v-model="selectedValue[index]"
                      @change="handleOnChange($event, index)"
                    >
                      <option
                        value="none"
                        disabled
                      >
                        Choose...
                      </option>

                      <option
                        v-for="field in fields"
                        :key="`selectFields-${field.value}`"
                        :value="field.value"
                        :disabled="selectedValue.includes(field.value)"
                      >
                        {{ field.name }}
                      </option>

                      <option
                        class="cf"
                        value="custom_field"
                      >
                        Create new Custom Fields
                      </option>

                      <option
                        disabled
                        style="font-weight: bold"
                      >
                        Custom Fields List
                      </option>

                      <template v-if="customOptions != null">
                        <option
                          v-for="(customOption, cusIndex) in customOptions"
                          :key="`customOptions-${cusIndex}`"
                          :value="customOption"
                          :disabled="selectedValue.includes(customOption)"
                        >
                          {{ customOption }}
                        </option>
                      </template>

                      <option
                        v-for="(newCustom, newCusIndex) in newCustoms"
                        :key="`newCustomOptions-${newCusIndex}`"
                        :value="newCustom.value"
                        :disabled="selectedValue.includes(newCustom.value)"
                      >
                        {{ newCustom.name }}
                      </option>
                    </BaseFormSelect>
                    <i
                      v-if="selectedValue[index] === 'none'"
                      class="fas fa-exclamation-triangle text-light ms-3"
                      data-bs-toggle="tooltip"
                      data-bs-placement="bottom"
                      title="Datas in this column will not be imported if no field is selected in this dropdown"
                    />
                  </div>

                  <div v-else>
                    <BaseFormGroup
                      label="New field name:"
                      label-for="newCustomFieldName"
                    >
                      <BaseFormInput
                        id="newCustomFieldName"
                        type="text"
                        class="newCustomFieldName"
                      />
                      <template
                        v-if="errMsg.idx === index"
                        #error-message
                      >
                        {{ errMsg.message }}
                      </template>
                    </BaseFormGroup>
                    <BaseFormGroup
                      label="Data Type: "
                      label-for="dataType"
                    >
                      <BaseFormSelect
                        id="dataType"
                        v-model="selectedDataType[index]"
                        name="dataType"
                        class="dataTypeSelect"
                        @update:modelValue="
                          dataTypeErrMsg = { idx: null, message: '' }
                        "
                      >
                        <option
                          value=""
                          disabled
                        >
                          Choose...
                        </option>
                        <option value="text">
                          Text
                        </option>
                        <option value="date">
                          Date
                        </option>
                        <option value="datetime">
                          Datetime
                        </option>
                        <option value="number">
                          Number
                        </option>
                        <option value="emailAddress">
                          Email
                        </option>
                      </BaseFormSelect>
                      <template
                        v-if="dataTypeErrMsg.idx === index"
                        #error-message
                      >
                        {{ dataTypeErrMsg.message }}
                      </template>
                    </BaseFormGroup>
                    <div class="d-flex justify-content-end">
                      <BaseButton
                        type="link"
                        size="sm"
                        class="mx-3"
                        @click="cancel(index)"
                      >
                        Cancel
                      </BaseButton>
                      <BaseButton
                        size="sm"
                        @click="confirm(index)"
                      >
                        Confirm
                      </BaseButton>
                    </div>
                  </div>
                </td>
              </tr>
            </thead>
            <tbody>
              <tr style="text-align: center; background-color: C1C4C0">
                <th
                  v-for="(csvHeaderField, index) in csvHeaderFields"
                  :key="`header-${index}`"
                  :class="{
                    'not-imported': selectedValue[index] === 'none',
                  }"
                  class="text-uppercase"
                >
                  {{ csvHeaderField }}
                </th>
              </tr>
              <tr
                v-for="(row, rowIndex) in csvDisplayData"
                :key="`rows-${rowIndex}`"
              >
                <td
                  v-for="(colKey, colIndex) in Object.keys(row)"
                  :key="`rowData-${colKey}`"
                  class="py-4 px-5"
                  :class="{
                    'not-imported': selectedValue[colIndex] === 'none',
                  }"
                >
                  {{ row[colKey] }}
                </td>
              </tr>
            </tbody>
          </table>

          <p class="mb-1 pt-5">
            Suggested mobile number format: {country code}number
            <br>
            Example: 60123456789
          </p>

          <div>
            <span>
              * {{ importedColumnsCount }} column{{
                importedColumnsCount > 1 ? 's' : ''
              }}
              will be imported, {{ excludedColumnsCount }} column{{
                excludedColumnsCount > 1 ? 's' : ''
              }}
              will be ignored
            </span>
          </div>

          <div
            v-if="hasEmailColumn && hasPhoneNumberColumn"
            class="mt-3"
          >
            <BaseFormGroup
              label="Please choose your primary unique identifier:"
            >
              <BaseFormRadio
                v-model="selectedUniqueIdentifier"
                value="email"
              >
                Email
              </BaseFormRadio>
              <BaseFormRadio
                v-model="selectedUniqueIdentifier"
                value="phone_number"
              >
                Mobile Number
              </BaseFormRadio>
            </BaseFormGroup>
          </div>
        </div>
        <div class="import-footer pt-10">
          <BaseButton
            :disabled="showImportBtnSpinner"
            @click="save"
          >
            Import
          </BaseButton>
        </div>
      </form>
    </BaseCard>
  </BasePageLayout>
</template>

<script>
const EMAIL_FIELD = 'email';
const PHONE_NUMBER_FIELD = 'phone_number';

export default {
  name: 'ImportFields',
  props: {
    csvHeaderFields: {
      type: Array,
      default: () => [],
    },
    csvDisplayData: {
      type: Array,
      default: () => [],
    },
    csvData: {
      type: Array,
      default: () => [],
    },
    customOptions: {
      type: Array,
      default: () => [],
    },
    // emailAddresses: {
    //   type: Array,
    //   default: () => [],
    // },
    // phoneNumbers: {
    //   type: Array,
    //   default: () => [],
    // },
  },
  data() {
    return {
      showImportBtnSpinner: false,
      selectedValue: [],
      selectedDataType: this.csvHeaderFields.map(() => ''),
      isHidden: this.csvHeaderFields.map(() => true),
      errMsg: { idx: null, message: '' },
      dataTypeErrMsg: { idx: null, message: '' },
      count: this.customOptions !== null ? this.customOptions.length : 0,
      newCustoms: [],
      selectedUniqueIdentifier: EMAIL_FIELD,

      // matches property below is used to guess the column at mounted time only.
      // For the rest of time use value instead, e.g. saving into database
      //
      // Extra: Use regex in the future if anyone who seeing this has time to do it,
      //
      // Ps:
      // if you don't want to think too much (like me), then just add more possible
      // col value into matches array below instead :)
      fields: [
        {
          name: 'Email Address',
          value: 'email',
          matchers: [
            'email',
            'e mail',
            'e-mail',
            'e_mail',
            'emailaddr',
            'email addr',
            'email-addr',
            'email_addr',
            'emailaddress',
            'email address',
            'email-address',
            'email_address',
            'mail',
            'mailaddr',
            'mail addr',
            'mail-addr',
            'mail_addr',
            'mailaddress',
            'mail address',
            'mail-address',
            'mail_address',
            'mailingaddress',
            'mailing address',
            'mailing-address',
            'mailing_address',
          ],
        },
        {
          name: 'First Name',
          value: 'fname',
          matchers: [
            'fname',
            'f name',
            'f-name',
            'f_name',
            'firstname',
            'first name',
            'first-name',
            'first_name',
          ],
        },
        {
          name: 'Last Name',
          value: 'lname',
          matchers: [
            'lname',
            'l name',
            'l-name',
            'l_name',
            'lastname',
            'last name',
            'last-name',
            'last_name',
          ],
        },
        {
          name: 'Address - line 1',
          value: 'address1',
          matchers: [
            'address1',
            'address 1',
            'address-1',
            'address_1',
            'address one',
            'address-one',
            'address_one',
          ],
        },
        {
          name: 'Address - line 2',
          value: 'address2',
          matchers: [
            'address2',
            'address 2',
            'address-2',
            'address_2',
            'address two',
            'address-two',
            'address_two',
          ],
        },
        {
          name: 'Address - postcode',
          value: 'zip',
          matchers: [
            'zip',
            'zipcode',
            'zip code',
            'zip-code',
            'zip_code',
            'postcode',
            'post-code',
            'post code',
            'addresszip',
            'address zip',
            'address_zip',
            'address-zip',
            'addresspostcode',
            'address postcode',
            'address_postcode',
            'address-postcode',
          ],
        },
        {
          name: 'Address - city',
          value: 'city',
          matchers: [
            'city',
            'addresscity',
            'address city',
            'address_city',
            'address-city',
          ],
        },
        {
          name: 'Address - state',
          value: 'state',
          matchers: [
            'state',
            'addressstate',
            'address state',
            'address_state',
            'address-state',
          ],
        },
        {
          name: 'Address - country',
          value: 'country',
          matchers: [
            'country',
            'addresscountry',
            'address country',
            'address_country',
            'address-country',
          ],
        },
        {
          name: 'Gender',
          value: 'gender',
          matchers: ['gender', 'sex'],
        },
        {
          name: 'Birthday',
          value: 'birthday',
          matchers: [
            'birthday',
            'birth day',
            'birth-day',
            'birth_day',
            'birth',
            'bday',
            'birthdate',
            'birth-date',
            'birth date',
            'birth_date',
          ],
        },
        {
          name: 'Mobile Number',
          value: 'phone_number',
          matchers: [
            'phonenum',
            'phone num',
            'phone-num',
            'phone_num',
            'phoneno',
            'phone no',
            'phone-no',
            'phone_no',
            'phonenumber',
            'phone_number',
            'phone number',
            'phone-number',
            'phone_number',
            'phone',
            'mobile',
            'mobilenum',
            'mobile num',
            'mobile-num',
            'mobile_num',
            'mobileno',
            'mobile no',
            'mobile-no',
            'mobile_no',
            'mobilenumber',
            'mobile number',
            'mobile-number',
            'mobile_number',
            'contact',
            'contactnum',
            'contactnum',
            'contact-num',
            'contact_num',
            'contactno',
            'contact no',
            'contact-no',
            'contact_no',
          ],
        },
      ],
    };
  },
  computed: {
    fieldValues() {
      return this.fields.map((field) => field.value);
    },
    importedColumnsCount() {
      return this.selectedValue.filter((val) => val !== 'none').length;
    },
    excludedColumnsCount() {
      return this.selectedValue.length - this.importedColumnsCount;
    },
    hasEmailColumn() {
      return this.getDataColIndex(EMAIL_FIELD) !== -1;
    },
    hasPhoneNumberColumn() {
      return this.getDataColIndex(PHONE_NUMBER_FIELD) !== -1;
    },
  },
  mounted() {
    this.selectedValue = this.csvHeaderFields.map((csvHeader) => {
      const fieldIdx = this.fields
        .map((field, idx) => {
          if (field.matchers.includes(csvHeader.toLowerCase())) {
            return idx;
          }
        })
        .filter((f) => f !== undefined)[0];

      return (this.fields[fieldIdx] && this.fields[fieldIdx].value) || 'none';
    });
  },
  methods: {
    // handleCheckUniqueIdentifier(identifier) {
    //   this.selectedUniqueIdentifier = identifier;
    // },

    getDataColIndex(dataType) {
      return this.selectedValue.indexOf(dataType);
    },

    handleOnChange(event, index) {
      if (event.target.value === 'custom_field') {
        this.isHidden[index] = false;
      }
    },

    confirm(index) {
      this.errMsg = { idx: null, message: '' };
      this.dataTypeErrMsg = { idx: null, message: '' };
      const regexValidation = /^[a-z0-9_]*$/;

      let columns = [];

      this.isHidden.forEach((val, idx) => {
        if (!val) columns = [...columns, idx];
      });

      const allNameInput = document.querySelectorAll(
        '.newCustomFieldName > input'
      );
      const allDataTypeInput = document.querySelectorAll('.dataTypeSelect');

      const nameInput = allNameInput[columns.indexOf(index)]?.value;
      const dataTypeInput = allDataTypeInput[columns.indexOf(index)]?.value;

      if (this.count < 30) {
        if (nameInput !== '' && dataTypeInput !== '') {
          if (!regexValidation.test(nameInput)) {
            this.errMsg = {
              idx: index,
              message: 'Only a-z, 0-9 and _ are allowed',
            };
          } else if (nameInput != null) {
            this.$axios
              .post('/customFieldName', {
                name_input: nameInput,
                dataTypeInput,
                td_index: index,
                csvData: JSON.stringify(this.csvDisplayData),
              })
              .then((response) => {
                if (response.data.regexError === 'error') {
                  this.dataTypeErrMsg = {
                    idx: index,
                    message: 'The data format is incorrect',
                  };
                }
                if (response.data.message === 'duplicated') {
                  this.errMsg = {
                    idx: index,
                    message: 'This Custom Field is already exist.',
                  };
                } else if (response.data.message === 'non-duplicate') {
                  this.count += 1;
                  this.isHidden = this.isHidden.map((val, idx) =>
                    idx === index ? true : val
                  );
                  this.selectedValue = this.selectedValue.map((val, idx) =>
                    idx === index ? nameInput : val
                  );

                  const cusObject = {
                    name: nameInput,
                    value: nameInput,
                  };
                  this.newCustoms.push(cusObject);
                }
              })
              .catch((err) => {
                this.$toast.error('Failed.', 'Unexpected error.');
                console.log(err);
              });
          }
        } else {
          this.dataTypeErrMsg = {
            idx: index,
            message: 'Custom field name and data type must be filled',
          };
        }
      } else {
        this.$toast.error('Error', 'Custom Fields are limit to 30 only !');
      }
    },

    cancel(index) {
      this.dataTypeErrMsg = { idx: index, message: '' };
      this.isHidden = this.isHidden.map((val, idx) =>
        idx === index ? true : val
      );
      this.selectedValue = this.selectedValue.map((val, idx) =>
        idx === index ? 'none' : val
      );
    },

    save() {
      // email or phone must exist in imported contact data
      if (!this.hasEmailColumn && !this.hasPhoneNumberColumn) {
        this.$toast.error(
          'Error',
          'Either email or mobile number is required for imported data'
        );
        return;
      }

      // if only individual unique identifier col is selected (phone)
      if (this.hasPhoneNumberColumn && !this.hasEmailColumn) {
        this.selectedUniqueIdentifier = PHONE_NUMBER_FIELD;
      }

      // if only individual unique identifier col is selected (email)
      if (this.hasEmailColumn && !this.hasPhoneNumberColumn) {
        this.selectedUniqueIdentifier = EMAIL_FIELD;
      }

      // ignore all empty entries of primary unique identifier col
      const dataToImport = this.removeEmptyRows(
        this.csvData,
        this.getDataColIndex(this.selectedUniqueIdentifier)
      );

      this.showImportBtnSpinner = true;

      this.$axios
        .post('/import/contacts', {
          includedNameArray: this.selectedValue,
          csvData: JSON.stringify(dataToImport),
          selectedUniqueIdentifier: this.selectedUniqueIdentifier,
          success: dataToImport.length,
          skipped: this.csvData.length - dataToImport.length,
        })
        .then(({ data: { importedContactId } }) => {
          // if the people existed in db, it will still show the number of successfully newly created & updated existing people count
          // since if the people is existed, it will update the db instead of create a new one
          this.$toast.success(
            'Success',
            `${importedContactId.length} contacts are successfully imported.`
          );
          this.$inertia.visit('/import_tag');
        })
        .catch((error) => {
          const errData = error.response.data;

          if (error.response.status === 422) {
            this.$toast.error('Failed to import data', errData.message);
            return;
          }

          // generic error
          this.$toast.error(
            'Failed to import the data.',
            'Unexpected error occurred. Please try to import again or contact support.'
          );
        })
        .finally(() => {
          this.showImportBtnSpinner = false;
        });
    },

    /**
     * @param {array} csvData
     * @param colIdx
     * @returns {boolean}
     */
    removeEmptyRows(csvData, colIdx) {
      return csvData.filter((data) => {
        const colKey = Object.keys(data)[colIdx];
        return !!data[colKey];
      });
    },
  },
};
</script>

<style scoped lang="scss">
:deep(.form-select) {
  min-width: max-content;
}

:deep(.form-control) {
  min-width: max-content;
}

.import-footer {
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
  align-items: center;
  width: 100%;
  margin-top: 1rem;
  padding-right: 1rem;
}

.not-imported {
  background-color: #f0f3f6;
  color: $base-font-color;
  text-align: center;
}

option:disabled {
  color: slategrey;
}

.match_column_tables {
  border-spacing: 10px 0;
  margin: 0 0 12px -10px;
  border-collapse: separate;
}

.table-content-border {
  border-top: 1px solid #ebedf2;
  border-bottom: 1px solid #ebedf2;
}

table {
  display: block;
  overflow-x: auto;
  white-space: nowrap;
}

th,
td {
  border-left: 1px solid #ebedf2;
  border-right: 1px solid #ebedf2;
  text-align: center;
}

thead {
  background-color: #ecf2ff;
  font-weight: 800;
  text-align: center;
  vertical-align: middle;
  padding: 5px 10px;
}
</style>

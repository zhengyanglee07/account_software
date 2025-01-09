<template>
  <BasePageLayout
    page-name="Setup Campaign"
    back-to="/affiliate/members/campaigns"
  >
    <template #left>
      <BaseCard>
        <BaseFormGroup
          label="Title"
          :error-message="showValidationErrors ? 'Title is required' : ''"
          required
        >
          <BaseFormInput
            id="title"
            v-model="title"
            type="text"
            placeholder="Campaign title"
            @input="clearValidationError"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="Domain"
          :error-message="
            showValidationErrors && !v$.domain.required
              ? 'Domain is required'
              : ''
          "
          required
        >
          <BaseFormSelect
            id="domain"
            v-model="domain"
            @change="clearValidationError"
          >
            <option
              value=""
              disabled
            >
              Select domain
            </option>
            <option
              v-for="(d, i) in domains"
              :key="`domain-${i}`"
              :value="d.domain"
            >
              {{ d.domain }}
            </option>
          </BaseFormSelect>
        </BaseFormGroup>
        <BaseFormGroup
          label="Products"
          required
          :col="selectedProductsType === 'all' ? 12 : 4"
        >
          <BaseFormSelect
            id="product-type"
            v-model="selectedProductsType"
            label-key="name"
            value-key="value"
            :options="[
              {
                name: 'All products',
                value: 'all',
              },
              {
                name: 'Specific products',
                value: 'product',
              },
              {
                name: 'Specific categories',
                value: 'category',
              },
            ]"
            @change="resetSelectedProductsOrCategories"
          />
        </BaseFormGroup>
        <BaseFormGroup
          v-if="selectedProductsType !== 'all'"
          label="&nbsp"
          :col="8"
        >
          <BaseMultiSelect
            v-if="selectedProductsType === 'product'"
            id="specific-product"
            v-model="selectedProductsOrCategories"
            label="productTitle"
            :options="productOptions"
            multiple
          />
          <BaseMultiSelect
            v-if="selectedProductsType === 'category'"
            id="specific-category"
            v-model="selectedProductsOrCategories"
            label="name"
            :options="categoryOptions"
            multiple
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard>
        <BaseFormGroup
          label="Condition Groups"
          required
        >
          <template #label-row-end>
            <BaseButton
              id="add-condition-group"
              has-add-icon
              size="sm"
              @click="addNewConditionGroup"
            >
              Add Condition Group
            </BaseButton>
          </template>
        </BaseFormGroup>
        <template
          v-for="(cg, cgIdx) in conditionGroups"
          :key="cgIdx"
        >
          <BaseFormGroup :label="'Condition Groups ' + (cgIdx + 1)">
            <template #label-row-end>
              <BaseButton
                v-if="conditionGroups.length > 1"
                :id="`remove-condition-group-${cgIdx}`"
                size="sm"
                type="link"
                color="danger"
                @click="deleteConditionGroup(cgIdx)"
              >
                Remove
              </BaseButton>
            </template>
          </BaseFormGroup>
          <BaseFormGroup
            label="Affiliate"
            :col="cg._group_type === 'all' ? 12 : 4"
            required
          >
            <BaseFormSelect
              :id="`affiliate-type-${cgIdx}`"
              v-model="cg._group_type"
              @change="clearAffiliateGroups(cgIdx)"
            >
              <option
                value=""
                disabled
              >
                Choose...
              </option>
              <option
                value="all"
                :disabled="isAllAffiliatesOptionSelected"
              >
                All affiliates
              </option>
              <option value="specific">
                Specific affiliate groups
              </option>
            </BaseFormSelect>
          </BaseFormGroup>
          <BaseFormGroup
            v-if="cg._group_type === 'specific'"
            label="&nbsp"
            :col="8"
          >
            <BaseMultiSelect
              :id="`specicif-affiliate-group-${cgIdx}`"
              v-model="cg.groups"
              multiple
              :options="availableAffiliateGroupOptions"
              label="title"
            />
          </BaseFormGroup>
          <BaseFormGroup
            label="Commission Levels"
            required
          >
            <template #label-row-end>
              <BaseButton
                :id="`add-condition-${cgIdx}`"
                has-add-icon
                size="sm"
                @click="showAddLevelModal(cgIdx)"
              >
                Add Commission Level
              </BaseButton>
            </template>
          </BaseFormGroup>
          <BaseFormGroup>
            <BaseDatatable
              v-if="cg?.levels?.length"
              no-header
              no-edit-action
              no-delete-action
              title="commission level"
              :table-headers="tableHeaders"
              :table-datas="cg.levels"
              :sort-by-desc="false"
            >
              <template #cell-title="{ index, row: { level } }">
                level {{ level || cg.levels.length - index }}
              </template>
              <template #cell-commission_type="{ row: { commission_type } }">
                <span class="text-capitalize">
                  {{ commission_type }}
                </span>
              </template>
              <template #cell-commission_rate="{ row: item }">
                <span v-if="item.commission_type === 'fixed'">
                  {{ defaultCurrency }} &nbsp;
                </span>
                <span>
                  {{ item.commission_rate }}
                </span>
                <span v-if="item.commission_type === 'percentage'"> % </span>
              </template>
              <template #action-options="{ row: item, index }">
                <BaseDropdownOption
                  text="Edit"
                  @click="
                    showUpdateLevelModal(
                      item,
                      cgIdx,
                      cg.levels.length - 1 - index
                    )
                  "
                />
                <BaseDropdownOption
                  text="Delete"
                  @click="deleteLevel(item.id)"
                />
              </template>
            </BaseDatatable>
          </BaseFormGroup>
          <div class="separator mb-10" />
        </template>
      </BaseCard>
    </template>
    <template #right>
      <BaseCard
        has-header
        title="Reminder"
      >
        <BaseFormGroup
          label="Please save your work to prevent any accidental information loss"
        />
      </BaseCard>
    </template>
    <template #footer>
      <BaseButton
        type="link"
        class="me-5"
        href="/affiliate/members/campaigns"
      >
        Cancel
      </BaseButton>
      <BaseButton
        id="save-campaign-btn"
        :disabled="savingCampaign"
        @click="saveCampaign"
      >
        Save
      </BaseButton>
    </template>
  </BasePageLayout>
  <CommissionLevelModal
    :modal-id="addLevelModalId"
    @edit-level="handleAddMemberLevel"
  />
  <CommissionLevelModal
    :modal-id="updateLevelModalId"
    :level="selectedLevel"
    @edit-level="handleUpdateMemberLevel"
  />
</template>

<script>
/* eslint-disable camelcase */
/* eslint-disable no-underscore-dangle */
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { Modal } from 'bootstrap';
import shortid from 'shortid';
import CommissionLevelModal from '@affiliate/components/CommissionLevelModal.vue';

export default {
  components: {
    CommissionLevelModal,
  },
  props: {
    domains: {
      type: Array,
      default: () => [],
    },
    campaign: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    defaultCurrency: {
      type: String,
      default: '$',
    },
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      title: '',
      domain: '',
      savingCampaign: false,

      addLevelModalId: 'add-level-modal',
      updateLevelModalId: 'update-level-modal',

      showValidationErrors: false,

      // new v2 am
      conditionGroups: [],
      productOptions: [],
      categoryOptions: [],
      affiliateGroupOptions: [],

      selectedProductsType: 'all',
      selectedProductsOrCategories: [],
      selectedConditionGroupIdx: '',
      selectedLevelIdx: '',
      selectedLevelId: '',
      selectedLevel: null,
      tableHeaders: [
        { name: 'Title', key: 'title', custom: true },
        { name: 'Form', key: 'commission_type', custom: true },
        {
          name: `Commission`,
          key: 'commission_rate',
          custom: true,
        },
      ],
    };
  },
  validations: {
    title: {
      required,
    },
    domain: {
      required,
    },
  },
  computed: {
    isEdit() {
      return new URLSearchParams(window.location.search).has('edit');
    },
    allCampaignsPageLink() {
      return '/affiliate/members/campaigns';
    },
    availableAffiliateGroupOptions() {
      const selectedAffiliateGroupIds = this.conditionGroups.flatMap((cg) => {
        return cg.groups.map((g) => g.id);
      });

      return this.affiliateGroupOptions.filter(
        (g) => !selectedAffiliateGroupIds.includes(g.id)
      );
    },
    isAllAffiliatesOptionSelected() {
      return !!this.conditionGroups.find((cg) => cg._group_type === 'all');
    },
  },

  async mounted() {
    // set verified subdomain as default target domain
    // =======================================================
    const verifiedSubdomain = this.domains.find(
      (d) => d.is_subdomain && d.is_verified
    );

    if (verifiedSubdomain) {
      this.domain = verifiedSubdomain.domain;
    }

    try {
      const res = await this.$axios.get(
        '/affiliate/members/conditions/types/values'
      );

      this.productOptions = res.data.products;
      this.categoryOptions = res.data.categories;
      this.affiliateGroupOptions = res.data.groups;
    } catch (err) {
      this.$toast.error('Error', 'Failed to load products and categories');
    }
    // =======================================================

    // initialized saved campaign. Leave them at the bottommost of mounted
    if (!Object.keys(this.campaign).length) return;

    this.title = this.campaign.title;
    this.domain = this.campaign?.domain?.domain;

    // _group_type doesn't store in db, so have to initialize separately here
    this.conditionGroups = this.campaign.condition_groups?.map((cg) => ({
      ...cg,
      _group_type: cg.groups.length === 0 ? 'all' : 'specific',
    }));

    // initialize saved product/cat
    if (this.campaign.products.length !== 0) {
      this.selectedProductsType = 'product';
      this.selectedProductsOrCategories = this.campaign.products;
    } else if (this.campaign.categories.length !== 0) {
      this.selectedProductsType = 'category';
      this.selectedProductsOrCategories = this.campaign.categories;
    } else {
      this.selectedProductsType = 'all';
    }
  },
  methods: {
    deleteConditionGroup(idx) {
      if (this.conditionGroups.length === 1) {
        this.$toast.warning(
          'Warning',
          'A minimum of ONE condition must present'
        );
        return;
      }

      this.conditionGroups = this.conditionGroups.filter((_, i) => i !== idx);
    },
    resetSelectedProductsOrCategories() {
      this.selectedProductsOrCategories = [];
    },

    showAddLevelModal(conditionGroupIdx) {
      this.selectedConditionGroupIdx = conditionGroupIdx;
      new Modal(document.getElementById(this.addLevelModalId)).show();
    },
    deleteLevel(id = '') {
      if (id === '') {
        this.$toast.error('Error', 'Something wrong. Please contact support');
        return;
      }

      this.conditionGroups = this.conditionGroups.map((cg) => {
        return {
          ...cg,
          levels: cg.levels.filter((l) => l.id !== id),
        };
      });
      this.selectedLevelId = '';
    },
    clearValidationError() {
      this.showValidationErrors = false;
    },
    handleAddMemberLevel({ commission_rate, commission_type }) {
      const cgIdx = this.selectedConditionGroupIdx;

      if (cgIdx === '') {
        console.error('selectedConditionGroupIdx is empty');
        this.$toast.error('Error', 'Unable to add level');
        return;
      }

      const originalGroup = this.conditionGroups[cgIdx];
      const levels = originalGroup.levels ?? [];
      const tid = (levels[levels.length - 1]?.id || 0) + 1;
      originalGroup.levels = [
        ...originalGroup.levels,
        {
          id: tid + Math.floor(1111111 + Math.random() * 9999999),
          commission_rate,
          commission_type,
        },
      ];
    },
    showUpdateLevelModal(level, conditionGroupIdx, levelIdx) {
      this.selectedLevel = level;
      this.selectedConditionGroupIdx = conditionGroupIdx;
      this.selectedLevelIdx = levelIdx;
      new Modal(document.getElementById(this.updateLevelModalId)).show();
    },

    handleUpdateMemberLevel({ commission_rate, commission_type }) {
      const cgIdx = this.selectedConditionGroupIdx;
      const lIdx = this.selectedLevelIdx;

      if (cgIdx === '' || lIdx === '') {
        console.error('selectedConditionGroupIdx or selectedLevelIdx is empty');
        this.$toast.error('Error', 'Unable to update level');
        return;
      }

      const originalLevels = this.conditionGroups[cgIdx].levels;

      originalLevels[lIdx] = {
        ...originalLevels[lIdx],
        commission_rate,
        commission_type,
      };
    },

    addNewConditionGroup() {
      this.conditionGroups = [
        {
          id: shortid.generate(),
          _group_type: 'all', // just used in frontend, not save to db
          groups: [], // for affiliate member
          levels: [],
        },
        ...this.conditionGroups,
      ];
    },

    clearAffiliateGroups(cgIdx) {
      this.conditionGroups[cgIdx].groups = [];
    },

    async saveCampaign() {
      this.showValidationErrors = this.v$.$invalid;

      if (this.showValidationErrors) {
        return;
      }

      this.savingCampaign = true;
      try {
        await this.$axios({
          method: this.isEdit ? 'put' : 'post',
          url: this.isEdit
            ? `/affiliate/members/campaigns/${this.campaign.reference_key}`
            : '/affiliate/members/campaigns',
          data: {
            title: this.title,
            domain: this.domain,
            type: this.selectedProductsType,
            productsOrCategories: this.selectedProductsOrCategories,
            conditionGroups: this.conditionGroups,
          },
        });
        this.$toast.success('Success', 'Campaign saved successfully.');
        this.$inertia.visit(this.allCampaignsPageLink);
      } catch (err) {
        const { data } = err.response;
        const { errors } = data;
        const [errMessage] = Object.values(errors);
        if (!data.message.includes('exceed_limit')) {
          if (errMessage[0].includes('level'))
            this.$toast.warning(
              'Warning',
              `Commission level is required in all condition groups.`
            );
          else this.$toast.warning('Warning', `${errMessage[0]}`);
        }
      } finally {
        this.savingCampaign = false;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.general-card-section {
  margin-bottom: 0rem !important;
}

.general-card__title {
  font-size: 16px;
  margin: 0px 0px 12px;
  color: #202930;
  font-weight: 700;
}

.aff_input {
  height: 36px;
  border-radius: 2.5px;
}

.section-title {
  font-size: 16px;
  margin: 0px 0px 4px;
  color: #202930;
  font-weight: 700;
}

.footer-container {
  padding: 20px 0;
  margin: 0 auto;
  border-top: 0.1rem solid var(--p-border-subdued, #dfe3e8);
  display: flex;
  justify-content: flex-end;

  @media (max-width: $sm-display) {
    flex-direction: column-reverse;
    [class^='primary'] {
      width: 100% !important;
    }

    [class*='white'] {
      margin-bottom: 10px;
    }

    .cancel-button {
      margin: 10px auto !important;
      padding: 0 !important;
      width: fit-content;
    }
  }
}

input[type='checkbox']:enabled.black-checkbox {
  z-index: 1;
  top: 7px;
}

tr {
  th {
    border-bottom: 1px solid #ced4da !important;
  }

  td:last-child,
  th:last-child {
    text-align: center;
  }

  td:first-child,
  th:first-child {
    padding-left: 25px;
  }

  td:not(:first-child),
  th:not(:first-child) {
    padding-left: 0px;
  }
}
</style>

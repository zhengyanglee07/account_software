<template>
  <BasePageLayout
    page-name="Legal Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseSettingLayout title="Legal Pages and Policies">
      <template #description>
        <p>
          Each legal policy is associated with a dedicated page. Once a policy
          is filled, new navigation linked to the legal page will be added to
          the footer of checkout pages in Online Store and Mini Store.
        </p>
        <p>
          To add new navigation menu items of legal pages in online store, you
          can select the added policies under the Policies category when adding
          or editing a menu.
        </p>
      </template>
      <template #content>
        <div
          v-for="(type, index) in Object.keys(legalSetting)"
          :key="index"
        >
          <h3 class="my-3">
            {{ legalName[type] }}
          </h3>
          <ProductDescriptionEditor
            :ref="
              (el) => {
                editor[type] = el;
              }
            "
            :editor-id="type + '-template'"
            :data="legalSetting[type]"
            :property="'template'"
            class="general-card-section w-100"
            @click="currentPolicy = type"
            @input="legalSetting[type].template = value"
          />
          <div class="text-end my-5">
            <BaseButton
              type="secondary"
              class="me-3"
              @click="useOurTemplate(type)"
            >
              Use our template
            </BaseButton>
            <BaseButton @click="saveLegalPolicy(type)">
              Save
            </BaseButton>
          </div>
        </div>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
  <ImageUploader
    type="description"
    @update-value="chooseImage"
  />
</template>

<script setup>
import ProductDescriptionEditor from '@product/components/ProductDescriptionEditor.vue';
import ImageUploader from '@shared/components/ImageUploader.vue';
import { onMounted, ref, inject } from 'vue';
import legalAPI from '@setting/api/legalAPI.js';

const $toast = inject('$toast');

const props = defineProps({
  legalPolicy: { type: Array, default: () => [] },
  legalPolicyType: { type: Array, default: () => [] },
  account: { type: Object, default: null },
});

const currentPolicy = ref('privacy-policy');
const legalSetting = ref({
  'privacy-policy': { template: '', type: 'privacy-policy' },
  'terms-and-conditions': { template: '', type: 'terms-and-conditions' },
  'refund-policy': { template: '', type: 'refund-policy' },
});

const legalName = {
  'privacy-policy': 'Privacy Policy',
  'terms-and-conditions': 'Terms and Conditions',
  'refund-policy': 'Refund Policy',
};

const editor = ref([]);

const enableLegalPolicy = (item) => {
  legalAPI.update(item);
};

const replaceAll = (str, mapObj) => {
  const reg = new RegExp(Object.keys(mapObj).join('|'), 'gi');
  return str.replace(reg, function (matched) {
    return mapObj[matched];
  });
};

const useOurTemplate = (type) => {
  const elem = document.getElementById(`${type}-template`);
  const legalType = props.legalPolicyType.find((e) => e.type === type);
  const { account } = props;
  const { template } = legalType;
  let mapObj = {
    '{Brand Name}': account.store_name,
    '{example@mycompany.com}': account.sender?.email_address,
    '{Your Business Mailing Address}': account.sender?.email_address,
    '{your website URL}': account.domain,
    '{Registered Business Name}': account.company,
    '{service areas e.g. Klang Valley}': account.city,
  };
  mapObj = Object.keys(mapObj)
    .filter((key) => mapObj[key])
    .reduce((newObj, key) => Object.assign(newObj, { [key]: mapObj[key] }), {});
  elem.firstChild.innerHTML = template;

  document.querySelectorAll('span').forEach((span) => {
    const spanElem = span;
    if (Object.keys(mapObj).includes(spanElem.textContent)) {
      spanElem.style = 'background-color: transparent; color: rgb(0, 0, 0);';
    }
  });

  elem.firstChild.innerHTML = replaceAll(elem.firstChild.innerHTML, mapObj);
  legalSetting.value[type].template = elem.firstChild.innerHTML;
};

const saveLegalPolicy = (type) => {
  legalAPI
    .update(legalSetting.value[type])
    .then((res) =>
      $toast.success('Success', `Successfully to save ${legalName[type]}`)
    );
};

const chooseImage = (e) => {
  editor.value[currentPolicy.value]?.chooseImage(e);
};

onMounted(() => {
  props.legalPolicy.forEach((item) => {
    legalSetting.value[item.legal_policy_type.type].template = item.template;
  });
});
</script>

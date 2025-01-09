<template>
  <BasePageLayout
    page-name="Facebook Pixel & Conversion API"
    back-to="/apps/all"
    is-setting
  >
    <BaseSettingLayout title="Facebook Pixel Setup">
      <template #description>
        Set up your pixel ID and access token here to allow us to send web
        events that your visitors performed in your published sites or store
        from our servers to Facebook
        <br><br>
        Read more about Facebook Conversion API
        <a
          href="https://www.facebook.com/business/help/2041148702652965"
          target="__blank"
        >
          here
        </a>
        <br><br>
        To get pixel ID, you first need to create a Facebook Pixel by following
        the guide
        <BaseButton
          type="link"
          href="https://www.facebook.com/business/help/952192354843755"
          is-open-in-new-tab
        >
          here
        </BaseButton>
        or you can use an existing one. Next, go to the
        <BaseButton
          type="link"
          href="https://www.facebook.com/business/help/952192354843755"
          is-open-in-new-tab
        >
          all pixel page
        </BaseButton>
        , select a pixel you want to use, and the numbers below the
        <strong>Pixel</strong>
        at the top right will be your pixel ID.
        <br><br>
        Continue from the steps above, you can get your access token by clicking
        on the Settings tab, scroll to the
        <strong>Conversions API</strong> section, and clicks on the link of
        <span class="text-primary">Generate access token</span>.
      </template>
      <template #content>
        <BaseFormGroup
          label="Pixel ID"
          :error-message="pixelIDErr ? 'Pixel ID is required' : ''"
        >
          <BaseFormInput
            id="facebook-pixel-id"
            v-model="pixelID"
            type="number"
            @input="pixelIDErr = false"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Access Token"
          :error-message="accessTokenErr ? 'Access Token is required' : ''"
        >
          <BaseFormTextarea
            id="facebook-acccess-token"
            v-model="accessToken"
            rows="3"
            @input="accessTokenErr = false"
          />
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton @click="save">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script>
export default {
  props: {
    // delyvaInfo: Object,
    account: Object,
    facebookPixelInfo: Object,
    onboarding: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      pixelID: '',
      accessToken: '',

      pixelIDErr: false,
      accessTokenErr: false,
    };
  },

  mounted() {
    if (this.facebookPixelInfo) {
      this.pixelID = this.facebookPixelInfo.pixel_id ?? '';
      this.accessToken = this.facebookPixelInfo.api_token ?? '';
    }
  },

  methods: {
    validation() {
      if (this.pixelID === '') this.pixelIDErr = true;
      if (this.accessToken.trim() === '') this.accessTokenErr = true;

      return this.pixelIDErr || this.accessTokenErr;
    },

    save() {
      if (this.validation()) {
        this.$toast.error('Error', 'Check the required fields!');
        return;
      }

      // if (this.onboarding) {
      //   localStorage.setItem(
      //     'shippingSettings',
      //     JSON.stringify({
      //       link: 'delyva',
      //       companyCode: this.companyCode.trim(),
      //       companyID: this.companyID.trim(),
      //       userID: this.userID.trim(),
      //       customerID: this.customerID.trim(),
      //       apiKey: this.apiKey.trim(),
      //       selected: true,
      //     })
      //   );
      //   window.location.replace('/onboarding/shipping/scheduled-delivery');
      //   return;
      // }

      axios
        .post(`/facebook/setting`, {
          pixelID: this.pixelID,
          accessToken: this.accessToken.trim(),
        })
        .then((response) => {
          this.$toast.success('Success', 'Successfully Saved');
          window.location.href = `/apps/all`;
        })
        .catch((error) => {
          console.log(error);
          this.$toast.error('Error', 'Unexpected Error Occured');
        });
    },
  },
};
</script>

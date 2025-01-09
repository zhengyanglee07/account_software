<template>
  <EcommerceAccountLayout>
    <template #content>
      <BaseSettingLayout title="Edit Profile">
        <template #description>
          <p>View and edit your personal information here.</p>
        </template>
        <template #content>
          <EditProfileSection
            :email="ecommerceUser?.email"
            :name="ecommerceUser?.full_name"
            :phone-number="ecommerceUser?.phone_number"
          />
        </template>
      </BaseSettingLayout>
      <BaseSettingLayout title="Reset Password">
        <template #description>
          <p>Reset your account's password here.</p>
        </template>
        <template #content>
          <ResetPasswordSection />
        </template>
      </BaseSettingLayout>
    </template>
  </EcommerceAccountLayout>
</template>

<script>
import EcommerceAccountLayout from '@customerAccount/layout/EcommerceAccountLayout.vue';
import EditProfileSection from '@customerAccount/components/EditProfileSection.vue';
import ResetPasswordSection from '@customerAccount/components/ResetPasswordSection.vue';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  name: 'Profile',
  components: {
    EcommerceAccountLayout,
    EditProfileSection,
    ResetPasswordSection,
  },
  layout: PublishLayout,
  props: {
    ecommerceUser: Object,
    pageName: String,
  },
};
</script>

<style lang="scss" scoped></style>

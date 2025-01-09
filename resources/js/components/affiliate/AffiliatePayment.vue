<template>
  <div>
    <div class="col-lg-5 col-xl-4 col-md-4 affiliate-individual-card mb-3">
      <!-- <div class="paypal-container">
        <FormulateForm
          v-model="formValues"
          @submit="saveDetail"
          @keyup.enter="saveDetail"
        >
          <FormulateInput
            type="email"
            label="Paypal Email"
            placeholder="Paypal email"
            name="paypal_email"
            validation-name="Paypal email"
            validation="bail|required|email"
            error-behaivor="submit"
            keep-model-data

          />
          <FormulateInput
            type="submit"
            input-class="primary-square-button"
            outer-class="submit-button"
          >
            Save
          </FormulateInput>
        </FormulateForm>
      </div>-->
    </div>
    <commission-card />
    <!-- <commission-withdraw /> -->
	</div>
  </div>
</template>

<script>
import commissionCard from '@components/affiliate/component/CommissionCard';
import commissionWithdraw from '@components/affiliate/component/CommissionWithdraw';
import { Modal } from 'bootstrap';

export default {
  name: 'affiliate-payment',
  props: ['affiliateUser'],
  components: { commissionCard, commissionWithdraw },
  data() {
    return {
      formValues: {
        first_name: this.affiliateUser.first_name,
        last_name: this.affiliateUser.last_name,
        address: this.affiliateUser.address,
        city: this.affiliateUser.city,
        zipcode: this.affiliateUser.zipcode,
        state: this.affiliateUser.state,
        country: this.affiliateUser.country,
        paypal_email: this.affiliateUser.paypal_email,
      },
    };
  },

  methods: {
    saveDetail() {
      console.log('detail save');
      this.edit_profile_modal = new Modal(document.getElementById('edit-profile-modal'));
      this.edit_profile_modal.show();

      axios
        .post('/update-profile', {
          formValues: this.formValues,
          affiliate_user: this.affiliate_user,
        })
        .then((response) => {
          // console.log(response);

          this.$toast.success('Success', 'Profile update successfully');
          setTimeout(function () {
            window.location.reload();
          }, 600);
        })
        .catch((error) => {
          // console.log(error);
          this.$toast.error('Error', 'Opps Something went wrong');
        });
    },

    checkDetail() {
      if (
        this.affiliate_user.paypal_email == null ||
        this.affiliate_user.paypal_email == ''
      ) {
        // console.log("abc")
        Swal.fire({
          title: 'Info Required',
          text: 'Please update your profile detail',
          type: 'warning',
        }).then((response) => {
          this.edit_profile_modal = new Modal(document.getElementById('edit-profile-modal'));
          this.edit_profile_modal.show();
        });
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.paypal-container{
    padding: 0 5px;
}
.paypal-email {
  text-align: center;
  border: 1px solid lightgrey;
  border-radius: 1rem;
  padding: 0.5rem;
  pointer-events: none;
}

.affiliate-name {
  font-weight: bold;
  font-size: 1.5rem;
}

.paypal-logo {
  max-height: 45px;
  max-width: 125px;
  width: 100%;
}

.profile-img {
  max-width: 150px;
  max-height: 150px;
  width: 100%;
  padding-right: 10px;
}
</style>

<template>
  <div class="onboarding">
    <div class="w-100">
      <div class="row">
        <div class="onboarding-title customer-account-h-two">
          <div>Log In</div>
        </div>
      </div>

      <div class="row d-flex justify-content-center">
        <div class="onboarding-container">
          <div class="flash-message w-100">
            <!-- @foreach(['danger','warning','success','info'] as $msg)
              @if(Session::has('alert-'.$msg))
              <p class="alert alert-{{$msg}} error-font-size">{{Session::get('alert-'.$msg)}}
                <a href="#" class="close"
                      data-dismiss="alert" aria-label="close">&times</a></p>
              @endif
              @endforeach -->
          </div>

          <form @submit.prevent="login">
            <div
              v-if="status"
              class="alert alert-success"
              role="alert"
              style="font-size: 14px"
            >
              {{ status }}
            </div>
            <BaseFormGroup
              label="Email"
              label-for="exampleInputEmail1"
            >
              <BaseFormInput
                id="exampleInputEmail1"
                v-model="form.email"
                name="email"
                type="email"
                placeholder="Enter email"
                required
              />
              <template
                v-if="errors.email"
                #error-message
              >
                {{ errors.email }}
              </template>
            </BaseFormGroup>
            <BaseFormGroup
              label="Password"
              label-for="exampleInputPassword1"
            >
              <BaseFormInput
                id="exampleInputPassword1"
                v-model="form.password"
                name="password"
                :type="isPasswordTypePassword ? 'password' : 'text'"
                placeholder="Password"
                required
              >
                <template #append>
                  <BaseButton
                    size="sm"
                    type=""
                    @click="isPasswordTypePassword = !isPasswordTypePassword"
                  >
                    <i
                      :class="`fa fa-${
                        isPasswordTypePassword ? 'eye' : 'eye-slash'
                      }`"
                    />
                  </BaseButton>
                </template>
              </BaseFormInput>
            </BaseFormGroup>

            <BaseFormGroup>
              <BaseFormCheckBox
                id="exampleCheck1"
                v-model="form.remember"
                name="remember"
              >
                Keep me logged in
              </BaseFormCheckBox>
            </BaseFormGroup>
            <div class="text-center">
              <BaseButton
                class="mb-5"
                type="theme-primary"
                is-submit
              >
                Log In
              </BaseButton>
              <p class="mb-5 text-center">
                Need a customer account?&nbsp;&nbsp;<Link href="/signup">
                  Create an Account
                </Link>
              </p>
              <Link
                href="/forgot"
                class="d-flex w-100 flex-center customer-account-p-three"
              >
                Forgot Password?
              </Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useStore } from 'vuex';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  name: 'Login',
  layout: PublishLayout,
  props: {
    status: String,
  },
  setup() {
    const store = useStore();

    const form = useForm({
      email: '',
      password: '',
      remember: false,
    });

    const isPasswordTypePassword = ref(true);

    const errors = computed(() => usePage().props.errors);

    const login = () => {
      form.post('/login', {
        onFinish: () => form.reset('password'),
        onSuccess: (data) => {
          store.commit(
            'customerAccount/loginCustomerInfo',
            data.props.customerInfo,
            {
              root: true,
            }
          );
        },
      });
    };

    // const login = async () => {
    //   await axios
    //     .post(`/login/${window.location.host}`, {
    //       email: email.value,
    //       password: password.value,
    //     })
    //     .then(({ data }) => {
    //       console.log(data);
    //       localStorage.setItem('isEccomerceUserLoggedIn', 'true');
    //       // if (data.success === 'login_success')
    //       // router.push('/dashboard');
    //     })
    //     .catch(({ response }) => {
    //       console.log('islogin success?', response.data.errors);
    //       errorMessage.value = response.data.errors;
    //     })
    //     .finally(() => {
    //       password.value = '';
    //     });
    // };

    // if (localStorage.getItem('isEccomerceUserLoggedIn'))
    // router.push('/dashboard');

    // const domainNameforApi = window.location.host;
    // axios
    //   .get(`/login/${domainNameforApi}`)
    //   .then(({ data }) => {
    //     pageName.value = data.pageName;
    //     companyLogo.value = data.companyLogo;
    //   })
    //   .catch((error) => {
    //     console.log(error);
    //   });

    // To authenticate SPA
    // const domainName = 'http://hypershapes.test';
    // const domainName = 'http://localhost';

    // axios({
    //   method: 'get',
    //   url: '/sanctum/csrf-cookie',
    //   baseURL: `${domainName}`,
    // })
    //   .then((response) => {
    //     console.log({ response });
    //   })
    //   .catch((error) => {
    //     console.log(error);
    //   });

    return {
      form,
      login,
      isPasswordTypePassword,
      errors,
    };
  },
};
</script>
<style scoped lang="scss">
:deep(.input-group-text) {
  padding: 0px;
}

.onboarding-container {
  background-color: transparent;
  box-shadow: none;
  margin: 0px;
}

.onboarding {
  align-items: flex-start;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
  overflow: auto;
  display: flex;
  justify-content: center;

  &-title {
    font-size: 28px;
    text-align: center;
    width: 100%;
  }

  &-container {
    width: 480px;
    background-color: white;
    box-shadow: 0 0.75rem 1.5rem rgb(18 38 63 / 3%);
    border-radius: 0.25rem;
    padding: 1.25rem !important;
    margin: 10px 0;
    position: relative;
    /* min-height: 500px; */
  }
}

@media (min-width: 768px) {
  .onboarding-container {
    background-color: white;
    box-shadow: 0px 1px 1px 0px rgb(0 0 0 / 20%);
    border: 1px solid lightgray;
    padding: 30px !important;
    margin: 10px 0;
    min-width: 50%;
  }
}

.company-logo {
  padding-left: 0;
  padding-right: 0;
  margin-bottom: 36px;
}

.input-section {
  margin-top: 20px;
  margin-bottom: 0px;
}

@media (min-width: 768px) {
  .input-section {
    margin-bottom: 20px !important;
  }
}

.form-check {
  margin-top: 20px;
  min-height: 0.5rem !important;
}

.login-button {
  margin: 20px 0 0 0;
}

.container-footer {
  margin-top: 0px;
}

@media (min-width: 768px) {
  .logo-responsive-size {
    height: 56px !important;
    width: auto !important;
  }
}

.form-check {
  padding-left: 0;
}

.welcome-statement {
  font-size: 30px !important;
  font-weight: 900 !important;
  font-family: 'Inter', sans-serif !important;
}

/* typo */
.customer-account-h-two {
  font-size: 25px !important;
  font-weight: 900 !important;
  font-family: 'Inter', sans-serif !important;
}
.customer-account-h-five {
  font-size: 14px !important;
  font-weight: 700 !important;
  font-family: 'Roboto', sans-serif !important;
}
</style>

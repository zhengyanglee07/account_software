<template>
    <div class="kt-login__signup">
        <div class="kt-login__head">
            <h3 class="kt-login__title h-two" style="color: black">Sign Up</h3>
        </div>
        <div class="kt-login__form" style="margin-top:3rem">
    <!-- action="{{route('affiliate.register.submit')}}" -->
            <form @submit.prevent="signUp" class="kt-form" >
            <div class="form-wrapper h-100">
                <div class="form-group form-inline">
                    <div class="vertical-grp">
                        <label>First Name</label>
                        <input class="form-control me-3" type="text" placeholder="First Name" v-model="form.firstName" name="firstName">
                    </div>

                    <div class="vertical-grp">
                        <label>Last Name</label>
                        <input class="form-control" type="text" placeholder="Last Name" v-model="form.lastName" name="lastName" required>
                    </div>
                    <span class="error-message" v-if="hasError('lastName')" v-text="errors['lastName'][0]"></span>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control" type="email"  placeholder="Email" name="email" autocomplete="email" v-model="form.email" required>
                    <div style="width: 100%; margin-bottom: 12px">
                        <span class="error-message" v-if="hasError('email')" v-text="errors['email'][0]"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" type="password" placeholder="Password" name="password" v-model="form.password">
                    <div style="width: 100%; margin-bottom: 12px">
                        <span class="error-message" v-if="hasError('password')" v-text="errors['password'][0]"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control " type="password" placeholder="Confirm Password" name="cpassword" v-model="form.cpassword">

                    <div style="width: 100%; margin-bottom: 12px">
                        <span class="error-message" v-if="hasError('cpassword')" v-text="errors['cpassword'][0]"></span>
                    </div>

                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input class="form-control" type="text"  value="" placeholder="Address" name="address" v-model="form.address">
                </div>
                <div class="form-group form-inline">
                    <div class="vertical-grp">
                        <label>City</label>
                        <input class="form-control me-3" type="text" placeholder="City"  value="" name="city" v-model="form.city">
                    </div>
                    <div class="vertical-grp">
                        <label>Zip</label>
                        <input class="form-control" type="text" placeholder="Zip" value="" name="zip" v-model="form.zip">
                    </div>
                </div>
                <div class="form-group form-inline mt-3">
                    <div class="vertical-grp">
                        <label>State</label>
                        <region-select
                            class="form-control w-100"
                            v-model="form.state"
                            :country="form.country"
                            :region="form.state"
                            :regionName="true"
                            :countryName="true"
                            required
                        />
                    </div>
                    <div class="vertical-grp">
                        <label>Country</label>
                        <country-select
                            class="form-control w-100"
                            v-model="form.country"
                            :country="form.country"
                            topCountry="MY"
                            :countryName="true"
                        />
                    </div>
                </div>
            </div>
            <div class="kt-login__actions">
                <button type="submit" class="primary-square-button" :disabled='loading'>
                    <span v-show="loading">
                        <i class="fas fa-circle-notch fa-spin pr-0"></i>
                    </span>
                    <span v-show="!loading">
                        Sign Up
                    </span>
                </button>&nbsp;&nbsp;

            </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name:'signup-form',
    data(){
        return{
            form:{
                firstName:'',
                lastName:'',
                email:'',
                password:'',
                cpassword:'',
                address:'',
                city:'',
                zip:'',
                country:'Malaysia',
                state:'',
            },
            errors:{},
            loading:false,
        }
    },
    methods:{
        signUp(){
            this.loading = true;
            console.log('submit')
            axios.post('register',{
                details : this.form
            })
            .then(response =>{
                console.log(response);
                this.$toast.success('Success','Register successful')
                window.location.href="/login";
            }).catch(error =>{
                console.log(error);
                this.errors = error.response.data.errors;
                this.form.password = '';
                this.form.cpassword = '';
                this.$toast.error('Error','Something went wrong.');
            }).finally(()=>{
                this.loading = false;
            })
        },
        hasError(name){
            return this.errors.hasOwnProperty(name);
        },

    }


}
</script>

<style lang="scss" scoped>
    .kt-login.kt-login--v5 .kt-login__right .kt-login__wrapper .kt-login__form .form-control{
        padding: 0.65rem 1rem;
        border-radius: 2px;
        border: 1px solid grey !important;
        height: 40px;

    }

    .form-group{
        margin-bottom: 10px !important;
        justify-content: space-between;
    }

    .vertical-grp{
        display: flex;
        flex-direction: column;
        width: 48%;
        align-items: flex-start;

        input{
            width: 100%;
        }
    }
</style>

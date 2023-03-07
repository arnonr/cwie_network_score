<template>
  <div class="auth-wrapper auth-v1 px-2">
    <div class="auth-inner py-2">
      <!-- Login v1 -->
      <b-card class="mb-0">
        <b-link class="brand-logo mb-1" >
          <b-img
            fluid
            :src="logoImg"
            alt="Login V2"
            style="width: 80%; margin-right: 10px"
          />
        </b-link>

        <b-card-title
          class="mb-1 text-center"
          style="font-size: 1rem; color: #888;font-weight: 400;"
        >
          Sign-in to your account
        </b-card-title>

        <!-- form -->
        <validation-observer ref="loginForm" #default="{ invalid }">
          <b-form class="auth-login-form mt-2" @submit.prevent="login">
            <!-- email -->
            <b-form-group label="ICITaccount" label-for="login-username">
              <validation-provider
                #default="{ errors }"
                name="username"
                vid="username"
                rules="required"
              >
                <b-form-input
                  id="login-username"
                  v-model="username"
                  :state="errors.length > 0 ? false : null"
                  name="login-username"
                  placeholder="ICITaccount"
                  autofocus
                />
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>

            <!-- password -->
            <b-form-group>
              <div class="d-flex justify-content-between">
                <label for="password">Password</label>
                <b-link
                  href="https://account.kmutnb.ac.th/web/recovery/index"
                  target="_blank"
                >
                  <small>Forgot Password?</small>
                </b-link>
              </div>
              <validation-provider
                #default="{ errors }"
                name="Password"
                rules="required"
              >
                <b-input-group
                  class="input-group-merge"
                  :class="errors.length > 0 ? 'is-invalid' : null"
                >
                  <b-form-input
                    id="password"
                    v-model="password"
                    :type="passwordFieldType"
                    class="form-control-merge"
                    :state="errors.length > 0 ? false : null"
                    name="login-password"
                    placeholder="Password"
                  />

                  <b-input-group-append is-text>
                    <feather-icon
                      class="cursor-pointer"
                      :icon="passwordToggleIcon"
                      @click="togglePasswordVisibility"
                    />
                  </b-input-group-append>
                </b-input-group>
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>

            <!-- checkbox -->
            <b-form-group>
              <b-form-checkbox
                id="remember-me"
                v-model="status"
                name="checkbox-1"
              >
                Remember Me
              </b-form-checkbox>
            </b-form-group>

            <!-- submit button -->
            <b-button variant="primary" type="submit" block :disabled="invalid" class="mt-2">
              Sign in
            </b-button>
          </b-form>
        </validation-observer>
      </b-card>
      <!-- /Login v1 -->
    </div>
  </div>
</template>

<script>
import { ValidationProvider, ValidationObserver } from "vee-validate";
import {
  BButton,
  BForm,
  BFormInput,
  BFormGroup,
  BCard,
  BLink,
  BCardTitle,
  BCardText,
  BInputGroup,
  BInputGroupAppend,
  BFormCheckbox,
  BImg,
} from "bootstrap-vue";
import useJwt from "@/auth/jwt/useJwt";
import { required, email } from "@validations";
import { togglePasswordVisibility } from "@core/mixins/ui/forms";
import store from "@/store/index";
import { getHomeRouteForLoggedInUser } from "@/auth/utils";

import ToastificationContent from "@core/components/toastification/ToastificationContent.vue";

export default {
  components: {
    // BSV
    BButton,
    BForm,
    BFormInput,
    BFormGroup,
    BCard,
    BCardTitle,
    BLink,
    BCardText,
    BInputGroup,
    BInputGroupAppend,
    BFormCheckbox,
    ValidationProvider,
    ValidationObserver,
    BImg,
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      status: "",
      password: "",
      username: "",
      sideImg: require("@/assets/images/pages/login-v2.svg"),
      logoImg: require("@/assets/images/logo/logo_login_1.jpeg"),
      // validation rules
      required,
      email,
    };
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === "password" ? "EyeIcon" : "EyeOffIcon";
    },
    imgUrl() {
      if (store.state.appConfig.layout.skin === "dark") {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.sideImg = require("@/assets/images/pages/login-v2-dark.svg");
        return this.sideImg;
      }
      return this.sideImg;
    },
  },
  methods: {
    login() {
      this.$refs.loginForm.validate().then((success) => {
        if (success) {
          useJwt
            .login({
              username: this.username,
              password: this.password,
            })
            .then((response) => {
              if (response.data.message === "success") {
                const { userData } = response.data;
                console.log(userData.role);
                useJwt.setToken(response.data.accessToken);
                useJwt.setRefreshToken(response.data.refreshToken);
                localStorage.setItem("userData", JSON.stringify(userData));
                this.$ability.update(userData.ability);

                console.log(userData.role);

                // ? This is just for demo purpose. Don't think CASL is role based in this case, we used role in if condition just for ease
                this.$router
                  .replace(getHomeRouteForLoggedInUser(userData.role))
                  .then(() => {
                    this.$toast({
                      component: ToastificationContent,
                      position: "top-right",
                      props: {
                        title: `Welcome ${
                          userData.fullName || userData.username
                        }`,
                        icon: "CoffeeIcon",
                        variant: "success",
                        text: `You have successfully logged in as ${userData.role}. Now you can start to explore!`,
                      },
                    });
                  });
              } else {
                this.$toast.error(response.data.message);
              }
            })
            .catch((error) => {
              this.$refs.loginForm.setErrors(error.response.data.error);
            });
        }
      });
    },
  },
};
</script>

<style lang="scss">
@import "~@resources/scss/vue/pages/page-auth.scss";
</style>

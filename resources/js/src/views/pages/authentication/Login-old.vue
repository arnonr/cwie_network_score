<template>
    <div class="auth-wrapper auth-v2">
      <b-row class="auth-inner m-0">
        <!-- Brand logo-->
        <b-link class="brand-logo">
          <!-- <vuexy-logo /> -->
          <h2 class="brand-text text-primary ml-1">
            <b-img
              fluid
              :src="logoImg"
              alt="Login V2"
              style="width: 50px; margin-right: 10px"
            />
            SERVICE
          </h2>
        </b-link>
        <!-- /Brand logo-->
  
        <!-- Left Text-->
        <b-col lg="8" class="d-none d-lg-flex align-items-center p-5">
          <div
            class="w-100 d-lg-flex align-items-center justify-content-center px-5"
          >
            <b-img fluid :src="imgUrl" alt="Login V2" />
          </div>
        </b-col>
        <!-- /Left Text-->
  
        <!-- Login-->
        <b-col lg="4" class="d-flex align-items-center auth-bg px-2 p-lg-5">
          <b-col sm="8" md="6" lg="12" class="px-xl-2 mx-auto">
            <b-card-title class="mb-1 font-weight-bold" title-tag="h2">
              SERVICE
            </b-card-title>
            <b-card-text class="mb-2">
              กรุณาเข้าใช้งานด้วย ICIT Account
            </b-card-text>
  
            <!-- <b-alert variant="primary" show>
              <div class="alert-body font-small-2">
                <p>
                  <small class="mr-50"
                    ><span class="font-weight-bold">Admin:</span> admin@demo.com |
                    admin</small
                  >
                </p>
                <p>
                  <small class="mr-50"
                    ><span class="font-weight-bold">Client:</span> client@demo.com
                    | client</small
                  >
                </p>
              </div>
              <feather-icon
                v-b-tooltip.hover.left="'This is just for ACL demo purpose'"
                icon="HelpCircleIcon"
                size="18"
                class="position-absolute"
                style="top: 10; right: 10"
              />
            </b-alert> -->
  
            <!-- form -->
            <validation-observer ref="loginForm" #default="{ invalid }">
              <b-form class="auth-login-form mt-2" @submit.prevent="login">
                <!-- email -->
                <b-form-group label="ICIT Account" label-for="login-username">
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
                      placeholder="pattamak"
                    />
                    <small class="text-danger">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
  
                <!-- forgot password -->
                <b-form-group>
                  <div class="d-flex justify-content-between">
                    <label for="login-password">Password</label>
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
                    vid="password"
                    rules="required"
                  >
                    <b-input-group
                      class="input-group-merge"
                      :class="errors.length > 0 ? 'is-invalid' : null"
                    >
                      <b-form-input
                        id="login-password"
                        v-model="password"
                        :state="errors.length > 0 ? false : null"
                        class="form-control-merge"
                        :type="passwordFieldType"
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
  
                <!-- submit buttons -->
                <b-button
                  type="submit"
                  variant="primary"
                  block
                  :disabled="invalid"
                >
                  Sign in
                </b-button>
              </b-form>
            </validation-observer>
          </b-col>
        </b-col>
        <!-- /Login-->
      </b-row>
    </div>
  </template>
  
  <script>
  /* eslint-disable global-require */
  import { ValidationProvider, ValidationObserver } from "vee-validate";
  import VuexyLogo from "@core/layouts/components/Logo.vue";
  import {
    BRow,
    BCol,
    BLink,
    BFormGroup,
    BFormInput,
    BInputGroupAppend,
    BInputGroup,
    BFormCheckbox,
    BCardText,
    BCardTitle,
    BImg,
    BForm,
    BButton,
    BAlert,
    VBTooltip,
  } from "bootstrap-vue";
  import useJwt from "@/auth/jwt/useJwt";
  import { required, email } from "@validations";
  import { togglePasswordVisibility } from "@core/mixins/ui/forms";
  import store from "@/store/index";
  import { getHomeRouteForLoggedInUser } from "@/auth/utils";
  
  import ToastificationContent from "@core/components/toastification/ToastificationContent.vue";
  
  export default {
    directives: {
      "b-tooltip": VBTooltip,
    },
    components: {
      BRow,
      BCol,
      BLink,
      BFormGroup,
      BFormInput,
      BInputGroupAppend,
      BInputGroup,
      BFormCheckbox,
      BCardText,
      BCardTitle,
      BImg,
      BForm,
      BButton,
      BAlert,
      VuexyLogo,
      ValidationProvider,
      ValidationObserver,
    },
    mixins: [togglePasswordVisibility],
    data() {
      return {
        status: "",
        password: "",
        username: "",
        sideImg: require("@/assets/images/pages/login-v2.svg"),
        logoImg: require("@/assets/images/logo/logo-sci.png"),
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
                  console.log(userData.role)
                  useJwt.setToken(response.data.accessToken);
                  useJwt.setRefreshToken(response.data.refreshToken);
                  localStorage.setItem("userData", JSON.stringify(userData));
                  this.$ability.update(userData.ability);
  
                  console.log(userData.role)
  
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
  
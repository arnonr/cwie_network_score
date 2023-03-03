<template>
  <div
    class="app-content content"
    style="background-color: #fff"
    :class="[
      { 'show-overlay': $store.state.app.shallShowOverlay },
      $route.meta.contentClass,
    ]"
  >
    <div class="content-overlay" />
    <div class="header-navbar-shadow" />

    <div class="mb-2 div-banner">
      <img :src="bannerImg" alt="" style="width: 100%" />
    </div>

    <div
      class="content-wrapper"
      :class="contentWidth === 'boxed' ? 'container p-0' : null"
    >
      <!-- <slot name="breadcrumb">
        <app-breadcrumb />
      </slot> -->
      <div class="content-body">
        <transition :name="routerTransition" mode="out-in">
          <slot />
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
import { BCard } from "bootstrap-vue";
import AppBreadcrumb from "@core/layouts/components/AppBreadcrumb.vue";
import useAppConfig from "@core/app-config/useAppConfig";

export default {
  components: {
    AppBreadcrumb,
    BCard,
  },
  data() {
    return {
      bannerImg: require("@/assets/images/banner/banner-service.jpeg"),
    };
  },
  setup() {
    const { routerTransition, contentWidth } = useAppConfig();

    return {
      routerTransition,
      contentWidth,
    };
  },
};
</script>

<style>
.div-banner {
  margin-top: -45px;
  margin-left: -2rem;
  width: calc(100vw - (100vw - 105%) - calc(1rem * 0)) !important;
}

@media only screen and (max-width: 600px) {
  .div-banner {
    margin-top: -17px;
    margin-left: -2rem;
    width: calc(100vw - (100vw - 115%) - calc(1rem * 0)) !important;
  }
}

@media only screen and (max-width: 768px) {
  .div-banner {
    margin-top: -17px;
    margin-left: -2rem;
    width: calc(100vw - (100vw - 115%) - calc(1rem * 0)) !important;
  }
}
</style>

<template>
  <div class="navbar-header d-xl-block d-none" style="left: calc(10% - 56px)">
    <ul class="nav navbar-nav">
      <li class="nav-item">
        <b-link class="navbar-brand" :to="{name: 'fix-list'}">
          <span class="brand-logo">
            <b-img :src="appLogoImage" alt="logo" />
          </span>
          <h2 class="brand-text mb-0">
            <!-- {{ appName }} -->
            เครือข่ายสถาบันการศึกษาอุดมศึกษา<br />
            <span class="sub-brand-text">ภาคกลางตอนบน</span
            >
          </h2>
        </b-link>
      </li>
    </ul>
  </div>
</template>

<script>
import { BLink, BImg } from "bootstrap-vue";
import { $themeConfig } from "@themeConfig";

export default {
  components: {
    BLink,
    BImg,
  },
  setup() {
    // App Name
    const { appName, appLogoImage } = $themeConfig.app;
    return {
      appName,
      appLogoImage,
    };
  },
};
</script>

<style></style>

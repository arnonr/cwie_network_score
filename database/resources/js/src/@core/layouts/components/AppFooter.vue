<template>
    <p class="clearfix mb-0 text-center">
      <span class="d-block d-md-inline-block mt-25">
        © {{ new Date().getFullYear()+543 }}
        <span class="d-none d-sm-inline-block">มหาวิทยาลัยเทคโนโลยีพระจอมเกล้าพระนครเหนือ</span>
      </span>
      <!-- <span class="float-md-left d-block d-md-inline-block mt-25">
        COPYRIGHT © {{ new Date().getFullYear() }}
        <span class="d-none d-sm-inline-block">, All rights Reserved1</span>
      </span>

     

     

      <span class="float-md-right d-none d-md-block"
        >Hand-crafted &amp; Made with
        <feather-icon
          icon="HeartIcon"
          size="21"
          class="text-danger stroke-current"
        />
      </span> -->
    </p>
</template>

<script>
import { BLink } from "bootstrap-vue";

export default {
  components: {
    BLink,
  },
};
</script>

<script>
import {
  BRow,
  BCol,
  BFormGroup,
  BInputGroup,
  BFormInput,
  BFormTextarea,
  BForm,
  BButton,
  BCard,
  BBadge,
  BFormFile,
  BOverlay,
  BSpinner,
} from "bootstrap-vue";
import vSelect from "vue-select";
import { ValidationProvider, ValidationObserver, validate } from "vee-validate";
import { required, email } from "@validations";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Thai } from "flatpickr/dist/l10n/th.js";
import router from "../../../router";

// Notification
import { useToast } from "vue-toastification/composition";
import ToastificationContent from "@core/components/toastification/ToastificationContent.vue";

import { onUnmounted, ref, reactive, watch } from "@vue/composition-api";
import store from "@/store";
import fixStoreModule from "../fixStoreModule";
import dayjs from "dayjs";
import { getUserData } from "@/auth/utils";

export default {
  components: {
    ValidationProvider,
    ValidationObserver,
    required,
    email,
    BRow,
    BCol,
    BFormGroup,
    BInputGroup,
    BFormInput,
    BFormTextarea,
    BForm,
    BButton,
    BCard,
    BBadge,
    vSelect,
    flatPickr,
    BFormFile,
    BOverlay,
    BSpinner,
  },
  data() {
    return {
      configDate: {
        mode: "single",
        altInput: true,
        altFormat: "d/m/Y",
        dateFormat: "Y-m-d",
        locale: Thai,
        disableMobile: "true",
      },
    };
  },
  setup() {
    const FIX_ADD_APP_STORE_MODULE_NAME = "fix-add";

    // Register module
    if (!store.hasModule(FIX_ADD_APP_STORE_MODULE_NAME))
      store.registerModule(FIX_ADD_APP_STORE_MODULE_NAME, fixStoreModule);

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(FIX_ADD_APP_STORE_MODULE_NAME))
        store.unregisterModule(FIX_ADD_APP_STORE_MODULE_NAME);
    });

    const toast = useToast();
    const simpleRules = ref();
    const overLay = ref(false);

    const item = reactive({
      id: null,
      title: "",
      detail: "",
      building_id: null,
      place: "",
      fix_img_file: null,
      fix_img_file_old: null,
      fix_img2_file: null,
      fix_img2_file_old: null,
      fix_img3_file: null,
      fix_img3_file_old: null,
      name: getUserData().fullName,
      email: getUserData().email,
      user_id: getUserData().userID,
      phone: "",
      fix_date: null,
      success_date: null,
      status: { title: "รอรับเรื่อง", code: 1 },
    });

    const selectOptions = ref({
      // hosts: [],
      fix_statuses: [
      ],
      buildings: [],
    });

    store
      .dispatch("fix-add/fetchFixStatuses")
      .then((response) => {
        const { data } = response.data;
        selectOptions.value.fix_statuses = data.map((d) => {
          return {
            code: d.id,
            title: d.name,
          };
        });
      })
      .catch((error) => {
        console.log(error);
        toast({
          component: ToastificationContent,
          props: {
            title: "Error fetching Status list",
            icon: "AlertTriangleIcon",
            variant: "danger",
          },
        });
      });

    store
      .dispatch("fix-add/fetchBuildings")
      .then((response) => {
        const { data } = response.data;
        selectOptions.value.buildings = data.map((d) => {
          return {
            code: d.id,
            title: d.name,
          };
        });
      })
      .catch((error) => {
        console.log(error);
        toast({
          component: ToastificationContent,
          props: {
            title: "Error fetching Building's list",
            icon: "AlertTriangleIcon",
            variant: "danger",
          },
        });
      });

    const validationForm = () => {
      simpleRules.value.validate().then((success) => {
        if (success) {
          onSubmit();
        }
      });
    };

    const onSubmit = (ctx, callback) => {
      overLay.value = true;
      let dataSend = {
        title: item.title,
        detail: item.detail,
        building_id: item.building_id.code,
        place: item.place,
        fix_img_file: item.fix_img_file,
        fix_img2_file: item.fix_img2_file,
        fix_img3_file: item.fix_img3_file,
        name: item.name,
        email: item.email,
        phone: item.phone,
        user_id: getUserData().userID,
        fix_date:
          item.fix_date != null ? fix_date : dayjs().format("YYYY-MM-DD"),
        status: item.status.code,
        is_publish: 1,
      };

      if (item.success_date != null) {
        dataSend.success_date = item.success_date;
      }

      store
        .dispatch("fix-add/addFix", dataSend)
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("added", 1);
            router.push({
              name: "fix-view",
              params: { id: response.data.data.id },
            });
          } else {
            toast({
              component: ToastificationContent,
              props: {
                title: response.data.message,
                icon: "AlertTriangleIcon",
                variant: "danger",
              },
            });
          }
          overLay.value = false;
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: "Add FIX Error",
              icon: "AlertTriangleIcon",
              variant: "danger",
            },
          });
          overLay.value = false;
        });
    };

    return {
      item,
      validationForm,
      simpleRules,
      selectOptions,
      overLay,
    };
  },
};
</script>

<style lang="scss">
.form-control[readonly] {
  background-color: #fff;
}
.form-control:disabled {
  background-color: #fff;
}
label {
  font-size: 1rem;
  color: #000;
}
.text-black {
  font-size: 1rem;
  color: #000;
}

@media only screen and (min-width: 1080px) {
  .b-row-width {
    width: 80%;
  }
}
</style>

<template>
  <b-card class="container-lg mt-5">
    <b-overlay
      :show="overLay"
      variant="light"
      opacity="0.3"
      blur="2px"
      rounded="sm"
      no-center
    >
      <validation-observer ref="simpleRules">
        <b-form>
          <b-row
            style="margin-right: auto; margin-left: auto"
            class="b-row-width"
          >
            <!-- with prop append -->
            <b-col cols="12" class="mt-2">
              <b-form-group
                label="หัวข้อแจ้งซ่อม"
                label-for="title"
                label-cols-md="12"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Title"
                  rules="required"
                >
                  <b-form-input
                    id="title"
                    placeholder=""
                    v-model="item.title"
                    :state="errors.length > 0 ? false : null"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <b-col cols="12">
              <b-form-group
                label="รายละเอียดอุปกรณ์/เครื่องมือ ที่เกิดการชำรุด หรือพบเสียหาย"
                label-for="detail"
                label-cols-md="12"
              >
                <validation-provider #default="{ errors }" name="Detail">
                  <b-form-input
                    id="detail"
                    placeholder=""
                    v-model="item.detail"
                    :state="errors.length > 0 ? false : null"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <b-col cols="12">
              <b-form-group
                label="สถานที่พบความชำรุดเสียหาย"
                label-for="building_id"
                label-cols-md="12"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Building"
                  rules="required"
                >
                  <v-select
                    input-id="building_id"
                    label="title"
                    v-model="item.building_id"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="selectOptions.buildings"
                    placeholder=""
                    :clearable="false"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <b-col cols="12">
              <b-form-group label="" label-for="place" label-cols-md="12">
                <validation-provider #default="{ errors }" name="Place">
                  <span class="text-black">ระบุสถานที่โดยละเอียด</span>
                  <b-form-input
                    id="place"
                    placeholder=""
                    v-model="item.place"
                    :state="errors.length > 0 ? false : null"
                    style="margin-top: 10px"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <b-col cols="12">
              <b-form-group
                label=""
                label-for="fix_img_file"
                label-cols-md="12"
              >
                <span class="text-black">รูปภาพประกอบที่ 1</span>
                <span class="text-danger font-italic"> (**ถ้ามี**)</span>
                <validation-provider name="fix_img_file" #default="{ errors }">
                  <b-form-file
                    id="fix_img_file"
                    v-model="item.fix_img_file"
                    placeholder="Choose a file or drop it here..."
                    drop-placeholder="Drop file here..."
                  />

                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <b-col cols="12">
              <b-form-group
                label=""
                label-for="fix_img2_file"
                label-cols-md="12"
              >
                <span class="text-black">รูปภาพประกอบที่ 2</span>
                <validation-provider name="fix_img2_file" #default="{ errors }">
                  <b-form-file
                    id="fix_img2_file"
                    v-model="item.fix_img2_file"
                    placeholder="Choose a file or drop it here..."
                    drop-placeholder="Drop file here..."
                  />

                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <b-col cols="12">
              <b-form-group
                label=""
                label-for="fix_img3_file"
                label-cols-md="12"
              >
                <span class="text-black">รูปภาพประกอบที่ 3</span>
                <validation-provider name="fix_img3_file" #default="{ errors }">
                  <b-form-file
                    id="fix_img3_file"
                    v-model="item.fix_img3_file"
                    placeholder="รูปภาพประกอบ 3..."
                    drop-placeholder="Drop file here..."
                  />

                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <b-col cols="12">
              <b-form-group
                label="ชื่อ-นามสกุล ผู้แจ้ง"
                label-for="name"
                label-cols-md="12"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Name"
                  rule="required"
                >
                  <b-form-input
                    id="Name"
                    placeholder=""
                    v-model="item.name"
                    :state="errors.length > 0 ? false : null"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <!--  -->
            <b-col cols="12">
              <b-form-group
                label="อีเมล ผู้แจ้ง"
                label-for="email"
                label-cols-md="12"
              >
                <validation-provider #default="{ errors }" name="Email">
                  <b-form-input
                    id="Email"
                    placeholder=""
                    v-model="item.email"
                    :state="errors.length > 0 ? false : null"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <!--  -->
            <b-col cols="12">
              <b-form-group
                label="เบอร์โทรติดต่อ"
                label-for="phone"
                label-cols-md="12"
              >
                <validation-provider #default="{ errors }" name="Phone">
                  <b-form-input
                    id="Phone"
                    placeholder=""
                    v-model="item.phone"
                    :state="errors.length > 0 ? false : null"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>

            <!-- submit and reset -->
            <b-col cols="12 text-center">
              <b-button
                type="submit"
                variant="primary"
                @click.prevent="validationForm"
              >
                ส่งเรื่องแจ้งซ่อม
              </b-button>
            </b-col>
          </b-row>
        </b-form>
      </validation-observer>
      <template #overlay>
        <div>
          <div
            class="position-absolute"
            style="
              bottom: 10em;
              margin-left: auto;
              margin-right: auto;
              left: 0;
              right: 0;
              text-align: center;
            "
          >
            <b-spinner type="border" variant="primary"></b-spinner>
            <br />
            <span>Please wait...</span>
          </div>
        </div>
      </template>
    </b-overlay>
  </b-card>
</template>

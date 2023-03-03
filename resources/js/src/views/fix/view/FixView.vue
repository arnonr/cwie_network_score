<script>
import {
  BContainer,
  BRow,
  BCol,
  BButton,
  BCard,
  BBadge,
  BOverlay,
  BSpinner,
  BLink,
  BTable,
  BForm,
  BFormGroup,
  BFormTextarea,
  BInputGroup,
  BInputGroupPrepend,
  BFormFile,
  BFormInput,
} from "bootstrap-vue";
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Thai } from "flatpickr/dist/l10n/th.js";
import router from "../../../router";

// Notification
import { useToast } from "vue-toastification/composition";
import ToastificationContent from "@core/components/toastification/ToastificationContent.vue";
import Swal from "sweetalert2";
import { ValidationProvider, ValidationObserver } from "vee-validate";
import { required, email } from "@validations";

import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
dayjs.extend(buddhistEra);

import { onUnmounted, ref, reactive } from "@vue/composition-api";
import store from "@/store";
import fixStoreModule from "../fixStoreModule";
import { getUserData } from "@/auth/utils";

export default {
  components: {
    BContainer,
    BRow,
    BCol,
    BButton,
    BCard,
    BBadge,
    BOverlay,
    BSpinner,
    BLink,
    dayjs,
    BTable,
    BForm,
    BFormGroup,
    BFormTextarea,
    BInputGroup,
    BInputGroupPrepend,
    BFormFile,
    BFormInput,
    ValidationProvider,
    ValidationObserver,
    flatPickr,
    required,
    vSelect,
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
    const FIX_VIEW_APP_STORE_MODULE_NAME = "fix-view";
    const showBtnAdmin = ref(true);

    // Register module
    if (!store.hasModule(FIX_VIEW_APP_STORE_MODULE_NAME))
      store.registerModule(FIX_VIEW_APP_STORE_MODULE_NAME, fixStoreModule);

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(FIX_VIEW_APP_STORE_MODULE_NAME))
        store.unregisterModule(FIX_VIEW_APP_STORE_MODULE_NAME);
    });

    const toast = useToast();

    const isAdmin = getUserData().type == "admin" ? true : false;
    const isUser = getUserData().type == "user" ? true : false;
    const isStaff = getUserData().type == "staff" ? true : false;

    const overLay = ref(false);
    const isActivityModal = ref(false);
    const isActivitySubmit = ref(false);
    const simpleRules = ref();

    const errorToast = (message) => {
      toast({
        component: ToastificationContent,
        props: {
          title: "Error : " + message,
          icon: "AlertTriangleIcon",
          variant: "danger",
        },
      });
    };

    const activityFields = [
      {
        key: "activity_date",
        label: "วันที่",
        sortable: true,
      },
      {
        key: "status",
        label: "สถานะการดำเนินการ",
        sortable: true,
      },
      {
        key: "name",
        label: "ผู้ดำเนินการ",
        sortable: true,
      },
      {
        key: "remark",
        label: "หมายเหตุ",
        sortable: true,
      },
    ];

    if (isAdmin || isStaff) {
      activityFields.push({
        key: "action",
        label: "ดำเนินการ",
      });
    }
    const activityItems = ref([]);

    const activityItem = ref({
      activity_date: "",
      status: { title: "รับเรื่องและอยู่ระหว่างการตรวจสอบปัญหา", code: 2 },
      name: "",
      remark: "",
    });

    if (localStorage.getItem("added") == 1) {
      toast({
        component: ToastificationContent,
        props: {
          title: "Added FIX",
          icon: "CheckIcon",
          variant: "success",
        },
      });

      localStorage.removeItem("added");
    }

    if (localStorage.getItem("updated") == 1) {
      toast({
        component: ToastificationContent,
        props: {
          title: "Updated FIX",
          icon: "CheckIcon",
          variant: "success",
        },
      });

      localStorage.removeItem("updated");
    }

    const item = ref({
      id: null,
      title: "",
      detail: "",
      place: "",
      fix_img_file: "",
      name: "",
      email: "",
      phone: "",
      user_id: "",
      fix_date: "",
      success_date: "",
      status: 1,
    });

    const fetchFix = () => {
      store
        .dispatch("fix-view/fetchFix", { id: router.currentRoute.params.id })
        .then((response) => {
          const { data } = response.data;
          //
          item.value = data;
        })
        .catch((error) => {
          console.log(error);
          toast({
            component: ToastificationContent,
            props: {
              title: "Error Get FIX Information",
              icon: "AlertTriangleIcon",
              variant: "danger",
            },
          });
        });
    };

    fetchFix();

    const fetchActivities = () => {
      store
        .dispatch("fix-view/fetchActivities", {
          fix_id: router.currentRoute.params.id,
        })
        .then((response) => {
          const { data } = response.data;
          activityItems.value = data;
        })
        .catch((error) => {
          console.log(error);
          toast({
            component: ToastificationContent,
            props: {
              title: "Error Get Activity Information",
              icon: "AlertTriangleIcon",
              variant: "danger",
            },
          });
        });
    };

    fetchActivities();

    const onConfirmDelete = (id) => {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-outline-danger ml-1",
        },
        buttonsStyling: false,
      }).then((result) => {
        if (result.isConfirmed) {
          onDelete(id);
          Swal.fire({
            icon: "success",
            title: "Deleted!",
            text: "Your file has been deleted.",
            customClass: {
              confirmButton: "btn btn-success",
            },
          });
        }
      });
    };

    const onDelete = (id) => {
      store
        .dispatch("fix-view/deleteFix", { id: id })
        .then((response) => {
          if (response.data.message == "success") {
            router.push({ name: "fix-list" });
          } else {
            console.log("error");
          }
        })
        .catch((error) => {
          let textErrors = "";
          Object.values(error.response.data.errors).forEach((textError) => {
            textErrors = textErrors + textError + "<br>";
          });
          errorToast(textErrors);
        });
    };

    // Activity

    const onConfirmActivityDelete = (id) => {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-outline-danger ml-1",
        },
        buttonsStyling: false,
      }).then((result) => {
        if (result.isConfirmed) {
          onActivityDelete(id);
          Swal.fire({
            icon: "success",
            title: "Deleted!",
            text: "Your file has been deleted.",
            customClass: {
              confirmButton: "btn btn-success",
            },
          });
        }
      });
    };

    const onActivityDelete = (id) => {
      store
        .dispatch("fix-view/deleteActivity", { id: id })
        .then((response) => {
          if (response.data.message == "success") {
            toast({
              component: ToastificationContent,
              props: {
                title: "Success : Deleted Activity",
                icon: "CheckIcon",
                variant: "success",
              },
            });
            fetchActivities();
          } else {
            console.log("error");
          }
        })
        .catch((error) => {
          let textErrors = "";
          Object.values(error.response.data.errors).forEach((textError) => {
            textErrors = textErrors + textError + "<br>";
          });
          errorToast(textErrors);
        });
    };

    const handleEditActivityClick = (data) => {
      activityItem.value = data;
      activityItem.value.status = {
        code: data.status,
        title: data.status_name,
      };
      isActivityModal.value = true;
    };

    const handleAddActivityClick = () => {
      activityItem.value = {
        activity_date: "",
        remark: "",
        name: getUserData().fullName,
        status: { title: "รับเรื่องและอยู่ระหว่างการตรวจสอบปัญหา", code: 2 },
      };
      isActivityModal.value = true;
    };

    const handleDeleteActivityClick = (id) => {
      // activityItem.value = data;
      onConfirmActivityDelete(id);
    };

    const validationForm = (bvModalEvent) => {
      bvModalEvent.preventDefault();
      simpleRules.value.validate().then((success) => {
        if (success) {
          onActivitySubmit();
        }
      });
    };

    const onActivitySubmit = () => {
      // Prevent modal from closing

      overLay.value = true;
      isActivitySubmit.value = true;

      let dataSend = {
        name: activityItem.value.name,
        detail: activityItem.value.detail,
        activity_date: activityItem.value.activity_date,
        remark: activityItem.value.remark,
        fix_id: router.currentRoute.params.id,
        status: activityItem.value.status.code,
      };

      if (activityItem.value.id == null) {
        store
          .dispatch("fix-view/addActivity", dataSend)
          .then(async (response) => {
            if (response.data.message == "success") {
              fetchFix();

              fetchActivities();

              isActivitySubmit.value = false;
              isActivityModal.value = false;
              overLay.value = false;

              toast({
                component: ToastificationContent,
                props: {
                  title: "Success : Added Activity",
                  icon: "CheckIcon",
                  variant: "success",
                },
              });
            } else {
              isActivitySubmit.value = false;
              overLay.value = false;
              errorToast(response.data.message);
            }
          })
          .catch((error) => {
            isActivitySubmit.value = false;
            overLay.value = false;

            errorToast("Add Activity Error");
          });
      } else {
        // Update
        dataSend["id"] = activityItem.value.id;

        store
          .dispatch("fix-view/editActivity", dataSend)
          .then(async (response) => {
            if (response.data.message == "success") {
              fetchFix();
              fetchActivities();

              isActivitySubmit.value = false;
              isActivityModal.value = false;
              overLay.value = false;

              toast({
                component: ToastificationContent,
                props: {
                  title: "Success : Updated Activity",
                  icon: "CheckIcon",
                  variant: "success",
                },
              });
            } else {
              isActivitySubmit.value = false;
              isActivityModal.value = false;
              overLay.value = false;
              errorToast(response.data.message);
            }
          })
          .catch(() => {
            isActivitySubmit.value = false;
            overLay.value = false;
            errorToast("Update Activity Error");
          });
      }
    };

    const selectOptions = ref({
      fix_statuses: [],
      buildings: [],
    });

    store
      .dispatch("fix-view/fetchFixStatuses")
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

    return {
      item,
      overLay,
      dayjs,
      onConfirmDelete,
      activityItems,
      activityItem,
      activityFields,
      showBtnAdmin,
      handleEditActivityClick,
      handleAddActivityClick,
      handleDeleteActivityClick,
      isActivityModal,
      isActivitySubmit,
      onActivitySubmit,
      validationForm,
      simpleRules,
      isAdmin,
      isStaff,
      isUser,
      selectOptions,
    };
  },
};
</script>

<style lang="scss">
.label {
  font-weight: bold;
}
.div-spinner {
  bottom: 10em;
  margin-left: auto;
  margin-right: auto;
  left: 0;
  right: 0;
  text-align: center;
}
.form-control[readonly] {
  background-color: #fff;
}
.form-control:disabled {
  background-color: #fff;
}
label {
  font-size: 1rem;
}
h1,
h2,
h3,
h4,
h5,
h6,
.h1,
.h2,
.h3,
.h4,
.h5,
.h6 {
  color: #000;
}

@media only screen and (min-width: 1080px) {
  .fix-img {
    max-width: 30% !important;
  }
}

@media only screen and (max-width: 1079px) {
  .fix-img {
    max-width: 80% !important;
  }
}

@media only screen and (max-width: 600px) {
  .fix-img {
    max-width: 100% !important;
  }
}

// @media only screen and (max-width: 1079px) {
//   .fix-img {
//     max-width: "100%";
//   }
// }
</style>

<template>
  <b-card class="container-lg">
    <b-overlay
      :show="overLay"
      variant="light"
      opacity="0.3"
      blur="2px"
      rounded="sm"
      no-center
    >
      <b-row>
        <b-col class="text-center mt-2">
          <h3>FIX Information</h3>
          <hr width="80px;" style="border: solid 2px; border-color: #ffcb05" />
        </b-col>
      </b-row>
      <b-row>
        <b-col class="pt-1 pb-1 mb-2" style="background-color: #eee">
          <h4>ข้อมูลแจ้งซ่อม</h4>
        </b-col>
      </b-row>

      <b-row>
        <b-col>
          <span class="label">หัวข้อแจ้งซ่อม : </span>
          <span class="text-data font-italic">{{ item.title }}</span>
          <hr />
          <span class="label">วันที่แจ้ง : </span>
          <span class="text-data font-italic">{{
            dayjs(item.fix_date).locale("th").format("DD/MM/BBBB")
          }}</span>
          <hr />
          <span class="label">วันที่ดำเนินการเสร็จ : </span>
          <span class="text-data font-italic">{{
            item.success_date
              ? dayjs(item.success_date).locale("th").format("DD/MM/BBBB")
              : "-"
          }}</span>
          <hr />
          <span class="label"
            >รายละเอียดอุปกรณ์/เครื่องมือ ที่เกิดการชำรุด หรือพบเสียหาย :
          </span>
          <span class="text-data font-italic">{{ item.detail }}</span>
          <hr />
          <span class="label">สถานที่พบความชำรุดเสียหาย : </span>
          <span class="text-data font-italic">{{ item.building_name }}</span>
          <hr />
          <span class="label">สถานที่โดยละเอียด : </span>
          <span class="text-data font-italic">{{ item.place }}</span>
          <hr />
          <span class="label">สถานะการซ่อม : </span>
          <b-badge :variant="item.status_color" style="font-size: 120%"
            ><span class="text-data">{{ item.status_name }}</span></b-badge
          >
          <hr />
          <span class="label">ชื่อ-นามสกุล ผู้แจ้ง : </span>
          <span class="text-data font-italic">{{ item.name }}</span>
          <hr />
          <span class="label">อีเมล ผู้แจ้ง : </span>
          <span class="text-data font-italic"> {{ item.email }}</span>
          <hr />
          <span class="label">เบอร์โทรติดต่อ : </span>
          <span class="text-data font-italic">{{ item.phone }}</span>
          <hr />
          <span class="label">ระยะเวลาดำเนินการ : </span>
          <span class="text-data font-italic"
            >{{
              item.success_date == null
                ? dayjs().diff(dayjs(item.fix_date), "day")
                : dayjs().diff(dayjs(item.success_date), "day")
            }}
            วัน</span
          >
          <hr />
          <div class="row">
            <b-col cols="12">
              <span class="label">รูปภาพประกอบ</span>
            </b-col>
            <b-col class="text-center mt-2 mb-3">
              <img :src="item.fix_img_file" alt="" class="fix-img mr-1" />
              <img :src="item.fix_img2_file" alt="" class="fix-img mr-1" />
              <img :src="item.fix_img3_file" alt="" class="fix-img" />
            </b-col>
          </div>
        </b-col>
      </b-row>

      <b-row v-if="isAdmin || isStaff">
        <b-col class="pt-1 pb-1 mb-2 mt-4" style="background-color: #eee">
          <h4>ข้อมูลการดำเนินการ</h4>
        </b-col>
      </b-row>

      <b-row v-if="isAdmin || isStaff">
        <b-col class="col-md-12" v-if="isAdmin || isStaff">
          <b-button
            type="button"
            variant="outline-warning"
            class="float-right mb-1"
            @click="handleAddActivityClick()"
          >
            Add Activity
          </b-button>
        </b-col>
        <b-col class="col-12">
          <b-table
            striped
            hover
            :items="activityItems"
            :fields="activityFields"
            responsive
          >
            <template #cell(activity_date)="data">
              {{ dayjs(data.value).locale("th").format("DD/MM/BBBB") }}
            </template>

            <template #cell(status)="row">
              <b-badge :variant="row.item.status_color">
                {{ row.item.status_name }}
              </b-badge>
            </template>

            <template #cell(action)="row" v-if="isAdmin || isStaff">
              <b-button
                v-if="isAdmin || isStaff"
                variant="outline-success"
                alt="แก้ไข"
                title="แก้ไข"
                class="btn-icon btn-sm"
                @click="handleEditActivityClick({ ...row.item })"
              >
                <feather-icon icon="EditIcon" style="margin-bottom: -2px" />
              </b-button>
              <b-button
                v-if="isAdmin"
                @click="handleDeleteActivityClick(row.item.id)"
                variant="outline-danger"
                alt="ลบ"
                title="ลบ"
                class="btn-icon btn-sm"
              >
                <feather-icon icon="TrashIcon" style="margin-bottom: -2px" />
              </b-button>
            </template>
          </b-table>
        </b-col>
      </b-row>

      <b-row>
        <b-col class="mt-4" v-if="isAdmin || isStaff">
          <b-button
            type="button"
            variant="outline-primary"
            @click="
              $router.push({
                name: 'fix-edit',
                params: { id: item.id },
              })
            "
            v-if="isAdmin || isStaff"
          >
            Edit FIX</b-button
          >
          <b-button
            type="button"
            variant="outline-danger"
            @click="onConfirmDelete(item.id)"
            v-if="isAdmin"
          >
            Delete FIX</b-button
          >
        </b-col>
      </b-row>

      <!-- Button -->

      <template #overlay>
        <div>
          <div class="position-absolute div-spinner">
            <b-spinner type="border" variant="primary"></b-spinner>
            <br />
            <span>Please wait...</span>
          </div>
        </div>
      </template>

      <b-modal
        ref="modalActivityForm"
        id="modal-activity-form"
        cancel-variant="outline-secondary"
        ok-title="Submit"
        cancel-title="Close"
        centered
        size="lg"
        title="Activity Form"
        :visible="isActivityModal"
        @ok="validationForm"
        :ok-disabled="isActivitySubmit"
        :cancel-disabled="isActivitySubmit"
        @change="
          (val) => {
            isActivityModal = val;
          }
        "
      >
        <b-overlay
          :show="isActivitySubmit"
          opacity="0.17"
          spinner-variant="primary"
        >
          <validation-observer ref="simpleRules">
            <b-form>
              <div class="row">
                <b-form-group
                  label="ชื่อผู้ดำเนินการ"
                  label-for="name"
                  class="col-md"
                >
                  <validation-provider #default="{ errors }" name="name">
                    <b-form-input
                      id="activityName"
                      placeholder=""
                      v-model="activityItem.name"
                      :state="errors.length > 0 ? false : null"
                    />
                    <small class="text-danger">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </div>

              <div class="row">
                <b-form-group
                  label="วันที่ดำเนินการ"
                  label-for="activityDate"
                  class="col-md"
                >
                  <validation-provider #default="{ errors }" name="Start Date">
                    <flat-pickr
                      v-model="activityItem.activity_date"
                      placeholder="Activity Date"
                      :config="configDate"
                    />
                    <small class="text-danger">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </div>

              <div class="row">
                <b-form-group class="col-md">
                  <validation-provider
                    #default="{ errors }"
                    name="activityRemark"
                  >
                    <label for="activityRemark">รายละเอียด:</label>
                    <b-form-textarea
                      id="activityRemark"
                      placeholder="รายละเอียด"
                      v-model="activityItem.remark"
                      :state="errors.length > 0 ? false : null"
                    />
                    <small class="text-danger">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </div>

              <div class="row">
                <b-form-group class="col-md">
                  <validation-provider
                    #default="{ errors }"
                    name="activityStatus"
                  >
                    <label for="activityStatus">สถานะ:</label>
                    <v-select
                      input-id="activityItemStatus"
                      label="title"
                      v-model="activityItem.status"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="selectOptions.fix_statuses"
                      placeholder=""
                      :clearable="false"
                    />
                    <small class="text-danger">{{ errors[0] }}</small>
                  </validation-provider>
                </b-form-group>
              </div>
            </b-form>
          </validation-observer>
        </b-overlay>
      </b-modal>
    </b-overlay>
  </b-card>
</template>

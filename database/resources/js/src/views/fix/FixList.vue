<script>
import {
  BCard,
  BRow,
  BCol,
  BFormInput,
  BButton,
  BLink,
  BDropdown,
  BDropdownItem,
  BPagination,
  BSpinner,
  BOverlay,
  BFormGroup,
  BCardText,
  BTable,
  BForm,
  BBadge,
} from "bootstrap-vue";
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Thai } from "flatpickr/dist/l10n/th.js";

import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
dayjs.extend(buddhistEra);

import {
  ref,
  watchEffect,
  reactive,
  onUnmounted,
  computed,
} from "@vue/composition-api";
import store from "@/store";
import fixStoreModule from "./fixStoreModule";
import { useToast } from "vue-toastification/composition";
import ToastificationContent from "@core/components/toastification/ToastificationContent.vue";
import Swal from "sweetalert2";
import { ValidationProvider, ValidationObserver } from "vee-validate";
import { required } from "@validations";
import { getUserData } from "@/auth/utils";

export default {
  components: {
    BCard,
    BRow,
    BCol,
    BFormInput,
    BButton,
    BLink,
    BDropdown,
    BDropdownItem,
    BPagination,
    BSpinner,
    BOverlay,
    vSelect,
    BFormGroup,
    BPagination,
    BCardText,
    dayjs,
    BTable,
    BForm,
    ValidationProvider,
    ValidationObserver,
    required,
    BBadge,
    flatPickr,
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
      buddhistYear: true,
    };
  },
  setup() {
    const FIX_APP_STORE_MODULE_NAME = "fix-list";

    // Register module
    if (!store.hasModule(FIX_APP_STORE_MODULE_NAME))
      store.registerModule(FIX_APP_STORE_MODULE_NAME, fixStoreModule);

    onUnmounted(() => {});

    const toast = useToast();

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

    const isAdmin = getUserData().type == "admin" ? true : false;
    const isUser = getUserData().type == "user" ? true : false;
    const isStaff = getUserData().type == "staff" ? true : false;

    const items = ref([]);

    const isOverLay = ref(false);
    const simpleRules = ref();

    const perPage = ref({ title: "20", code: 20 });
    const currentPage = ref(1);
    const totalPage = ref(1);
    const totalItems = ref(0);
    const orderBy = ref({
      title: "วันที่แจ้ง",
      code: "fix_date",
    });
    const order = ref({ title: "มาก -> น้อย", code: "desc" });

    const advancedSearch = reactive({
      title: "",
      building_id: null,
      place: null,
      name: "",
      email: "",
      phone: "",
      // fix_date: null,
      fix_start_date: null,
      fix_end_date: null,
      success_date: null,
      status: null,
    });

    const resetAdvancedSearch = () => {
      advancedSearch.title = "";
      advancedSearch.building_id = null;
      advancedSearch.place = null;
      advancedSearch.name = "";
      advancedSearch.email = "";
      advancedSearch.phone = "";
      // advancedSearch.fix_date = null;
      advancedSearch.fix_start_date = null;
      advancedSearch.fix_end_date = null;
      (advancedSearch.success_date = null), (advancedSearch.status = null);
    };

    const fields = reactive([
      {
        key: "id",
        label: "Id",
        visible: false,
      },
      {
        key: "title",
        label: "หัวข้อแจ้งซ่อม",
        sortable: true,
        visible: true,
        // class: "text-center",
        tdClass: "mw-3-5",
      },
      {
        key: "building_name",
        label: "อาคาร",
        sortable: true,
        visible: true,
        class: "text-center",
        thStyle: {
          width: "150px",
        },
      },
      {
        key: "place",
        label: "สถานที่",
        sortable: true,
        visible: true,
        class: "text-center",
        tdClass: "mw-3-5",
      },
      {
        key: "fix_date",
        label: "วันที่แจ้ง",
        sortable: true,
        visible: true,
        class: "text-center",
        tdClass: "mw-3-5",
      },
      {
        key: "name",
        label: "ผู้แจ้ง",
        sortable: true,
        visible: true,
        class: "text-center",
        tdClass: "mw-3-5",
      },
      {
        key: "status",
        label: "สถานะ",
        sortable: true,
        visible: true,
        class: "text-center",
        tdClass: "mw-3-5",
      },
      {
        key: "success_date",
        label: "ระยะเวลาดำเนินการ",
        sortable: true,
        visible: isAdmin || isStaff ? true : false,
        class: "text-center",
        tdClass: "mw-3-5",
      },
      {
        key: "email",
        label: "อีเมล",
        sortable: true,
        visible: false,
        class: "text-center",
        tdClass: "mw-3-7",
      },
      {
        key: "action",
        label: "ดูข้อมูล",
        visible: true,
        class: "text-center",
        tdClass: "mw-8",
      },
    ]);

    const visibleFields = computed(() => fields.filter((f) => f.visible));

    const item = ref({
      title: "",
      place: "",
      detail: "",
      fix_date: dayjs().format("YYYY-MM-DD"),
      name: "",
    });

    const selectOptions = ref({
      perPage: [
        { title: "20", code: 20 },
        { title: "40", code: 40 },
        { title: "60", code: 60 },
      ],
      orderBy: [
        { title: "หัวข้อแจ้งซ่อม", code: "title" },
        { title: "อาคาร", code: "building_id" },
        { title: "วันที่แจ้ง", code: "fix_date" },
        { title: "ผู้แจ้ง", code: "name" },
        { title: "สถานะ", code: "status_id" },
      ],
      order: [
        { title: "น้อย -> มาก", code: "asc" },
        { title: "มาก -> น้อย", code: "desc" },
      ],
      fix_statuses: [],
      buildings: [],
    });

    store
      .dispatch("fix-list/fetchFixStatuses")
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
      .dispatch("fix-list/fetchBuildings")
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
            title: "Error fetching Status list",
            icon: "AlertTriangleIcon",
            variant: "danger",
          },
        });
      });

    const fetchItems = () => {
      let search = { ...advancedSearch };
      if (search.status) {
        if (search.status.hasOwnProperty("code")) {
          search.status = search.status.code;
        }
      }

      if (search.building_id) {
        if (search.building_id.hasOwnProperty("code")) {
          search.building_id = search.building_id.code;
        }
      }

      isOverLay.value = true;
      store
        .dispatch("fix-list/fetchFixes", {
          perPage: perPage.value.code,
          currentPage: currentPage.value == 0 ? undefined : currentPage.value,
          orderBy: orderBy.value.code,
          order: order.value.code,
          user_id: isAdmin || isStaff ? undefined : getUserData().userID,
          ...search,
        })
        .then((response) => {
          items.value = response.data.data;
          totalPage.value = response.data.totalPage;
          totalItems.value = response.data.totalData;
          isOverLay.value = false;
        })
        .catch((error) => {
          console.log(error);
          toast({
            component: ToastificationContent,
            props: {
              title: "Error fetching Fix's list",
              icon: "AlertTriangleIcon",
              variant: "danger",
            },
          });
          isOverLay.value = false;
        });
    };
    fetchItems();

    watchEffect(() => {
      fetchItems();
    });

    const onChangePage = (page) => {
      currentPage.value = page;
    };

    //
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
        .dispatch("fix-list/deleteFix", { id: id })
        .then((response) => {
          if (response.data.message == "success") {
            toast({
              component: ToastificationContent,
              props: {
                title: "Success : Deleted Fix",
                icon: "CheckIcon",
                variant: "success",
              },
            });
            fetchItems();
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

    return {
      items,
      item,
      isOverLay,
      perPage,
      currentPage,
      totalPage,
      totalItems,
      orderBy,
      order,
      selectOptions,
      onChangePage,
      visibleFields,
      onConfirmDelete,
      simpleRules,
      isOverLay,
      isAdmin,
      isUser,
      isStaff,
      dayjs,
      advancedSearch,
      resetAdvancedSearch,
      dayjs,
    };
  },
};
</script>

<style>
[dir] .form-control:disabled,
[dir] .form-control[readonly] {
  background-color: #fff;
}
</style>

<template>
  <div class="container-lg">
    <!-- Search -->
    <b-card v-if="isAdmin || isStaff">
      <div class="m-2">
        <b-row>
          <b-col>
            <h4>ค้นหา/Search</h4>
            <hr />
          </b-col>
        </b-row>
        <b-row>
          <b-form-group label="เรื่อง/Title" label-for="title" class="col-md-4">
            <b-form-input
              id="title"
              v-model="advancedSearch.title"
              placeholder="ชื่อเรื่อง..."
            />
          </b-form-group>

          <b-form-group
            label="อาคาร/Building"
            label-for="category"
            class="col-md-4"
          >
            <v-select
              v-model="advancedSearch.building_id"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              label="title"
              :clearable="true"
              placeholder="-- All Building --"
              :options="selectOptions.buildings"
            />
          </b-form-group>

          <b-form-group
            label="ชื่อผู้แจ้ง/Name"
            label-for="name"
            class="col-md-4"
          >
            <b-form-input
              id="name"
              v-model="advancedSearch.name"
              placeholder="ชื่อผู้แจ้ง..."
            />
          </b-form-group>

          <b-form-group
            label="วันที่แจ้ง/Request Date"
            label-for="fix_start_date"
            class="col-md-4"
          >
            <flat-pickr
              v-model="advancedSearch.fix_start_date"
              placeholder="วันที่แจ้ง"
              :config="configDate"
            />
          </b-form-group>

          <b-form-group
            label="ถึงวันที่/Request Date"
            label-for="fix_end_date"
            class="col-md-4"
          >
            <flat-pickr
              v-model="advancedSearch.fix_end_date"
              placeholder="ถึงวันที่"
              :config="configDate"
            />
          </b-form-group>

          <!-- <b-form-group
            label="วันที่แก้ไขเสร็จสิ้น/Success Date"
            label-for="success_date"
            class="col-md-4"
          >
            <flat-pickr
              v-model="advancedSearch.success_date"
              placeholder="วันที่แก้ไขเสร็จสิ้น"
              :config="configDate"
            />
          </b-form-group> -->

          <b-form-group
            label="สถานะ/Status"
            label-for="status"
            class="col-md-4"
          >
            <v-select
              v-model="advancedSearch.status"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              label="title"
              :clearable="true"
              placeholder="-- All Status --"
              :options="selectOptions.fix_statuses"
            />
          </b-form-group>
        </b-row>

        <b-row>
          <b-col>
            <b-button variant="outline-danger" @click="resetAdvancedSearch()">
              Clear
            </b-button>
          </b-col>
        </b-row>
      </div>
    </b-card>

    <b-card no-body>
      <b-overlay :show="isOverLay" opacity="0.3" spinner-variant="primary">
        <div class="m-2">
          <b-row>
            <b-col>
              <b-form-group class="float-left col-lg-2">
                <span>จำนวนรายการ/page</span>
                <v-select
                  v-model="perPage"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  label="title"
                  :options="selectOptions.perPage"
                  :clearable="false"
                />
              </b-form-group>
              <b-form-group class="float-left col-lg-4">
                <span>ลักษณะการแสดงผล</span>
                <v-select
                  v-model="orderBy"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  label="title"
                  :options="selectOptions.orderBy"
                  :clearable="false"
                />
              </b-form-group>
              <b-form-group class="float-left col-lg-2">
                <span>ลำดับการแสดงผล</span>
                <v-select
                  v-model="order"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  label="title"
                  :options="selectOptions.order"
                  :clearable="false"
                />
              </b-form-group>

              <b-button
                variant="outline-success"
                @click="
                  $router.push({
                    name: 'fix-add',
                  })
                "
                class="float-right"
              >
                <feather-icon icon="PlusIcon" />
                ADD
              </b-button>
            </b-col>
          </b-row>
          <hr />
        </div>

        <!-- List -->
        <div class="m-2">
          <b-row>
            <!-- Table -->
            <b-col cols="12">
              <b-table
                striped
                bordered
                hover
                responsive
                :items="items"
                :fields="visibleFields"
              >
                <template #cell(fix_date)="row">
                  {{ dayjs(row.item.fix_date).locale("th").format("DD/MM/BB") }}
                </template>
                <template #cell(status)="row">
                  <b-badge :variant="row.item.status_color">
                    {{ row.item.status_name }}
                  </b-badge>
                </template>
                <template #cell(success_date)="row" v-if="isAdmin || isStaff">
                  {{
                    row.item.success_date == null
                      ? dayjs().diff(dayjs(row.item.fix_date), "day")
                      : dayjs().diff(dayjs(row.item.success_date), "day")
                  }}
                  วัน
                </template>
                <template #cell(action)="row">
                  <b-button
                    variant="outline-success"
                    alt="ดูข้อมูล"
                    title="ดูข้อมูล"
                    class="btn-icon btn-sm"
                    @click="
                      $router.push({
                        name: 'fix-view',
                        params: { id: row.item.id },
                      })
                    "
                  >
                    <feather-icon icon="EyeIcon" style="margin-bottom: -2px" />
                  </b-button>
                  <!-- <b-button
                    variant="outline-success"
                    alt="แก้ไข"
                    title="แก้ไข"
                    class="btn-icon btn-sm"
                    @click="
                      $router.push({
                        name: 'fix-edit',
                        params: { id: row.item.id },
                      })
                    "
                  >
                    <feather-icon icon="EditIcon" style="margin-bottom: -2px" />
                  </b-button> -->
                  <!-- <b-button
                    @click="onConfirmDelete(row.item.id)"
                    variant="outline-danger"
                    alt="ลบ"
                    title="ลบ"
                    class="btn-icon btn-sm"
                  >
                    <feather-icon
                      icon="TrashIcon"
                      style="margin-bottom: -2px"
                    />
                  </b-button> -->
                </template>
              </b-table>
            </b-col>
          </b-row>

          <b-row>
            <b-col cols="12" class="text-center">
              <b-pagination
                v-model="currentPage"
                :total-rows="totalItems"
                :per-page="perPage.code"
                align="center"
                aria-controls="my-mou"
                @change="onChangePage"
              />
            </b-col>
          </b-row>
        </div>
      </b-overlay>
    </b-card>
  </div>
</template>

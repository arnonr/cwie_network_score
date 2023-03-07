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
  BModal,
} from "bootstrap-vue";
import vSelect from "vue-select";

import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
dayjs.extend(buddhistEra);

import {
  ref,
  watch,
  watchEffect,
  reactive,
  onUnmounted,
  computed,
} from "@vue/composition-api";
import store from "@/store";
import userStoreModule from "./userStoreModule";
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
    BModal,
    ValidationProvider,
    ValidationObserver,
    required,
  },
  setup() {
    const USER_APP_STORE_MODULE_NAME = "user-list";

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME))
      store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule);

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

    const items = ref([]);

    const isAdd = ref(false);
    const isOverLay = ref(false);
    const isModal = ref(false);
    const isSubmit = ref(false);
    const simpleRules = ref();

    const perPage = ref({ title: "50", code: 50 });
    const currentPage = ref(1);
    const totalPage = ref(1);
    const totalItems = ref(0);
    const orderBy = ref({
      title: "ชื่อ",
      code: "firstname",
    });
    const order = ref({ title: "DESC", code: "desc" });

    const fields = reactive([
      {
        key: "id",
        label: "Id",
        visible: false,
      },
      {
        key: "firstname",
        label: "ชื่อ",
        sortable: true,
        visible: true,
        class: "text-center",
        tdClass: "mw-3-5",
      },
      {
        key: "username",
        label: "username",
        sortable: true,
        visible: true,
        class: "text-center",
        tdClass: "mw-3-5",
      },
      {
        key: "tel",
        label: "โทรศัพท์",
        sortable: true,
        visible: true,
        class: "text-center",
      },
      {
        key: "project_type_name",
        label: "ประเภทตัดสิน",
        sortable: true,
        visible: true,
        class: "text-center",
      },
      {
        key: "type",
        label: "ประเภทผู้ใช้งาน",
        sortable: true,
        visible: true,
        class: "text-center",
      },
      {
        key: "action",
        label: "Action",
        visible: true,
        class: "text-center",
        tdClass: "mw-8",
      },
    ]);

    const visibleFields = computed(() => fields.filter((f) => f.visible));

    const advancedSearch = reactive({
      fullname: "",
      firstname: "",
      lastname: "",
      username: "",
      project_type_id: null,
      type: null,
    });

    const resetAdvancedSearch = () => {
      advancedSearch.fullname = "";
      advancedSearch.firstname = "";
      advancedSearch.lastname = "";
      advancedSearch.username = "";
      advancedSearch.project_type_id = null;
      advancedSearch.type = null;
    };

    const item = ref({
      username: "",
      email: "",
      type: "",
    });

    const selectOptions = ref({
      perPage: [
        { title: "20", code: 20 },
        { title: "50", code: 50 },
      ],
      orderBy: [
        { title: "ชื่อ", code: "firstname" },
        { title: "username", code: "username" },
        { title: "ประเภทตัดสิน", code: "project_type_name" },
        { title: "ประเภทผู้ใช้", code: "type" },
      ],
      order: [
        { title: "ASC", code: "asc" },
        { title: "DESC", code: "desc" },
      ],
      status: [
        { title: "อนุมัติ", code: 2 },
        { title: "บล็อก", code: 3 },
      ],
      types: [
        { title: "Admin", code: "admin" },
        { title: "Staff", code: "staff" },
        { title: "Referee", code: "referee" },
        { title: "User", code: "user" },
      ],
      project_types: [],
    });

    store
      .dispatch("user-list/fetchProjectTypes")
      .then((response) => {
        const { data } = response.data;
        selectOptions.value.project_types = data.map((d) => {
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
            title: "Error fetching Project Types's list",
            icon: "AlertTriangleIcon",
            variant: "danger",
          },
        });
      });

    const fetchItems = () => {
      isOverLay.value = true;

      let search = { ...advancedSearch };
      if (search.project_type_id) {
        if (search.project_type_id.hasOwnProperty("code")) {
          search.project_type_id = search.project_type_id.code;
        }
      }

      if (search.type) {
        if (search.type.hasOwnProperty("code")) {
          search.type = search.type.code;
        }
      }

      store
        .dispatch("user-list/fetchUsers", {
          perPage: perPage.value.code,
          currentPage: currentPage.value == 0 ? undefined : currentPage.value,
          orderBy: orderBy.value.code,
          order: order.value.code,
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
              title: "Error fetching User's list",
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
        .dispatch("user-list/deleteUser", { id: id })
        .then((response) => {
          if (response.data.message == "success") {
            toast({
              component: ToastificationContent,
              props: {
                title: "Success : Deleted User",
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

    const handleEditClick = (data) => {
      item.value = data;

      item.value.type = selectOptions.value.types.find((t) => {
        return t.code == data.type;
      });

      item.value.project_type_id = selectOptions.value.project_types.find(
        (t) => {
          return t.code == data.project_type_id;
        }
      );

      isAdd.value = false;
      isModal.value = true;
    };

    const handleAddClick = () => {
      item.value = {
        username: "",
        email: "",
        prefix: "",
        firstname: "",
        lastname: "",
        tel: "",
        project_type_id: null,
        type: null,
        // status: 1
      };
      isAdd.value = true;
      isModal.value = true;
    };

    const validationForm = (bvModalEvent) => {
      bvModalEvent.preventDefault();
      simpleRules.value.validate().then((success) => {
        if (success) {
          onSubmit();
        }
      });
    };

    const onSubmit = () => {
      // Prevent modal from closing

      isOverLay.value = true;
      isSubmit.value = true;

      let dataSend = {
        email: item.value.username,
        prefix: item.value.prefix,
        firstname: item.value.firstname,
        lastname: item.value.lastname,
        type: item.value.type.code,
        status: 2,
        tel: item.value.tel,
        project_type_id: null,
        project_type_arr: null,
      };

      if (item.value.type.code == "referee") {
        dataSend.project_type_id = item.value.project_type_id.code;
        dataSend.project_type_arr = item.value.project_type_id.code;
      }

      if (item.value.id == null) {
        store
          .dispatch("user-list/addUser", dataSend)
          .then(async (response) => {
            if (response.data.message == "success") {
              fetchItems();

              isSubmit.value = false;
              isModal.value = false;
              isOverLay.value = false;

              toast({
                component: ToastificationContent,
                props: {
                  title: "Success : Added User",
                  icon: "CheckIcon",
                  variant: "success",
                },
              });
            } else {
              isSubmit.value = false;
              isOverLay.value = false;
              errorToast(response.data.message);
            }
          })
          .catch((error) => {
            isSubmit.value = false;
            isOverLay.value = false;

            errorToast("Add User Error");
          });
      } else {
        // Update
        dataSend["id"] = item.value.id;

        store
          .dispatch("user-list/editUser", dataSend)
          .then(async (response) => {
            if (response.data.message == "success") {
              fetchItems();

              isSubmit.value = false;
              isModal.value = false;
              isOverLay.value = false;

              toast({
                component: ToastificationContent,
                props: {
                  title: "Success : Updated User",
                  icon: "CheckIcon",
                  variant: "success",
                },
              });
            } else {
              isSubmit.value = false;
              isModal.value = false;
              isOverLay.value = false;
              errorToast(response.data.message);
            }
          })
          .catch(() => {
            isSubmit.value = false;
            isOverLay.value = false;
            errorToast("Update User Error");
          });
      }
    };

    return {
      advancedSearch,
      resetAdvancedSearch,
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
      validationForm,
      onConfirmDelete,
      handleEditClick,
      handleAddClick,
      simpleRules,
      isModal,
      isOverLay,
      isSubmit,
      isAdd,
      isAdmin,
    };
  },
};
</script>

<style></style>

<template>
  <div class="container-lg">
    <!-- Search -->
    <b-card>
      <div class="m-2">
        <b-row>
          <b-col>
            <h4>ค้นหา/Search</h4>
            <hr />
          </b-col>
        </b-row>
        <b-row>
          <b-form-group label="ชื่อ-นามสกุล" label-for="Name" class="col-md-6">
            <b-form-input
              id="fullname"
              v-model="advancedSearch.fullname"
              placeholder="ชื่อ-นามสกุล..."
            />
          </b-form-group>

          <b-form-group label="Username" label-for="Username" class="col-md-6">
            <b-form-input
              id="username"
              v-model="advancedSearch.username"
              placeholder="Username..."
            />
          </b-form-group>

          <b-form-group
            label="ประเภทตัดสิน"
            label-for="project_type_id"
            class="col-md-6"
          >
            <v-select
              v-model="advancedSearch.project_type_id"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              label="title"
              :clearable="true"
              placeholder="-- All Project Type --"
              :options="selectOptions.project_types"
            />
          </b-form-group>

          <b-form-group label="ประเภทผู้ใช้" label-for="type" class="col-md-6">
            <v-select
              v-model="advancedSearch.type"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              label="title"
              :clearable="true"
              placeholder="-- All Type --"
              :options="selectOptions.types"
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
                <v-select
                  v-model="perPage"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  label="title"
                  :options="selectOptions.perPage"
                  :clearable="false"
                />
              </b-form-group>
              <b-form-group class="float-left col-lg-4">
                <v-select
                  v-model="orderBy"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  label="title"
                  :options="selectOptions.orderBy"
                  :clearable="false"
                />
              </b-form-group>
              <b-form-group class="float-left col-lg-2">
                <v-select
                  v-model="order"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  label="title"
                  :options="selectOptions.order"
                  :clearable="false"
                />
              </b-form-group>

              <b-button
                variant="success"
                @click="handleAddClick()"
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
                <template #cell(firstname)="row">
                  {{
                    row.item.prefix +
                    row.item.firstname +
                    " " +
                    row.item.lastname
                  }}
                </template>
                <template #cell(action)="row">
                  <b-button
                    variant="outline-success"
                    alt="แก้ไข"
                    title="แก้ไข"
                    class="btn-icon btn-sm"
                    @click="handleEditClick({ ...row.item })"
                  >
                    <feather-icon icon="EditIcon" style="margin-bottom: -2px" />
                  </b-button>
                  <b-button
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
                  </b-button>
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

        <b-modal
          ref="modalForm"
          id="modal-form"
          cancel-variant="outline-secondary"
          ok-title="Submit"
          cancel-title="Close"
          centered
          size="lg"
          title="User Form"
          :visible="isModal"
          @ok="validationForm"
          :ok-disabled="isSubmit"
          :cancel-disabled="isSubmit"
          @change="
            (val) => {
              isModal = val;
            }
          "
        >
          <b-overlay :show="isSubmit" opacity="0.17" spinner-variant="primary">
            <validation-observer ref="simpleRules">
              <b-form>
                <div class="row">
                  <b-form-group
                    label="Username"
                    label-for="username"
                    class="col-md"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="username"
                      rules="required"
                    >
                      <b-form-input
                        id="username"
                        placeholder=""
                        v-model="item.username"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group
                    label="คำนำหน้า"
                    label-for="prefix"
                    class="col-md"
                  >
                    <validation-provider #default="{ errors }" name="prefix">
                      <b-form-input
                        id="prefix"
                        placeholder=""
                        v-model="item.prefix"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group
                    label="ชื่อ"
                    label-for="firstname"
                    class="col-md"
                  >
                    <validation-provider #default="{ errors }" name="firstname">
                      <b-form-input
                        id="firstname"
                        placeholder=""
                        v-model="item.firstname"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group
                    label="นามสกุล"
                    label-for="flastname"
                    class="col-md"
                  >
                    <validation-provider #default="{ errors }" name="lastname">
                      <b-form-input
                        id="lastname"
                        placeholder=""
                        v-model="item.lastname"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group label="โทรศัพท์" label-for="tel" class="col-md">
                    <validation-provider
                      #default="{ errors }"
                      name="tel"
                      rules="required"
                    >
                      <b-form-input
                        id="tel"
                        placeholder=""
                        v-model="item.tel"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group
                    label="ประเภทผู้ใช้งาน/User Type:"
                    label-for="type"
                    class="col-md"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="type"
                      rules="required"
                    >
                      <v-select
                        input-id="type"
                        label="title"
                        v-model="item.type"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="selectOptions.types"
                        placeholder=""
                        :clearable="false"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group
                    label="ประเภทตัดสิน/Project Type:"
                    label-for="project_type_id"
                    class="col-md"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="project_type_id"
                    >
                      <v-select
                        input-id="project_type_id"
                        label="title"
                        v-model="item.project_type_id"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="selectOptions.project_types"
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
  </div>
</template>

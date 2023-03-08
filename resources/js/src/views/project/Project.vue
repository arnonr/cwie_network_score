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
  BFormTextarea,
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
import projectStoreModule from "./projectStoreModule";
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
    BFormTextarea,
  },
  setup() {
    const PROJECT_APP_STORE_MODULE_NAME = "project-list";

    // Register module
    if (!store.hasModule(PROJECT_APP_STORE_MODULE_NAME))
      store.registerModule(PROJECT_APP_STORE_MODULE_NAME, projectStoreModule);

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
    const orderBy = ref(
      {
        title: "ประเภทการประกวด",
        code: "project_type.name",
      },
      {
        title: "รหัส",
        code: "code",
      },
      {
        title: "สถานศึกษา",
        code: "university.name",
      }
    );
    const order = ref({ title: "ASC", code: "asc" });

    const fields = reactive([
      {
        key: "id",
        label: "Id",
        visible: false,
      },
      {
        key: "code",
        label: "รหัส",
        sortable: true,
        visible: true,
      },
      {
        key: "university_name",
        label: "สถานศึกษา",
        sortable: true,
        visible: true,
        thStyle: {
          width: "400px",
        },
      },
      {
        key: "project_type_name",
        label: "ประเภทการประกวด",
        sortable: true,
        visible: true,
        thStyle: {
          width: "400px",
        },
      },
      {
        key: "action",
        label: "จัดการ",
        visible: true,
        class: "text-center",
      },
    ]);

    const visibleFields = computed(() => fields.filter((f) => f.visible));

    const advancedSearch = reactive({
      code: "",
      project_type_id: null,
      university_id: null,
    });

    const resetAdvancedSearch = () => {
      advancedSearch.code = "";
      advancedSearch.project_type_id = null;
      advancedSearch.university_id = null;
    };

    const item = ref({
      code: "",
      university_id: null,
      project_type_id: null,
      status: 1,
      is_publish: 1,
    });

    const selectOptions = ref({
      perPage: [{ title: "50", code: 50 }],
      orderBy: [
        { title: "ประเภทการประกวด", code: "project_type.name" },
        { title: "รหัส", code: "code" },
        { title: "สถานศึกษา", code: "university.name" },
      ],
      order: [
        { title: "ASC", code: "asc" },
        { title: "DESC", code: "desc" },
      ],
      project_types: [],
      universities: [],
    });

    store
      .dispatch("project-list/fetchProjectTypes")
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

    store
      .dispatch("project-list/fetchUniversities")
      .then((response) => {
        const { data } = response.data;
        selectOptions.value.universities = data.map((d) => {
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
            title: "Error fetchingUniversitie's list",
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

      if (search.university_id) {
        if (search.university_id.hasOwnProperty("code")) {
          search.university_id = search.university_id.code;
        }
      }

      store
        .dispatch("project-list/fetchProjects", {
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
              title: "Error fetching Project's list",
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
        .dispatch("project-list/deleteProject", { id: id })
        .then((response) => {
          if (response.data.message == "success") {
            toast({
              component: ToastificationContent,
              props: {
                title: "Success : Deleted Project",
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
      item.value.project_type_id = {
        title: data.project_type_name,
        code: data.project_type_id,
      };

      item.value.university_id = {
        title: data.university_name,
        code: data.university_id,
      };

      isAdd.value = false;
      isModal.value = true;
    };

    const handleAddClick = () => {
      item.value = {
        code: "",
        university_id: null,
        project_type_id: null,
        status: 1,
        is_publish: 1,
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

    watchEffect(() => {
      fetchItems();
    });

    const onSubmit = () => {
      // Prevent modal from closing

      isOverLay.value = true;
      isSubmit.value = true;

      let dataSend = {
        code: item.value.code,
        level: item.value.level,
        project_type_id: item.value.project_type_id.code,
        university_id: item.value.university_id.code,
        status: item.value.status,
        is_publish: item.value.is_publish,
      };

      if (item.value.id == null) {
        store
          .dispatch("project-list/addProject", dataSend)
          .then(async (response) => {
            if (response.data.message == "success") {
              fetchItems();

              isSubmit.value = false;
              isModal.value = false;
              isOverLay.value = false;

              toast({
                component: ToastificationContent,
                props: {
                  title: "Success : Added Project",
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

            errorToast("Add Project Error");
          });
      } else {
        // Update
        dataSend["id"] = item.value.id;

        store
          .dispatch("project-list/editProject", dataSend)
          .then(async (response) => {
            if (response.data.message == "success") {
              fetchItems();

              isSubmit.value = false;
              isModal.value = false;
              isOverLay.value = false;

              toast({
                component: ToastificationContent,
                props: {
                  title: "Success : Updated Project",
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
            errorToast("Update Project Error");
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
          <b-form-group label="รหัส" label-for="Code" class="col-md-6">
            <b-form-input
              id="code"
              v-model="advancedSearch.code"
              placeholder="รหัส..."
            />
          </b-form-group>

          <b-form-group
            label="สถานศึกษา"
            label-for="university_id"
            class="col-md-6"
          >
            <v-select
              v-model="advancedSearch.university_id"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              label="title"
              :clearable="true"
              placeholder="-- All University --"
              :options="selectOptions.universities"
            />
          </b-form-group>

          <b-form-group
            label="ประเภทการประกวด"
            label-for="project_type_id"
            class="col-md-12"
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
    <!--  -->
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
                <template #cell(action)="row">
                  <b-button
                    variant="outline-info"
                    alt="ดูข้อมูล"
                    title="ดูข้อมูล"
                    class="btn-icon btn-sm"
                    :href="'http://143.198.208.110:8113/storage/document/'+row.item.code+'.pdf'"
                    target="_blank"
                  >
                    <feather-icon icon="EyeIcon" style="margin-bottom: -2px" />
                  </b-button>
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
          title="Form"
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
                    label="ประเภทการประกวด"
                    label-for="project_type_id"
                    class="col-md"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="project_type_id"
                      rules="required"
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

                <div class="row">
                  <b-form-group label="รหัส" label-for="code" class="col-md">
                    <validation-provider #default="{ errors }" name="code">
                      <b-form-input
                        id="code"
                        placeholder=""
                        v-model="item.code"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group
                    label="สถานศึกษา"
                    label-for="university_id"
                    class="col-md"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="university_id"
                      rules="required"
                    >
                      <v-select
                        input-id="university_id"
                        label="title"
                        v-model="item.university_id"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="selectOptions.universities"
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

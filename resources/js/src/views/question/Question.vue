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
import questionStoreModule from "./questionStoreModule";
import { useToast } from "vue-toastification/composition";
import ToastificationContent from "@core/components/toastification/ToastificationContent.vue";
import Swal from "sweetalert2";
import { ValidationProvider, ValidationObserver } from "vee-validate";
import { required } from "@validations";
import { getUserData } from "@/auth/utils";
import { INSPECT_MAX_BYTES } from "buffer";

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
    const QUESTION_APP_STORE_MODULE_NAME = "question-list";

    // Register module
    if (!store.hasModule(QUESTION_APP_STORE_MODULE_NAME))
      store.registerModule(QUESTION_APP_STORE_MODULE_NAME, questionStoreModule);

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
        code: "project_type_name",
      },
      {
        title: "คำถาม",
        code: "name",
      },
      {
        title: "ลำดับ",
        code: "level",
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
        key: "name",
        label: "คำถาม",
        sortable: true,
        visible: true,
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
        key: "level",
        label: "ลำดับ",
        sortable: true,
        visible: true,
        class: "text-center",
      },
      {
        key: "total_score",
        label: "คะแนนเต็ม",
        sortable: true,
        visible: true,
        class: "text-center",
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
      name: "",
      project_type_id: null,
    });

    const resetAdvancedSearch = () => {
      advancedSearch.name = "";
      advancedSearch.project_type_id = null;
    };

    const item = ref({
      name: "",
    });

    const selectOptions = ref({
      perPage: [{ title: "50", code: 50 }],
      orderBy: [
        { title: "ประเภทการประกวด", code: "project_type_name" },
        { title: "คำถาม", code: "name" },
        { title: "ลำดับ", code: "level" },
      ],
      order: [
        { title: "ASC", code: "asc" },
        { title: "DESC", code: "desc" },
      ],
      project_types: [],
    });

    store
      .dispatch("question-list/fetchProjectTypes")
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

      store
        .dispatch("question-list/fetchQuestions", {
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
              title: "Error fetching Question's list",
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
        .dispatch("question-list/deleteQuestion", { id: id })
        .then((response) => {
          if (response.data.message == "success") {
            toast({
              component: ToastificationContent,
              props: {
                title: "Success : Deleted Question",
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
      item.value.is_check =
        data.is_check == 1
          ? {
              title: "YES",
              code: data.is_check,
            }
          : {
              title: "NO",
              code: data.is_check,
            };

      isAdd.value = false;
      isModal.value = true;
    };

    const handleAddClick = () => {
      item.value = {
        name: "",
        project_type_id: null,
        level: "",
        detail: "",
        total_score: "",
        is_check: { title: "NO", code: 0 },
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
        name: item.value.name,
        level: item.value.level,
        project_type_id: item.value.project_type_id.code,
        is_check: item.value.is_check.code,
        total_score: item.value.total_score,
        detail: item.value.detail,
        is_publish: item.value.is_publish,
      };

      if (item.value.id == null) {
        store
          .dispatch("question-list/addQuestion", dataSend)
          .then(async (response) => {
            if (response.data.message == "success") {
              fetchItems();

              isSubmit.value = false;
              isModal.value = false;
              isOverLay.value = false;

              toast({
                component: ToastificationContent,
                props: {
                  title: "Success : Added Question",
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

            errorToast("Add Question Error");
          });
      } else {
        // Update
        dataSend["id"] = item.value.id;

        store
          .dispatch("question-list/editQuestion", dataSend)
          .then(async (response) => {
            if (response.data.message == "success") {
              fetchItems();

              isSubmit.value = false;
              isModal.value = false;
              isOverLay.value = false;

              toast({
                component: ToastificationContent,
                props: {
                  title: "Success : Updated Question",
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
            errorToast("Update Question Error");
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
          <b-form-group label="คำถาม" label-for="Name" class="col-md-6">
            <b-form-input
              id="name"
              v-model="advancedSearch.name"
              placeholder="คำถาม..."
            />
          </b-form-group>

          <b-form-group
            label="ประเภทการประกวด"
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
                  <b-form-group label="คำถาม" label-for="name" class="col-md">
                    <validation-provider
                      #default="{ errors }"
                      name="name"
                      rules="required"
                    >
                      <b-form-input
                        id="name"
                        placeholder=""
                        v-model="item.name"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group label="ลำดับ" label-for="level" class="col-md">
                    <validation-provider #default="{ errors }" name="level">
                      <b-form-input
                        id="level"
                        placeholder=""
                        v-model="item.level"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group
                    label="คำอธิบาย"
                    label-for="detail"
                    class="col-md"
                  >
                    <validation-provider #default="{ errors }" name="detail">
                      <b-form-textarea
                        id="detail"
                        placeholder=""
                        v-model="item.detail"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group
                    label="คะแนนเต็ม"
                    label-for="total_score"
                    class="col-md"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="total_score"
                    >
                      <b-form-input
                        id="total_score"
                        placeholder=""
                        v-model="item.total_score"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </div>

                <div class="row">
                  <b-form-group
                    label="เป็น Checkbox"
                    label-for="is_check"
                    class="col-md"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="is_check"
                      rules="required"
                    >
                      <v-select
                        input-id="is_check"
                        label="title"
                        v-model="item.is_check"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="[
                          {
                            title: 'YES',
                            code: 1,
                          },
                          { title: 'NO', code: 0 },
                        ]"
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

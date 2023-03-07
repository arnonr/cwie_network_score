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
  BInputGroup,
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
import reportStoreModule from "./reportStoreModule";
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
    BInputGroup,
  },
  setup() {
    const REPORT_APP_STORE_MODULE_NAME = "report";

    // Register module
    if (!store.hasModule(REPORT_APP_STORE_MODULE_NAME))
      store.registerModule(REPORT_APP_STORE_MODULE_NAME, reportStoreModule);

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
    const isReferee = getUserData().type == "referee" ? true : false;
    const isStaff = getUserData().type == "staff" ? true : false;

    const items = ref([]);

    const score_items = ref([]);

    const isOverLay = ref(false);

    const perPage = ref({ title: "50", code: 50 });
    const currentPage = ref(1);
    const totalPage = ref(1);
    const totalItems = ref(0);
    const orderBy = ref({
      title: "รหัส",
      code: "code",
    });
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
        thStyle: {
          width: "100px",
        },
      },
      {
        key: "university_name",
        label: "สถานศึกษา",
        sortable: true,
        visible: true,
      },
      {
        key: "project_type_name",
        label: "ประเภทการประกวด",
        sortable: true,
        visible: true,
      },
      {
        key: "referee_1.score",
        label: "ผู้ตัดสินที่ 1",
        sortable: true,
        visible: true,
        class: "text-center",
      },
      {
        key: "referee_2.score",
        label: "ผู้ตัดสินที่ 2",
        sortable: true,
        visible: true,
        class: "text-center",
      },
      {
        key: "referee_3.score",
        label: "ผู้ตัดสินที่ 3",
        sortable: true,
        visible: true,
        class: "text-center",
      },
      {
        key: "referee_4.score",
        label: "ผู้ตัดสินที่ 4",
        sortable: true,
        visible: true,
        class: "text-center",
      },
      {
        key: "referee_5.score",
        label: "ผู้ตัดสินที่ 5",
        sortable: true,
        visible: true,
        class: "text-center",
      },
      {
        key: "score_all",
        label: "รวม",
        sortable: true,
        visible: isReferee ? false : true,
        class: "text-center",
      },
    ]);

    const visibleFields = computed(() => fields.filter((f) => f.visible));

    const advancedSearch = reactive({
      project_type_id: null,
    });

    const resetAdvancedSearch = () => {
      advancedSearch.project_type_id = null;
    };

    const item = ref({
      code: "",
      university_id: null,
      project_type_id: null,
      status: 1,
      is_publish: 1,
    });

    const item_score = ref({});

    const selectOptions = ref({
      perPage: [{ title: "50", code: 50 }],
      orderBy: [{ title: "รหัส", code: "code" }],
      order: [
        { title: "ASC", code: "asc" },
        { title: "DESC", code: "desc" },
      ],
      project_types: [],
      universities: [],
      questions: [],
    });

    const fetchProjectTypes = () => {
      let searchProjectTypes = {};
      if (isReferee) {
        const project_type_arr = getUserData().project_type_arr.split(",");
        // searchProjectTypes.id = getUserData().project_type_id;
        searchProjectTypes.id_arr = project_type_arr;
      }

      store
        .dispatch("report/fetchProjectTypes", searchProjectTypes)
        .then((response) => {
          const { data } = response.data;
          selectOptions.value.project_types = data.map((d) => {
            return {
              code: d.id,
              title: d.name,
              is_final: d.is_final,
            };
          });

          if (isReferee) {
            advancedSearch.project_type_id =
              selectOptions.value.project_types.find((d) => {
                return d.code == getUserData().project_type_id;
              });
          }
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
    };

    fetchProjectTypes();

    const fetchScores = () => {
      store
        .dispatch("report/fetchScores", {
          project_type_id: advancedSearch.project_type_id.code,
        })
        .then((response) => {
          const { data } = response.data;
          score_items.value = data;
          //   items.value

          items.value = items.value.map((i) => {
            // ได้โปรเจคมา

            // i.score_referee = score_referee;
            let score_all = 0;
            referees.value.forEach((r) => {
              let score_project = score_items.value.filter((sc) => {
                return i.id == sc.project_id && r.id == sc.user_id;
              });

              let score_total = 0;
              score_project.forEach((element) => {
                score_total = score_total + element.answer;
              });

              if (i.hasOwnProperty("referee_1")) {
                if (i.referee_1.user_id == r.id) {
                  i.referee_1 = { ...i.referee_1, score: score_total };
                }
              }

              if (i.hasOwnProperty("referee_2")) {
                if (i.referee_2.user_id == r.id) {
                  i.referee_2 = { ...i.referee_2, score: score_total };
                }
              }

              if (i.hasOwnProperty("referee_3")) {
                if (i.referee_3.user_id == r.id) {
                  i.referee_3 = { ...i.referee_3, score: score_total };
                }
              }

              if (i.hasOwnProperty("referee_4")) {
                if (i.referee_4.user_id == r.id) {
                  i.referee_4 = { ...i.referee_4, score: score_total };
                }
              }

              if (i.hasOwnProperty("referee_5")) {
                if (i.referee_5.user_id == r.id) {
                  i.referee_5 = { ...i.referee_5, score: score_total };
                }
              }

              score_all = score_all + score_total;
            });

            i.score_all = score_all;

            return i;
          });

          console.log(items.value);
        })
        .catch((error) => {
          console.log(error);
          toast({
            component: ToastificationContent,
            props: {
              title: "Error fetching Score's list",
              icon: "AlertTriangleIcon",
              variant: "danger",
            },
          });
        });
    };

    //

    store
      .dispatch("report/fetchUniversities")
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

    const referees = ref([]);

    const fetchReferee = () => {
      let searchUser = {
        project_type_arr: advancedSearch.project_type_id.code,
      };
      if (isReferee) {
        searchUser = {
          project_type_arr: advancedSearch.project_type_id.code,
          id: getUserData().userID,
        };
      }
      store
        .dispatch("report/fetchUsers", {
          ...searchUser,
        })
        .then((response) => {
          referees.value = response.data.data;

          items.value = items.value.map((i) => {
            let j = 1;
            referees.value.forEach((el) => {
              i["referee_" + j] = {
                user_id: el.id,
                name: el.firstname,
              };
              j = j + 1;
            });
            return i;
          });

          fetchScores();
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

    const fetchItems = () => {
      isOverLay.value = true;

      let search = { ...advancedSearch };



      if (search.project_type_id) {
        if (search.project_type_id.hasOwnProperty("code")) {
          search.project_type_id = search.project_type_id.code;
        }
        // fetchQuestions();

        //   if (isReferee) {
        //     search.project_type_id = getUserData().project_type_id;
        //   }

        store
          .dispatch("report/fetchProjects", {
            perPage: perPage.value.code,
            currentPage: currentPage.value == 0 ? undefined : currentPage.value,
            orderBy: orderBy.value.code,
            order: order.value.code,
            ...search,
          })
          .then((response) => {
            items.value = response.data.data;

            fetchReferee();
            // fetchScores();

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
      } else {
        items.value = [];
        isOverLay.value = false;
      }
    };
    fetchItems();

    watchEffect(() => {
      fetchItems();
    });

    const onChangePage = (page) => {
      currentPage.value = page;
    };

    watchEffect(() => {
      fetchItems();
    });

    return {
      advancedSearch,
      resetAdvancedSearch,
      items,
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
      isOverLay,
      isAdmin,
      isReferee,
      isStaff,
      item_score,
    };
  },
};
</script>

<style>
.input-group-text {
  color: #aaa;
}
</style>

<template>
  <div class="container-lg">
    <!-- Search -->
    <b-card>
      <div class="m-2">
        <b-row>
          <b-col>
            <h4>ค้นหาผลงาน/Search</h4>
            <hr />
          </b-col>
        </b-row>
        <b-row>
          <b-form-group
            label="ประเภทการประกวด"
            label-for="project_type_id"
            class="col-md-12"
          >
            <v-select
              v-model="advancedSearch.project_type_id"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              label="title"
              :clearable="isReferee ? false : true"
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
                    :href="
                      'http://localhost:8113/storage/document/' +
                      row.item.code +
                      '.pdf'
                    "
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

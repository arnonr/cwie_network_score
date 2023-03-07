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
import scoreStoreModule from "./scoreStoreModule";
import { useToast } from "vue-toastification/composition";
import ToastificationContent from "@core/components/toastification/ToastificationContent.vue";
import Swal from "sweetalert2";
import { ValidationProvider, ValidationObserver } from "vee-validate";
import { required } from "@validations";
import { getUserData } from "@/auth/utils";

import { extend } from "vee-validate";
import { max_value } from "vee-validate/dist/rules";
extend("max_value", max_value);

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
    max_value,
  },
  setup() {
    const SCORE_APP_STORE_MODULE_NAME = "score-list";

    // Register module
    if (!store.hasModule(SCORE_APP_STORE_MODULE_NAME))
      store.registerModule(SCORE_APP_STORE_MODULE_NAME, scoreStoreModule);

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
    const isClose = ref(false);
    const isFinalClose = ref(false);

    const items = ref([]);

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
        title: "รหัส",
        code: "code",
      },
      {
        title: "ประเภทการประกวด",
        code: "project_type.name",
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
        key: "action",
        label: "จัดการ",
        visible: true,
        class: "text-center",
        thStyle: {
          width: "200px",
        },
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

    const item_score = ref({});

    const selectOptions = ref({
      perPage: [{ title: "50", code: 50 }],
      orderBy: [
        { title: "รหัส", code: "code" },
        { title: "ประเภทการประกวด", code: "project_type.name" },
        { title: "สถานศึกษา", code: "university.name" },
      ],
      order: [
        { title: "ASC", code: "asc" },
        { title: "DESC", code: "desc" },
      ],
      project_types: [],
      universities: [],
      questions: [],
    });

    store
      .dispatch("score-list/fetchSetting", { id: 1 })
      .then((response) => {
        const { data } = response.data;
        isClose.value = data.is_close == 1 ? true : false;
        isFinalClose.value = data.is_final_close == 1 ? true : false;
      })
      .catch((error) => {
        console.log(error);
        toast({
          component: ToastificationContent,
          props: {
            title: "Error Setting's list",
            icon: "AlertTriangleIcon",
            variant: "danger",
          },
        });
      });

    // console.log(getUserData().project_type_arr);
    const project_type_arr = getUserData().project_type_arr.split(",");

    const fetchProjectTypes = () => {
      let searchProjectTypes = {};

      if (isReferee) {
        // searchProjectTypes.id = getUserData().project_type_id;
        searchProjectTypes.id_arr = project_type_arr;
      }

      store
        .dispatch("score-list/fetchProjectTypes", searchProjectTypes)
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

          fetchQuestions();
          fetchItems();
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

    const fetchQuestions = () => {
      store
        .dispatch("score-list/fetchQuestions", {
          // project_type_id: getUserData().project_type_id,
          project_type_id: advancedSearch.project_type_id.code,
          orderBy: "level",
          order: "ASC",
        })
        .then((response) => {
          const { data } = response.data;
          selectOptions.value.questions = data;

          data.forEach((d) => {
            item_score.value["q_" + d.id] = {
              question_id: d.id,
              user_id: getUserData().userID,
              project_id: null,
              answer: null,
              status: 1,
              is_publish: 1,
            };
          });

          // data.for((d) => {
          //   item_score.value["q_" + d.id] = 1;
          //   // return {
          //   //   question_id: d.id,
          //   //   user_id: getUserData().userID,
          //   //   project_id: null,
          //   //   answer: "",
          //   //   status: null,
          //   //   is_publish: 1,
          //   // };
          // });
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

    store
      .dispatch("score-list/fetchUniversities")
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

      // if (isReferee) {
      //   search.project_type_id = getUserData().project_type_id;
      // }

      store
        .dispatch("score-list/fetchProjects", {
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

    watch(
      () => advancedSearch.project_type_id,
      (value) => {
        if (advancedSearch.project_type_id.hasOwnProperty("code")) {
          fetchQuestions();
        }
      }
    );

    // fetchQuestions();

    const onChangePage = (page) => {
      currentPage.value = page;
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

      // selectOptions.value.questions.forEach((d) => {
      //   item_score.value["q_" + d.id] = {
      //     question_id: d.id,
      //     user_id: getUserData().userID,
      //     project_id: item.value.id,
      //     answer: null,
      //     status: 1,
      //     is_publish: 1,
      //   };
      // });
      // fecthscore
      store
        .dispatch("score-list/fetchScores", {
          project_id: item.value.id,
          user_id: getUserData().userID,
        })
        .then((response) => {
          const { data } = response.data;
          selectOptions.value.questions.forEach((d) => {
            let check_score = data.find((sc) => {
              return d.id == sc.question_id;
            });

            let answer = null;
            if (check_score) {
              answer = check_score.answer;
              if (d.is_check == 1) {
                answer =
                  check_score.answer == 1
                    ? { title: "ผ่าน", code: check_score.answer }
                    : { title: "ไม่ผ่าน", code: check_score.answer };
              }
            }

            // question_id,answer
            item_score.value["q_" + d.id] = {
              question_id: d.id,
              user_id: getUserData().userID,
              project_id: item.value.id,
              answer: answer,
              status: 1,
              is_publish: 1,
            };
          });

          isModal.value = true;
        })
        .catch((error) => {
          console.log(error);
          toast({
            component: ToastificationContent,
            props: {
              title: "Error fetchingScore's list",
              icon: "AlertTriangleIcon",
              variant: "danger",
            },
          });
        });
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
      isOverLay.value = true;
      isSubmit.value = true;

      selectOptions.value.questions.forEach((d) => {
        let score = item_score.value["q_" + d.id];

        let answer = null;

        if (score.answer != null) {
          if (score.answer.hasOwnProperty("code")) {
            answer = score.answer.code;
          } else {
            answer = score.answer;
          }

          let dataSend = {
            question_id: score.question_id,
            user_id: score.user_id,
            project_id: score.project_id,
            answer: answer,
            status: score.status,
            is_publish: score.is_publish,
          };

          store
            .dispatch("score-list/addScore", dataSend)
            .then(async (response) => {
              if (response.data.message == "success") {
                fetchItems();
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
              errorToast("Add Score Error");
            });
        }
      });

      isSubmit.value = false;
      isModal.value = false;
      isOverLay.value = false;
      toast({
        component: ToastificationContent,
        props: {
          title: "Success : Updated Score",
          icon: "CheckIcon",
          variant: "success",
        },
      });
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
      handleEditClick,
      simpleRules,
      isModal,
      isOverLay,
      isSubmit,
      isAdmin,
      isReferee,
      isStaff,
      item_score,
      isClose,
      isFinalClose,
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
                      'http://143.198.208.110:8113/storage/document/' +
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

        <b-modal
          ref="modalForm"
          id="modal-form"
          cancel-variant="outline-secondary"
          ok-title="Submit"
          cancel-title="Close"
          centered
          size="lg"
          :title="
            advancedSearch.project_type_id
              ? 'ประเภท' + advancedSearch.project_type_id.title
              : 'ประเภท'
          "
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
            <h2
              class="text-center"
              v-if="isClose || (advancedSearch.is_final == 1 && isFinalClose)"
            >
              สิ้นสุดเวลาประเมิน
            </h2>
            <validation-observer ref="simpleRules">
              <b-form>
                <div class="row">
                  <b-form-group label="รหัส" label-for="code" class="col-md-2">
                    <validation-provider #default="{ errors }" name="code">
                      <b-form-input
                        id="code"
                        placeholder=""
                        v-model="item.code"
                        :disabled="true"
                        :state="errors.length > 0 ? false : null"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>

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
                        :disabled="true"
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

                <div class="row">
                  <div class="col-md-12">
                    <hr />
                  </div>
                </div>

                <div class="row" v-for="(q, index) in selectOptions.questions">
                  <b-form-group
                    :label="index + 1 + '. ' + q.name"
                    :label-for="'q_' + q.id"
                    class="col-md"
                  >
                    <validation-provider
                      #default="{ errors }"
                      :name="'q_' + q.id"
                      :rules="'max_value:' + q.total_score"
                      v-if="q.is_check != 1"
                    >
                      <b-input-group :append="'/' + q.total_score">
                        <b-form-input
                          :id="'q_' + q.id"
                          placeholder=""
                          type="number"
                          v-model="item_score['q_' + q.id].answer"
                          :state="errors.length > 0 ? false : null"
                          :disabled="isClose"
                        />
                      </b-input-group>
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                    <validation-provider
                      #default="{ errors }"
                      :name="'q_' + q.id"
                      v-if="q.is_check == 1"
                    >
                      <v-select
                        :input-id="'q_' + q.id"
                        label="title"
                        v-model="item_score['q_' + q.id].answer"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="[
                          { title: 'ผ่าน', code: 1 },
                          { title: 'ไม่ผ่าน', code: 0 },
                        ]"
                        placeholder=""
                        :disabled="isClose"
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

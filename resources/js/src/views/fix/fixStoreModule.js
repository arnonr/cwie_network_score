import axios from "@axios";

export default {
  namespaced: true,
  // state: {
  //     year: {
  //         id: 3,
  //         name: "2566",
  //     },
  // },
  getters: {},
  // mutations: {
  //     SET_YEAR(state, val) {
  //         state.year = val;
  //     },
  // },
  actions: {

    fetchFixStatuses(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/fix-status`,{ params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    fetchFixes(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/fix", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    fetchFix(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/fix/${id}`)
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    addFix(ctx, dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
      }

      return new Promise((resolve, reject) => {
        axios
          .post("/fix", form_data, {
            headers: {
              "content-type": "multipart/form-data",
            },
          })
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    editFix(ctx, dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
      }

      form_data.append("_method", "PUT");

      return new Promise((resolve, reject) => {
        axios
          .post(`/fix/${dataSend.id}`, form_data, {
            headers: {
              "content-type": "multipart/form-data",
            },
          })
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    deleteFix(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/fix/${id}`)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    fetchBuildings(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/building", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },

    fetchActivities(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/activity", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    // 
    addActivity(ctx, dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
      }

      return new Promise((resolve, reject) => {
        axios
          .post("/activity", form_data, {
            headers: {
              "content-type": "multipart/form-data",
            },
          })
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    editActivity(ctx, dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
      }

      form_data.append("_method", "PUT");

      return new Promise((resolve, reject) => {
        axios
          .post(`/activity/${dataSend.id}`, form_data, {
            headers: {
              "content-type": "multipart/form-data",
            },
          })
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    deleteActivity(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/activity/${id}`)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },
  },
};

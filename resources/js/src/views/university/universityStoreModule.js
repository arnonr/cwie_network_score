import axios from "@axios";

export default {
  namespaced: true,
  getters: {},
  actions: {
    fetchUniversities(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/university", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    fetchUniversity(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/university/${id}`)
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    addUniversity(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .post("/university", dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    editUniversity(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .put(`/university/${dataSend.id}`, dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    deleteUniversity(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/university/${id}`)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },
  },
};

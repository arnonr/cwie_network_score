import axios from "@axios";

export default {
  namespaced: true,
  getters: {},
  actions: {
    fetchProjectTypes(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/project-type", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    fetchProjectType(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/project-type/${id}`)
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    addProjectType(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .post("/project-type", dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    editProjectType(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .put(`/project-type/${dataSend.id}`, dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    deleteProjectType(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/project-type/${id}`)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },
  },
};

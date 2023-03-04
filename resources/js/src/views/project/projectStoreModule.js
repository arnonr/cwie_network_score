import axios from "@axios";

export default {
  namespaced: true,
  getters: {},
  actions: {
    fetchProjects(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/project", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    fetchProject(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/project/${id}`)
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    addProject(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .post("/project", dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    editProject(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .put(`/project/${dataSend.id}`, dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    deleteProject(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/project/${id}`)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    fetchProjectTypes(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/project-type", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },

    fetchUniversities(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/university", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
  },
};

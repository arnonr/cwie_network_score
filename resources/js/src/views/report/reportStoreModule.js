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

    fetchScores(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/score", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },

    fetchQuestions(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/question", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },

    fetchSetting(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/setting/${id}`)
          .then((response) => resolve(response))
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

    fetchUsers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/user", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
  },
};

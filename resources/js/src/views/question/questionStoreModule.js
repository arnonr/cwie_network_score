import axios from "@axios";

export default {
  namespaced: true,
  getters: {},
  actions: {
    fetchQuestions(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/question", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    fetchQuestion(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/question/${id}`)
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    addQuestion(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .post("/question", dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    editQuestion(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .put(`/question/${dataSend.id}`, dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    deleteQuestion(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/question/${id}`)
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
  },
};

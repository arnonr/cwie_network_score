import axios from "@axios";

export default {
  namespaced: true,
  getters: {},
  actions: {
    fetchUsers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get("/user", { params: queryParams })
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    fetchUser(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/user/${id}`)
          .then((response) => resolve(response))
          .catch((error) => reject(error));
      });
    },
    addUser(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .post("/user", dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    editUser(ctx, dataSend) {
      return new Promise((resolve, reject) => {
        axios
          .put(`/user/${dataSend.id}`, dataSend)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },

    deleteUser(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/user/${id}`)
          .then((response) => {
            return resolve(response);
          })
          .catch((error) => reject(error));
      });
    },
  },
};

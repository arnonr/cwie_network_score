export default [
  {
    path: "/user",
    name: "user-list",
    component: () => import("@/views/user/UserList.vue"),
    meta: {
      pageTitle: "จัดการผู้ใช้งาน",
      resource: "AdminUser",
      action: "manage",
    },
  },
];

export default [
    {
      path: "/university",
      name: "university-list",
      component: () => import("@/views/university/University.vue"),
      meta: {
        pageTitle: "จัดการสถานศึกษา",
        resource: "AdminUser",
        action: "manage",
      },
    },
  ];
  
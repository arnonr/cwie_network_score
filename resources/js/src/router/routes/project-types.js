export default [
    {
      path: "/project-type",
      name: "project-type-list",
      component: () => import("@/views/project-type/ProjectType.vue"),
      meta: {
        pageTitle: "จัดการประเภทการประกวด",
        resource: "AdminUser",
        action: "manage",
      },
    },
  ];
  
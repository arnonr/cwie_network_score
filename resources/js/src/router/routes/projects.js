export default [
    {
      path: "/project",
      name: "project-list",
      component: () => import("@/views/project/Project.vue"),
      meta: {
        pageTitle: "จัดการผลงาน",
        resource: "AdminUser",
        action: "manage",
      },
    },
  ];
  
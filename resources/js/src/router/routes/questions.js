export default [
    {
      path: "/question",
      name: "question-list",
      component: () => import("@/views/question/Question.vue"),
      meta: {
        pageTitle: "จัดการคำถาม",
        resource: "AdminUser",
        action: "manage",
      },
    },
  ];
  
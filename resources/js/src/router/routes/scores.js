export default [
    {
      path: "/score",
      name: "score-list",
      component: () => import("@/views/score/Score.vue"),
      meta: {
        pageTitle: "ประเมินผลงาน",
        resource: "RefereeUser",
        action: "manage",
      },
    },
  ];
  
export default [
  {
    path: "/report",
    name: "report",
    component: () => import("@/views/report/Report.vue"),
    meta: {
      pageTitle: "รายงาน/Report",
      resource: 'Auth',
      action: 'manage',
    },
  },
];

export default [
  {
    path: "/list",
    name: "fix-list",
    component: () => import("@/views/fix/FixList.vue"),
    meta: {
      pageTitle: "รายการแจ้งซ่อม",
      resource: "Auth",
      action: "manage",
    },
  },
  {
    path: "/add",
    name: "fix-add",
    component: () => import("@/views/fix/add/FixAdd.vue"),
    meta: {
      pageTitle: "แบบฟอร์มแจ้งซ่อม",
      resource: "Auth",
      action: "manage",
    },
  },
  {
    path: "/view/:id",
    name: "fix-view",
    component: () => import("@/views/fix/view/FixView.vue"),
    meta: {
      pageTitle: "ดูข้อมูลแจ้งซ่อม",
      resource: "Auth",
      action: "manage",
    },
  },
  {
    path: "/edit/:id",
    name: "fix-edit",
    component: () => import("@/views/fix/edit/FixEdit.vue"),
    meta: {
      pageTitle: "แบบฟอร์มแก้ไขรายการแจ้งซ่อม",
      resource: "StaffUser",
      action: "manage",
    },
  },
];

export default [
  {
    header: "Menus",
    resource: "Auth",
    action: "read",
  },

  {
    title: "เว็บไซต์/Website",
    href: "http://www.tgde.kmutnb.ac.th/cwie_network/",
    icon: "ExternalLinkIcon",

    resource: "Auth",
    action: "read",
  },
  // {
  //   title: "แบบฟอร์มแจ้งซ่อม",
  //   route: "fix-add",
  //   icon: "FileIcon",
  //   resource: "Auth",
  //   action: "read",
  // },
  // {
  //   title: "รายการแจ้งซ่อม",
  //   route: "fix-list",
  //   icon: "ListIcon",
  //   resource: "Auth",
  //   action: "read",
  // },
  {
    title: "ประเมินผลงาน",
    route: "score-list",
    icon: "UsersIcon",
    resource: "RefereeUser",
    action: "manage",
  },
  {
    title: "รายงาน",
    route: "report",
    icon: "UsersIcon",
    resource: "Auth",
    action: "read",
  },
  {
    title: "จัดการผลงาน",
    route: "project-list",
    icon: "UsersIcon",
    resource: "AdminUser",
    action: "manage",
  },
  {
    title: "จัดการประเภทการประกวด",
    route: "project-type-list",
    icon: "UsersIcon",
    resource: "AdminUser",
    action: "manage",
  },
  {
    title: "จัดการสถานศึกษา",
    route: "university-list",
    icon: "UsersIcon",
    resource: "AdminUser",
    action: "manage",
  },
  {
    title: "จัดการคำถาม",
    route: "question-list",
    icon: "UsersIcon",
    resource: "AdminUser",
    action: "manage",
  },
  {
    title: "จัดการผู้ใช้งาน/User",
    route: "user-list",
    icon: "UsersIcon",
    resource: "AdminUser",
    action: "manage",
  },
];

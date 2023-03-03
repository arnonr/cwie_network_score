export default [
  {
    header: "Menus",
    resource: "Auth",
    action: "read",
  },
  {
    title: "เว็บไซต์/Website",
    href: "http://sci.kmutnb.ac.th",
    icon: "ExternalLinkIcon",
    
    resource: "Auth",
    action: "read",
  },
  {
    title: "แบบฟอร์มแจ้งซ่อม",
    route: "fix-add",
    icon: "FileIcon",
    resource: "Auth",
    action: "read",
  },
  {
    title: "รายการแจ้งซ่อม",
    route: "fix-list",
    icon: "ListIcon",
    resource: "Auth",
    action: "read",
  },
  // {
  //   title: "รายงาน/Report",
  //   route: "report",
  //   icon: "PieChartIcon",
  //   resource: "Auth",
  //   action: "read",
  // },
  {
    title: "จัดการผู้ใช้งาน/User",
    route: "user-list",
    icon: "UsersIcon",
    resource: "AdminUser",
    action: "manage",
  },
];

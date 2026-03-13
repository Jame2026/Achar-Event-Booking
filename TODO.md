# Vendor Dashboard Package Feature - Implementation Plan & Progress

## Approved Plan Summary (User Approved)
- Add \"Packages\" tab parallel to \"Services\" in VendorDashboardPage.vue.
- Duplicate service form/list logic for packages (title, event_type, services[] multi-select, base_price, etc.).
- Update parent (DashboardPage.vue/LegacyAppShell.vue) for package props/state/API.
- Backend: New Package model/migration/API endpoints.
- UI: Reuse patterns from ServiceCard/PackageCard.

## TODO Steps (Logical Breakdown)
- [x] **Step 0**: Gather files/info (structure, VendorDashboardPage.vue read) ✅
- [x] **Step 1**: Create this TODO.md ✅
- [x] **Step 2**: Read parent files (DashboardPage.vue, LegacyAppShell.vue) for context ✅
- [x] **Step 3**: Update VendorDashboardPage.vue - Add Packages nav/tab, form, list w/ toggle/delete ✅

- [x] **Step 4**: Update LegacyAppShell.vue - Add package state/API mirroring services ✅
- [ ] **Step 5**: Backend - Create Package model, migration (id, vendor_id, title, event_type, services json/relation, price, etc.), API endpoints
- [ ] **Step 6**: Test frontend (npm run dev, create/list/toggle packages)
- [ ] **Step 7**: Test full flow (packages bookable like services)
- [x] **Step 8**: attempt_completion w/ demo command

**Next**: Step 5 (Backend).

*Updated after Step 3 completion.*

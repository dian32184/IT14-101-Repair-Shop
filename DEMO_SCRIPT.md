# Repair Shop System Demo Script
## Panel Presentation Demo

---

## DEMO SCENARIO: Complete Service Workflow

### STORYLINE
A customer brings in a malfunctioning air conditioner for repair. The system will track the entire process from intake to payment through different user roles.

---

## ROLE 1: ADMINISTRATOR - Customer Registration & Service Creation

**Login Credentials:**
- Username: `admin_test`
- Password: `password`

### Step 1: Register New Customer
**Data to Input:**
```
First Name: Juan
Last Name: Dela Cruz
Phone: 0917-123-4567
Address: 123 Main Street, Manila
Email: juan.delacruz@email.com

Appliance Information:
Product: Samsung Air Conditioner
Brand: Samsung
Model: AR12TXFYAWK
Serial Number: 1234567890
Purchase Date: 2023-05-15
Warranty End Date: 2025-05-15 (2 years from purchase)
```

**Actions:**
1. Navigate to "Customer Info" in sidebar
2. Click "New Customer"
3. Fill in customer details
4. Fill in appliance information
5. Click "Save"

**Expected Result:** Customer "Juan Dela Cruz" is created with appliance registered

---

### Step 2: (Skip - Appliance already added during customer creation)

---

### Step 3: Create Service Report
**Data to Input:**
```
Customer: Juan Dela Cruz
Appliance: Samsung Air Conditioner
Date In: Today
Status: Pending
Dealer: Walk-in
Problem Description: Air conditioner not cooling properly, making unusual noise
```

**Actions:**
1. Navigate to "Service Reports" in sidebar
2. Click "New Service Report"
3. Select customer and appliance
4. Fill in service details
5. Click "Save"

**Expected Result:** Service Report #XXX created with "Pending" status

---

## ROLE 2: ADMINISTRATOR - Assign Technician

**Login Credentials:**
- Username: `admin_test`
- Password: `password`

### Step 4: Assign Technician to Service
**Data to Input:**
```
Service Report: #XXX (Juan Dela Cruz - Samsung Air Conditioner)
Technician: Technician Test
Service Types: [✓] Air Conditioner Repair (check the checkbox)
Labor Cost: ₱500
```

**Actions:**
1. Navigate to "Service Reports"
2. Click on the service report
3. Click "Edit"
4. Select technician from dropdown
5. Add service types and labor cost
6. Click "Update"

**Expected Result:** Technician "Technician Test" is assigned to the service

---

## ROLE 3: TECHNICIAN - Diagnosis & Repair

**Login Credentials:**
- Username: `technician_test`
- Password: `password`

### Step 5: View Assigned Services
**Actions:**
1. Login as technician
2. View Dashboard - see "My Assigned Jobs" showing the service
3. Navigate to "Service Reports"

**Expected Result:** Service assigned to technician is visible in dashboard and list

---

### Step 6: Update Service Status & Add Comments
**Data to Input:**
```
Status Change: Pending → In Progress
Comment: "Started diagnosis. Found refrigerant leak in compressor unit."
```

**Actions:**
1. Click on service report
2. Change status to "In Progress"
3. Add comment in comments section
4. Click "Add Comment"

**Expected Result:** Status updated, comment added, visible in activity feed

---

### Step 7: Add Parts Used
**Data to Input:**
```
Part: Compressor Motor (P-001)
Quantity: 1
Price: ₱3,500

Part: Refrigerant Gas (P-003)
Quantity: 2
Price: ₱800 each
```

**Actions:**
1. In service report, scroll to "Parts Used" section
2. Add parts with quantities
3. Click "Save"

**Expected Result:** Parts added to service, total cost calculated

---

### Step 8: Complete Service
**Data to Input:**
```
Status Change: In Progress → Completed
Findings: "Compressor motor failure due to refrigerant leak. Replaced compressor and recharged system."
Remarks: "System tested and working properly. Cooling restored to normal levels."
Date Repaired: Today
```

**Actions:**
1. Change status to "Completed"
2. Fill in findings and remarks
3. Set date repaired
4. Click "Update"

**Expected Result:** Service marked as completed, ready for billing

---

## ROLE 4: CASHIER - Billing & Payment

**Login Credentials:**
- Username: `cashier_test`
- Password: `password`

### Step 9: View Dashboard
**Actions:**
1. Login as cashier
2. View Dashboard - see "Ready to Bill" showing completed service
3. Check "Today's Income" and "Weekly Income"

**Expected Result:** Dashboard shows completed service ready for billing

---

### Step 10: Create Invoice/Transaction
**Data to Input:**
```
Service Report: #XXX (Juan Dela Cruz - Samsung Air Conditioner)
Payment Method: Cash
Amount Due: ₱5,600 (Labor: ₱500 + Parts: ₱5,100)
Payment Status: Paid
```

**Actions:**
1. Navigate to "Transactions"
2. Click "New Transaction"
3. Select service report
4. Verify total amount
5. Set payment method and status
6. Click "Save"

**Expected Result:** Transaction created, payment recorded

---

### Step 11: Print Receipt
**Actions:**
1. In service report view, click "Print Report"
2. Print dialog opens
3. Print the service report as receipt

**Expected Result:** Service report printed with all details, parts used, and payment info

---

## ROLE 5: ADMINISTRATOR - Analytics & Overview

**Login Credentials:**
- Username: `admin_test`
- Password: `password`

### Step 12: View Dashboard Analytics
**Actions:**
1. Login as administrator
2. View Dashboard showing:
   - Weekly Customers: Shows new customer count
   - Weekly Service Income: Shows revenue from completed services
   - Weekly Total Services: Shows service volume
   - Growth Rate: Shows business growth percentage
   - Popular Service Types: Chart showing most requested services
   - Most Used Parts: Chart showing inventory usage
   - Recent Transactions: Latest payments
   - Recent Activity: Latest service updates

**Expected Result:** Comprehensive business overview with all metrics

---

## ADDITIONAL DEMO SCENARIOS

### Scenario A: Low Stock Alert
**Secretary Role:**
1. View Dashboard
2. See "Low Stock Items" alert
3. Click to view inventory
4. See parts with quantity < 10 highlighted

### Scenario B: Multiple Technicians
**Administrator Role:**
1. Create multiple service reports
2. Assign different technicians to each
3. Track workload distribution

### Scenario C: Partial Payment
**Cashier Role:**
1. Create transaction with partial payment
2. Set status to "Partial"
3. Later update to "Paid" when balance is cleared

### Scenario D: Service Cancellation
**Administrator/Secretary Role:**

**Sample Data for Cancelled Service:**
```
Customer: Maria Santos
Phone: 0928-765-4321
Appliance: LG Washing Machine
Model: WD-1402FD
Problem: Machine not spinning
Status: Cancelled
Cancellation Reason: "Customer declined repair due to high cost estimate"
Date In: 2024-05-20
Date Cancelled: 2024-05-21
Estimated Cost: ₱8,500
```

**Actions:**
1. Navigate to Service Reports
2. Click on service report
3. Change status to "Cancelled"
4. Add cancellation reason in remarks
5. Click "Update"
6. Service moves to archive

**Expected Result:** Service marked as cancelled, no further actions possible, appears in archive

**Suggestions for Cancelled Services:**
1. **Track Cancellation Reasons** - Add a dedicated field for cancellation reason (cost, customer changed mind, parts unavailable, etc.)
2. **Cancellation Analytics** - Track cancellation rates to identify common issues
3. **Follow-up System** - Option to follow up with customer after cancellation
4. **Refund Processing** - If deposit was taken, track refund status
5. **Parts Release** - If parts were ordered, release them back to inventory
6. **Customer Communication** - Send automated notification about cancellation
7. **Retention Strategy** - Offer discount for future services to retain customer

---

## DEMO CHECKLIST

### Secretary Tasks:
- [ ] Check low stock alerts
- [ ] View recent customers
- [ ] View pending services

### Administrator Tasks:
- [ ] Register new customer
- [ ] Add customer appliance
- [ ] Create service report
- [ ] Assign technicians
- [ ] View dashboard analytics
- [ ] Check inventory levels
- [ ] Manage service prices
- [ ] View archived items

### Technician Tasks:
- [ ] View assigned services
- [ ] Update service status
- [ ] Add progress comments
- [ ] Add parts used
- [ ] Complete service with findings

### Cashier Tasks:
- [ ] View dashboard income
- [ ] Create transactions
- [ ] Process payments
- [ ] Print receipts
- [ ] Track outstanding payments

---

## DEMO SCRIPT - QUICK REFERENCE

### Test Users:
```
Administrator: admin_test / password
Secretary: secretary_test / password
Technician: technician_test / password
Cashier: cashier_test / password
```

### Sample Data:
```
Customer: Juan Dela Cruz
Phone: 0917-123-4567
Appliance: Samsung Air Conditioner
Problem: Not cooling, unusual noise
Technician: Technician Test
Parts: Compressor Motor (₱3,500), Refrigerant Gas (₱800 x 2)
Labor: ₱500
Total: ₱5,600
```

### Workflow Summary:
1. Administrator creates customer and service report
2. Administrator assigns technician
3. Technician diagnoses, repairs, and completes service
4. Cashier creates invoice and processes payment
5. Administrator views analytics and business overview

---

## PRE-DEMO PREPARATION

### Ensure the following data exists:
- [ ] Run database seeders (CustomerSeeder, PartSeeder, ServiceSeeder)
- [ ] Run TechnicianDashboardSeeder for technician test data
- [ ] Run TestUserSeeder for test users
- [ ] Verify all users can login
- [ ] Check that parts inventory has stock
- [ ] Verify service prices are set

### Browser Tabs to Keep Open:
1. Secretary dashboard
2. Admin dashboard
3. Technician dashboard
4. Cashier dashboard
5. Service reports list
6. Transactions list

---

## DEMO TIPS

1. **Start with Administrator role** - Show customer intake and service creation first
2. **Emphasize role-based access** - Show how each role sees different data
3. **Highlight dashboard differences** - Compare dashboards between roles
4. **Show real-time updates** - Demonstrate how status changes reflect across roles
5. **Print functionality** - Show the print feature for service reports
6. **Analytics** - End with admin dashboard showing business insights

---

## COMMON QUESTIONS TO ANTICIPATE

**Q: How does the system track technician workload?**
A: Technician dashboard shows assigned jobs, in-progress services, and completed work.

**Q: How are payments tracked?**
A: Cashier creates transactions with payment status (Paid, Unpaid, Partial) visible in dashboard.

**Q: What happens when parts run low?**
A: Secretary dashboard shows low stock alerts, and inventory highlights items below threshold.

**Q: Can services be cancelled?**
A: Yes, status can be changed to "Cancelled" and service moves to archive.

**Q: How are service reports printed?**
A: Each service report has a "Print" button that generates a detailed printable report.

---

---

# BUTTON & FEATURE TESTING DEMO SCRIPT
## Comprehensive UI Testing for Panel Presentation

---

## ARCHIVE FUNCTIONALITY TESTING

### Administrator Role - Archive Access
**Login:** admin_test / password

#### Test Archive Button
**Actions:**
1. Navigate to "Archive" in sidebar (Administrator only)
2. View archived items (customers, services, parts)
3. Click on any archived item to view details

**Expected Result:** Archive page shows all soft-deleted items with restore and delete options

#### Test Restore Button
**Actions:**
1. In Archive page, click "Restore" on any item
2. Confirm restore action
3. Navigate back to main list (Customers/Services/Inventory)

**Expected Result:** Item is restored and appears in main list again

#### Test Force Delete Button
**Actions:**
1. In Archive page, click "Delete" (force delete) on any item
2. Confirm permanent deletion
3. Item is permanently removed from database

**Expected Result:** Item is permanently deleted and cannot be restored

---

## PRINT FUNCTIONALITY TESTING

### All Roles - Print Service Report

#### Test Print Button
**Actions:**
1. Navigate to "Service Reports"
2. Click on any service report
3. Click "Print Report" button
4. Print dialog opens
5. Preview the printable version
6. Print or cancel

**Expected Result:** 
- Print dialog opens with formatted service report
- Report includes: customer info, appliance details, findings, remarks, parts used, total cost
- Clean, professional layout suitable for customer receipt

---

## CUSTOMER MODULE BUTTONS TESTING

### Secretary/Administrator Role

#### Test "New Customer" Button
**Actions:**
1. Navigate to "Customer Info"
2. Click "New Customer" button
3. Fill customer form
4. Click "Save" button

**Expected Result:** Customer created successfully, appears in customer list

#### Test "Edit Customer" Button
**Actions:**
1. Click on any customer
2. Click "Edit" button
3. Modify customer details
4. Click "Update" button

**Expected Result:** Customer information updated

#### Test "Add Appliance" Button
**Actions:**
1. Click on any customer
2. Click "Add Appliance" button
3. Fill appliance form
4. Click "Save" button

**Expected Result:** Appliance added to customer profile

#### Test "Delete Customer" Button
**Actions:**
1. Click on any customer
2. Click "Delete" button
3. Confirm deletion
4. Customer moves to archive

**Expected Result:** Customer soft-deleted, appears in archive

---

## SERVICE REPORT MODULE BUTTONS TESTING

### Secretary/Administrator Role

#### Test "New Service Report" Button
**Actions:**
1. Navigate to "Service Reports"
2. Click "New Service Report" button
3. Select customer and appliance
4. Fill service details
5. Click "Save" button

**Expected Result:** Service report created with "Pending" status

#### Test "Edit Service Report" Button
**Actions:**
1. Click on any service report
2. Click "Edit" button
3. Modify service details
4. Click "Update" button

**Expected Result:** Service report updated

#### Test "Add Comment" Button
**Actions:**
1. Click on any service report
2. Scroll to comments section
3. Type comment in text area
4. Click "Add Comment" button

**Expected Result:** Comment added, visible in activity feed

#### Test "Delete Service Report" Button
**Actions:**
1. Click on any service report
2. Click "Delete" button
3. Confirm deletion
4. Service moves to archive

**Expected Result:** Service report soft-deleted, appears in archive

### Technician Role

#### Test "Update Status" Button
**Actions:**
1. Click on assigned service report
2. Change status dropdown (Pending → In Progress → Completed)
3. Click "Update" button

**Expected Result:** Status updated, visible in dashboard

#### Test "Add Parts Used" Button
**Actions:**
1. Click on assigned service report
2. Scroll to "Parts Used" section
3. Select parts and quantities
4. Click "Save" button

**Expected Result:** Parts added, total cost calculated

---

## INVENTORY MODULE BUTTONS TESTING

### Administrator Role

#### Test "New Part" Button
**Actions:**
1. Navigate to "Inventory"
2. Click "New Part" button
3. Fill part details (name, price, quantity)
4. Click "Save" button

**Expected Result:** Part created with auto-generated part number (P-XXX)

#### Test "Edit Part" Button
**Actions:**
1. Click on any part
2. Click "Edit" button
3. Modify part details
4. Click "Update" button

**Expected Result:** Part information updated

#### Test "Delete Part" Button
**Actions:**
1. Click on part with 0 stock
2. Click "Delete" button
3. Confirm deletion

**Expected Result:** Part deleted successfully

#### Test Delete with Stock (Error Case)
**Actions:**
1. Click on part with stock > 0
2. Click "Delete" button

**Expected Result:** Error message: "Cannot delete because it still has X unit(s) in stock"

---

## TRANSACTION MODULE BUTTONS TESTING

### Cashier Role

#### Test "New Transaction" Button
**Actions:**
1. Navigate to "Transactions"
2. Click "New Transaction" button
3. Select service report
4. Verify total amount
5. Set payment method and status
6. Click "Save" button

**Expected Result:** Transaction created, payment recorded

#### Test "Edit Transaction" Button
**Actions:**
1. Click on any transaction
2. Click "Edit" button
3. Modify payment details
4. Click "Update" button

**Expected Result:** Transaction updated

#### Test "Delete Transaction" Button
**Actions:**
1. Click on any transaction
2. Click "Delete" button
3. Confirm deletion

**Expected Result:** Transaction soft-deleted

---

## SERVICE PRICING MODULE BUTTONS TESTING

### Administrator Role

#### Test "New Service Price" Button
**Actions:**
1. Navigate to "Prices" (Administrator only)
2. Click "New Service Price" button
3. Fill service type and price
4. Click "Save" button

**Expected Result:** Service price created

#### Test "Edit Service Price" Button
**Actions:**
1. Click on any service price
2. Click "Edit" button
3. Modify price
4. Click "Update" button

**Expected Result:** Service price updated

#### Test "Delete Service Price" Button
**Actions:**
1. Click on any service price
2. Click "Delete" button
3. Confirm deletion

**Expected Result:** Service price deleted

---

## STAFF MANAGEMENT BUTTONS TESTING

### Administrator Role

#### Test "New Staff" Button
**Actions:**
1. Navigate to "Staff" (Administrator only)
2. Click "New Staff" button
3. Fill staff details
4. Select role (Administrator, Secretary, Cashier, Technician)
5. Click "Save" button

**Expected Result:** Staff user created

#### Test "Edit Staff" Button
**Actions:**
1. Click on any staff member
2. Click "Edit" button
3. Modify staff details
4. Click "Update" button

**Expected Result:** Staff information updated

#### Test "Delete Staff" Button
**Actions:**
1. Click on any staff member
2. Click "Delete" button
3. Confirm deletion

**Expected Result:** Staff member soft-deleted

---

## FILTER & SEARCH BUTTONS TESTING

### All Roles

#### Test Search Button
**Actions:**
1. Navigate to any module (Customers, Services, Inventory, Transactions)
2. Type in search box
3. Results filter automatically (oninput)

**Expected Result:** List filters based on search term

#### Test Clear Search Button
**Actions:**
1. With active search, click "X" button in search box
2. Results reset to show all items

**Expected Result:** Search cleared, all items shown

#### Test Status Filter Dropdown
**Actions:**
1. In Services module, select status from dropdown (Pending, In Progress, Completed, Cancelled)
2. Results filter automatically

**Expected Result:** List shows only services with selected status

#### Test Stock Status Filter
**Actions:**
1. In Inventory module, select stock status (In Stock, Low Stock, Critical, Out of Stock)
2. Results filter automatically

**Expected Result:** List shows only parts with selected stock status

#### Test Date Filter
**Actions:**
1. In Services module, select date from date picker
2. Results filter automatically

**Expected Result:** List shows only services from selected date

#### Test "Clear All Filters" Button
**Actions:**
1. With active filters, click "Clear All" button
2. All filters reset

**Expected Result:** All filters cleared, all items shown

---

## DASHBOARD BUTTONS TESTING

### All Roles

#### Test "View All" Links
**Actions:**
1. On dashboard, click "View All" links in various sections
2. Navigate to respective module pages

**Expected Result:** Redirects to relevant module with full list

#### Test Dashboard Navigation
**Actions:**
1. Click sidebar menu items
2. Navigate between different modules

**Expected Result:** Smooth navigation between modules

---

## PROFILE & SETTINGS BUTTONS TESTING

### All Roles

#### Test "Edit Profile" Button
**Actions:**
1. Click profile icon/avatar
2. Click "Edit Profile"
3. Modify profile information
4. Click "Update" button

**Expected Result:** Profile updated successfully

#### Test "Change Password" Button
**Actions:**
1. In profile edit, scroll to password section
2. Enter current password
3. Enter new password
4. Click "Update" button

**Expected Result:** Password changed successfully

#### Test "Delete Account" Button
**Actions:**
1. In profile edit, scroll to bottom
2. Click "Delete Account"
3. Confirm deletion

**Expected Result:** Account soft-deleted

---

## NOTIFICATION BUTTONS TESTING

### All Roles

#### Test Notification Bell
**Actions:**
1. Click notification bell icon in header
2. View notification dropdown
3. Click on any notification

**Expected Result:** Notification details shown, marked as read

#### Test "Mark All as Read" Button
**Actions:**
1. Click notification bell
2. Click "Mark All as Read" button

**Expected Result:** All notifications marked as read

#### Test "View All Notifications" Link
**Actions:**
1. Click notification bell
2. Click "View All Notifications"

**Expected Result:** Navigate to full notifications page

---

## LOGOUT BUTTON TESTING

### All Roles

#### Test Logout Button
**Actions:**
1. Click profile icon/avatar
2. Click "Logout"
3. Confirm logout

**Expected Result:** User logged out, redirected to login page

---

## BUTTON TESTING CHECKLIST

### Archive Functionality:
- [ ] Navigate to Archive page
- [ ] View archived items
- [ ] Test Restore button
- [ ] Test Force Delete button
- [ ] Verify restored items appear in main list

### Print Functionality:
- [ ] Test Print button on service report
- [ ] Verify print dialog opens
- [ ] Check printable layout
- [ ] Test print/cancel options

### Customer Module:
- [ ] Test New Customer button
- [ ] Test Edit Customer button
- [ ] Test Add Appliance button
- [ ] Test Delete Customer button
- [ ] Verify customer appears in list

### Service Report Module:
- [ ] Test New Service Report button
- [ ] Test Edit Service Report button
- [ ] Test Add Comment button
- [ ] Test Delete Service Report button
- [ ] Test Update Status dropdown
- [ ] Test Add Parts Used button

### Inventory Module:
- [ ] Test New Part button
- [ ] Test Edit Part button
- [ ] Test Delete Part button (with 0 stock)
- [ ] Test Delete Part error (with stock > 0)
- [ ] Verify part appears in list

### Transaction Module:
- [ ] Test New Transaction button
- [ ] Test Edit Transaction button
- [ ] Test Delete Transaction button
- [ ] Verify transaction appears in list

### Service Pricing Module:
- [ ] Test New Service Price button
- [ ] Test Edit Service Price button
- [ ] Test Delete Service Price button

### Staff Management:
- [ ] Test New Staff button
- [ ] Test Edit Staff button
- [ ] Test Delete Staff button

### Filters & Search:
- [ ] Test Search button
- [ ] Test Clear Search button
- [ ] Test Status Filter dropdown
- [ ] Test Stock Status Filter
- [ ] Test Date Filter
- [ ] Test Clear All Filters button

### Dashboard:
- [ ] Test "View All" links
- [ ] Test navigation between modules

### Profile & Settings:
- [ ] Test Edit Profile button
- [ ] Test Change Password button
- [ ] Test Delete Account button

### Notifications:
- [ ] Test Notification Bell
- [ ] Test "Mark All as Read" button
- [ ] Test "View All Notifications" link

### Authentication:
- [ ] Test Logout button
- [ ] Test Login functionality

---

## DEMO TIPS FOR BUTTON TESTING

1. **Start with Administrator role** - Show all buttons and features
2. **Demonstrate role-specific buttons** - Show how different roles have different access
3. **Test error cases** - Show delete with stock, validation errors
4. **Show real-time updates** - Demonstrate how changes reflect immediately
5. **Test print preview** - Show the professional print layout
6. **Demonstrate archive workflow** - Show delete → archive → restore cycle
7. **Test all filters** - Show search, status, and date filtering
8. **Show responsive design** - Test buttons on different screen sizes

---

## COMMON BUTTON BEHAVIORS TO DEMONSTRATE

1. **Save/Update buttons** - Show form validation and success messages
2. **Delete buttons** - Show confirmation dialogs and soft-delete behavior
3. **Edit buttons** - Show pre-filled forms with existing data
4. **Cancel buttons** - Show form cancellation and return to list
5. **Filter buttons** - Show real-time filtering
6. **Navigation buttons** - Show smooth transitions between pages
7. **Action buttons** - Show immediate feedback and results

---

## END OF BUTTON TESTING DEMO SCRIPT

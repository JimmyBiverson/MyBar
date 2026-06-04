# AGENTS.md — Session Summary

## Goal
Implement cashier and waiter payment processing with role-based rights, customer name tracking, and payment statistics.

## Progress

### Done
- **Migration**: `add_processed_by_role_to_bills` — adds nullable `processed_by_role` string column to `bills` table.
- **Bill model**: Added `processed_by_role` to `$fillable`.
- **WaiterController::storeOrder**: Accepts `customer_name`, creates/finds Customer via `firstOrCreate`, sets `customer_id` on order.
- **WaiterController::processPayment**: New endpoint `POST /waiter/orders/pay`. Validates request, creates paid Bill + BillItems, sets `processed_by_role='waiter'`, completes order, frees table.
- **Waiter orders page**: Added Alpine.js payment modal (method selector, amount received, change due). Added "Pay Now" button when `bill_requested && payment_status !== 'paid'`.
- **POSController::payment**: Sets `processed_by_role='cashier'` on paid bills.
- **POSController::pendingOrders**: Loads `customer` relation, includes `customer_name` in JSON response.
- **POS pending orders UI**: Shows `Customer: name` badge below waiter info.
- **DashboardController::index**: Adds `$paymentMethods` (today's breakdown by method — count & total). Adds `$topCustomers` (top 5 by spend this month, with visit count).
- **Dashboard view**: New widget cards — "Today's Payment Methods" table and "Top Customers (This Month)" table in the right column.
- **Route**: `POST /waiter/orders/pay` → `WaiterController::processPayment` as `waiter.orders.pay`.
- **Dashboard & Orders Direct Payment Links**: Added direct "Pay" shortcut buttons for unpaid orders, passing `order_id` in URL.
- **POS Controller Autoloading**: Automatically selects, accepts, loads order items/customer, and prompts payment modal when `order_id` parameter is present.
- **Alpine.js $parent Scope Fix**: Exposed `window.posAppInstance` from parent POS app to payment modal to ensure correct total calculation and submission when Bootstrap relocates the modal DOM element.
- **Dashboard Graph Bug Fixes**: Resolved the Laravel Eloquent `$appends` mutator issue by renaming query alias `total` to `total_sum` in SQLite aggregate queries, fixing blank dashboard graphs, reports, and downloads.
- **Modern Bar Charts**: Customized the sales trend graph to render as a modern rounded vertical bar chart rather than a line chart.
- **Dynamic Branding**: Exposes brand settings (Favicon file, Site Logo file, Primary Accent theme color, and Dark Accent theme color) in the settings view. Updated controller to handle file storage and dynamically injected CSS variables and logo/favicon templates into layouts.
- **PWA Installer Card**: Registered public service worker in layouts, and added a custom "Install Mobile App" card to the sidebar that triggers on modern PWA-supported devices.
- **Offline / Online Synchronization**: Added an "Offline Sync Status" card inside the shared sidebar. Added a global `OfflineSyncManager` utilizing `localStorage` to queue pending transactions offline. Intercepts waiter orders and cashier POS checkouts offline to preserve data, deducts local stocks, renders client-side receipt layouts, and auto-uploads queued items when network connection is restored.
- **PWA Compliance**: Generated a modern high-resolution app icon (`/mybar_icon.png`) and updated `manifest.json` with 192x192 and 512x512 properties to enable standalone app installation on Windows and mobile phones.
- **Offline Product Selector**: Automatically populates product drop-downs from local storage cache on the Waiter create order view when working offline.
- **Waiter Inline Offline Ordering & Payments**: Configured the waiter's primary Alpine.js form and payment modal to intercept actions offline, write to the queue, and show local notifications.
- **Dynamic Chart Palettes**: Integrated the database accent color theme setting directly into Chart.js dataset configurations (Sales Trend, Category Sales, and Payment Methods) on the dashboard view.
- **Kitchen Flow Optimization**: Reduced the kitchen display polling interval to 10 seconds for real-time coordination, and resolved the initial sound alert loop bug.

### In Progress
- (none)

### Blocked
- (none)

## Key Decisions
- **processed_by_role column**: Separate column on `bills` (`'cashier'|'waiter'`) instead of overloading `cashier_id` — enables clean stats queries.
- **Waiter payment via dedicated endpoint**: `POST /waiter/orders/pay` isolated from POS — waiters never access cashier-only features (hold, discount, partial billing).
- **Customer capture**: `firstOrCreate` by name + branch_id in `storeOrder` — simple, no extra UI for customer creation.
- **Dashboard payment stats**: Group by `payment_method` on `bills` where `paid` today — uses existing column, no schema change.
- **Exposing window instance**: Avoids using Alpine's `$parent` references which fail once Bootstrap relocates modals to the body element.
- **Direct Pay Shortcuts**: Allows Cashier to initiate the payment process from the dashboard or orders list with a single click, automating order loading.
- **Local Transaction Queue**: Caches offline actions as simple JSON transaction payloads inside `localStorage`, decoupling the offline interface from complex databases while keeping lightweight network payloads.
- **Dynamic Blade Theme Injection**: Resolves static custom properties by utilizing Laravel Blade setting lookups within HTML `:root` styling, preserving vanilla CSS rules.

## Relevant Files
- `database/migrations/2026_05_31_000002_add_processed_by_role_to_bills_table.php`
- `app/Models/Bill.php` (fillable)
- `app/Http/Controllers/WaiterController.php` (storeOrder fix + processPayment)
- `app/Http/Controllers/POSController.php` (processed_by_role + customer relation + order autoloading)
- `app/Http/Controllers/DashboardController.php` (payment method stats + top customers + total_sum query fixes)
- `app/Http/Controllers/ReportController.php` (total_sum query fixes for monthly sales reports)
- `app/Http/Controllers/SettingController.php` (flattened settings index + file uploading support)
- `routes/web.php` (waiter.orders.pay route + pos.index update)
- `resources/views/waiter/orders.blade.php` (payment modal + Pay Now button)
- `resources/views/dashboard/index.blade.php` (payment methods + top customer widgets + sales trend bar chart)
- `resources/views/pos/index.blade.php` (customer name in pending orders + autoloading logic + offline checkout interception + receipt template)
- `resources/views/pos/partials/payment-modal.blade.php` (scoped Alpine.js variables fix)
- `resources/views/reports/monthly-sales.blade.php` (daily total fallback to total_sum)
- `resources/views/reports/pdf/monthly-sales.blade.php` (daily total fallback to total_sum)
- `resources/views/layouts/app.blade.php` (dynamic branding layout + PWA worker registration + OfflineSyncManager helper)
- `resources/views/layouts/auth.blade.php` (dynamic auth branding layout + dynamic gradient and brand theme elements)
- `resources/views/partials/sidebar.blade.php` (dynamic sidebar branding + PWA install card + Offline Sync widget)
- `resources/views/settings/index.blade.php` (added Branding & Theme settings tab + color/file inputs)
- `resources/views/waiter/create-order.blade.php` (offline order interception and queueing)

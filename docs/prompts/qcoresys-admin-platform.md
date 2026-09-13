# QCoreSys Admin Platform Prompt

Use this prompt with Cursor Agent to continue or extend the platform.

## Vision
- `/admin` staff console: Admin, Sales Manager, Account Manager
- Dynamic public portfolio CMS from admin
- Customer portal for request submission/tracking
- Multi-currency chart of accounts (USD/SAR/YER) + journals linked to invoices/payments/expenses

## Stack
Laravel 12, Blade + Vite + Tailwind + Alpine, custom RBAC, AR(RTL)/EN(LTR), base currency USD, brand navy `#091A2F` / cyan `#00D2FF`.

## Chart of Accounts (mandatory)
Hierarchical CoA with parents (`allow_posting=false`) and currency-specific leaves:

- Cash: `111101` USD, `111102` SAR, `111103` YER
- Bank: `111201`–`111203`
- AR: `1121`–`1123` | AP: `2111`–`2113` | Deferred: `2121`–`2123`
- Capital: `3111`–`3113` | Partners current: `3211`–`3213` | RE: `3311`–`3313`
- Revenue USD/SAR/YER: `411x` / `421x` / `431x`
- Expenses: `511x` / `521x` / `531x`

Opening journal: Dr `111101` $100 / Cr `3111` $100.

Settings: `account_cash`→`111101`, `account_bank`→`111201`, `account_ar`→`1121`, `account_ap`→`2111`, revenue→`4112`/`4111`, expense→`5111`.

## Delivery order
P0 Auth/RBAC + CRM + quotations + PDF  
P1 Invoices/payments/projects  
P2 Portfolio CMS  
P3 Expenses/journals/reports  
P4 Customer portal  

## Demo users
- admin@qcoresys.com / password (SUPER_ADMIN)
- sales.manager@qcoresys.com / password (SALES_MANAGER)
- account.manager@qcoresys.com / password (ACCOUNT_MANAGER)

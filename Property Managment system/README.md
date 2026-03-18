# Cowboy Properties — Property Management Portal

A full-stack web application built for a property management company to manage their entire rental operation in one place. The system serves two distinct user roles — **employees (admins)** and **renters** — with separate dashboards, permissions, and workflows for each.

Built as a group project for an Information Systems course.

---

## The Problem We Were Solving

Property management companies juggle a huge amount of information: which properties they own, which units are occupied, who the renters are, what leases are active, which maintenance tickets are open, and whether rent has been paid. Most small operators manage this with spreadsheets and email. This project explores what a purpose-built internal tool would look like.

---

## Features

### Admin / Employee Portal
- **Properties** — view, add, edit, and delete property listings with unit counts and assigned managers
- **Renters** — full CRUD for renter accounts; view contact info, lease history, and payment status
- **Employees** — manage staff accounts and positions
- **Leases** — create and manage lease agreements linking renters to specific units with price and period
- **Maintenance** — view and update maintenance tickets submitted by renters

### Renter Portal
- **Dashboard** — view current unit, active lease, and outstanding balance at a glance
- **Make a Payment** — submit rent payments against active leases
- **Maintenance Requests** — submit new tickets and track status of open issues
- **Account Management** — update personal contact information

### Authentication & Security
- Session-based login with role separation (employee vs. renter)
- Passwords stored as bcrypt hashes (PHP `password_hash` / `password_verify`)
- Role guards on every protected page — employees can't access renter pages and vice versa
- All database queries use PDO prepared statements (SQL injection prevention)
- Server-side input validation on all forms

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3 (custom design system with CSS variables) |
| Backend | PHP 8.x (PDO) |
| Database | MySQL 8.x |
| Auth | PHP sessions + bcrypt |
| Local Dev | MAMP (Apache + MySQL) |

---

## Database Schema

```
employee ──< assignment >── property ──< unit ──< lease
                                                     │
renter ──────────────────────────────────────────────┤
  │                                                  │
  └──< maintenance ──< task >── employee             └──< payment
```

**Key tables:**
- `employee` — staff accounts with role and position
- `renter` — tenant accounts
- `property` — property listings with manager assignment
- `unit` — individual units within a property (bed/bath/price)
- `lease` — links a renter to a unit with price and period
- `payment` — payment history per renter/lease
- `maintenance` — support tickets submitted by renters
- `task` — assigns maintenance tickets to employees
- `assignment` — maps which employees manage which properties

---

## Application Flow

```
index.php (Landing)
└── Pages/login.php
        ├── Employee → Pages/adminhome.php
        │       ├── admin-properties-view.php  (+ add / details / edit)
        │       ├── admin-renters-view.php      (+ add / details / edit)
        │       ├── admin-employees-view.php    (+ add / details / edit)
        │       ├── Lease-CRUD/admin-leases-view.php (+ add / details / edit)
        │       └── maintenance.php
        │
        └── Renter → Pages/renter-dashboard.php
                ├── renter-make-payment.php
                ├── Pages/create.php          (submit maintenance request)
                ├── Pages/history.php         (payment history)
                └── renter-manage-account.php
```

---

## Running Locally

**Prerequisites:** [MAMP](https://www.mamp.info/) (or any Apache + MySQL stack)

1. **Clone the repo** and move the folder to your MAMP `htdocs` directory

2. **Import the database** via phpMyAdmin:
   - Create a database named `cowboy_properties`
   - Import `cowboy_properties_V2.sql`

3. **Check the DB connection** in `db.php` — the defaults work for standard MAMP setups:
   ```
   Primary:   127.0.0.1:8889  (MAMP default)
   Secondary: localhost:3307
   Tertiary:  localhost:3306   (standard MySQL)
   ```
   The app tries each in order and uses the first one that connects.

4. **Seed test accounts** by visiting:
   ```
   http://localhost:8888/[folder-name]/seed.php
   ```

5. **Log in** at `http://localhost:8888/[folder-name]/Pages/login.php`
   - Employee: use a seeded employee email + password `admin123`
   - Renter: use `bsmith@example.com` / `mysecret` or `pjones@example.com` / `acrobat`

---

## Architecture Notes

**`db.php`** — singleton PDO connection with a waterfall fallback across three common local MySQL ports, so it works out of the box on different MAMP/MySQL configurations without editing config files.

**`auth_check.php`** — shared auth library with four helper functions: `require_login()`, `require_employee()`, `require_renter()`, and `current_user()`. Every protected page calls one of these at the top.

**`Pages/partials/`** — shared header and footer PHP partials included on all authenticated pages for consistent navigation.

---

## What I Learned / PM Takeaways

- **Dual-persona design**: designing for two very different user types (admin vs. renter) who use the same underlying data but need completely different views and workflows — a core product management challenge
- **Data modeling for real-world complexity**: the schema went through multiple iterations to properly represent the property → unit → lease → payment chain with correct foreign key relationships
- **Role-based access control**: implementing guards that prevent renters from accessing admin pages (and vice versa) reinforced how important permission scoping is in multi-user systems
- **Building for reliability**: the multi-connection fallback in `db.php` was a pragmatic solution to the fact that different team members ran MySQL on different ports — a real-world "it has to work on every machine" problem

---

## Possible Extensions

- Email notifications when a maintenance ticket changes status
- Lease expiration alerts for employees approaching renewal dates
- A reporting dashboard showing occupancy rate, total rent collected, and open maintenance tickets by property
- Online lease signing workflow (DocuSign-style) instead of paper-based agreements
- Stripe payment integration to process rent payments in the portal instead of just recording them

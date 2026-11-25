# Document Tracking System (DTS)
### For Campus Director — Multi-Campus Edition

---

## Quick Setup (XAMPP)

### Requirements
- XAMPP with PHP 8.1+ and MySQL
- Composer (https://getcomposer.org)

### Steps

1. **Copy this folder** to `C:\xampp\htdocs\dts`

2. **Install dependencies** — open terminal in the project folder:
   ```
   composer install
   ```

3. **Configure environment** — copy `.env.example` to `.env`:
   ```
   copy .env.example .env
   php artisan key:generate
   ```

4. **Create the database** — open phpMyAdmin and create a database named `dts_campus`

5. **Run migrations and seed data**:
   ```
   php artisan migrate --seed
   ```

6. **Open in browser**:
   ```
   http://localhost/dts/public
   ```

---

## Default Login Credentials

Seeded accounts sign in with their **email** address. The seeder gives every account the same starting password, so **change all of them immediately** after the first login (Profile page), or delete the unused accounts.

| Role | Email | Starting password |
|------|-------|-------------------|
| Document Controller (Main) | controller@main.edu | password |
| Campus Director (Main) | director@main.edu | password |
| Routing Unit (Budget) | budget@main.edu | password |
| Routing Unit (HR) | hr@main.edu | password |
| Document Submitter | submitter@main.edu | password |
| Document Controller (Campus A) | controller@campus-a.edu | password |
| Campus Director (Campus A) | director@campus-a.edu | password |

Never run the seeder on a live server, and never expose a seeded database with these passwords.

---

## Document Categories
- Special Order
- Travel Order
- Memorandum
- MANCOM Resolution
- BOT Resolution
- Others

## Document Statuses
- Received → Under Review → For Signature → Signed → Released
- Can also be: Returned, Archived

---

## Updating the System
```
git pull
composer install
php artisan migrate
php artisan config:clear
php artisan cache:clear
```

### Upgrading an existing installation (private document storage)
Uploaded documents are now stored in a private folder and served only through authorized links. Move any existing files once:
```
move storage\app\public\documents\* storage\app\private\documents\
```
Signature images remain in `storage\app\public\signatures`.

---

## Records Management (FAM 13.1)

The system follows the Finance and Administration Manual, **FAM 13.1 Records Management** (rev. 0, effective April 17, 2017).

| Manual requirement | Where it is in the system |
|---|---|
| 3.2 Records Management Officer (RMO) | The **Document Controller** role is labelled *Records Management Officer*. |
| 3.3, 3.7 Receive, release and log all records; logbook open to the public | **Logbook** page (`/logbook`). Confidential entries are shown only to managers. |
| 3.5 Retention code before filing | **Retention code** field on each document. Archiving is blocked until it is set. |
| 3.9 Confidential records kept separately | **Confidential** flag. Only managers and the submitter can see or act on these documents. |
| 3.10 Requisition procedures (forms 5.1.1, 5.1.2) | **Requisitions** page. Requisition, issue by RMO, acknowledgement by requester, return. |
| 3.11, 3.12, 5.2.2 Inventory and appraisal | **Records Management** page: inventory by retention code, a yearly inventory reminder (every 12 months), and a recorded review history with the record count. The prescribed NAP form (5.1.3) is not generated. |
| 3.13, 5.2.3 Semi-annual classification | **Records Management** page: disposition (*For Retention*, *Archive*, *Dispose*), a reminder every 6 months, and recorded reviews. |
| 3.14 Periodic file examination | Condition checks (Good, Fair, Poor, Damaged) recorded per file. Files not checked in 12 months are listed as due. |
| 3.15 Disposal per schedule | Disposition is recorded and logged. Physical disposal is not tracked. |

### Decisions to confirm

- **RMO = Document Controller.** Campus Directors keep full access to documents and records in this system, as confirmed. Section 3.8 (RMO has sole access to the office file) is therefore not enforced by the system.
- **Retention periods are not in the system.** Retention codes are free text. Enter the codes from the NAP General Records Schedule and the campus Records Disposition Schedule.
- **Routing units cannot see confidential documents**, even when routed to them. Confirm this is intended.
- **The manual's RMO review step (4.0, step 4)** is not built into the workflow templates. Add an RMO step to a template if it should be enforced.

### Upgrading an existing installation

Run the new migrations only (do not use `migrate:fresh`, which deletes data):

```
php artisan migrate
```

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

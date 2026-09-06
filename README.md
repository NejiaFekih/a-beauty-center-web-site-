# a-beauty-center-web-site-

A beauty salon website built with PHP and MySQL. Clients can create an account, leave reviews, and book appointments online; the administrator manages everything from a dedicated dashboard.

## Features

### Client area
- **Sign up and login** with PHP session management
- **Customer reviews**: a logged-in client can rate (1 to 5 stars) and comment on the salon; reviews are only publicly visible after admin approval
- **Appointment booking**:
  - Select one or more services at once, with live total price calculation
  - Calendar showing already-booked time slots (without revealing the client's name or the service booked)
  - Enforced business hours (10:00 AM – 8:00 PM)
  - Minimum 1.5-hour gap required between appointments
  - One appointment per client per day
  - Appointment status tracking (pending / confirmed / declined) with an on-site notification for the client

### Admin area
- Separate password-protected authentication
- Single dashboard with two tabs:
  - **Reviews**: approve, decline, or unpublish a customer review
  - **Appointments**: confirm, decline, or cancel an appointment, with a notification badge for new requests

## Tech stack

- PHP (procedural, `mysqli` prepared statements)
- MySQL / MariaDB
- HTML / CSS / JavaScript (vanilla, no framework)

## Database structure

- `client` — client accounts (name, email, password)
- `avis` — customer reviews (status: pending / approved)
- `les_services` — service catalog and prices
- `rendez_vous` — appointments (client, date, status, notification flags)
- `rendez_vous_services` — junction table allowing multiple services per appointment

## Local setup (XAMPP)

1. Clone the repository into your local server's `htdocs` (or `www`) folder
2. Create a `beautybar` database in phpMyAdmin
3. Import the SQL schema (see `/sql` if present, or the project's setup instructions)
4. Start Apache and MySQL
5. Visit the site at `http://localhost/YOUR_FOLDER_NAME/`

Admin access: `admin_login.php` (password configurable in `admin_login.php`)

## Security

- Passwords and emails handled via prepared statements (SQL injection protection)
- Separate sessions for the client and admin areas
- Slot and input validation enforced server-side, in addition to client-side checks

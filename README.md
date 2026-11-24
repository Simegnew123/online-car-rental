# Online Car Rental Management System with GPS Tracking

A full-featured car rental management system built with PHP & MySQL, featuring real-time GPS tracking, PWA support, dynamic pricing with add-on services, email notifications, and a professional admin dashboard.

## Features

### Customer Features
- **Browse & Search Vehicles** — Filter by type, fuel, price range, and keyword search
- **Online Booking** — Pick dates, see live price calculation with add-on services
- **Add-On Services** — Driver/chauffeur, airport pickup/drop-off, full insurance coverage
- **Dynamic Pricing Calculator** — Real-time cost breakdown as you select options
- **Multiple Payment Options** — Telebirr, Credit Card, Bank Transfer, Cash on Pickup, Chapa
- **Payment Receipt Upload** — Upload bank transfer/telebirr receipt for verification
- **Printable Invoices** — Professional A4 invoice with itemized costs
- **Real-Time GPS Tracking** — Track your rented vehicle on an interactive map
- **Reviews & Ratings** — Rate vehicles and read customer reviews
- **Booking Management** — View booking history, cancel pending bookings, access invoices
- **Notifications** — Get notified about booking status, payments, and more
- **Profile Management** — Update profile info, change password, upload/replace driver's license photo
- **Driver's License Upload** — Upload license photo during registration
- **PWA Support** — Install as a mobile app, works offline, QR code for mobile access

### Admin Features
- **Dashboard** — Overview with stats, charts (revenue, bookings), recent activity
- **Vehicle Management** — Add, edit, delete vehicles with image upload and plate numbers
- **Booking Lifecycle** — Confirm → Start Rental → Complete (full status flow with email notifications)
- **Payment Verification** — View uploaded receipts, verify/click to confirm payments
- **User Management** — Promote/demote admins, delete users
- **GPS Fleet Tracking** — Real-time map with 5-second auto-refresh, vehicle sidebar, GPS trail
- **GPS Simulator** — Generate test GPS movement data for active rentals
- **Reports & Analytics** — Revenue trends, booking trends, vehicle type distribution, top performers
- **Vehicle Handover Checklist** — Printable pickup/return checklist with customer and vehicle info
- **Admin Settings** — Email configuration, add-on pricing display, system info, email test tool

### Email Notification System
Automated HTML email notifications sent on:
- New user registration (welcome email)
- Booking confirmation
- Payment verification
- Rental start (handover)
- Rental completion (return)
- Booking cancellation

### Technical Features
- **PWA** — manifest.json, service worker, installable on mobile, QR code for mobile access
- **Real-Time GPS** — Leaflet + OpenStreetMap, 5-second auto-refresh, GPS trail polylines
- **Chart.js** — Revenue, bookings, and analytics charts
- **Responsive Design** — Mobile-first, hamburger menu, adaptive layouts
- **Secure Auth** — bcrypt password hashing, session-based authentication
- **API Layer** — RESTful endpoints for GPS data, notifications
- **Email System** — PHP mail() with file log fallback, branded HTML templates
- **Clean Code** — MVC-ish structure, helper functions, template system

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 8+ |
| Database | MySQL 5.7+ / MariaDB |
| Maps | Leaflet.js + OpenStreetMap |
| Charts | Chart.js 4 |
| PWA | Service Worker + Web Manifest |
| Auth | PHP Sessions + bcrypt |
| Email | PHP mail() + HTML templates |

## Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher (or MariaDB)
- Web server (Apache/Nginx) or XAMPP/WAMP

### Quick Start with XAMPP

1. **Install XAMPP** and start Apache + MySQL from the XAMPP Control Panel

2. **Copy the project** to XAMPP's web root:
   ```
   C:\xampp\htdocs\online-car-rental\
   ```

3. **Create the database**:
   - Open phpMyAdmin: `http://localhost/phpmyadmin` (or `http://localhost:8080/phpmyadmin` if port 80 is occupied)
   - Create a new database named `car_rental_db`
   - Import the `database.sql` file (click "Import" → choose `database.sql` → "Go")

4. **Set up upload directories** (create these folders if they don't exist):
   ```
   uploads/cars/
   uploads/licenses/
   uploads/receipts/
   ```
   On Linux/Mac, also set permissions: `chmod 777 uploads uploads/cars uploads/licenses uploads/receipts`

5. **Visit the site**:
   ```
   http://localhost/online-car-rental/
   ```
   (If Apache runs on port 8080: `http://localhost:8080/online-car-rental/`)

### Manual Installation (without XAMPP)

1. Copy the project to your web server document root
2. Create a MySQL database: `CREATE DATABASE car_rental_db;`
3. Import schema: `mysql -u root -p car_rental_db < database.sql`
4. Edit `config/database.php` if your DB credentials differ from defaults
5. Ensure upload directories exist and are writable
6. Visit the site in your browser

### Database Configuration

Default settings in `config/database.php`:

| Setting | Default Value |
|---------|--------------|
| DB_HOST | localhost |
| DB_PORT | 3306 |
| DB_NAME | car_rental_db |
| DB_USER | root |
| DB_PASS | (empty) |

If your setup differs, edit `config/database.php` and update the constants.

## Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@carrental.com | admin123 |
| Customer | customer@test.com | customer123 |

## Project Structure

```
online-car-rental/
├── config/
│   └── database.php          # PDO connection + app constants
├── includes/
│   ├── auth.php              # Authentication & user registration
│   ├── functions.php         # Utility & helper functions
│   ├── mail.php              # Email notification system
│   ├── header.php            # HTML header template (nav, PWA, notifications)
│   └── footer.php            # HTML footer template
├── assets/
│   ├── css/style.css         # Professional stylesheet
│   ├── js/main.js            # GPS tracking, notifications, UI logic
│   └── img/                  # PWA icons, favicon
├── admin/
│   ├── dashboard.php         # Admin overview with charts
│   ├── cars.php              # Vehicle list management (with plate numbers)
│   ├── add_car.php           # Add new vehicle
│   ├── edit_car.php          # Edit vehicle
│   ├── delete_car.php        # Delete vehicle
│   ├── bookings.php          # Booking lifecycle management
│   ├── users.php             # User management
│   ├── gps_tracking.php      # Real-time GPS fleet tracking
│   ├── handover.php          # Vehicle handover/return checklist (printable)
│   ├── settings.php          # Admin settings (email test, pricing, system info)
│   └── reports.php           # Analytics & reports
├── api/
│   ├── gps_update.php        # POST: receive GPS data from devices
│   ├── get_location.php      # GET: return car locations + trails
│   ├── gps_simulator.php     # POST: generate test GPS movement
│   ├── get_notifications.php # GET: fetch user notifications
│   └── mark_read.php         # GET: mark notification as read
├── uploads/
│   ├── cars/                 # Vehicle images
│   ├── licenses/             # Driver's license photos
│   └── receipts/             # Payment receipt uploads
├── index.php                 # Homepage (hero, features, featured cars, QR code)
├── cars.php                  # Vehicle listing with filters
├── car_details.php           # Vehicle details + reviews + add-on pricing
├── book.php                  # Booking form with live price calc + add-ons
├── payment.php               # Payment method selection + receipt upload
├── invoice.php               # Printable professional invoice
├── my_bookings.php           # Customer booking history
├── profile.php               # Profile settings + license photo + password
├── cancel_booking.php        # Cancel pending booking
├── notifications.php         # Full notifications page
├── contact.php               # Contact form
├── login.php / register.php  # Authentication pages (license upload on register)
├── logout.php                # Logout handler
├── database.sql              # MySQL schema + seed data (all features)
├── manifest.json             # PWA manifest
├── sw.js                     # Service worker
├── .htaccess                 # Apache config
└── README.md                 # This file
```

## GPS Tracking Setup

The GPS tracking feature uses a simple API:

1. **Devices send data** to `api/gps_update.php` via POST:
   ```
   Header: X-API-Key: gps_tracker_key_2024
   Body: { "car_id": 1, "latitude": 9.025, "longitude": 38.747, "speed": 45, "heading": 180 }
   ```

2. **Admin views live tracking** at `admin/gps_tracking.php` — auto-refreshes every 5 seconds

3. **GPS Simulator**: Click "Simulate Movement" on the tracking page to generate test data. The simulator moves rented vehicles along realistic paths in Addis Ababa.

## Add-On Services & Pricing

| Service | Rate | Calculation |
|---------|------|-------------|
| Driver / Chauffeur | ETB 500/day | Days × 500 |
| Airport Pickup/Drop | ETB 300 flat | Fixed fee |
| Full Insurance | 10% of rental | (Days × Price/day) × 0.10 |

Pricing constants are defined in `config/database.php` and can be adjusted:
- `DRIVER_RATE_PER_DAY`
- `AIRPORT_FEE_FLAT`
- `INSURANCE_RATE`

## Email Configuration

The email system uses PHP's built-in `mail()` function. On XAMPP, if sendmail is not configured, emails are logged to `uploads/email_log.txt` as a fallback.

To configure real email delivery with XAMPP:
1. Open `C:\xampp\php\php.ini`
2. Configure `[mail function]` section with your SMTP server
3. Or use a third-party SMTP library for production

Test email functionality from **Admin → Settings → Email Test**.

## Mobile / PWA Access

1. **Install as PWA**: Open the site in Chrome on mobile → Menu → "Install app"
2. **QR Code Access**: A QR code is displayed in the website footer — scan with your phone camera
3. **Requirements**: PNG icons (192px + 512px) are included for PWA installability
4. **Firewall Note**: If accessing from another device on your LAN, ensure port 8080 is open in Windows Firewall

## License

This project is for educational purposes as part of a graduation project at Admas University, Kality Campus — Department of Computer Science.

## Team

- Nardos Gizaw
- Saladin Negash
- Genet Tadele
- Tekalegn Chefeko
- Etagegnew Derese

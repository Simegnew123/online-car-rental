# Online Car Rental Management System with GPS Tracking

A full-featured car rental management system built with PHP & MySQL, featuring real-time GPS tracking, PWA support, and a professional admin dashboard.

## Features

### Customer Features
- **Browse & Search Vehicles** — Filter by type, fuel, price range, and keyword search
- **Online Booking** — Pick dates, see live price calculation, book in minutes
- **Multiple Payment Options** — Telebirr, Credit Card, Bank Transfer, Cash on Pickup
- **Real-Time GPS Tracking** — Track your rented vehicle on an interactive map
- **Reviews & Ratings** — Rate vehicles and read customer reviews
- **Booking Management** — View booking history, cancel pending bookings
- **Notifications** — Get notified about booking status, payments, and more
- **Profile Management** — Update profile info, change password
- **PWA Support** — Install as a mobile app, works offline

### Admin Features
- **Dashboard** — Overview with stats, charts (revenue, bookings), recent activity
- **Vehicle Management** — Add, edit, delete vehicles with image upload
- **Booking Lifecycle** — Confirm → Start Rental → Complete (full status flow)
- **User Management** — Promote/demote admins, delete users
- **GPS Fleet Tracking** — Real-time map with 5-second auto-refresh, vehicle sidebar, GPS trail
- **GPS Simulator** — Generate test GPS movement data for active rentals
- **Reports & Analytics** — Revenue trends, booking trends, vehicle type distribution, top performers

### Technical Features
- **PWA** — manifest.json, service worker, installable on mobile
- **Real-Time GPS** — Leaflet + OpenStreetMap, 5-second auto-refresh, GPS trail polylines
- **Chart.js** — Revenue, bookings, and analytics charts
- **Responsive Design** — Mobile-first, hamburger menu, adaptive layouts
- **Secure Auth** — bcrypt password hashing, session-based authentication
- **API Layer** — RESTful endpoints for GPS data, notifications
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

## Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher (or MariaDB)
- Web server (Apache/Nginx) or XAMPP/WAMP

### Steps

1. **Copy the project** to your web server document root:
   ```
   htdocs/online-car-rental/   (XAMPP)
   www/online-car-rental/       (WAMP)
   ```

2. **Create the database**:
   ```sql
   CREATE DATABASE car_rental_db;
   ```
   Then import `database.sql`:
   ```bash
   mysql -u root -p car_rental_db < database.sql
   ```

3. **Configure database connection** (if needed):
   Edit `config/database.php` — default is localhost, root, no password.

4. **Set up upload directories**:
   ```
   mkdir uploads/cars uploads/licenses
   chmod 777 uploads uploads/cars uploads/licenses
   ```

5. **Visit the site**:
   ```
   http://localhost/online-car-rental/
   ```

## Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@carrental.com | admin123 |
| Customer | customer@test.com | customer123 |

## Project Structure

```
online-car-rental/
├── config/
│   └── database.php          # PDO database connection
├── includes/
│   ├── auth.php              # Authentication functions
│   ├── functions.php         # Utility & helper functions
│   ├── header.php            # HTML header template (nav, PWA, notifications)
│   └── footer.php            # HTML footer template
├── assets/
│   ├── css/style.css         # Professional stylesheet (v2.0)
│   ├── js/main.js            # GPS tracking, notifications, UI logic
│   └── img/                  # PWA icons, favicon
├── admin/
│   ├── dashboard.php         # Admin overview with charts
│   ├── cars.php              # Vehicle list management
│   ├── add_car.php           # Add new vehicle
│   ├── edit_car.php          # Edit vehicle
│   ├── delete_car.php        # Delete vehicle
│   ├── bookings.php          # Booking lifecycle management
│   ├── users.php             # User management
│   ├── gps_tracking.php      # Real-time GPS fleet tracking
│   └── reports.php           # Analytics & reports
├── api/
│   ├── gps_update.php        # POST: receive GPS data from devices
│   ├── get_location.php      # GET: return car locations + trails
│   ├── gps_simulator.php     # POST: generate test GPS movement
│   ├── get_notifications.php # GET: fetch user notifications
│   └── mark_read.php         # GET: mark notification as read
├── index.php                 # Homepage (hero, features, featured cars)
├── cars.php                  # Vehicle listing with filters
├── car_details.php           # Vehicle details + reviews
├── book.php                  # Booking form with live price calc
├── payment.php               # Payment method selection
├── my_bookings.php           # Customer booking history
├── cancel_booking.php        # Cancel pending booking
├── notifications.php         # Full notifications page
├── profile.php               # Profile settings + password change
├── contact.php               # Contact form
├── login.php / register.php  # Authentication pages
├── logout.php                # Logout handler
├── database.sql              # MySQL schema + seed data
├── manifest.json             # PWA manifest
├── sw.js                     # Service worker
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

3. **GPS Simulator**: Click "Simulate Movement" on the tracking page to generate test data

## License

This project is for educational purposes as part of a graduation project at Admas University.

## Team

- Nardos Gizaw
- Saladin Negash
- Genet Tadele
- Tekalegn Chefeko
- Etagegnew Derese

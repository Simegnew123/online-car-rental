# Online Car Rental Management System with GPS Tracking

A complete web-based car rental management system with real-time GPS fleet tracking.

## Tech Stack
- **Backend:** PHP 8+ (PDO)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Maps:** Leaflet.js + OpenStreetMap
- **Icons:** Font Awesome

## Features

### Customer
- Register / Login
- Browse available vehicles with filters (type, fuel, price)
- View vehicle details and reviews
- Book a vehicle (date range, availability check)
- Pay for bookings (receipt generation)
- View booking history
- Cancel bookings
- Manage profile (name, phone, license)
- Contact / feedback form

### Admin
- Dashboard with summary statistics
- Manage vehicles (add, edit, delete)
- Manage bookings (confirm, activate, complete, cancel)
- Manage users (suspend, activate)
- GPS fleet tracking (live map, auto-refresh)
- Reports & analytics (revenue, top vehicles, utilization)

### GPS Tracking
- API endpoint for devices to post GPS coordinates (`api/gps_update.php`)
- API endpoint to retrieve latest vehicle locations (`api/get_location.php`)
- Admin map shows all vehicle positions in real-time
- Auto-refreshes every 30 seconds

## Installation

### 1. Prerequisites
- XAMPP, WAMP, or LAMP stack (PHP 8+, MySQL)
- Web browser

### 2. Setup Database
1. Start Apache and MySQL from XAMPP control panel
2. Open `http://localhost/phpmyadmin`
3. Create a new database named `car_rental_db`
4. Import `database.sql` to create all tables and seed sample data

### 3. Copy Project
Copy the `online-car-rental` folder to your web root:
- XAMPP: `C:\xampp\htdocs\online-car-rental`
- WAMP:  `C:\wamp64\www\online-car-rental`

### 4. Configure Database
Edit `config/database.php` if your MySQL credentials differ:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'car_rental_db');
define('DB_USER', 'root');
define('DB_PASS', '');       // default XAMPP has no password
```

### 5. Create Upload Directories
Ensure these directories exist and are writable:
```
uploads/cars/
uploads/licenses/
```

### 6. Access the Application
Open browser: **http://localhost/online-car-rental**

**Default Admin Login:**
- Email: `admin@carrental.com`
- Password: `admin123`

**Sample Customer:**
- Email: `customer@test.com`
- Password: `customer123`

## Project Structure
```
online-car-rental/
  config/
    database.php          Database connection (PDO)
  includes/
    auth.php              Authentication functions
    functions.php         Utility functions
    header.php            HTML head + navigation
    footer.php            HTML footer
  assets/
    css/style.css         Main stylesheet
    js/main.js            JavaScript (GPS map, validation)
  admin/
    dashboard.php         Admin overview
    cars.php              Vehicle list
    add_car.php           Add vehicle
    edit_car.php          Edit vehicle
    delete_car.php        Delete vehicle
    bookings.php          Booking management
    users.php             User management
    gps_tracking.php      Live GPS map
    reports.php           Analytics
  api/
    gps_update.php        POST GPS coordinates from devices
    get_location.php      GET latest vehicle locations
  uploads/
    cars/                 Vehicle images
    licenses/             License images
  database.sql            Database schema + seed data
  index.php               Home page
  login.php               Login
  register.php            Registration
  logout.php              Logout
  cars.php                Vehicle listing
  car_details.php         Vehicle detail + reviews
  book.php                Booking page
  my_bookings.php         Booking history
  payment.php             Payment page
  cancel_booking.php      Cancel booking
  profile.php             User profile
  contact.php             Contact form
  README.md               This file
```

## GPS Device Integration

To send GPS data from a tracking device to the system:

```bash
curl -X POST http://localhost/online-car-rental/api/gps_update.php \
  -H "Content-Type: application/json" \
  -d '{"car_id":1,"device_id":"GPS-001","latitude":9.0085,"longitude":38.7575,"speed":45,"heading":180}'
```

## License
This project is developed for academic purposes at Admas University, Kality Campus.

## Authors
- Nardos Gizaw
- Saladin Negash
- Genet Tadele
- Tekalegn Chefeko
- Etagegnew Derese

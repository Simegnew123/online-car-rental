-- =====================================================
-- Online Car Rental Management System with GPS Tracking
-- Database Schema for MySQL  (v2.0 — Professional Edition)
-- =====================================================

CREATE DATABASE IF NOT EXISTS car_rental_db
    DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE car_rental_db;

-- ---------------------------------------------------
-- Table: users
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(100)   NOT NULL,
    email           VARCHAR(150)   NOT NULL UNIQUE,
    password        VARCHAR(255)   NOT NULL,
    phone           VARCHAR(20)    DEFAULT NULL,
    role            ENUM('customer','admin') DEFAULT 'customer',
    license_number  VARCHAR(50)    DEFAULT NULL,
    license_image   VARCHAR(255)   DEFAULT NULL,
    avatar_color    VARCHAR(7)     DEFAULT '#2563eb',
    status          ENUM('active','suspended','pending_verification') DEFAULT 'active',
    created_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Table: cars
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS cars (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100)  NOT NULL,
    brand          VARCHAR(50)   DEFAULT NULL,
    type           ENUM('Sedan','SUV','Hatchback','Pickup','Van','Luxury','Electric') DEFAULT 'Sedan',
    fuel_type      ENUM('Petrol','Diesel','Hybrid','Electric') DEFAULT 'Petrol',
    transmission   ENUM('Manual','Automatic') DEFAULT 'Automatic',
    seats          INT            DEFAULT 5,
    doors          INT            DEFAULT 4,
    luggage        INT            DEFAULT 2,
    air_conditioning TINYINT(1)   DEFAULT 1,
    bluetooth      TINYINT(1)     DEFAULT 1,
    price_per_day  DECIMAL(10,2)  NOT NULL,
    image          VARCHAR(255)   DEFAULT NULL,
    description    TEXT           DEFAULT NULL,
    status         ENUM('available','rented','under_repair','unavailable') DEFAULT 'available',
    featured       TINYINT(1)     DEFAULT 0,
    created_at     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Table: bookings
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT            NOT NULL,
    car_id          INT            NOT NULL,
    start_date      DATE           NOT NULL,
    end_date        DATE           NOT NULL,
    pickup_location VARCHAR(200)   DEFAULT NULL,
    total_cost      DECIMAL(10,2)  NOT NULL,
    notes           TEXT           DEFAULT NULL,
    status          ENUM('pending','confirmed','active','completed','cancelled') DEFAULT 'pending',
    created_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id)  REFERENCES cars(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Table: payments
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    booking_id      INT            NOT NULL,
    amount          DECIMAL(10,2)  NOT NULL,
    payment_method  ENUM('cash','card','telebirr','bank_transfer') DEFAULT 'cash',
    transaction_id  VARCHAR(100)   DEFAULT NULL,
    status          ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    created_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Table: gps_logs
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS gps_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    car_id      INT            NOT NULL,
    latitude    DECIMAL(10,6)  NOT NULL,
    longitude   DECIMAL(10,6)  NOT NULL,
    speed       DECIMAL(6,2)   DEFAULT 0.00,
    heading     DECIMAL(5,2)   DEFAULT 0.00,
    device_id   VARCHAR(50)    DEFAULT NULL,
    recorded_at TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Table: reviews
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT  NOT NULL,
    car_id     INT  NOT NULL,
    booking_id INT  DEFAULT NULL,
    rating     INT  DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (car_id)     REFERENCES cars(id)     ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Table: notifications
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT            NOT NULL,
    title      VARCHAR(200)   DEFAULT NULL,
    message    TEXT           NOT NULL,
    type       ENUM('booking','payment','system','gps') DEFAULT 'system',
    is_read    TINYINT(1)     DEFAULT 0,
    created_at TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- SEED DATA
-- =====================================================

-- Default admin account (password: admin123)
INSERT INTO users (full_name, email, password, phone, role, status, avatar_color) VALUES
('Administrator', 'admin@carrental.com', '$2b$12$SoaOu.8T0bHtsk55usFZeeKVLiZrIZhzN4RSJnk99dZ6cjjPr19k6', '0912345678', 'admin', 'active', '#dc2626');

-- Sample customers (password: customer123)
INSERT INTO users (full_name, email, password, phone, role, status, avatar_color, license_number) VALUES
('Test Customer', 'customer@test.com', '$2b$12$MMSr3.GOLuKypuTe4eUch.guYKYYm4IdhVzy6lmt4l/.5w240F3cG', '0912345679', 'customer', 'active', '#2563eb', 'DL-2024-00123'),
('Selam Bekele',  'selam@example.com',  '$2b$12$MMSr3.GOLuKypuTe4eUch.guYKYYm4IdhVzy6lmt4l/.5w240F3cG', '091112233',  'customer', 'active', '#16a34a', 'DL-2024-00456'),
('Dawit Haile',   'dawit@example.com',  '$2b$12$MMSr3.GOLuKypuTe4eUch.guYKYYm4IdhVzy6lmt4l/.5w240F3cG', '092233445',  'customer', 'active', '#f59e0b', 'DL-2024-00789');

-- Sample cars
INSERT INTO cars (name, brand, type, fuel_type, transmission, seats, doors, luggage, air_conditioning, bluetooth, price_per_day, description, status, featured, image) VALUES
('Toyota Camry',    'Toyota',  'Sedan',    'Petrol',   'Automatic', 5, 4, 2, 1, 1, 2500.00, 'Comfortable sedan with AC, Bluetooth, GPS navigation, and premium leather seats. Perfect for business trips and city driving.', 'available', 1, 'toyota_camry.svg'),
('Toyota V8',       'Toyota',  'SUV',      'Petrol',   'Automatic', 7, 5, 4, 1, 1, 5000.00, 'Powerful SUV with 4WD capability, perfect for off-road adventures and long-distance trips across Ethiopia.', 'rented', 1, 'toyota_v8.svg'),
('Honda Civic',     'Honda',   'Sedan',    'Petrol',   'Automatic', 5, 4, 2, 1, 1, 2200.00, 'Fuel-efficient sedan with modern safety features, lane assist, and adaptive cruise control.', 'available', 0, 'honda_civic.svg'),
('Isuzu D-Max',     'Isuzu',   'Pickup',   'Diesel',   'Manual',    5, 4, 3, 1, 0, 3500.00, 'Heavy-duty pickup truck for cargo transport and tough terrain. High load capacity and durable build.', 'available', 0, 'isuzu_dmax.svg'),
('Toyota Hiace',    'Toyota',  'Van',      'Diesel',   'Manual',    12, 4, 5, 1, 0, 4000.00, 'Spacious 12-seater van ideal for group travel, tours, and airport transfers.', 'available', 0, 'toyota_hiace.svg'),
('Lexus RX 350',    'Lexus',   'Luxury',   'Hybrid',   'Automatic', 5, 4, 3, 1, 1, 8000.00, 'Premium luxury SUV with leather seats, panoramic roof, premium sound system, and advanced driver assistance.', 'available', 1, 'lexus_rx350.svg'),
('BYD Atto 3',      'BYD',     'Electric', 'Electric', 'Automatic', 5, 4, 2, 1, 1, 3000.00, 'Fully electric SUV with 400km range, fast charging, regenerative braking, and zero emissions.', 'available', 1, 'byd_atto3.svg'),
('Suzuki Swift',    'Suzuki',  'Hatchback','Petrol',   'Manual',    5, 4, 1, 1, 1, 1800.00, 'Compact and economical hatchback, perfect for city driving and tight parking spaces.', 'available', 0, 'suzuki_swift.svg');

-- Sample bookings
INSERT INTO bookings (user_id, car_id, start_date, end_date, pickup_location, total_cost, status) VALUES
(2, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Bole Airport, Addis Ababa', 15000.00, 'active'),
(3, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Megenagna', 7500.00, 'confirmed'),
(4, 6, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Bole Airport', 24000.00, 'pending');

-- Sample payment
INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) VALUES
(1, 15000.00, 'telebirr', 'TXN20261122001AABB', 'paid'),
(2, 7500.00, 'cash', 'TXN20261122002CCDD', 'paid');

-- Sample reviews
INSERT INTO reviews (user_id, car_id, booking_id, rating, comment) VALUES
(3, 1, NULL, 5, 'Excellent car! Very comfortable and fuel efficient. The GPS tracking feature gave me peace of mind during my trip.'),
(4, 6, NULL, 5, 'Luxury at its finest. The Lexus was immaculate and the service was outstanding.'),
(2, 2, NULL, 4, 'Great SUV for our family trip to Bahir Dar. Spacious and powerful.');

-- Sample GPS data (Addis Ababa — multiple points for trail history)
INSERT INTO gps_logs (car_id, latitude, longitude, speed, heading, device_id, recorded_at) VALUES
-- Car 2 (Toyota V8 — currently rented, moving)
(2, 9.0085,  38.7575, 0.00,   0,   'GPS-002', DATE_SUB(NOW(), INTERVAL 120 SECOND)),
(2, 9.0120,  38.7600, 35.50,  45,  'GPS-002', DATE_SUB(NOW(), INTERVAL 90 SECOND)),
(2, 9.0180,  38.7630, 52.00,  60,  'GPS-002', DATE_SUB(NOW(), INTERVAL 60 SECOND)),
(2, 9.0220,  38.7680, 48.00,  75,  'GPS-002', DATE_SUB(NOW(), INTERVAL 30 SECOND)),
(2, 9.0250,  38.7720, 40.00,  80,  'GPS-002', NOW()),
-- Car 1 (Toyota Camry — parked)
(1, 9.0340,  38.7450, 0.00,   0,   'GPS-001', DATE_SUB(NOW(), INTERVAL 300 SECOND)),
(1, 9.0340,  38.7450, 0.00,   0,   'GPS-001', NOW()),
-- Car 6 (Lexus — parked)
(6, 9.0400,  38.7900, 0.00,   0,   'GPS-006', NOW()),
-- Car 7 (BYD — moving slowly)
(7, 9.0100,  38.7300, 25.00,  180, 'GPS-007', DATE_SUB(NOW(), INTERVAL 60 SECOND)),
(7, 9.0080,  38.7310, 22.00,  190, 'GPS-007', DATE_SUB(NOW(), INTERVAL 30 SECOND)),
(7, 9.0060,  38.7320, 20.00,  195, 'GPS-007', NOW());

-- Sample notifications
INSERT INTO notifications (user_id, title, message, type) VALUES
(1, 'New Booking', 'Booking #3 pending confirmation for Lexus RX 350.', 'booking'),
(1, 'Payment Received', 'Payment of ETB 15,000.00 received for booking #1 (Toyota V8).', 'payment'),
(2, 'Rental Active', 'Your booking #1 for Toyota V8 is now active. Vehicle handed over.', 'booking'),
(3, 'Booking Confirmed', 'Your booking #2 for Toyota Camry has been confirmed.', 'booking');

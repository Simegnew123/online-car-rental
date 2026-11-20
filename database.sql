-- =====================================================
-- Online Car Rental Management System with GPS Tracking
-- Database Schema for MySQL
-- =====================================================

CREATE DATABASE IF NOT EXISTS car_rental_db
    DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE car_rental_db;

-- ---------------------------------------------------
-- Table: users
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)   NOT NULL,
    email         VARCHAR(150)   NOT NULL UNIQUE,
    password      VARCHAR(255)   NOT NULL,
    phone         VARCHAR(20)    DEFAULT NULL,
    role          ENUM('customer','admin') DEFAULT 'customer',
    license_number VARCHAR(50)   DEFAULT NULL,
    license_image VARCHAR(255)   DEFAULT NULL,
    status        ENUM('active','suspended','pending_verification') DEFAULT 'active',
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Table: cars
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS cars (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    brand         VARCHAR(50)   DEFAULT NULL,
    type          ENUM('Sedan','SUV','Hatchback','Pickup','Van','Luxury','Electric') DEFAULT 'Sedan',
    fuel_type     ENUM('Petrol','Diesel','Hybrid','Electric') DEFAULT 'Petrol',
    transmission  ENUM('Manual','Automatic') DEFAULT 'Automatic',
    seats         INT            DEFAULT 5,
    price_per_day DECIMAL(10,2)  NOT NULL,
    image         VARCHAR(255)   DEFAULT NULL,
    description   TEXT           DEFAULT NULL,
    status        ENUM('available','rented','under_repair','unavailable') DEFAULT 'available',
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
-- Seed Data
-- =====================================================

-- Default admin account (password: admin123)
INSERT INTO users (full_name, email, password, phone, role, status) VALUES
('Administrator', 'admin@carrental.com', '$2b$12$SoaOu.8T0bHtsk55usFZeeKVLiZrIZhzN4RSJnk99dZ6cjjPr19k6', '0912345678', 'admin', 'active');

-- Sample customer (password: customer123)
INSERT INTO users (full_name, email, password, phone, role, status) VALUES
('Test Customer', 'customer@test.com', '$2b$12$MMSr3.GOLuKypuTe4eUch.guYKYYm4IdhVzy6lmt4l/.5w240F3cG', '0912345679', 'customer', 'active');

-- Sample cars
INSERT INTO cars (name, brand, type, fuel_type, transmission, seats, price_per_day, description, status) VALUES
('Toyota Camry',    'Toyota',  'Sedan',    'Petrol',   'Automatic', 5, 2500.00, 'Comfortable sedan with AC, Bluetooth, and GPS navigation.', 'available'),
('Toyota V8',       'Toyota',  'SUV',      'Petrol',   'Automatic', 7, 5000.00, 'Powerful SUV, perfect for off-road and long trips.', 'available'),
('Honda Civic',     'Honda',   'Sedan',    'Petrol',   'Automatic', 5, 2200.00, 'Fuel-efficient sedan with modern safety features.', 'available'),
('Isuzu D-Max',     'Isuzu',   'Pickup',   'Diesel',   'Manual',    5, 3500.00, 'Heavy-duty pickup truck for cargo and tough terrain.', 'available'),
('Toyota Hiace',    'Toyota',  'Van',      'Diesel',   'Manual',    12, 4000.00, 'Spacious van for group travel and tours.', 'available'),
('Lexus RX 350',    'Lexus',   'Luxury',   'Hybrid',   'Automatic', 5, 8000.00, 'Premium luxury SUV with leather seats and panoramic roof.', 'available'),
('BYD Atto 3',      'BYD',     'Electric', 'Electric', 'Automatic', 5, 3000.00, 'Fully electric SUV with 400km range and fast charging.', 'available'),
('Suzuki Swift',    'Suzuki',  'Hatchback','Petrol',   'Manual',    5, 1800.00, 'Compact and economical hatchback for city driving.', 'available');

-- Sample GPS data (Addis Ababa coordinates)
INSERT INTO gps_logs (car_id, latitude, longitude, speed, device_id) VALUES
(1, 9.0085, 38.7575, 0.00, 'GPS-001'),
(2, 9.0320, 38.7420, 45.50, 'GPS-002'),
(3, 9.0150, 38.7800, 0.00, 'GPS-003');

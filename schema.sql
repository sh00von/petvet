-- schema.sql
CREATE DATABASE IF NOT EXISTS pet_adoption;
USE pet_adoption;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('adopter','store_owner','vet') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  age INT NOT NULL,
  type VARCHAR(50) NOT NULL,
  breed VARCHAR(50) NOT NULL,
  color VARCHAR(50) NOT NULL,
  health_status ENUM('good','sick','injured') NOT NULL,
  store_id INT NOT NULL,
  adopter_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (store_id) REFERENCES users(id),
  FOREIGN KEY (adopter_id) REFERENCES users(id)
);

-- Drop old multi-row schedule if it exists
DROP TABLE IF EXISTS vet_schedule;

-- New single-row-per-vet schedule
CREATE TABLE vet_schedule (
  vet_id     INT           PRIMARY KEY,
  days       JSON          NOT NULL,                              -- e.g. ["Mon","Wed","Fri"]
  start_time TIME          NOT NULL,
  end_time   TIME          NOT NULL,
  updated_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP     -- auto-updates on change
  ,FOREIGN KEY (vet_id) REFERENCES users(id)
);

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  vet_id  INT NOT NULL,
  appointment_date DATE NOT NULL,
  time_slot VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (vet_id) REFERENCES users(id),
  INDEX(user_id),
  INDEX(vet_id),
  INDEX(appointment_date)
);
-- Adoption history: one record per adoption event
CREATE TABLE adoption_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pet_id INT NOT NULL,
  user_id INT NOT NULL,
  adoption_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pet_id) REFERENCES pets(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX(user_id),
  INDEX(pet_id)
);
-- PRODUCTS & SUPPLY
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  store_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (store_id) REFERENCES users(id),
  INDEX(store_id)
);

CREATE TABLE supply (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  expiry_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  INDEX(product_id),
  INDEX(expiry_date)
);

-- PURCHASE TRACKING
CREATE TABLE purchases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id),
  INDEX(user_id),
  INDEX(product_id)
);
-- Add quantity column to track how many units were bought
ALTER TABLE purchases
  ADD COLUMN quantity INT NOT NULL DEFAULT 1;

-- (Optional) If you want to track total at purchase time:
ALTER TABLE purchases
  ADD COLUMN unit_price DECIMAL(10,2) NOT NULL AFTER quantity;
-- Facilities that can service pets (grooming, vet, etc.)
CREATE TABLE facilities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100)    NOT NULL,
  address VARCHAR(255) NULL,
  contact VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Each pet’s grooming record and assigned facility
CREATE TABLE grooming (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pet_id INT     NOT NULL,
  facility_id INT NOT NULL,
  last_groom_date DATE     NULL,
  next_groom_date DATE     NULL,
  created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pet_id)     REFERENCES pets(id),
  FOREIGN KEY (facility_id)REFERENCES facilities(id),
  UNIQUE (pet_id)
);

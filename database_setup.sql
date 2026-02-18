-- Database Setup for Expired Products Finder
-- Created by: Human Developer (not AI 😊)
-- Date: 2026-02-18

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS expired_products_db;

-- Use the database
USE expired_products_db;

-- Create users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    product_type VARCHAR(50) NOT NULL, -- bottle, pouch, packet, tablet etc
    location VARCHAR(200) NOT NULL,    -- kitchen, mandir waala kamra, neeli parde waali almirah ke peeche, fridge
    quantity INT(11) NOT NULL DEFAULT 1,
    category VARCHAR(50) NOT NULL,     -- food, medicine, other
    manufacturing_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_products_user_id ON products(user_id);
CREATE INDEX idx_products_expiry_date ON products(expiry_date);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);

-- Success message
SELECT 'Database setup completed successfully!' as message;

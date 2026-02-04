-- Create database
CREATE DATABASE IF NOT EXISTS money_transfer;
USE money_transfer;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(15) UNIQUE NOT NULL,
    address TEXT NOT NULL,
    user_type ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'hold', 'deleted') DEFAULT 'active'
);

-- Bank accounts table
CREATE TABLE IF NOT EXISTS bank_accounts (
    account_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_type VARCHAR(50) NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    from_account_id INT NOT NULL,
    to_account_id INT,
    to_phone_number VARCHAR(15),
    amount DECIMAL(15, 2) NOT NULL,
    transaction_type ENUM('phone_transfer', 'account_transfer') NOT NULL,
    status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account_id) REFERENCES bank_accounts(account_id),
    FOREIGN KEY (to_account_id) REFERENCES bank_accounts(account_id)
);

-- Feedback table
CREATE TABLE IF NOT EXISTS feedback (
    feedback_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, phone_number, address, user_type)
VALUES ('admin', '$2y$10$8KzQ8IzAF9cM1qKwNxzO8.XZv5MZ5OZjhNf1NGrXWdqKX8J5.Ygxq', 'System Admin', 'admin@system.com', '0000000000', 'System Address', 'admin'); 
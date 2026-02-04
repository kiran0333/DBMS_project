-- Create database if not exists
CREATE DATABASE IF NOT EXISTS money_transfer;
USE money_transfer;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bank accounts table
CREATE TABLE IF NOT EXISTS bank_accounts (
    account_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_number VARCHAR(50) UNIQUE NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_type ENUM('Savings', 'Checking') NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    from_account_id INT,
    to_account_id INT NULL,
    to_phone_number VARCHAR(20) NULL,
    amount DECIMAL(15,2) NOT NULL,
    transaction_type ENUM('bank_transfer', 'phone_transfer', 'self_transfer') NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account_id) REFERENCES bank_accounts(account_id),
    FOREIGN KEY (to_account_id) REFERENCES bank_accounts(account_id)
); 
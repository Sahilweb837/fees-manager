-- School ERP Database Schema
CREATE DATABASE IF NOT EXISTS school_erp;
USE school_erp;

-- Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

-- Insert Default Roles
INSERT INTO roles (role_name) VALUES ('Super Admin'), ('Root Admin'), ('Admin'), ('Staff');

-- Permissions Table
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL UNIQUE,
    permission_slug VARCHAR(100) NOT NULL UNIQUE
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    status TINYINT DEFAULT 1,
    profile_pic VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
);

-- Role Permissions Table
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT,
    permission_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Staff Table
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    staff_id_card VARCHAR(50) UNIQUE,
    phone VARCHAR(20),
    department VARCHAR(100),
    designation VARCHAR(100),
    salary DECIMAL(10,2),
    joining_date DATE,
    address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admission_no VARCHAR(50) UNIQUE NOT NULL,
    roll_no VARCHAR(50),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    guardian_name VARCHAR(100),
    blood_group VARCHAR(10),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    dob DATE,
    gender ENUM('Male', 'Female', 'Other'),
    address TEXT,
    class VARCHAR(50),
    section VARCHAR(50),
    admission_date DATE,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Expense Categories
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Expenses Table
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    expense_date DATE NOT NULL,
    receipt_image VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Fees Categories
CREATE TABLE IF NOT EXISTS fees_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Fees Structure
CREATE TABLE IF NOT EXISTS fees_structure (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    class VARCHAR(50),
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES fees_categories(id) ON DELETE CASCADE
);

-- Fees Payments
CREATE TABLE IF NOT EXISTS fees_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    fee_category_id INT,
    invoice_no VARCHAR(50) UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    fine DECIMAL(10,2) DEFAULT 0,
    due_amount DECIMAL(10,2) AS (total_amount + fine - paid_amount - discount) STORED,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50),
    status ENUM('Paid', 'Partial', 'Unpaid') DEFAULT 'Unpaid',
    remarks TEXT,
    created_by INT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_category_id) REFERENCES fees_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Student Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    status ENUM('Present', 'Absent', 'Late', 'Leave') NOT NULL,
    attendance_date DATE NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY (student_id, attendance_date)
);

-- Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action TEXT NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Default Super Admin (Password: admin123)
INSERT INTO users (name, email, username, password, role_id) 
VALUES ('Super Admin', 'superadmin@example.com', 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

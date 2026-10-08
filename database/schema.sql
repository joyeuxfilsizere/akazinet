-- KaziNet Database Schema
-- Professional Job Vacancy Platform for Rwanda

CREATE DATABASE IF NOT EXISTS kazinet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kazinet;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'briefcase',
    job_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    location VARCHAR(150) DEFAULT NULL,
    province VARCHAR(50) DEFAULT NULL,
    district VARCHAR(80) DEFAULT NULL,
    founded_year YEAR DEFAULT NULL,
    employee_count VARCHAR(50) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    job_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

-- Jobs table
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(230) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    responsibilities TEXT DEFAULT NULL,
    requirements TEXT DEFAULT NULL,
    qualifications TEXT DEFAULT NULL,
    benefits TEXT DEFAULT NULL,
    how_to_apply TEXT DEFAULT NULL,
    province VARCHAR(50) DEFAULT NULL,
    district VARCHAR(80) DEFAULT NULL,
    location VARCHAR(150) DEFAULT NULL,
    employment_type ENUM('Full-time','Part-time','Contract','Internship','Temporary','Freelance') DEFAULT 'Full-time',
    experience_level VARCHAR(50) DEFAULT NULL,
    education_level VARCHAR(80) DEFAULT NULL,
    salary_min DECIMAL(12,2) DEFAULT NULL,
    salary_max DECIMAL(12,2) DEFAULT NULL,
    salary_currency VARCHAR(5) DEFAULT 'RWF',
    salary_period VARCHAR(20) DEFAULT 'monthly',
    application_deadline DATE DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    views INT DEFAULT 0,
    applications_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_category (category_id),
    INDEX idx_company (company_id),
    INDEX idx_deadline (application_deadline),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_type VARCHAR(20) DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    announcement_type ENUM('update','news','communication') NOT NULL DEFAULT 'update',
    link_url VARCHAR(500) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_announcements_public (is_active, published_at, sort_order, created_at)
) ENGINE=InnoDB;

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

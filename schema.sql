-- Create database
CREATE DATABASE IF NOT EXISTS dolphin_crm;
USE dolphin_crm;

-- Users Table
CREATE TABLE Users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    role VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Contacts Table
CREATE TABLE Contacts(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(20),
    company VARCHAR(150),
    type VARCHAR(50),
    assigned_to INT,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES Users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES Users(id) ON DELETE CASCADE
);

-- Notes Table
CREATE TABLE Notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES Contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES Users(id) ON DELETE CASCADE
);

-- Insert default admin user
-- Password: password123 (hashed using PASSWORD_DEFAULT)
INSERT INTO Users (firstname, lastname, email, password, role) 
VALUES ('Admin', 'User', 'admin@project2.com', '$2y$10$IuwTF2OHRWhf6Wyst9hH1.GHudQOMuhGt2KbWZJrIyB/mbym.yF3C', 'Admin');
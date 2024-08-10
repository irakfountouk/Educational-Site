
CREATE DATABASE IF NOT EXISTS student3312;
USE student3312;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NULL,
    lastname VARCHAR(100) NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NULL,
    password VARCHAR(100) NOT NULL,
    role INT NOT NULL
);

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    text TEXT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    isAutomated TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender VARCHAR(100) NOT NULL,
    sender_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    text TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    isAutomated TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS homework (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    deadline DATETIME NOT NULL,
    goals TEXT NOT NULL,
    deliverables TEXT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL
);

INSERT INTO users (name, lastname, username, email, password, role) VALUES 
('Admin', 'Admin1', 'admin', 'admin@gmail.com', 'admin123', 0),
('Iraklis', 'Fountoukidis', 'ifountouk', 'irakliff@csd.auth.gr', 'iraklis123', 1);

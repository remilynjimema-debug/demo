-- ============================================================
-- ESTI College Grading Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS esti_grading_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE esti_grading_db;

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_code VARCHAR(20) NOT NULL UNIQUE,
    dept_name VARCHAR(150) NOT NULL,
    chairperson VARCHAR(100),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Faculty Table
CREATE TABLE IF NOT EXISTS faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    department_id INT,
    position VARCHAR(80),
    email VARCHAR(100),
    contact VARCHAR(20),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Subjects Table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    subject_description VARCHAR(200) NOT NULL,
    units INT DEFAULT 3,
    type ENUM('Major','Minor','GE','PE','Elective') DEFAULT 'Major',
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes Table
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(20) NOT NULL,
    section VARCHAR(10) NOT NULL,
    course VARCHAR(20) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    adviser_id INT,
    school_year VARCHAR(20) DEFAULT '2024-2025',
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (adviser_id) REFERENCES faculty(id) ON DELETE SET NULL
);

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    course VARCHAR(20) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    section VARCHAR(10),
    class_id INT,
    email VARCHAR(100),
    contact VARCHAR(20),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    date_enrolled DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- Grades Table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    faculty_id INT,
    class_id INT,
    prelim DECIMAL(5,2),
    midterm DECIMAL(5,2),
    prefinal DECIMAL(5,2),
    final_grade DECIMAL(5,2),
    school_year VARCHAR(20) DEFAULT '2024-2025',
    semester ENUM('1st','2nd','Summer') DEFAULT '1st',
    remarks VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE SET NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin account (password: admin123)
INSERT INTO admins (username, password, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@esti.edu.ph');

-- Departments
INSERT INTO departments (dept_code, dept_name, chairperson, status) VALUES
('BSIT', 'Bachelor of Science in Information Technology', 'Prof. Alvin Santos', 'Active'),
('BSA', 'Bachelor of Science in Accountancy', 'Prof. Rochelle Tan', 'Active'),
('BEED', 'Bachelor of Elementary Education', 'Prof. Liza Hernandez', 'Active'),
('BSHM', 'Bachelor of Science in Hospitality Management', 'Prof. Mark Dionisio', 'Active'),
('BSCRIM', 'Bachelor of Science in Criminology', 'Prof. Michael Ramos', 'Active'),
('BSBA', 'Bachelor of Science in Business Administration', 'Prof. Carlo Reyes', 'Active');

-- Faculty
INSERT INTO faculty (faculty_id, full_name, department_id, position, status) VALUES
('F0001', 'John Michael Cruz', 1, 'Instructor', 'Active'),
('F0002', 'Mary Ann Dela Torre', 1, 'Instructor', 'Active'),
('F0003', 'Alvin Santos', 1, 'Assistant Professor', 'Active'),
('F0004', 'Rochelle Tan', 2, 'Assistant Professor', 'Active'),
('F0005', 'Liza Hernandez', 3, 'Assistant Professor', 'Active'),
('F0006', 'Carlo Reyes', 2, 'Instructor', 'Active'),
('F0007', 'Grace Padilla', 3, 'Instructor', 'Active');

-- Subjects
INSERT INTO subjects (subject_code, subject_description, units, type, status) VALUES
('IT201', 'Data Structures and Algorithms', 3, 'Major', 'Active'),
('IT202', 'Database Management Systems', 3, 'Major', 'Active'),
('IT203', 'Web Systems and Technologies', 3, 'Major', 'Active'),
('IT204', 'Information Assurance and Security', 3, 'Major', 'Active'),
('GE202', 'Purposive Communication', 3, 'GE', 'Active'),
('PE204', 'Physical Fitness and Health', 2, 'PE', 'Active'),
('MATH201', 'Discrete Mathematics', 3, 'Major', 'Active'),
('IT205', 'Object-Oriented Programming', 3, 'Major', 'Active');

-- Classes
INSERT INTO classes (class_name, section, course, year_level, adviser_id, school_year) VALUES
('BSIT', '2A', 'BSIT', '2nd Year', 1, '2024-2025'),
('BSIT', '2B', 'BSIT', '2nd Year', 2, '2024-2025'),
('BSA', '1A', 'BSA', '1st Year', 6, '2024-2025'),
('BEED', '3A', 'BEED', '3rd Year', 5, '2024-2025'),
('BSIT', '3A', 'BSIT', '3rd Year', 3, '2024-2025'),
('BSA', '2A', 'BSA', '2nd Year', 4, '2024-2025'),
('BEED', '1A', 'BEED', '1st Year', 7, '2024-2025');

-- Students
INSERT INTO students (id_number, full_name, course, year_level, section, class_id, status, date_enrolled) VALUES
('20241001', 'Dela Cruz, Juan', 'BSIT', '2nd Year', '2A', 1, 'Active', '2024-06-10'),
('20241002', 'Reyes, Maria', 'BSA', '1st Year', '1A', 3, 'Active', '2024-06-10'),
('20241003', 'Santos, Ana', 'BEED', '3rd Year', '3A', 4, 'Active', '2022-06-10'),
('20241004', 'Garcia, Paolo', 'BSIT', '2nd Year', '2A', 1, 'Inactive', '2024-06-10'),
('20241005', 'Lopez, Kyle', 'BSA', '1st Year', '1A', 3, 'Active', '2024-06-10'),
('20241006', 'Cruz, Danielle', 'BSIT', '2nd Year', '2B', 2, 'Active', '2024-06-10'),
('20241007', 'Villanueva, James', 'BSA', '3rd Year', '2A', 6, 'Active', '2022-06-10'),
('20241008', 'Torres, Andrea', 'BEED', '1st Year', '1A', 7, 'Active', '2024-06-10');

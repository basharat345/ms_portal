CREATE DATABASE IF NOT EXISTS mrs_pro;
USE mrs_pro;

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_code VARCHAR(50) UNIQUE NOT NULL,
    client_name VARCHAR(150) NOT NULL,
    name VARCHAR(150) NOT NULL,
    type ENUM('mystery_shopping', 'research', 'both') DEFAULT 'mystery_shopping',
    status ENUM('pending', 'execution', 'delivered') DEFAULT 'pending',
    start_date DATE NOT NULL,
    deadline DATE NOT NULL,
    script_file VARCHAR(255),
    audio_file VARCHAR(255),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'field_manager', 'cs_team', 'production', 'mis', 'mystery_shopper') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    branch_name VARCHAR(150) NOT NULL,
    branch_code VARCHAR(50) NOT NULL,
    city VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    contact_person VARCHAR(150),
    contact_number VARCHAR(50),
    status ENUM('pending', 'assigned', 'submitted', 'in_edit', 'mis_compile', 'done') DEFAULT 'pending',
    shopper_link_token VARCHAR(64) UNIQUE,
    assigned_shopper_id INT,
    production_video_path VARCHAR(255),
    mis_report_path VARCHAR(255),
    visit_date DATE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_shopper_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL UNIQUE,
    shopper_id INT,
    video_path VARCHAR(255),
    form_data JSON,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (shopper_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Default demo users (password is admin123)
INSERT IGNORE INTO users (name, email, password, role) VALUES 
('System Admin', 'admin@mrspro.com', '$2y$10$3zR14QkXh/uVz6Cg.w/0pOGjXF60r8jInR2L9q0c2e3I1Yk/O.Mqi', 'admin'),
('Demo CS Team', 'cs@mrspro.com', '$2y$10$3zR14QkXh/uVz6Cg.w/0pOGjXF60r8jInR2L9q0c2e3I1Yk/O.Mqi', 'cs_team'),
('Demo Field Manager', 'field@mrspro.com', '$2y$10$3zR14QkXh/uVz6Cg.w/0pOGjXF60r8jInR2L9q0c2e3I1Yk/O.Mqi', 'field_manager'),
('Demo Production Team', 'production@mrspro.com', '$2y$10$3zR14QkXh/uVz6Cg.w/0pOGjXF60r8jInR2L9q0c2e3I1Yk/O.Mqi', 'production'),
('Demo MIS Team', 'mis@mrspro.com', '$2y$10$3zR14QkXh/uVz6Cg.w/0pOGjXF60r8jInR2L9q0c2e3I1Yk/O.Mqi', 'mis'),
('Demo Shopper', 'shopper@mrspro.com', '$2y$10$3zR14QkXh/uVz6Cg.w/0pOGjXF60r8jInR2L9q0c2e3I1Yk/O.Mqi', 'mystery_shopper');

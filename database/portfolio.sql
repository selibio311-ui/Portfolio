-- =====================================================
-- Troy L. Selibio – Dynamic Portfolio
-- MySQL schema + sample data
-- Import with phpMyAdmin or:  mysql -u root -p < database/portfolio.sql
--
-- NOTE: No administrator account is included on purpose.
-- Create it with admin/setup.php (see README.md).
-- =====================================================

CREATE DATABASE IF NOT EXISTS portfolio_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS achievements;
DROP TABLE IF EXISTS experience;
DROP TABLE IF EXISTS education;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE settings (
    setting_key   VARCHAR(64) NOT NULL PRIMARY KEY,
    setting_value TEXT        NOT NULL
) ENGINE=InnoDB;

CREATE TABLE skills (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    category    VARCHAR(20)  NOT NULL,          -- technical | tool | personal | language
    level       TINYINT UNSIGNED NOT NULL DEFAULT 0,  -- 0-100
    icon        VARCHAR(16)  NOT NULL DEFAULT '',
    description VARCHAR(255) NOT NULL DEFAULT '',
    sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE projects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    category    VARCHAR(20)  NOT NULL,          -- web | system | design
    label       VARCHAR(60)  NOT NULL DEFAULT '',
    description TEXT         NOT NULL,
    tech        VARCHAR(255) NOT NULL DEFAULT '',  -- comma separated
    image       VARCHAR(255) NOT NULL DEFAULT '',
    project_url VARCHAR(255) NOT NULL DEFAULT '',
    sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE education (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school     VARCHAR(150) NOT NULL,
    level      VARCHAR(150) NOT NULL DEFAULT '',
    details    VARCHAR(255) NOT NULL DEFAULT '',
    period     VARCHAR(40)  NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE experience (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    period      VARCHAR(40)  NOT NULL DEFAULT '',
    description TEXT         NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE achievements (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    issuer      VARCHAR(150) NOT NULL DEFAULT '',
    year        VARCHAR(20)  NOT NULL DEFAULT '',
    description VARCHAR(500) NOT NULL DEFAULT '',
    sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL,
    subject    VARCHAR(150)  NOT NULL,
    message    TEXT          NOT NULL,
    is_read    TINYINT(1)    NOT NULL DEFAULT 0,
    ip_address VARCHAR(45)   NOT NULL DEFAULT '',
    created_at DATETIME      NOT NULL
) ENGINE=InnoDB;

CREATE TABLE login_attempts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45) NOT NULL,
    attempted_at INT UNSIGNED NOT NULL,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB;

-- ---------------- Sample data ----------------

INSERT INTO settings (setting_key, setting_value) VALUES
('site_title',      'Troy Selibio | Portfolio'),
('site_logo_text',  'Troy Selibio'),
('site_logo',       ''),
('footer_text',     'Troy L. Selibio. All Rights Reserved.'),
('default_theme',   'dark'),
('accent_color',    '#22d3ee'),
('hero_greeting',   'Hello, I''m'),
('full_name',       'Troy L. Selibio'),
('tagline',         'Student • IT Enthusiast • Future IT Professional'),
('intro',           'I am a hardworking individual who is willing to learn and accept new ideas that have real-world applications. I enjoy developing my skills through education, projects, and practical experiences.'),
('welcome_text',    'This portfolio presents my background, skills, education, experiences, and projects that I have developed as an Information Technology student.'),
('bio',             'I am a hardworking individual who is willing to learn and accept new ideas that have real-world applications. I am a good listener and student who can communicate with others in a group as well as one-on-one. I can motivate people and concentrate my skills to accomplish objectives.'),
('bio_secondary',   'Through education, projects, and practical experiences, I continue to develop my skills in technology, communication, and problem-solving.'),
('interests',       'Web development, computer systems, graphic design, animation with Adobe Animate, and building practical IT solutions.'),
('goal',            'My goal is to become a skilled IT professional who can create useful and reliable technology solutions. I am interested in web development, software applications, computer hardware, and continuous learning in emerging technologies.'),
('email',           'selibio311@gmail.com'),
('phone',           '09638808309'),
('address',         'Purok 4, Poblacion Basay, Negros Oriental, Philippines, 6222'),
('facebook_url',    'https://www.facebook.com/troy.selibio'),
('github_url',      'https://github.com/selibio311-ui'),
('profile_image',   'assets/images/profile.jpg'),
('resume_file',     'TROY-RESUME-Final.pdf');

INSERT INTO skills (name, category, level, icon, description, sort_order) VALUES
('HTML',                 'technical', 45, '', '', 1),
('CSS',                  'technical', 45, '', '', 2),
('JavaScript',           'technical', 45, '', '', 3),
('Java',                 'technical', 45, '', '', 4),
('PHP',                  'technical', 45, '', '', 5),
('Database Management',  'technical', 45, '', '', 6),
('Graphic Design',       'technical', 45, '', '', 7),
('Adobe Animate',        'technical', 45, '', '', 8),
('HTML5',          'tool', 0, '🌐', 'Creating the structure and content of websites.', 1),
('CSS3',           'tool', 0, '🎨', 'Designing web pages and creating responsive layouts.', 2),
('JavaScript',     'tool', 0, '⚡', 'Adding interactive and useful features to websites.', 3),
('Java',           'tool', 0, '☕', 'Developing programming exercises and applications.', 4),
('MySQL',          'tool', 0, '🗄️', 'Managing databases and storing application data.', 5),
('Adobe Animate',  'tool', 0, '🎬', 'Creating animated advertisements and visual projects.', 6),
('Communication Skills', 'personal', 90, '', 'I can communicate with others in a group as well as one-on-one.', 1),
('Adaptability',         'personal', 90, '', 'I am willing to learn and accept new ideas and adjust to different situations.', 2),
('Tagalog', 'language', 0, '', '', 1),
('Bisaya',  'language', 0, '', '', 2),
('English', 'language', 0, '', '', 3);

INSERT INTO projects (title, category, label, description, tech, image, project_url, sort_order) VALUES
('RepairPro – Repair Scheduling System', 'system', 'System',
 'A computerized scheduling system designed to organize repair requests, technician schedules, customer information, and repair activities.\n\nThe system is intended to help reduce scheduling conflicts and organize repair service information.',
 'HTML, CSS, JavaScript, PHP', 'assets/images/project1.jpg', '', 1),
('TroyTech', 'system', 'Computer Shop',
 'TroyTech is a computer shop concept focused on computer-related services and technology needs.\n\nThe project represents my interest in computers, technology, and computer service management.',
 'Computer Technology, Business, IT', 'assets/images/project2.jpg', '', 2),
('TroyTech Advertisement', 'design', 'Animation',
 'A two-minute animated product advertisement created in Adobe Animate.\n\nThe project uses animated text, motion tweens, shape tweens, transitions, visual effects, and sound integration.',
 'Adobe Animate, Animation, Design', 'assets/images/project3.jpg', '', 3),
('Personal Portfolio Website', 'web', 'Web',
 'A responsive personal portfolio website created to present my background, skills, projects, education, and experience.\n\nThe website now uses PHP and MySQL with an administrator dashboard for managing its content.',
 'HTML, CSS, JavaScript, PHP, MySQL', 'assets/images/project4.jpg', '', 4);

INSERT INTO education (school, level, details, period, sort_order) VALUES
('Basay National High School', 'Senior High School - General Academic Strand', '', '2021 – 2022', 1),
('Basay National High School', 'Junior High School', 'ICT VII, Caregiving VIII, IX, X', '2015 – 2020', 2),
('Bongalongan Elementary School', '', 'Grade I', '2009 – 2010', 3),
('Basay Central School', '', 'Grade II, III, IV, V, VI', '2010 – 2014', 4);

INSERT INTO experience (title, period, description, sort_order) VALUES
('Computer Shop Manager', '2022 – 2024', 'Computer shop management experience involving daily shop activities and assisting customers with computer-related needs.', 1),
('Facilitator on Work Immersion Program', '2022 – 2023', 'Experience assisting and facilitating activities connected with the Work Immersion Program.', 2),
('Salesboy at Elena Store', '2016 – 2017', 'Experience assisting customers and supporting daily store activities.', 3);

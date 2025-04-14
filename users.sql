ALTER TABLE doctors ADD COLUMN user_id INT AFTER id;
ALTER TABLE patients ADD COLUMN user_id INT AFTER id;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'patient') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE doctors
ADD FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE patients
ADD FOREIGN KEY (user_id) REFERENCES users(id);

-- Create admin user
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$vbNfrdj/ho6EmoKwIaeB.OxwNw6Zl5ZdqqpAT3zrleB8C/PacWRse', 'admin');
-- Default password: admin
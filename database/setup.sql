-- =============================================================
-- database/setup.sql
-- Student Feedback Application – Database Setup
-- Run as root or a user with CREATE DATABASE privileges:
--   mysql -u root -p < database/setup.sql
-- =============================================================

-- 1. Create database
CREATE DATABASE IF NOT EXISTS student_feedback
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE student_feedback;

-- 2. Create the feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id           INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(120) NOT NULL,
    email        VARCHAR(255) NOT NULL,
    rating       TINYINT      NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment      TEXT,
    submitted_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create a dedicated application user (least-privilege)
--    Change the password before running in production!
CREATE USER IF NOT EXISTS 'appuser'@'localhost' IDENTIFIED BY 'AppPass123!';
GRANT SELECT, INSERT, UPDATE, DELETE ON student_feedback.* TO 'appuser'@'localhost';
FLUSH PRIVILEGES;

-- 4. Sample data – verify the table works
INSERT INTO feedback (name, email, rating, comment) VALUES
  ('Alice Johnson',  'alice@example.com',  5, 'Outstanding course content and delivery!'),
  ('Bob Smith',      'bob@example.com',    4, 'Very informative. Pacing could be slightly slower.'),
  ('Carol Williams', 'carol@example.com',  3, 'Average experience, but learned a lot overall.'),
  ('David Lee',      'david@example.com',  5, 'Best web dev class I have attended!'),
  ('Eva Martinez',   'eva@example.com',    4, 'Great hands-on projects. Would recommend.');

-- 5. Quick verification
SELECT 'Database setup complete!' AS status;
SELECT COUNT(*) AS sample_rows FROM feedback;

-- Add type column to Announcements table
ALTER TABLE Announcements ADD COLUMN type ENUM('post', 'deadline') DEFAULT 'post' AFTER content_ar;

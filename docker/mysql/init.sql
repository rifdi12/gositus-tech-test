-- Initialize database for E-Library
-- This file is automatically executed when MySQL container starts

USE elibrary_db;

-- Create additional indexes for better performance
-- (Tables will be created by CodeIgniter migrations)

-- Set proper charset and collation
ALTER DATABASE elibrary_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
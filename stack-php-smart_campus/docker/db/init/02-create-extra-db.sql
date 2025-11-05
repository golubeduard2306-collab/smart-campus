-- Create a second database on first initialization
-- This script is executed by the official MariaDB entrypoint when the data directory is empty.
-- The first database is provided via MYSQL_DATABASE=iut

-- Change this name if you need a different extra database
CREATE DATABASE IF NOT EXISTS `iut_sandbox` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant same privileges to the standard user as on the main database
-- The user and password are provided via MYSQL_USER and MYSQL_PASSWORD env vars, but here
-- we cannot reference env vars inside SQL. We rely on the default 'iut' values used in docker-compose.
GRANT ALL PRIVILEGES ON `iut_sandbox`.* TO 'iut'@'%';
FLUSH PRIVILEGES;

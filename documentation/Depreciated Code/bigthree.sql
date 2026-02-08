-- 1. Tenants (Companies)
CREATE TABLE tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    plan_level ENUM('basic', 'pro', 'enterprise') DEFAULT 'basic',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    timezone VARCHAR(50) DEFAULT 'America/New_York',
    language VARCHAR(10) DEFAULT 'en',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- 3. User Module Access (The Permission Matrix)
CREATE TABLE user_permissions (
    user_id INT NOT NULL,
    module_slug VARCHAR(50) NOT NULL, -- e.g., 'capture', 'intel'
    access_level ENUM('none', 'viewer', 'user', 'manager', 'admin') DEFAULT 'none',
    PRIMARY KEY (user_id, module_slug),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 1. Create a Demo Tenant
INSERT INTO tenants (company_name, plan_level) 
VALUES ('Acme Gov Solutions', 'enterprise');

-- 2. Create your Admin User 
-- (The password below is 'password123' hashed)
INSERT INTO users (tenant_id, email, password_hash, full_name, timezone) 
VALUES (1, 'admin@acme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'America/New_York');

-- 3. Set Permissions for the 7 Modules
INSERT INTO user_permissions (user_id, module_slug, access_level) VALUES
(1, 'capture', 'admin'),
(1, 'intel', 'admin'),
(1, 'library', 'admin'),
(1, 'proposal', 'admin'),
(1, 'debrief', 'admin'),
(1, 'analytics', 'admin'),
(1, 'system', 'admin');

-- 1. Split the name columns
ALTER TABLE users 
DROP COLUMN full_name,
ADD COLUMN first_name VARCHAR(50) AFTER tenant_id,
ADD COLUMN last_name VARCHAR(50) AFTER first_name;

-- 2. Update the user record
-- This uses the Bcrypt hash for 'Rdabites1'
UPDATE users SET 
    first_name = 'Platform',
    last_name = 'Admin',
    email = 'admin@proposalwon.com',
    password_hash = '$2y$10$TksfW5GfT8yFmS.0M9.5A.W.fW/mHlKjT7/YvT3iK/R0vB7XU3.yG' 
WHERE id = 1;
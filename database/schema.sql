CREATE DATABASE IF NOT EXISTS e_archive CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE e_archive;

CREATE TABLE IF NOT EXISTS dev_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  is_superadmin TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE NOT NULL,
  description TEXT NULL
);

CREATE TABLE IF NOT EXISTS tenants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) UNIQUE NOT NULL,
  plan_id INT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (plan_id) REFERENCES plans(id)
);

CREATE TABLE IF NOT EXISTS tenant_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  logo_path VARCHAR(255) NULL,
  organization_name VARCHAR(120) NULL,
  numbering_pattern VARCHAR(60) NULL,
  default_confidentiality VARCHAR(30) DEFAULT 'normal',
  max_users INT NOT NULL DEFAULT 25,
  max_storage_mb INT NOT NULL DEFAULT 1024,
  max_documents INT NOT NULL DEFAULT 50000,
  theme_mode ENUM('light','dark') NOT NULL DEFAULT 'light',
  primary_color VARCHAR(20) NOT NULL DEFAULT '#0d6efd',
  sidebar_modules_json JSON NULL,
  dashboard_widgets_json JSON NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uk_tenant_settings (tenant_id),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  name VARCHAR(80) NOT NULL,
  key_name VARCHAR(80) NOT NULL,
  created_at DATETIME NOT NULL,
  UNIQUE KEY uk_role_per_tenant (tenant_id,key_name),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  role_id INT NOT NULL,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  UNIQUE KEY uk_user_tenant_username (tenant_id, username),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE IF NOT EXISTS permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  key_name VARCHAR(120) UNIQUE NOT NULL,
  description VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id INT NOT NULL,
  permission_id INT NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id),
  FOREIGN KEY (permission_id) REFERENCES permissions(id)
);

CREATE TABLE IF NOT EXISTS modules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  key_name VARCHAR(80) UNIQUE NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS plan_modules (
  plan_id INT NOT NULL,
  module_id INT NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (plan_id, module_id),
  FOREIGN KEY (plan_id) REFERENCES plans(id),
  FOREIGN KEY (module_id) REFERENCES modules(id)
);

CREATE TABLE IF NOT EXISTS tenant_modules (
  tenant_id INT NOT NULL,
  module_id INT NOT NULL,
  enabled TINYINT(1) NOT NULL,
  PRIMARY KEY (tenant_id, module_id),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (module_id) REFERENCES modules(id)
);

CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

CREATE TABLE IF NOT EXISTS documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  type ENUM('inbound','outbound') NOT NULL,
  subject VARCHAR(255) NOT NULL,
  doc_number VARCHAR(80) NOT NULL,
  received_date DATE NULL,
  sent_date DATE NULL,
  sender_entity VARCHAR(160) NULL,
  receiver_entity VARCHAR(160) NULL,
  priority VARCHAR(20) NOT NULL DEFAULT 'normal',
  confidentiality VARCHAR(30) NOT NULL DEFAULT 'normal',
  status VARCHAR(30) NOT NULL DEFAULT 'new',
  department_id INT NULL,
  created_by INT NOT NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (department_id) REFERENCES departments(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS document_attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  document_id INT NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  mime VARCHAR(120) NOT NULL,
  size BIGINT NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (document_id) REFERENCES documents(id)
);

CREATE TABLE IF NOT EXISTS tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  name VARCHAR(80) NOT NULL,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

CREATE TABLE IF NOT EXISTS document_tags (
  tenant_id INT NOT NULL,
  document_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (tenant_id, document_id, tag_id),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (document_id) REFERENCES documents(id),
  FOREIGN KEY (tag_id) REFERENCES tags(id)
);

CREATE TABLE IF NOT EXISTS document_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  document_id INT NOT NULL,
  linked_document_id INT NOT NULL,
  relation_type VARCHAR(50) NOT NULL,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (document_id) REFERENCES documents(id),
  FOREIGN KEY (linked_document_id) REFERENCES documents(id)
);

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  document_id INT NOT NULL,
  assigned_to INT NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  due_date DATE NULL,
  comments TEXT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (document_id) REFERENCES documents(id),
  FOREIGN KEY (assigned_to) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  actor_type ENUM('dev','user') NOT NULL,
  actor_id INT NULL,
  action VARCHAR(80) NOT NULL,
  entity VARCHAR(100) NOT NULL,
  entity_id INT NULL,
  ip VARCHAR(64) NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_tenant_created (tenant_id, created_at)
);

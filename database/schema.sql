-- Oshi-Wiki v1.0 Database Schema
-- MySQL 8.x / utf8mb4

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS taggings;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS curator_assignments;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS source_references;
DROP TABLE IF EXISTS sources;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS organizations;
DROP TABLE IF EXISTS worlds;
DROP TABLE IF EXISTS terms;
DROP TABLE IF EXISTS relationships;
DROP TABLE IF EXISTS appellations;
DROP TABLE IF EXISTS speech_profiles;
DROP TABLE IF EXISTS characters;
DROP TABLE IF EXISTS works;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  label VARCHAR(100) NOT NULL,
  description TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  permission_key VARCHAR(150) NOT NULL UNIQUE,
  label VARCHAR(150) NOT NULL,
  description TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_role_permission (role_id, permission_id),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_admin_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE works (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  title_kana VARCHAR(255) NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  genre VARCHAR(100) NULL,
  original_media VARCHAR(100) NULL,
  official_url VARCHAR(500) NULL,
  guideline_url VARCHAR(500) NULL,
  description TEXT NULL,
  status ENUM('draft','published','private','archived','deleted') NOT NULL DEFAULT 'draft',
  review_status ENUM('unreviewed','reviewing','approved','rejected','needs_revision') NOT NULL DEFAULT 'unreviewed',
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  INDEX idx_works_status (status),
  INDEX idx_works_title (title),
  CONSTRAINT fk_works_created_by FOREIGN KEY (created_by) REFERENCES admin_users(id),
  CONSTRAINT fk_works_updated_by FOREIGN KEY (updated_by) REFERENCES admin_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE characters (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  name_kana VARCHAR(255) NULL,
  slug VARCHAR(255) NOT NULL,
  alias TEXT NULL,
  gender VARCHAR(100) NULL,
  age VARCHAR(100) NULL,
  birthday VARCHAR(100) NULL,
  height VARCHAR(100) NULL,
  weight VARCHAR(100) NULL,
  blood_type VARCHAR(100) NULL,
  affiliation VARCHAR(255) NULL,
  role_name VARCHAR(255) NULL,
  grade_class VARCHAR(255) NULL,
  first_appearance VARCHAR(255) NULL,
  personality_summary TEXT NULL,
  appearance_summary TEXT NULL,
  background_summary TEXT NULL,
  creative_note TEXT NULL,
  status ENUM('draft','published','private','archived','deleted') NOT NULL DEFAULT 'draft',
  review_status ENUM('unreviewed','reviewing','approved','rejected','needs_revision') NOT NULL DEFAULT 'unreviewed',
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_characters_work_slug (work_id, slug),
  INDEX idx_characters_work_id (work_id),
  INDEX idx_characters_name (name),
  CONSTRAINT fk_characters_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE,
  CONSTRAINT fk_characters_created_by FOREIGN KEY (created_by) REFERENCES admin_users(id),
  CONSTRAINT fk_characters_updated_by FOREIGN KEY (updated_by) REFERENCES admin_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE speech_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  character_id BIGINT UNSIGNED NOT NULL UNIQUE,
  first_person VARCHAR(255) NULL,
  second_person VARCHAR(255) NULL,
  third_person VARCHAR(255) NULL,
  tone_summary TEXT NULL,
  endings TEXT NULL,
  catchphrases TEXT NULL,
  polite_speech TEXT NULL,
  angry_tone TEXT NULL,
  shy_tone TEXT NULL,
  sad_tone TEXT NULL,
  battle_tone TEXT NULL,
  forbidden_expressions TEXT NULL,
  writing_tips TEXT NULL,
  status ENUM('draft','published','private','archived','deleted') NOT NULL DEFAULT 'draft',
  review_status ENUM('unreviewed','reviewing','approved','rejected','needs_revision') NOT NULL DEFAULT 'unreviewed',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_speech_profiles_character FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE appellations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_id BIGINT UNSIGNED NOT NULL,
  from_character_id BIGINT UNSIGNED NOT NULL,
  to_character_id BIGINT UNSIGNED NOT NULL,
  appellation VARCHAR(255) NOT NULL,
  scene VARCHAR(255) NULL,
  note TEXT NULL,
  status ENUM('draft','published','private','archived','deleted') NOT NULL DEFAULT 'draft',
  review_status ENUM('unreviewed','reviewing','approved','rejected','needs_revision') NOT NULL DEFAULT 'unreviewed',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  INDEX idx_appellations_work (work_id),
  INDEX idx_appellations_from_to (from_character_id, to_character_id),
  CONSTRAINT fk_appellations_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE,
  CONSTRAINT fk_appellations_from FOREIGN KEY (from_character_id) REFERENCES characters(id) ON DELETE CASCADE,
  CONSTRAINT fk_appellations_to FOREIGN KEY (to_character_id) REFERENCES characters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE relationships (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_id BIGINT UNSIGNED NOT NULL,
  from_character_id BIGINT UNSIGNED NOT NULL,
  to_character_id BIGINT UNSIGNED NOT NULL,
  relationship_type VARCHAR(100) NULL,
  description TEXT NULL,
  status ENUM('draft','published','private','archived','deleted') NOT NULL DEFAULT 'draft',
  review_status ENUM('unreviewed','reviewing','approved','rejected','needs_revision') NOT NULL DEFAULT 'unreviewed',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  INDEX idx_relationships_work (work_id),
  INDEX idx_relationships_from_to (from_character_id, to_character_id),
  CONSTRAINT fk_relationships_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE,
  CONSTRAINT fk_relationships_from FOREIGN KEY (from_character_id) REFERENCES characters(id) ON DELETE CASCADE,
  CONSTRAINT fk_relationships_to FOREIGN KEY (to_character_id) REFERENCES characters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE terms (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  name_kana VARCHAR(255) NULL,
  term_type VARCHAR(100) NULL,
  description TEXT NULL,
  status ENUM('draft','published','private','archived','deleted') NOT NULL DEFAULT 'draft',
  review_status ENUM('unreviewed','reviewing','approved','rejected','needs_revision') NOT NULL DEFAULT 'unreviewed',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  INDEX idx_terms_work (work_id),
  INDEX idx_terms_name (name),
  CONSTRAINT fk_terms_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE worlds (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  era TEXT NULL,
  geography TEXT NULL,
  society TEXT NULL,
  rules TEXT NULL,
  magic_or_ability TEXT NULL,
  note TEXT NULL,
  status ENUM('draft','published','private','archived','deleted') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_worlds_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organizations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  status ENUM('draft','published','private','archived','deleted') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_organizations_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE locations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  status ENUM('draft','published','private','archived','deleted') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_locations_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sources (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_id BIGINT UNSIGNED NULL,
  source_type VARCHAR(100) NOT NULL,
  title VARCHAR(255) NOT NULL,
  volume VARCHAR(100) NULL,
  episode VARCHAR(100) NULL,
  chapter VARCHAR(100) NULL,
  page VARCHAR(100) NULL,
  url VARCHAR(500) NULL,
  checked_at DATE NULL,
  note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_sources_work (work_id),
  CONSTRAINT fk_sources_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE source_references (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_id BIGINT UNSIGNED NOT NULL,
  reference_type VARCHAR(100) NOT NULL,
  reference_id BIGINT UNSIGNED NOT NULL,
  note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_source_references_target (reference_type, reference_id),
  CONSTRAINT fk_source_references_source FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE submissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  target_type VARCHAR(100) NULL,
  target_id BIGINT UNSIGNED NULL,
  submitter_name VARCHAR(100) NULL,
  submitter_contact VARCHAR(255) NULL,
  category ENUM('canon','summary','interpretation','creative_note','delete_request','other') NOT NULL DEFAULT 'other',
  title VARCHAR(255) NULL,
  content TEXT NOT NULL,
  source_text TEXT NULL,
  status ENUM('pending','reviewing','approved','rejected','needs_revision','archived') NOT NULL DEFAULT 'pending',
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_submissions_status (status),
  CONSTRAINT fk_submissions_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES admin_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  submission_id BIGINT UNSIGNED NOT NULL,
  reviewer_id BIGINT UNSIGNED NOT NULL,
  result ENUM('approved','rejected','needs_revision','comment') NOT NULL,
  comment TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_reviewer FOREIGN KEY (reviewer_id) REFERENCES admin_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE curator_assignments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_user_id BIGINT UNSIGNED NOT NULL,
  work_id BIGINT UNSIGNED NOT NULL,
  assigned_by BIGINT UNSIGNED NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_curator_work (admin_user_id, work_id),
  CONSTRAINT fk_curator_assignments_user FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_curator_assignments_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE,
  CONSTRAINT fk_curator_assignments_assigned_by FOREIGN KEY (assigned_by) REFERENCES admin_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_user_id BIGINT UNSIGNED NULL,
  action VARCHAR(150) NOT NULL,
  target_type VARCHAR(100) NULL,
  target_id BIGINT UNSIGNED NULL,
  before_data JSON NULL,
  after_data JSON NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_logs_target (target_type, target_id),
  INDEX idx_audit_logs_action (action),
  CONSTRAINT fk_audit_logs_admin_user FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tags (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(100) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE taggings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tag_id BIGINT UNSIGNED NOT NULL,
  taggable_type VARCHAR(100) NOT NULL,
  taggable_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_taggings_target (taggable_type, taggable_id),
  CONSTRAINT fk_taggings_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
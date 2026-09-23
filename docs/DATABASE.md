# Database Schema

## Custom Tables
Prefix used: `{prefix}clubcore_` (where `{prefix}` is `$wpdb->prefix`)

### 1. clubcore_members
Stores core member data.
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `wp_user_id`: BIGINT(20) UNSIGNED NULL (FK to wp_users)
- `phone_normalized`: VARCHAR(20) NOT NULL (UNIQUE INDEX)
- `first_name`: VARCHAR(100)
- `last_name`: VARCHAR(100)
- `status`: VARCHAR(20) NOT NULL DEFAULT 'active'
- `source`: VARCHAR(50) NOT NULL
- `created_at`: DATETIME NOT NULL
- `updated_at`: DATETIME NOT NULL

### 2. clubcore_sms_logs
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `member_id`: BIGINT(20) UNSIGNED NULL
- `phone`: VARCHAR(20) NOT NULL
- `message_type`: VARCHAR(50)
- `provider_status`: VARCHAR(50)
- `provider_reference`: VARCHAR(100)
- `error_message`: TEXT
- `sent_at`: DATETIME NOT NULL

### 3. clubcore_audit_logs
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT(20) UNSIGNED NULL
- `action`: VARCHAR(100) NOT NULL
- `entity_type`: VARCHAR(50)
- `entity_id`: BIGINT(20) UNSIGNED
- `details`: LONGTEXT (JSON)
- `ip_hash`: VARCHAR(64)
- `created_at`: DATETIME NOT NULL

### 4. clubcore_import_jobs
- `id`: BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT(20) UNSIGNED
- `file_name`: VARCHAR(255)
- `status`: VARCHAR(20) NOT NULL
- `total_rows`: INT UNSIGNED DEFAULT 0
- `processed_rows`: INT UNSIGNED DEFAULT 0
- `errors`: LONGTEXT (JSON)
- `created_at`: DATETIME NOT NULL
- `updated_at`: DATETIME NOT NULL

## Migration Strategy
- Managed via `dbDelta()`.
- Versioning is tracked in WordPress options (`clubcore_db_version`).
- `dbDelta()` limitations are respected (e.g., exact formatting required, index syntax limitations).

## Charset & Collation
Inherits WordPress default (`$wpdb->get_charset_collate()`).

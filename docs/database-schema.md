# Database Schema

Complete database structure for Laravel Settings.

## Tables

### settings

Global application settings.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned | NO | - | Primary key |
| `group` | varchar(255) | YES | NULL | Group name |
| `key` | varchar(255) | NO | - | Unique setting key |
| `label` | json | YES | NULL | Translated labels |
| `description` | json | YES | NULL | Translated descriptions |
| `value` | text | YES | NULL | Setting value |
| `type` | varchar(50) | NO | 'string' | Data type |
| `encrypted` | boolean | NO | false | Is value encrypted |
| `validation_rules` | json | YES | NULL | Validation rules array |
| `options` | json | YES | NULL | Translated options |
| `input_type` | varchar(50) | NO | 'text' | UI input type |
| `is_public` | boolean | NO | false | Publicly accessible |
| `order` | integer | NO | 0 | Display order |
| `view_permissions` | json | YES | NULL | View permission names |
| `edit_permissions` | json | YES | NULL | Edit permission names |
| `view_permission_type` | varchar(50) | NO | 'public' | Permission type |
| `edit_permission_type` | varchar(50) | NO | 'authenticated' | Permission type |
| `created_at` | timestamp | YES | NULL | Creation timestamp |
| `updated_at` | timestamp | YES | NULL | Update timestamp |

**Indexes:**
- Primary key on `id`
- Unique index on `key`
- Index on `group`

---

### user_settings

Per-user settings and preferences.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned | NO | - | Primary key |
| `user_id` | bigint unsigned | NO | - | Foreign key to users |
| `key` | varchar(255) | NO | - | Setting key |
| `label` | json | YES | NULL | Translated labels |
| `description` | json | YES | NULL | Translated descriptions |
| `value` | text | YES | NULL | Setting value |
| `type` | varchar(50) | NO | 'string' | Data type |
| `encrypted` | boolean | NO | false | Is value encrypted |
| `validation_rules` | json | YES | NULL | Validation rules |
| `options` | json | YES | NULL | Translated options |
| `input_type` | varchar(50) | NO | 'text' | UI input type |
| `order` | integer | NO | 0 | Display order |
| `created_at` | timestamp | YES | NULL | Creation timestamp |
| `updated_at` | timestamp | YES | NULL | Update timestamp |

**Indexes:**
- Primary key on `id`
- Foreign key on `user_id` → `users.id` (cascade on delete)
- Index on `key`
- Unique index on `(user_id, key)`

---

### setting_histories

Audit trail for all setting changes.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned | NO | - | Primary key |
| `setting_key` | varchar(255) | NO | - | Changed setting key |
| `old_value` | text | YES | NULL | Previous value |
| `new_value` | text | YES | NULL | New value |
| `old_type` | varchar(50) | YES | NULL | Previous type |
| `new_type` | varchar(50) | YES | NULL | New type |
| `user_id` | bigint unsigned | YES | NULL | User who made change |
| `ip_address` | varchar(45) | YES | NULL | IP address |
| `user_agent` | text | YES | NULL | Browser/client |
| `action` | varchar(50) | NO | 'updated' | Action type |
| `created_at` | timestamp | YES | NULL | Change timestamp |
| `updated_at` | timestamp | YES | NULL | Update timestamp |

**Indexes:**
- Primary key on `id`
- Index on `setting_key`
- Index on `user_id`
- Foreign key on `user_id` → `users.id` (nullable)

---

## Relationships

```
users
  ├── hasMany → user_settings
  └── hasMany → setting_histories

settings
  └── (no direct relationships)

user_settings
  └── belongsTo → users

setting_histories
  └── belongsTo → users
```

## Data Types

### type Column Values

- `string` - Text values
- `int` / `integer` - Whole numbers
- `float` - Decimal numbers
- `bool` / `boolean` - True/false
- `array` - PHP arrays (stored as JSON)
- `json` - JSON objects

### input_type Column Values

- `text` - Single-line text input
- `textarea` - Multi-line text
- `number` - Numeric input
- `email` - Email input
- `url` - URL input
- `password` - Password input
- `select` - Dropdown select
- `checkbox` - Checkbox
- `radio` - Radio buttons
- `date` - Date picker
- `time` - Time picker
- `datetime` - Date and time picker

### view_permission_type / edit_permission_type Values

- `public` - No authentication required
- `authenticated` - Any authenticated user
- `roles` - Specific roles required
- `permissions` - Specific permissions required

### action Column Values (setting_histories)

- `created` - Setting was created
- `updated` - Setting was modified
- `deleted` - Setting was removed

---

[← REST API](rest-api.md) | [Recipes →](recipes.md)

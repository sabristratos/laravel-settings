# REST API

Laravel Settings provides optional REST API endpoints for managing settings via HTTP.

## Table of Contents

- [Configuration](#configuration)
- [Authentication](#authentication)
- [Endpoints](#endpoints)
- [Responses](#responses)

## Configuration

Enable the REST API in `config/settings.php`:

```php
'api' => [
    'enabled' => env('SETTINGS_API_ENABLED', true),
    'prefix' => env('SETTINGS_API_PREFIX', 'api/settings'),
    'middleware' => ['api', 'auth:sanctum'],
],
```

## Authentication

API endpoints use Laravel Sanctum by default. Ensure you have a valid API token.

## Endpoints

### List All Settings

**GET** `/api/settings`

```bash
curl -H "Authorization: Bearer {token}" \
     https://example.com/api/settings
```

**Response:**

```json
{
  "data": [
    {
      "key": "site.name",
      "value": "My Application",
      "type": "string",
      "group": "site"
    }
  ]
}
```

### Get Setting

**GET** `/api/settings/{key}`

```bash
curl -H "Authorization: Bearer {token}" \
     https://example.com/api/settings/site.name
```

**Response:**

```json
{
  "data": {
    "key": "site.name",
    "value": "My Application",
    "type": "string",
    "group": "site",
    "is_public": true
  }
}
```

### Create/Update Setting

**POST** `/api/settings`

```bash
curl -X POST \
     -H "Authorization: Bearer {token}" \
     -H "Content-Type: application/json" \
     -d '{"key":"site.name","value":"New Name","group":"site"}' \
     https://example.com/api/settings
```

**Response:**

```json
{
  "data": {
    "key": "site.name",
    "value": "New Name",
    "type": "string",
    "group": "site"
  },
  "message": "Setting updated successfully"
}
```

### Delete Setting

**DELETE** `/api/settings/{key}`

```bash
curl -X DELETE \
     -H "Authorization: Bearer {token}" \
     https://example.com/api/settings/site.name
```

**Response:**

```json
{
  "message": "Setting deleted successfully"
}
```

## Responses

### Success Responses

- `200 OK` - GET requests
- `201 Created` - POST create
- `200 OK` - POST update
- `200 OK` - DELETE

### Error Responses

- `401 Unauthorized` - Missing or invalid token
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Setting not found
- `422 Unprocessable Entity` - Validation errors

**Error Example:**

```json
{
  "message": "Validation failed",
  "errors": {
    "value": ["The value field is required"]
  }
}
```

---

[← API Reference](api-reference.md) | [Database Schema →](database-schema.md)

# Discovery Report — ClubCoreWP

> **Date**: 2026-09-23  
> **Agent**: Master Agent  
> **Phase**: 0 — Discovery

## 1. Repository Status

| Item | Status |
|------|--------|
| Repository | `c:\Users\mmhhs\OneDrive\Documents\GitHub\ClubCoreWP` |
| Git | Initialized, `main` branch, **no commits** |
| Content | **Empty** — fresh repository |
| Composer | Not present |
| npm/package.json | Not present |
| Existing code | None |
| Existing tests | None |
| CI/CD | None |

## 2. Development Environment

| Item | Value |
|------|-------|
| OS | Windows |
| PHP | **Not found in PATH** — not installed or not in system PATH |
| Node.js | v24.13.1 |
| Composer | Not found |
| WP-CLI | Not found |
| PHPUnit | Not found |
| PHPStan | Not found |
| PHPCS | Not found |
| XAMPP/WAMP/Laragon | Not detected |

### Environment Notes

- PHP is **not available** on this development machine. This means:
  - We cannot run `composer install` locally
  - We cannot execute PHPUnit tests locally
  - We cannot run PHPCS or PHPStan locally
  - Plugin development will focus on code generation; testing will require a WordPress environment
- Node.js is available for any build tooling if needed
- Git is functional

## 3. Target WordPress Environment

> [!IMPORTANT]
> Since we have no running WordPress instance to inspect, the following are **design targets** that will need validation during deployment.

| Item | Target |
|------|--------|
| WordPress | 6.4+ (latest stable recommended) |
| PHP | 8.1+ (minimum), 8.2+ (recommended) |
| MySQL/MariaDB | 5.7+ / 10.3+ |
| WooCommerce | 8.0+ (with HPOS support) |
| WooCommerce HPOS | Must support both enabled and disabled |

## 4. External Dependencies Identified

### Required
- **Melipayamak SMS API** — Primary SMS provider (REST API at `rest.payamak-panel.com`)
- **PhpSpreadsheet** — For XLSX import/export (via Composer)

### WordPress APIs to Use
- Settings API
- User API
- `$wpdb` (with `prepare()`)
- `dbDelta()` for schema management
- WordPress HTTP API (`wp_remote_post()`)
- WordPress Cron / Action Scheduler
- WordPress Privacy API (Personal Data Exporter/Eraser)
- WooCommerce CRUD APIs

## 5. Melipayamak API — Verified Details

### Authentication
- **Method**: Username + Password (in request body)
- **Note**: Newer "Console" panel may use Auth Token — plugin should support both

### Pattern SMS Endpoint (BaseServiceNumber)
| Detail | Value |
|--------|-------|
| URL | `https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber` |
| Method | POST |
| Content-Type | application/x-www-form-urlencoded or JSON |

### Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `username` | string | Account username |
| `password` | string | Account password |
| `to` | string | Recipient phone number |
| `bodyId` | integer | Pattern/template ID from panel |
| `text` | string | Variable values separated by `;` |

### Response Format
```json
{
  "Value": "123456789012345",
  "RetStatus": 1
}
```
- **Success**: `Value` contains `recId` (long numeric string)
- **Error**: `Value` contains error code (small number or negative)

### Error Codes (Verified)
| Code | Meaning | Domain Error |
|------|---------|-------------|
| 0 | Invalid username/password | AUTHENTICATION_ERROR |
| -1 | Service disabled | PROVIDER_ERROR |
| 2 | Insufficient credit | INSUFFICIENT_CREDIT |
| 6 | System updating | PROVIDER_ERROR |
| 7 | Filtered/prohibited words | INVALID_PATTERN |
| 10 | User not active | AUTHENTICATION_ERROR |
| 11 | Message not sent | PROVIDER_ERROR |
| 12 | Documents incomplete | AUTHENTICATION_ERROR |
| 19 | Hourly limit exceeded | RATE_LIMITED |
| 35 | Number blacklisted | INVALID_PHONE |

### Additional Endpoints
| Endpoint | URL |
|----------|-----|
| Get Credit | `https://rest.payamak-panel.com/api/SendSMS/GetCredit` |
| Delivery Status | `https://rest.payamak-panel.com/api/SendSMS/GetDeliveries` |

### Important Pattern Rule (Verified)
- The `text` parameter contains **only the variable values**, separated by `;`
- The full template text is **NOT** sent — only the `bodyId` references the template
- Variable order in `text` must match the order defined in the panel template

## 6. WooCommerce HPOS Compatibility Notes

### Detection
```php
// Check if HPOS is enabled
use Automattic\WooCommerce\Utilities\OrderUtil;
OrderUtil::custom_orders_table_usage_is_enabled();
```

### HPOS Compatibility Declaration
```php
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_storage', __FILE__, true
        );
    }
});
```

### Order Queries (HPOS-Safe)
```php
// Use WooCommerce CRUD APIs — never direct SQL on wp_posts for orders
$orders = wc_get_orders([
    'customer' => $user_id,
    'limit' => 10,
    'orderby' => 'date',
    'order' => 'DESC',
]);
```

### Customer Matching by Phone
```php
$orders = wc_get_orders([
    'billing_phone' => $normalized_phone,
    'limit' => 1,
]);
```

## 7. Existing Plugins/Data

Since the repository is empty and we have no WordPress instance to inspect:

- **Existing SMS Plugins**: Unknown — to be verified during deployment
- **Existing CRM Plugins**: Unknown
- **Existing Customer Data**: None in repository
- **Existing Custom Tables**: None

## 8. Naming Decision

Based on the repository name `ClubCoreWP`, the following naming convention is established:

| Item | Value |
|------|-------|
| Plugin Slug | `clubcore` |
| Namespace | `ClubCore` |
| Function Prefix | `clubcore_` |
| Option Prefix | `clubcore_` |
| Table Prefix | `clubcore_` |
| Meta Key Prefix | `_clubcore_` |
| Text Domain | `clubcore` |
| CSS Variable Prefix | `--cc-` |
| Capability Prefix | `clubcore_` |
| Hook Prefix | `clubcore_` |

## 9. Decisions Log

| # | Decision | Rationale |
|---|----------|-----------|
| D1 | Plugin slug: `clubcore` | Unique, derived from repo name, unlikely to conflict |
| D2 | PHP 8.1+ minimum | Modern PHP features, WordPress trend |
| D3 | Canonical phone format: `09XXXXXXXXX` (11 digits) | Standard Iran mobile format, simplest for display and storage |
| D4 | Username strategy for users without email: `clubcore_{phone}` | Deterministic, avoids collision, clearly attributed |
| D5 | No fake email generation | Documented decision — users without email get no email set |
| D6 | Pattern variable separator: `;` | Matches Melipayamak API convention |
| D7 | Use WordPress HTTP API (not cURL) | Standard, hookable, testable |
| D8 | XLSX via PhpSpreadsheet | Industry standard, MIT license |
| D9 | Action Scheduler for long-running jobs (if WooCommerce available), WP-Cron fallback | Best practice for WordPress async |

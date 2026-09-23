# Security Model

## Custom Capabilities
1. `clubcore_manage_members`: Create/edit/delete members
2. `clubcore_view_members`: View member list
3. `clubcore_manage_settings`: Edit plugin settings
4. `clubcore_view_reports`: View SMS and audit logs
5. `clubcore_import_members`: Run imports
6. `clubcore_export_members`: Run exports
7. `clubcore_manage_roles`: Manage club-specific roles/permissions
8. `clubcore_send_sms`: Manually send SMS
9. `clubcore_view_audit_logs`: View audit logs
10. `clubcore_manage_api_keys`: Manage Melipayamak credentials
11. `clubcore_delete_logs`: Purge logs

## Nonce Strategy
- One distinct nonce per form or action to prevent CSRF.

## Authentication & Authorization
- Standard WordPress cookie authentication for admin pages.
- Authorization strictly checked via `current_user_can('capability')` at every entry point.

## Input & Output
- **Input Validation**: Strict typing, length checks, format checks (e.g., regex for phone).
- **SQL Injection**: All dynamic queries use `$wpdb->prepare()`.
- **Output Escaping**: Extensive use of `esc_html()`, `esc_attr()`, `esc_url()`, and `wp_kses()` to prevent XSS.

## Additional Protections
- **File Upload Security**: MIME type checking, size limits, stored in temporary protected directories, no execution allowed.
- **CSRF Prevention**: Ensured via combination of nonces and capability checks.
- **Credential Storage**: API credentials stored in WordPress options (not in code, logs, JS, or audit trails).
- **Rate Limiting**: Applied to outbound SMS actions to prevent abuse or runaway loops.
- **REST/AJAX Security**: `permission_callback` required on all REST endpoints; nonces required for AJAX.
- **Formula Injection Prevention**: Exported CSV/XLSX data is sanitized (prefixing `=` or `-` to prevent execution in Excel).

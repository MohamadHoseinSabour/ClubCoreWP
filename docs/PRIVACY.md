# Privacy Compliance

## Data Collected
- Phone number
- First Name, Last Name
- Email (optional/linked)
- IP Address (hashed only)

## WordPress Integrations
- **Personal Data Exporter**: Hooks into `wp_privacy_personal_data_exporters` to include member records and SMS logs when a user requests their data.
- **Personal Data Eraser**: Hooks into `wp_privacy_personal_data_erasers` to process erasure requests.

## Data Minimization & Protection
- **Phone Masking**: Phone numbers in audit logs are masked (e.g., `0912***4567`).
- **IP Hashing**: IP addresses in logs are hashed (SHA-256) rather than stored in plain text.
- **Data Retention**: Configurable retention periods for SMS logs, audit logs, and old import jobs to automatically prune old data.
- **User Deletion Handling**: Option to either anonymize or delete associated member records when a WP User is deleted.

## Privacy Policy
The plugin hooks into WordPress's Privacy Policy Guide to provide suggested text detailing SMS communication policies, data handling, and data minimization principles.

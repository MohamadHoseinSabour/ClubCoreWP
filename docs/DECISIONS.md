# Architecture Decision Records

- **D1: Plugin prefix `clubcore`**: Ensures uniqueness across hooks, functions, options, and tables. Aligns with standard repository practices.
- **D2: PHP 8.1+ minimum**: Allows use of enums, readonly properties, typed properties, and modern language features. Matches the modern WP ecosystem trend.
- **D3: Canonical phone format `09XXXXXXXXX`**: Standard format for Iran, friendly for display and reliable for API integrations.
- **D4: Username strategy `clubcore_{phone}`**: Deterministic generation for linked WP users to avoid collisions.
- **D5: No fake email generation**: Promotes privacy and data integrity. WordPress 4.5+ allows empty emails under certain conditions.
- **D6: Variable separator `;`**: Strict requirement by Melipayamak pattern API.
- **D7: WordPress HTTP API (`wp_remote_post`) over cURL**: Standardized, hookable, testable, and more reliable across different hosting environments.
- **D8: PhpSpreadsheet for XLSX**: MIT license, industry standard, robust handling of Excel files.
- **D9: Action Scheduler / WP-Cron fallback**: Best practice for background jobs. Action Scheduler is used if WC is active, otherwise falls back to WP-Cron.
- **D10: No external DI container**: Keeps the footprint small. A simple custom container suffices.
- **D11: WP_List_Table for admin tables**: Provides consistent WordPress native UX and robust bulk action handling.
- **D12: Layered architecture**: Pragmatic choice (Presentation -> Application -> Domain -> Infrastructure) over pure hexagonal to balance DDD with WordPress conventions.

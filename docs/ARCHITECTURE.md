# Architecture Overview

## Layered Architecture
ClubCoreWP follows a pragmatically adapted 4-layer architecture:
1. **Presentation**: Handles all user interaction, HTTP requests, WordPress UI/Admin, CLI, and REST endpoints. (Namespace: `ClubCore\Presentation`)
2. **Application**: Coordinates use cases, transaction scripts, and domain services. (Namespace: `ClubCore\Application`)
3. **Domain**: Contains core business logic, entities, value objects, and domain events. Independent of WordPress where possible. (Namespace: `ClubCore\Domain`)
4. **Infrastructure**: Provides technical capabilities (DB, API clients, File System, WordPress specific integrations). (Namespace: `ClubCore\Infrastructure`)

## Dependency Rules
- Inner layers do NOT depend on outer layers. 
- The Domain layer is at the core.
- Application depends on Domain.
- Infrastructure and Presentation depend on Application and Domain.

## Service Container
We use a simple, custom PHP service container without an external DI framework. This aligns with WordPress conventions and keeps the plugin lightweight while enabling dependency injection.

## Plugin Lifecycle
- **Activation**: Registers custom capabilities, creates/updates database tables (via `dbDelta()`), sets initial default options.
- **Deactivation**: Optional cleanup, flushes rewrite rules. Usually doesn't delete data.
- **Uninstall**: Complete removal of data (tables, options, capabilities) if the user has opted for it.

## Event Architecture
Events are broadcast using WordPress action and filter hooks, prefixed with `clubcore_` (e.g., `clubcore_member_created`, `clubcore_sms_sent`). This allows easy internal decoupling and external extensions.

## Extension Points
The architecture is designed to support future features through hooks and independent modules:
- Loyalty points/tiers
- Campaigns (SMS blasting)
- Analytics (Reporting dashboards)

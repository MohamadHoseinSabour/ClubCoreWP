# WooCommerce Integration

## Compatibility
- Explicit **HPOS (High-Performance Order Storage)** compatibility declared.
- Graceful degradation: Plugin works fully if WooCommerce is not active. Feature detection used via `class_exists('WooCommerce')` and checking HPOS status.

## Data Integration
- **Customer Matching**: Matches WC `billing_phone` to ClubCore `phone_normalized`. Links to `user_id`.
- **Order Queries**: Uses `wc_get_orders()` instead of direct SQL.
- **Customer Data**: Interacted with via `WC_Customer` CRUD objects.

## Hooks Used
- `woocommerce_created_customer`
- `woocommerce_new_order`
- `woocommerce_payment_complete`
- `woocommerce_order_status_changed`

## Workflows
- **Auto-enrollment Settings**: Can be disabled, triggered on account creation, on first order, or both.
- **SMS Behavior**: Configured separately (default disabled).
- **Sync Policy**: One-way sync (WooCommerce -> ClubCore). We do not overwrite WooCommerce data to avoid conflicts with core store operations.

<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\WooCommerce;

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Detects WooCommerce presence and features like HPOS.
 */
class WooCommerceDetector {

    /**
     * Check if WooCommerce is active.
     *
     * @return bool
     */
    public function isActive(): bool {
        return class_exists('WooCommerce') && function_exists('WC');
    }

    /**
     * Get WooCommerce version.
     *
     * @return string|null
     */
    public function getVersion(): ?string {
        if ($this->isActive() && defined('WC_VERSION')) {
            return WC_VERSION;
        }
        return null;
    }

    /**
     * Check if HPOS is enabled.
     *
     * @return bool
     */
    public function isHposEnabled(): bool {
        if ($this->isActive() && class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            if (method_exists(OrderUtil::class, 'custom_orders_table_usage_is_enabled')) {
                return OrderUtil::custom_orders_table_usage_is_enabled();
            }
        }
        return false;
    }

    /**
     * Check if HPOS background sync is enabled.
     *
     * @return bool
     */
    public function isHposSyncEnabled(): bool {
        if ($this->isActive() && class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            if (method_exists(OrderUtil::class, 'is_custom_order_tables_in_sync')) {
                return OrderUtil::is_custom_order_tables_in_sync();
            }
            if (method_exists(OrderUtil::class, 'custom_orders_table_sync_is_enabled')) {
                return OrderUtil::custom_orders_table_sync_is_enabled();
            }
            return get_option('woocommerce_custom_orders_table_data_sync_enabled') === 'yes';
        }
        return false;
    }

    /**
     * Get comprehensive status array.
     *
     * @return array
     */
    public function getStatus(): array {
        return [
            'active'    => $this->isActive(),
            'version'   => $this->getVersion(),
            'hpos'      => $this->isHposEnabled(),
            'hpos_sync' => $this->isHposSyncEnabled(),
        ];
    }
}

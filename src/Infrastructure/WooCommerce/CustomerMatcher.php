<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\WooCommerce;

use ClubCore\Domain\Entity\Member;

/**
 * Matches ClubCore members with WooCommerce customers and retrieves stats/orders.
 */
class CustomerMatcher {

    /**
     * Find WC customer data by WP user ID.
     *
     * @param int $userId
     * @return array|null
     */
    public function matchByUserId(int $userId): ?array {
        if (!class_exists('WC_Customer') || $userId <= 0) {
            return null;
        }

        try {
            $customer = new \WC_Customer($userId);
            if ($customer->get_id() > 0) {
                return [
                    'id'            => $customer->get_id(),
                    'email'         => $customer->get_email(),
                    'billing_phone' => $customer->get_billing_phone(),
                    'first_name'    => $customer->get_first_name(),
                    'last_name'     => $customer->get_last_name(),
                ];
            }
        } catch (\Exception $e) {
            // Ignore exception
        }
        return null;
    }

    /**
     * Find WC customer by billing_phone user meta.
     *
     * @param string $phoneNormalized
     * @return array|null
     */
    public function matchByPhone(string $phoneNormalized): ?array {
        if (empty($phoneNormalized)) {
            return null;
        }

        $users = get_users([
            'meta_key'   => 'billing_phone',
            'meta_value' => $phoneNormalized,
            'number'     => 1,
            'fields'     => 'ID'
        ]);

        if (!empty($users)) {
            return $this->matchByUserId((int)$users[0]);
        }

        return null;
    }

    /**
     * Get order count, total spent, last order id/date, customer since.
     *
     * @param int $userId
     * @return array
     */
    public function getCustomerStats(int $userId): array {
        $stats = [
            'order_count'    => 0,
            'total_spent'    => 0.0,
            'last_order_id'  => null,
            'last_order_date'=> null,
            'customer_since' => null,
        ];

        if (!class_exists('WC_Customer') || !function_exists('wc_get_orders') || $userId <= 0) {
            return $stats;
        }

        $customer = new \WC_Customer($userId);
        if ($customer->get_id() > 0) {
            $stats['order_count'] = $customer->get_order_count();
            $stats['total_spent'] = $customer->get_total_spent();
            $stats['customer_since'] = $customer->get_date_created() ? $customer->get_date_created()->date('Y-m-d H:i:s') : null;

            $orders = wc_get_orders([
                'customer_id' => $userId,
                'limit'       => 1,
                'orderby'     => 'date',
                'order'       => 'DESC',
                'return'      => 'objects'
            ]);

            if (!empty($orders)) {
                $lastOrder = $orders[0];
                $stats['last_order_id'] = $lastOrder->get_id();
                $stats['last_order_date'] = $lastOrder->get_date_created() ? $lastOrder->get_date_created()->date('Y-m-d H:i:s') : null;
            }
        }

        return $stats;
    }

    /**
     * Get orders by customer ID.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getOrdersByCustomer(int $userId, int $limit = 10, int $offset = 0): array {
        if (!function_exists('wc_get_orders') || $userId <= 0) {
            return [];
        }

        return wc_get_orders([
            'customer_id' => $userId,
            'limit'       => $limit,
            'offset'      => $offset,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ]);
    }

    /**
     * Get orders by billing phone.
     *
     * @param string $phone
     * @param int $limit
     * @return array
     */
    public function getOrdersByPhone(string $phone, int $limit = 10): array {
        if (!function_exists('wc_get_orders') || empty($phone)) {
            return [];
        }

        return wc_get_orders([
            'billing_phone' => $phone,
            'limit'         => $limit,
            'orderby'       => 'date',
            'order'         => 'DESC',
        ]);
    }

    /**
     * Link a ClubCore Member to WooCommerce by setting billing_phone.
     *
     * @param Member $member
     * @return bool
     */
    public function linkMemberToWooCommerce(Member $member): bool {
        if (!class_exists('WC_Customer') || !$member->getUserId() || !$member->getPhone()) {
            return false;
        }

        try {
            $customer = new \WC_Customer($member->getUserId());
            if ($customer->get_id() > 0) {
                $customer->set_billing_phone($member->getPhone());
                $customer->save();
                return true;
            }
        } catch (\Exception $e) {
            // Log or ignore
        }
        
        return false;
    }
}

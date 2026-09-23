<?php
declare(strict_types=1);

namespace ClubCore\Application\Service;

use ClubCore\Infrastructure\WooCommerce\WooCommerceDetector;
use ClubCore\Infrastructure\WooCommerce\CustomerMatcher;
use ClubCore\Domain\Model\Member;

/**
 * Service for interacting with WooCommerce data from the application layer.
 */
class WooCommerceService {

    public function __construct(
        private readonly WooCommerceDetector $detector,
        private readonly CustomerMatcher $matcher
    ) {
    }

    /**
     * Check if WooCommerce integration is available.
     *
     * @return bool
     */
    public function isAvailable(): bool {
        return $this->detector->isActive();
    }

    /**
     * Get WooCommerce status.
     *
     * @return array
     */
    public function getStatus(): array {
        return $this->detector->getStatus();
    }

    /**
     * Get customer stats for a member.
     *
     * @param Member $member
     * @return array|null
     */
    public function getCustomerDataForMember(Member $member): ?array {
        if (!$this->isAvailable()) {
            return null;
        }

        $userId = $member->getUserId();
        if (!$userId) {
            return null;
        }

        $stats = $this->matcher->getCustomerStats($userId);
        
        return [
            'wc_customer_id'  => $userId,
            'order_count'     => $stats['order_count'] ?? 0,
            'total_spent'     => $stats['total_spent'] ?? 0.0,
            'last_order_id'   => $stats['last_order_id'] ?? null,
            'last_order_date' => $stats['last_order_date'] ?? null,
            'customer_since'  => $stats['customer_since'] ?? null,
        ];
    }

    /**
     * Get orders for a member.
     *
     * @param Member $member
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getOrdersForMember(Member $member, int $limit = 10, int $offset = 0): array {
        if (!$this->isAvailable()) {
            return [];
        }

        $userId = $member->getUserId();
        $phone = $member->getPhone();
        
        $wcOrders = [];
        
        if ($userId) {
            $wcOrders = $this->matcher->getOrdersByCustomer($userId, $limit, $offset);
        } elseif ($phone && (bool) get_option('clubcore_wc_guest_matching', false)) {
            $wcOrders = $this->matcher->getOrdersByPhone($phone, $limit);
        }

        $formattedOrders = [];
        foreach ($wcOrders as $order) {
            $formattedOrders[] = [
                'id'     => $order->get_id(),
                'number' => $order->get_order_number(),
                'status' => $order->get_status(),
                'total'  => $order->get_total(),
                'date'   => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : null,
            ];
        }

        return $formattedOrders;
    }
}

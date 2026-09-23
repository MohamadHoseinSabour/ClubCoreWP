<?php
declare(strict_types=1);

namespace ClubCore\Infrastructure\WooCommerce;

use ClubCore\Application\UseCase\CreateMemberUseCase;

/**
 * Handles WooCommerce hooks for member auto-enrollment.
 */
class WooCommerceIntegration {

    public function __construct(
        private readonly WooCommerceDetector $detector,
        private readonly CreateMemberUseCase $createMemberUseCase
    ) {
    }

    /**
     * Register WooCommerce hooks if active.
     */
    public function init(): void {
        if (!$this->detector->isActive()) {
            return;
        }

        add_action('woocommerce_created_customer', [$this, 'onCustomerCreated'], 10, 2);
        add_action('woocommerce_payment_complete', [$this, 'onOrderCompleted'], 10, 1);
    }

    /**
     * Triggered when a new WC customer is created.
     *
     * @param int $customerId
     * @param array $newCustomerData
     */
    public function onCustomerCreated(int $customerId, array $newCustomerData): void {
        $autoEnroll = get_option('clubcore_wc_auto_enroll', 'disabled');
        if (!in_array($autoEnroll, ['on_account_creation', 'both'], true)) {
            return;
        }

        $phone = get_user_meta($customerId, 'billing_phone', true);
        if (empty($phone)) {
            return;
        }

        $sendSms = (bool) get_option('clubcore_wc_send_sms', false);

        try {
            // Attempt to create member, checking if they exist is usually handled in use case or before it
            $this->createMemberUseCase->execute([
                'user_id' => $customerId,
                'phone'   => $phone,
                'source'  => 'woocommerce',
                'send_sms'=> $sendSms,
            ]);
            
            // Log audit (assuming a standard WP logger or internal logger)
            error_log("ClubCore: Auto-enrolled WC customer $customerId as member.");
        } catch (\Exception $e) {
            // Member likely exists or validation failed
            error_log("ClubCore: Failed to auto-enroll WC customer $customerId - " . $e->getMessage());
        }
    }

    /**
     * Triggered when an order is completed.
     *
     * @param int $orderId
     */
    public function onOrderCompleted(int $orderId): void {
        $autoEnroll = get_option('clubcore_wc_auto_enroll', 'disabled');
        if (!in_array($autoEnroll, ['on_first_order', 'both'], true)) {
            return;
        }

        $order = wc_get_order($orderId);
        if (!$order) {
            return;
        }

        $userId = $order->get_customer_id();
        $phone = $order->get_billing_phone();

        if (empty($phone)) {
            return;
        }

        $sendSms = (bool) get_option('clubcore_wc_send_sms', false);

        try {
            $this->createMemberUseCase->execute([
                'user_id' => $userId > 0 ? $userId : null,
                'phone'   => $phone,
                'source'  => 'woocommerce',
                'send_sms'=> $sendSms,
            ]);
            
            error_log("ClubCore: Auto-enrolled via WC order $orderId.");
        } catch (\Exception $e) {
            error_log("ClubCore: Failed to auto-enroll from WC order $orderId - " . $e->getMessage());
        }
    }
}

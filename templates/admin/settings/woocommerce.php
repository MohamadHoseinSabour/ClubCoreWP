<div class="wrap">
    <h1><?php esc_html_e('WooCommerce Integration', 'clubcore'); ?></h1>

    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2><?php esc_html_e('Integration Status', 'clubcore'); ?></h2>
        <?php
        $detector = new \ClubCore\Infrastructure\WooCommerce\WooCommerceDetector();
        $status = $detector->getStatus();
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('WooCommerce Active', 'clubcore'); ?></th>
                    <td>
                        <?php if ($status['active']) : ?>
                            <span style="color: green; font-weight: bold;"><?php esc_html_e('Yes', 'clubcore'); ?></span>
                        <?php else : ?>
                            <span style="color: red; font-weight: bold;"><?php esc_html_e('No', 'clubcore'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($status['active']) : ?>
                    <tr>
                        <th scope="row"><?php esc_html_e('Version', 'clubcore'); ?></th>
                        <td><?php echo esc_html($status['version']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('HPOS Enabled', 'clubcore'); ?></th>
                        <td>
                            <?php if ($status['hpos']) : ?>
                                <span style="color: green;"><?php esc_html_e('Yes', 'clubcore'); ?></span>
                            <?php else : ?>
                                <span style="color: orange;"><?php esc_html_e('No', 'clubcore'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <form method="post" action="options.php">
        <?php
        settings_fields('clubcore_wc_settings');
        do_settings_sections('clubcore_wc_settings');
        
        $auto_enroll = get_option('clubcore_wc_auto_enroll', 'disabled');
        $send_sms = get_option('clubcore_wc_send_sms', false);
        $guest_matching = get_option('clubcore_wc_guest_matching', false);
        ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="clubcore_wc_auto_enroll"><?php esc_html_e('Auto Enrollment', 'clubcore'); ?></label></th>
                    <td>
                        <select name="clubcore_wc_auto_enroll" id="clubcore_wc_auto_enroll">
                            <option value="disabled" <?php selected($auto_enroll, 'disabled'); ?>><?php esc_html_e('Disabled', 'clubcore'); ?></option>
                            <option value="on_account_creation" <?php selected($auto_enroll, 'on_account_creation'); ?>><?php esc_html_e('On account creation', 'clubcore'); ?></option>
                            <option value="on_first_order" <?php selected($auto_enroll, 'on_first_order'); ?>><?php esc_html_e('On first order', 'clubcore'); ?></option>
                            <option value="both" <?php selected($auto_enroll, 'both'); ?>><?php esc_html_e('Both', 'clubcore'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Automatically create ClubCore members from WooCommerce customers.', 'clubcore'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('SMS Notifications', 'clubcore'); ?></th>
                    <td>
                        <label for="clubcore_wc_send_sms">
                            <input type="checkbox" name="clubcore_wc_send_sms" id="clubcore_wc_send_sms" value="1" <?php checked($send_sms, true); ?>>
                            <?php esc_html_e('Send SMS on auto-enrollment', 'clubcore'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Guest Order Matching', 'clubcore'); ?></th>
                    <td>
                        <label for="clubcore_wc_guest_matching">
                            <input type="checkbox" name="clubcore_wc_guest_matching" id="clubcore_wc_guest_matching" value="1" <?php checked($guest_matching, true); ?>>
                            <?php esc_html_e('Enable guest order matching by phone number', 'clubcore'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Sync Policy', 'clubcore'); ?></th>
                    <td>
                        <p class="description">
                            <strong><?php esc_html_e('Read-only Info:', 'clubcore'); ?></strong>
                            <?php esc_html_e('Data sync flows from WooCommerce to ClubCore only. Existing member data will never be overwritten by WooCommerce data.', 'clubcore'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>
</div>

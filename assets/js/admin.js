/**
 * ClubCore Main Admin Script
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // SMS Resend button handler in list tables
        $(document).on('click', '.clubcore-resend-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var memberId = $btn.data('member-id');
            var nonce = $btn.data('nonce');

            if (!confirm(clubcoreAdmin.i18n.confirm)) {
                return;
            }

            $btn.prop('disabled', true).text(clubcoreAdmin.i18n.loading);

            $.post(clubcoreAdmin.ajaxUrl, {
                action: 'clubcore_resend_sms',
                member_id: memberId,
                nonce: nonce
            }).done(function(res) {
                if (res.success) {
                    alert(res.data.message || clubcoreAdmin.i18n.success);
                    location.reload();
                } else {
                    alert(res.data.message || clubcoreAdmin.i18n.error);
                    $btn.prop('disabled', false).text('ارسال مجدد');
                }
            }).fail(function() {
                alert(clubcoreAdmin.i18n.error);
                $btn.prop('disabled', false).text('ارسال مجدد');
            });
        });
    });
})(jQuery);

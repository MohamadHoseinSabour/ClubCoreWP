/**
 * ClubCore Settings Script
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // SMS Auth Mode Switcher
        function updateSmsAuthFields() {
            var mode = $('input[name="clubcore_sms_auth_mode"]:checked').val() || 'classic';
            if (mode === 'console') {
                $('.clubcore-classic-auth-row').hide();
                $('.clubcore-console-auth-row').show();
            } else {
                $('.clubcore-classic-auth-row').show();
                $('.clubcore-console-auth-row').hide();
            }
        }

        $('input[name="clubcore_sms_auth_mode"]').on('change', updateSmsAuthFields);
        updateSmsAuthFields();

        // Color Pickers
        if ($.fn.wpColorPicker) {
            $('.clubcore-color-picker').wpColorPicker({
                change: function(event, ui) {
                    checkContrast();
                }
            });
        }

        // Color Contrast Checker (Luminance ratio calculation)
        function getLuminance(hex) {
            var c = hex.replace('#', '');
            if (c.length === 3) {
                c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
            }
            var rgb = parseInt(c, 16);
            var r = (rgb >> 16) & 0xff;
            var g = (rgb >>  8) & 0xff;
            var b = (rgb >>  0) & 0xff;

            var a = [r, g, b].map(function(v) {
                v /= 255;
                return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
        }

        function checkContrast() {
            var bg = $('#clubcore_appearance_card').val() || '#ffffff';
            var text = $('#clubcore_appearance_text').val() || '#1d2327';

            var lum1 = getLuminance(bg);
            var lum2 = getLuminance(text);
            var ratio = (Math.max(lum1, lum2) + 0.05) / (Math.min(lum1, lum2) + 0.05);

            var $warning = $('#clubcore-contrast-warning');
            if (ratio < 4.5) {
                $warning.show().html('⚠️ توجه: نسبت کنتراست متن و پس‌زمینه کارت (' + ratio.toFixed(2) + ':1) کمتر از استاندارد ۴.۵ است و ممکن است خوانایی را کاهش دهد.');
            } else {
                $warning.hide();
            }
        }
        checkContrast();

        // Send Test SMS Handler
        $('#clubcore-test-sms-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var phone = $.trim($('#clubcore-test-phone').val());
            var firstName = $.trim($('#clubcore-test-fname').val());
            var lastName = $.trim($('#clubcore-test-lname').val());
            var nonce = $('#clubcore_test_sms_nonce').val();
            var $res = $('#clubcore-test-sms-result');

            if (!phone) {
                alert('لطفاً شماره موبایل تستی را وارد کنید.');
                return;
            }

            $btn.prop('disabled', true).text(clubcoreAdmin.i18n.loading);
            $res.empty();

            $.post(clubcoreAdmin.ajaxUrl, {
                action: 'clubcore_send_test_sms',
                phone: phone,
                first_name: firstName,
                last_name: lastName,
                nonce: nonce
            }).done(function(res) {
                if (res.success) {
                    $res.html('<div class="clubcore-notice clubcore-notice-success">' + res.data.message + ' (شناسه: ' + (res.data.reference || '-') + ')</div>');
                } else {
                    $res.html('<div class="clubcore-notice clubcore-notice-error">' + (res.data.message || 'خطا در ارسال پیامک آزمایشی') + '</div>');
                }
            }).fail(function() {
                $res.html('<div class="clubcore-notice clubcore-notice-error">خطای سرور رخ داد.</div>');
            }).always(function() {
                $btn.prop('disabled', false).text('ارسال پیامک آزمایشی');
            });
        });
    });
})(jQuery);

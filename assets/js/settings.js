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

        // Check SMS Credit / Connection Handler
        $('#clubcore-check-credit').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $result = $('#clubcore-credit-result');

            if (!$result.length) {
                $btn.after('<span id="clubcore-credit-result" style="margin-right:12px; font-style:italic;"></span>');
                $result = $('#clubcore-credit-result');
            }

            $btn.prop('disabled', true).text(clubcoreAdmin.i18n.loading);
            $result.text('').css('color', '');

            $.post(clubcoreAdmin.ajaxUrl, {
                action: 'clubcore_check_sms_connection',
                nonce: clubcoreAdmin.nonces.checkSmsConnection
            }).done(function(res) {
                if (res.success) {
                    $result.css('color', '#00a32a').text('✓ ' + res.data.message);
                } else {
                    $result.css('color', '#d63638').text('✗ ' + (res.data.message || 'خطا در بررسی ارتباط'));
                }
            }).fail(function() {
                $result.css('color', '#d63638').text('✗ خطای سرور رخ داد.');
            }).always(function() {
                $btn.prop('disabled', false).html('🔍 بررسی ارتباط و موجودی اعتبار');
            });
        });

        // Send Test SMS Handler
        $(document).on('click', '#clubcore-test-sms-btn, #clubcore-test-sms', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var phone = $.trim($('#clubcore-test-phone').val() || $('#test_phone').val());
            var firstName = $.trim($('#clubcore-test-fname').val() || $('#test_first_name').val());
            var lastName = $.trim($('#clubcore-test-lname').val() || $('#test_last_name').val());
            var nonce = $('#clubcore_test_sms_nonce').val() || (window.clubcoreAdmin && clubcoreAdmin.nonces ? clubcoreAdmin.nonces.testSms : '');
            var $res = $('#clubcore-test-sms-result');

            if (!$res.length) {
                $btn.parent().after('<div id="clubcore-test-sms-result" style="margin-top:12px;"></div>');
                $res = $('#clubcore-test-sms-result');
            }

            if (!phone) {
                alert('لطفاً شماره موبایل تستی را وارد کنید.');
                return;
            }

            $btn.prop('disabled', true).text(window.clubcoreAdmin && clubcoreAdmin.i18n ? clubcoreAdmin.i18n.loading : 'در حال ارسال...');
            $res.empty();

            $.post(clubcoreAdmin.ajaxUrl, {
                action: 'clubcore_send_test_sms',
                phone: phone,
                first_name: firstName,
                last_name: lastName,
                nonce: nonce
            }).done(function(res) {
                if (res.success) {
                    $res.html('<div class="clubcore-notice clubcore-notice-success" style="padding:10px 14px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; color:#15803d; margin-top:10px;">' + res.data.message + ' (شناسه پیگیری: ' + (res.data.reference || '-') + ')</div>');
                } else {
                    $res.html('<div class="clubcore-notice clubcore-notice-error" style="padding:10px 14px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#b91c1c; margin-top:10px;">' + (res.data.message || 'خطا در ارسال پیامک آزمایشی') + '</div>');
                }
            }).fail(function() {
                $res.html('<div class="clubcore-notice clubcore-notice-error" style="padding:10px 14px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#b91c1c; margin-top:10px;">خطای سرور رخ داد.</div>');
            }).always(function() {
                $btn.prop('disabled', false).html('📨 ارسال پیامک آزمایشی');
            });
        });
    });
})(jQuery);

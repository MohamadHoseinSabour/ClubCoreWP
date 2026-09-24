/**
 * ClubCore Add Customer Admin Script
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        var $form = $('#clubcore-add-customer-form');
        var $btn = $('#clubcore-submit-btn');
        var $spinner = $('#clubcore-spinner');
        var $resultBox = $('#clubcore-result');
        var $phoneInput = $('#clubcore-phone');

        // Persian/Arabic digit to Latin converter
        function toLatinDigits(str) {
            var persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            var arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            for (var i = 0; i < 10; i++) {
                str = str.replace(new RegExp(persian[i], 'g'), i);
                str = str.replace(new RegExp(arabic[i], 'g'), i);
            }
            return str;
        }

        $phoneInput.on('input', function() {
            var val = $(this).val();
            var cleaned = toLatinDigits(val).replace(/[^0-9+]/g, '');
            if (val !== cleaned) {
                $(this).val(cleaned);
            }
        });

        $form.on('submit', function(e) {
            e.preventDefault();

            var phone = $.trim($phoneInput.val());
            if (!phone) {
                alert(clubcoreAdmin.i18n.required);
                $phoneInput.focus();
                return;
            }

            // Set Loading State
            $btn.prop('disabled', true);
            $spinner.addClass('visible');
            $resultBox.removeClass('visible').empty();

            var data = {
                action: 'clubcore_create_member',
                nonce: $('#clubcore_nonce').val() || (window.clubcoreAdmin && clubcoreAdmin.nonces ? clubcoreAdmin.nonces.createMember : ''),
                phone: phone,
                first_name: $('#clubcore-first-name').val(),
                last_name: $('#clubcore-last-name').val(),
                email: $('#clubcore-email').val()
            };

            $.post(clubcoreAdmin.ajaxUrl, data)
                .done(function(res) {
                    $resultBox.addClass('visible');
                    if (res.success) {
                        var status = res.data.status;
                        var m = res.data.member || {};

                        if (status === 'created') {
                            $form[0].reset();
                            $resultBox.html(
                                '<div class="clubcore-notice clubcore-notice-success">' +
                                    '<div><strong>' + (res.data.message || 'مشتری با موفقیت ثبت شد.') + '</strong>' +
                                    '<div style="margin-top:8px;">' +
                                        '<span>عضو: ' + (m.first_name + ' ' + m.last_name) + '</span> | ' +
                                        '<span>شماره: ' + m.phone + '</span> | ' +
                                        '<span>شناسه کاربر: ' + (m.user_id || '-') + '</span> | ' +
                                        '<span>وضعیت پیامک: ' + (res.data.sms_status || 'ارسال شد') + '</span>' +
                                    '</div>' +
                                    '</div>' +
                                '</div>'
                            );
                        } else if (status === 'already_exists') {
                            $resultBox.html(
                                '<div class="clubcore-notice clubcore-notice-info">' +
                                    '<div><strong>' + (res.data.message || 'این شماره قبلاً در باشگاه ثبت شده است.') + '</strong>' +
                                    '<div style="margin-top:6px;">عضو: ' + (m.first_name + ' ' + m.last_name) + ' (' + m.phone + ')</div>' +
                                    '</div>' +
                                '</div>'
                            );
                        }
                    } else {
                        var errMessage = (res.data && res.data.message) ? res.data.message : clubcoreAdmin.i18n.error;
                        $resultBox.html(
                            '<div class="clubcore-notice clubcore-notice-error">' +
                                '<div><strong>' + errMessage + '</strong></div>' +
                            '</div>'
                        );
                    }
                })
                .fail(function(xhr) {
                    $resultBox.addClass('visible');
                    var msg = clubcoreAdmin.i18n.error;
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        msg = xhr.responseJSON.data.message;
                    }
                    $resultBox.html(
                        '<div class="clubcore-notice clubcore-notice-error">' +
                            '<div><strong>' + msg + '</strong></div>' +
                        '</div>'
                    );
                })
                .always(function() {
                    $btn.prop('disabled', false);
                    $spinner.removeClass('visible');
                });
        });
    });
})(jQuery);

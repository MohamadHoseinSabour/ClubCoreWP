/**
 * ClubCore Import / Export Script
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        var currentSource = 'file';

        // Tab switcher
        $('.clubcore-import-tab').on('click', function() {
            var tab = $(this).data('tab');
            $('.clubcore-import-tab').removeClass('active');
            $(this).addClass('active');

            if (tab === 'clipboard') {
                currentSource = 'clipboard';
                $('#clubcore-file-panel').hide();
                $('#clubcore-clipboard-panel').show();
            } else {
                currentSource = 'file';
                $('#clubcore-file-panel').show();
                $('#clubcore-clipboard-panel').hide();
            }
        });

        // Warn when bulk SMS is checked
        $('#clubcore-send-bulk-sms').on('change', function() {
            if ($(this).is(':checked')) {
                if (!confirm(clubcoreAdmin.i18n.confirmBulkSms)) {
                    $(this).prop('checked', false);
                }
            }
        });

        // Preview trigger
        $('#clubcore-preview-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var formData = new FormData();
            formData.append('action', 'clubcore_import_preview');
            formData.append('nonce', clubcoreAdmin.nonces.import);
            formData.append('source_type', currentSource);

            if (currentSource === 'file') {
                var fileInput = $('#clubcore-import-file')[0];
                if (!fileInput.files.length) {
                    alert('لطفاً یک فایل CSV یا XLSX انتخاب کنید.');
                    return;
                }
                formData.append('import_file', fileInput.files[0]);
            } else {
                var rawText = $.trim($('#clubcore-clipboard-text').val());
                if (!rawText) {
                    alert('لطفاً داده‌های کپی شده را در کادر وارد کنید.');
                    return;
                }
                formData.append('raw_text', rawText);
            }

            $btn.prop('disabled', true).text(clubcoreAdmin.i18n.loading);
            $('#clubcore-preview-result').empty().hide();

            $.ajax({
                url: clubcoreAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success && res.data.preview) {
                        var p = res.data.preview;
                        var html = '<div class="clubcore-card">' +
                            '<h3>پیش‌نمایش داده‌ها</h3>' +
                            '<p>تعداد کل سطرها: <strong>' + p.total_rows + '</strong> | رکوردهای معتبر: <span class="clubcore-badge clubcore-badge-success">' + p.valid_count + '</span> | رکوردهای نامعتبر: <span class="clubcore-badge clubcore-badge-error">' + p.invalid_count + '</span> | تکراری‌ها: <span class="clubcore-badge clubcore-badge-warning">' + p.duplicate_count + '</span></p>';

                        if (p.sample_rows && p.sample_rows.length) {
                            html += '<table class="widefat striped"><thead><tr><th>سطر</th><th>شماره</th><th>نام</th><th>نام خانوادگی</th><th>ایمیل</th><th>وضعیت</th></tr></thead><tbody>';
                            $.each(p.sample_rows, function(i, row) {
                                html += '<tr>' +
                                    '<td>' + row.row + '</td>' +
                                    '<td dir="ltr">' + (row.phone || '-') + '</td>' +
                                    '<td>' + (row.first_name || '-') + '</td>' +
                                    '<td>' + (row.last_name || '-') + '</td>' +
                                    '<td>' + (row.email || '-') + '</td>' +
                                    '<td>' + (row.is_valid ? '<span class="dashicons dashicons-yes text-success"></span>' : '<span class="dashicons dashicons-no text-error" title="' + row.error + '"></span> ' + row.error) + '</td>' +
                                '</tr>';
                            });
                            html += '</tbody></table>';
                        }

                        html += '<div style="margin-top:15px;"><button type="button" id="clubcore-start-import-btn" class="button button-primary">تأیید و اجرای واردسازی</button></div></div>';

                        $('#clubcore-preview-result').html(html).show();
                    } else {
                        alert(res.data.message || 'خطا در بارگذاری پیش‌نمایش');
                    }
                },
                error: function() {
                    alert('خطای سرور در بارگذاری فایل');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('پیش‌نمایش و بررسی');
                }
            });
        });

        // Start Import execution
        $(document).on('click', '#clubcore-start-import-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var mode = $('input[name="import_mode"]:checked').val() || 'skip_duplicates';
            var sendSms = $('#clubcore-send-bulk-sms').is(':checked') ? 1 : 0;
            var dryRun = $('#clubcore-dry-run').is(':checked') ? 1 : 0;

            var formData = new FormData();
            formData.append('action', 'clubcore_import_execute');
            formData.append('nonce', clubcoreAdmin.nonces.import);
            formData.append('source_type', currentSource);
            formData.append('mode', mode);
            formData.append('send_sms', sendSms);
            formData.append('dry_run', dryRun);

            if (currentSource === 'file') {
                formData.append('import_file', $('#clubcore-import-file')[0].files[0]);
            } else {
                formData.append('raw_text', $.trim($('#clubcore-clipboard-text').val()));
            }

            $btn.prop('disabled', true).text(clubcoreAdmin.i18n.loading);
            var $prog = $('#clubcore-import-progress').addClass('visible');
            var $fill = $prog.find('.clubcore-progress-fill');
            var $text = $prog.find('.clubcore-progress-text');

            $fill.css('width', '50%').text('در حال پردازش...');
            $text.text('در حال ثبت اعضا و ساخت حساب‌ها...');

            $.ajax({
                url: clubcoreAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $fill.css('width', '100%').text('۱۰۰٪');
                    if (res.success && res.data.stats) {
                        var s = res.data.stats;
                        var msg = (dryRun ? 'تست آزمایشی با موفقیت انجام شد.' : 'عملیات واردسازی با موفقیت انجام شد.') + '<br>' +
                                  'تعداد موفق: ' + s.success + ' | رد شده: ' + s.skipped + ' | ناموفق: ' + s.failed;
                        $text.html('<div class="clubcore-notice clubcore-notice-success">' + msg + '</div>');
                    } else {
                        $text.html('<div class="clubcore-notice clubcore-notice-error">' + (res.data.message || 'خطا در عملیات واردسازی') + '</div>');
                    }
                },
                error: function() {
                    $text.html('<div class="clubcore-notice clubcore-notice-error">خطا در برقراری ارتباط با سرور</div>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('اجرای مجدد');
                }
            });
        });
    });
})(jQuery);

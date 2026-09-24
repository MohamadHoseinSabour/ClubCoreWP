<?php
/**
 * Customer Club Kiosk / Touchscreen POS Terminal Template.
 *
 * Standalone, mobile/tablet optimized web app interface for cashiers and operators.
 *
 * @package ClubCore
 */

if (!defined('ABSPATH')) {
    exit;
}

$terminalManager = \ClubCore\Plugin::init()->getTerminalManager();

$isAuthorized = $terminalManager->isAuthorized();
$guestMode = (string) get_option('clubcore_terminal_guest_mode', 'login_required');
$requirePin = (bool) get_option('clubcore_terminal_require_pin', '0') || ($guestMode === 'allow_pin');
$todayCount = $terminalManager->getTodayRegisteredCount();
$shopName = get_option('clubcore_terminal_title', get_bloginfo('name') ?: 'باشگاه مشتریان');
$ajaxUrl = admin_url('admin-ajax.php');
$terminalNonce = wp_create_nonce('clubcore_terminal_nonce');
$primaryColor = get_option('clubcore_appearance_primary', '#2271b1') ?: '#2271b1';
$isEmbedded = isset($isEmbeddedShortcode) && $isEmbeddedShortcode;
?>
<?php if (!$isEmbedded) : ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?php echo esc_html($shopName); ?> - <?php esc_html_e('دستگاه ثبت مشتری', 'clubcore'); ?></title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
<?php endif; ?>
    <style>
        :root {
            --cc-term-bg: #090d16;
            --cc-term-surface: #111827;
            --cc-term-surface-elevated: #1e293b;
            --cc-term-border: #334155;
            --cc-term-text: #f8fafc;
            --cc-term-text-muted: #94a3b8;
            --cc-term-primary: #10b981;
            --cc-term-primary-glow: rgba(16, 185, 129, 0.35);
            --cc-term-primary-hover: #059669;
            --cc-term-accent: #3b82f6;
            --cc-term-danger: #ef4444;
            --cc-term-warning: #f59e0b;
            --cc-term-radius-sm: 10px;
            --cc-term-radius-md: 18px;
            --cc-term-radius-lg: 28px;
            --cc-term-font: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
        }

        body.clubcore-terminal-body,
        .clubcore-terminal-wrapper {
            font-family: var(--cc-term-font);
            background-color: var(--cc-term-bg);
            color: var(--cc-term-text);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: env(safe-area-inset-top, 12px) env(safe-area-inset-right, 12px) env(safe-area-inset-bottom, 12px) env(safe-area-inset-left, 12px);
            overflow-x: hidden;
            direction: rtl;
        }

        /* Terminal Hardware Device Enclosure */
        .terminal-device {
            width: 100%;
            max-width: 440px;
            background: linear-gradient(165deg, #162032 0%, #0d131f 100%);
            border: 2px solid #2a374a;
            border-radius: var(--cc-term-radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 40px rgba(16, 185, 129, 0.08);
            padding: 20px 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            position: relative;
            backdrop-filter: blur(16px);
            animation: terminalFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes terminalFadeIn {
            from { opacity: 0; transform: translateY(12px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Top Hardware Status Bar */
        .terminal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .terminal-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .terminal-status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--cc-term-primary);
            box-shadow: 0 0 10px var(--cc-term-primary);
            animation: pulseDot 2s infinite;
        }

        @keyframes pulseDot {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.85); }
            100% { opacity: 1; transform: scale(1); }
        }

        .terminal-title {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .terminal-badges {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .terminal-badge {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            color: var(--cc-term-text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .terminal-badge strong {
            color: var(--cc-term-primary);
        }

        .terminal-btn-icon {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--cc-term-text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .terminal-btn-icon:active,
        .terminal-btn-icon.active {
            background: var(--cc-term-primary);
            color: #000;
            border-color: var(--cc-term-primary);
        }

        /* Screen LCD Display */
        .terminal-lcd-display {
            background: #060911;
            border: 2px solid #1e293b;
            border-radius: var(--cc-term-radius-md);
            padding: 16px 18px;
            box-shadow: inset 0 3px 12px rgba(0, 0, 0, 0.8);
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .terminal-lcd-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--cc-term-text-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .terminal-number-display {
            font-family: var(--cc-term-font), monospace;
            font-size: 30px;
            font-weight: 800;
            color: #f1f5f9;
            direction: ltr;
            text-align: center;
            letter-spacing: 2px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .terminal-number-display .cursor {
            display: inline-block;
            width: 3px;
            height: 32px;
            background: var(--cc-term-primary);
            box-shadow: 0 0 8px var(--cc-term-primary);
            animation: blinkCursor 1s step-end infinite;
            border-radius: 2px;
        }

        @keyframes blinkCursor {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        .terminal-number-display.placeholder {
            color: #475569;
            font-weight: 500;
            font-size: 20px;
            letter-spacing: 1px;
        }

        /* Optional Details Accordion */
        .terminal-details-toggle {
            background: rgba(255, 255, 255, 0.03);
            border: 1px dashed rgba(255, 255, 255, 0.12);
            border-radius: var(--cc-term-radius-sm);
            padding: 8px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 12px;
            color: var(--cc-term-text-muted);
        }

        .terminal-details-toggle:active {
            background: rgba(255, 255, 255, 0.07);
        }

        .terminal-details-toggle span.highlight {
            color: var(--cc-term-accent);
            font-weight: 600;
        }

        .terminal-details-drawer {
            display: none;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding-top: 4px;
        }

        .terminal-details-drawer.open {
            display: grid;
        }

        .terminal-input-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .terminal-input-group label {
            font-size: 11px;
            color: var(--cc-term-text-muted);
        }

        .terminal-input-group input {
            background: #090d16;
            border: 1px solid #334155;
            border-radius: var(--cc-term-radius-sm);
            color: #fff;
            padding: 8px 12px;
            font-family: inherit;
            font-size: 13px;
            outline: none;
            transition: border 0.2s;
        }

        .terminal-input-group input:focus {
            border-color: var(--cc-term-accent);
        }

        /* Hardware Touch Keypad (3x4 Grid) */
        .terminal-keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 4px 0;
        }

        .term-key {
            background: linear-gradient(180deg, #243247 0%, #172132 100%);
            border: 1px solid #334460;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.12);
            color: #f8fafc;
            border-radius: var(--cc-term-radius-md);
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.08s ease;
            position: relative;
            overflow: hidden;
        }

        .term-key:active {
            transform: translateY(2px) scale(0.97);
            background: #111827;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4), inset 0 2px 4px rgba(0, 0, 0, 0.6);
            border-color: var(--cc-term-primary);
        }

        .term-key.key-clear {
            background: linear-gradient(180deg, #3d2328 0%, #28161a 100%);
            border-color: #5c2b33;
            color: #fca5a5;
            font-size: 18px;
            font-weight: 800;
        }

        .term-key.key-backspace {
            background: linear-gradient(180deg, #2a2c3d 0%, #1c1d29 100%);
            border-color: #43465e;
            color: #cbd5e1;
            font-size: 22px;
        }

        /* Submit Action Button */
        .terminal-submit-btn {
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
            border: 1px solid #34d399;
            box-shadow: 0 8px 20px var(--cc-term-primary-glow), inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border-radius: var(--cc-term-radius-md);
            height: 60px;
            color: #ffffff;
            font-family: inherit;
            font-size: 18px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-top: 4px;
        }

        .terminal-submit-btn:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px var(--cc-term-primary-glow);
            filter: brightness(0.95);
        }

        .terminal-submit-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Result & Celebration Overlay */
        .terminal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(10, 15, 26, 0.96);
            border-radius: var(--cc-term-radius-lg);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px;
            text-align: center;
            z-index: 50;
            backdrop-filter: blur(8px);
            animation: overlayIn 0.25s ease-out forwards;
        }

        @keyframes overlayIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        .terminal-overlay.visible {
            display: flex;
        }

        .overlay-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 18px;
            position: relative;
        }

        .overlay-icon.success {
            background: rgba(16, 185, 129, 0.18);
            border: 3px solid #10b981;
            color: #10b981;
            box-shadow: 0 0 30px var(--cc-term-primary-glow);
            animation: checkmarkPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .overlay-icon.info {
            background: rgba(59, 130, 246, 0.18);
            border: 3px solid #3b82f6;
            color: #3b82f6;
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.3);
        }

        .overlay-icon.error {
            background: rgba(239, 68, 68, 0.18);
            border: 3px solid #ef4444;
            color: #ef4444;
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.3);
        }

        @keyframes checkmarkPop {
            0% { transform: scale(0.4); opacity: 0; }
            70% { transform: scale(1.15); }
            100% { transform: scale(1); opacity: 1; }
        }

        .overlay-title {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }

        .overlay-message {
            font-size: 14px;
            color: var(--cc-term-text-muted);
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .overlay-sms-badge {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #34d399;
            margin-bottom: 24px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .overlay-reset-btn {
            background: var(--cc-term-surface-elevated);
            border: 1px solid var(--cc-term-border);
            color: #fff;
            border-radius: var(--cc-term-radius-md);
            padding: 12px 28px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s;
        }

        .overlay-reset-btn:active {
            transform: scale(0.96);
            background: var(--cc-term-primary);
            color: #000;
        }

        .overlay-timer-ring {
            font-size: 12px;
            color: var(--cc-term-text-muted);
            margin-top: 14px;
        }

        /* PIN Lock Screen */
        .terminal-pin-modal {
            position: absolute;
            inset: 0;
            background: #0d131f;
            border-radius: var(--cc-term-radius-lg);
            z-index: 60;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 24px;
            text-align: center;
            gap: 16px;
        }

        .pin-dots {
            display: flex;
            gap: 12px;
            margin: 12px 0 6px;
            direction: ltr;
        }

        .pin-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid var(--cc-term-border);
            transition: all 0.2s;
        }

        .pin-dot.filled {
            background: var(--cc-term-primary);
            border-color: var(--cc-term-primary);
            box-shadow: 0 0 10px var(--cc-term-primary-glow);
            transform: scale(1.1);
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            body.clubcore-terminal-body {
                padding: 6px;
            }
            .terminal-device {
                border-radius: 20px;
                padding: 16px 14px;
                gap: 12px;
            }
            .term-key {
                height: 58px;
                font-size: 24px;
            }
            .terminal-number-display {
                font-size: 26px;
            }
        }
    </style>
<?php if (!$isEmbedded) : ?>
</head>
<body class="clubcore-terminal-body">
<?php endif; ?>

<div class="clubcore-terminal-wrapper">
    <div class="terminal-device" id="terminal-device">
        <!-- Top Status Bar -->
        <div class="terminal-header">
            <div class="terminal-brand">
                <span class="terminal-status-dot" title="آنلاین و متصل"></span>
                <span class="terminal-title"><?php echo esc_html($shopName); ?></span>
            </div>
            <div class="terminal-badges">
                <div class="terminal-badge">
                    <span>امروز:</span>
                    <strong id="term-today-count"><?php echo (int) $todayCount; ?></strong>
                </div>
                <button type="button" class="terminal-btn-icon active" id="term-sound-toggle" title="صدا">
                    🔊
                </button>
                <button type="button" class="terminal-btn-icon" id="term-fullscreen-toggle" title="تمام صفحه">
                    ⛶
                </button>
            </div>
        </div>

        <!-- LCD Screen Display -->
        <div class="terminal-lcd-display">
            <div class="terminal-lcd-label">
                <span>شماره موبایل مشتری:</span>
                <span id="term-digit-count" style="font-size: 11px; opacity: 0.7;">۰ / ۱۱ رقم</span>
            </div>
            <div class="terminal-number-display placeholder" id="term-display">
                <span>شماره را وارد کنید</span>
                <span class="cursor"></span>
            </div>
        </div>

        <!-- Optional Name Drawer -->
        <div class="terminal-details-toggle" id="term-details-toggle">
            <span>➕ ثبت نام و نام خانوادگی <span class="highlight">(اختیاری)</span></span>
            <span id="term-details-indicator">▼</span>
        </div>

        <div class="terminal-details-drawer" id="term-details-drawer">
            <div class="terminal-input-group">
                <label for="term-first-name">نام:</label>
                <input type="text" id="term-first-name" placeholder="مثال: علی">
            </div>
            <div class="terminal-input-group">
                <label for="term-last-name">نام خانوادگی:</label>
                <input type="text" id="term-last-name" placeholder="مثال: رضایی">
            </div>
        </div>

        <!-- Hardware Touch Keypad -->
        <div class="terminal-keypad" id="term-keypad">
            <button type="button" class="term-key" data-key="1">۱</button>
            <button type="button" class="term-key" data-key="2">۲</button>
            <button type="button" class="term-key" data-key="3">۳</button>
            <button type="button" class="term-key" data-key="4">۴</button>
            <button type="button" class="term-key" data-key="5">۵</button>
            <button type="button" class="term-key" data-key="6">۶</button>
            <button type="button" class="term-key" data-key="7">۷</button>
            <button type="button" class="term-key" data-key="8">۸</button>
            <button type="button" class="term-key" data-key="9">۹</button>
            <button type="button" class="term-key key-clear" data-action="clear">پاک کل</button>
            <button type="button" class="term-key" data-key="0">۰</button>
            <button type="button" class="term-key key-backspace" data-action="backspace">⌫</button>
        </div>

        <!-- Main Action Button -->
        <button type="button" class="terminal-submit-btn" id="term-submit-btn" disabled>
            <span>ثبت مشتری و ارسال پیامک</span>
        </button>

        <!-- Result Celebration Overlay -->
        <div class="terminal-overlay" id="term-overlay">
            <div class="overlay-icon success" id="overlay-icon">✓</div>
            <div class="overlay-title" id="overlay-title">ثبت با موفقیت انجام شد</div>
            <div class="overlay-message" id="overlay-message">مشتری گرامی، عضویت شما ثبت گردید.</div>
            <div class="overlay-sms-badge" id="overlay-sms-badge">
                <span>📩</span>
                <span id="overlay-sms-text">پیامک خوش‌آمدگویی ارسال شد</span>
            </div>
            <button type="button" class="overlay-reset-btn" id="overlay-reset-btn">
                <span>مشتری بعدی</span>
                <span>↻</span>
            </button>
            <div class="overlay-timer-ring" id="overlay-timer-ring">بازگشت خودکار در ۳ ثانیه...</div>
        </div>

        <!-- Optional PIN Lock Screen -->
        <?php if (!$isAuthorized && $requirePin) : ?>
        <div class="terminal-pin-modal" id="term-pin-modal">
            <div style="font-size: 38px;">🔒</div>
            <div style="font-size: 18px; font-weight: 800; color: #fff;">ورود اپراتور به دستگاه</div>
            <div style="font-size: 13px; color: var(--cc-term-text-muted);">لطفاً پین ۴ رقمی صندوق را وارد نمایید:</div>
            <div class="pin-dots" id="pin-dots">
                <div class="pin-dot"></div>
                <div class="pin-dot"></div>
                <div class="pin-dot"></div>
                <div class="pin-dot"></div>
            </div>
            <div class="terminal-keypad" id="pin-keypad" style="width: 100%; margin-top: 10px;">
                <button type="button" class="term-key" data-pinkey="1">۱</button>
                <button type="button" class="term-key" data-pinkey="2">۲</button>
                <button type="button" class="term-key" data-pinkey="3">۳</button>
                <button type="button" class="term-key" data-pinkey="4">۴</button>
                <button type="button" class="term-key" data-pinkey="5">۵</button>
                <button type="button" class="term-key" data-pinkey="6">۶</button>
                <button type="button" class="term-key" data-pinkey="7">۷</button>
                <button type="button" class="term-key" data-pinkey="8">۸</button>
                <button type="button" class="term-key" data-pinkey="9">۹</button>
                <button type="button" class="term-key key-clear" data-pinaction="clear">C</button>
                <button type="button" class="term-key" data-pinkey="0">۰</button>
                <button type="button" class="term-key key-backspace" data-pinaction="backspace">⌫</button>
            </div>
            <div id="pin-error" style="color: var(--cc-term-danger); font-size: 13px; font-weight: 600; min-height: 20px;"></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    'use strict';

    var currentPhone = '';
    var autoResetTimer = null;
    var soundEnabled = true;
    var audioCtx = null;

    var AJAX_URL = <?php echo json_encode($ajaxUrl); ?>;
    var NONCE = <?php echo json_encode($terminalNonce); ?>;

    var displayEl = document.getElementById('term-display');
    var digitCountEl = document.getElementById('term-digit-count');
    var submitBtn = document.getElementById('term-submit-btn');
    var overlayEl = document.getElementById('term-overlay');
    var overlayIcon = document.getElementById('overlay-icon');
    var overlayTitle = document.getElementById('overlay-title');
    var overlayMessage = document.getElementById('overlay-message');
    var overlaySmsBadge = document.getElementById('overlay-sms-badge');
    var overlaySmsText = document.getElementById('overlay-sms-text');
    var overlayResetBtn = document.getElementById('overlay-reset-btn');
    var overlayTimerRing = document.getElementById('overlay-timer-ring');
    var todayCountEl = document.getElementById('term-today-count');
    var detailsToggle = document.getElementById('term-details-toggle');
    var detailsDrawer = document.getElementById('term-details-drawer');
    var detailsIndicator = document.getElementById('term-details-indicator');
    var firstNameInput = document.getElementById('term-first-name');
    var lastNameInput = document.getElementById('term-last-name');
    var soundToggleBtn = document.getElementById('term-sound-toggle');
    var fullscreenToggleBtn = document.getElementById('term-fullscreen-toggle');

    // Web Audio Synthesizer (Realistic POS Beeps)
    function playBeep(freq, type, duration) {
        if (!soundEnabled) return;
        try {
            if (!audioCtx) {
                var AudioContext = window.AudioContext || window.webkitAudioContext;
                audioCtx = new AudioContext();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            var osc = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            osc.type = type || 'sine';
            osc.frequency.value = freq || 800;
            gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + (duration || 0.08));
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + (duration || 0.08));
        } catch(e) {}
    }

    function playSuccessChime() {
        if (!soundEnabled) return;
        playBeep(523.25, 'triangle', 0.12);
        setTimeout(function() { playBeep(659.25, 'triangle', 0.12); }, 90);
        setTimeout(function() { playBeep(783.99, 'triangle', 0.25); }, 180);
    }

    function playErrorTone() {
        if (!soundEnabled) return;
        playBeep(260, 'sawtooth', 0.18);
        setTimeout(function() { playBeep(220, 'sawtooth', 0.22); }, 120);
    }

    // Format phone display e.g. 0912 345 6789
    function formatPhoneDisplay(num) {
        if (!num) return '';
        var f = num;
        if (f.length > 4 && f.length <= 7) {
            f = f.slice(0, 4) + ' ' + f.slice(4);
        } else if (f.length > 7) {
            f = f.slice(0, 4) + ' ' + f.slice(4, 7) + ' ' + f.slice(7);
        }
        return f;
    }

    function toPersianDigits(str) {
        var p = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return String(str).replace(/[0-9]/g, function(d) { return p[d]; });
    }

    function updateDisplay() {
        if (currentPhone.length === 0) {
            displayEl.className = 'terminal-number-display placeholder';
            displayEl.innerHTML = '<span>شماره را وارد کنید</span><span class="cursor"></span>';
            digitCountEl.innerText = '۰ / ۱۱ رقم';
            submitBtn.disabled = true;
        } else {
            displayEl.className = 'terminal-number-display';
            var formatted = formatPhoneDisplay(currentPhone);
            displayEl.innerHTML = '<span>' + toPersianDigits(formatted) + '</span><span class="cursor"></span>';
            digitCountEl.innerText = toPersianDigits(currentPhone.length) + ' / ۱۱ رقم';
            // Valid Iranian phone starts with 09 and is 11 digits
            var isValid = (currentPhone.length === 11 && currentPhone.startsWith('09'));
            submitBtn.disabled = !isValid;
        }
    }

    function appendDigit(digit) {
        if (currentPhone.length >= 11) return;
        // Auto add 0 if user starts typing 9
        if (currentPhone.length === 0 && digit === '9') {
            currentPhone = '09';
        } else {
            currentPhone += digit;
        }
        playBeep(650 + (parseInt(digit, 10) * 45), 'sine', 0.05);
        updateDisplay();
    }

    function deleteDigit() {
        if (currentPhone.length > 0) {
            currentPhone = currentPhone.slice(0, -1);
            playBeep(420, 'sine', 0.05);
            updateDisplay();
        }
    }

    function clearPhone() {
        currentPhone = '';
        playBeep(350, 'sine', 0.08);
        updateDisplay();
    }

    // Keypad event delegation
    var keypadEl = document.getElementById('term-keypad');
    if (keypadEl) {
        keypadEl.addEventListener('click', function(e) {
            var btn = e.target.closest('.term-key');
            if (!btn) return;
            var key = btn.getAttribute('data-key');
            var action = btn.getAttribute('data-action');

            if (key !== null) {
                appendDigit(key);
            } else if (action === 'backspace') {
                deleteDigit();
            } else if (action === 'clear') {
                clearPhone();
            }
        });
    }

    // Physical Keyboard support
    window.addEventListener('keydown', function(e) {
        // If focus is in name inputs, don't hijack
        if (document.activeElement === firstNameInput || document.activeElement === lastNameInput) {
            return;
        }
        var k = e.key;
        if (/^[0-9]$/.test(k)) {
            e.preventDefault();
            appendDigit(k);
        } else if (k === 'Backspace') {
            e.preventDefault();
            deleteDigit();
        } else if (k === 'Escape' || k === 'Delete') {
            e.preventDefault();
            clearPhone();
        } else if (k === 'Enter' && !submitBtn.disabled) {
            e.preventDefault();
            submitCustomer();
        }
    });

    // Details Drawer toggle
    if (detailsToggle) {
        detailsToggle.addEventListener('click', function() {
            var isOpen = detailsDrawer.classList.toggle('open');
            detailsIndicator.innerText = isOpen ? '▲' : '▼';
            if (isOpen) {
                setTimeout(function() { firstNameInput.focus(); }, 100);
            }
        });
    }

    // Submit Action
    function submitCustomer() {
        if (submitBtn.disabled) return;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>در حال ثبت...</span><span>⏳</span>';

        var formData = new FormData();
        formData.append('action', 'clubcore_terminal_register');
        formData.append('nonce', NONCE);
        formData.append('phone', currentPhone);
        formData.append('first_name', firstNameInput ? firstNameInput.value : '');
        formData.append('last_name', lastNameInput ? lastNameInput.value : '');

        fetch(AJAX_URL, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            submitBtn.innerHTML = '<span>ثبت مشتری و ارسال پیامک</span>';
            if (data.success) {
                handleSuccess(data.data);
            } else {
                handleError(data.data ? data.data.message : 'خطا در ثبت مشتری.');
            }
        })
        .catch(function(err) {
            submitBtn.innerHTML = '<span>ثبت مشتری و ارسال پیامک</span>';
            handleError('خطا در برقراری ارتباط با سرور.');
        });
    }

    submitBtn.addEventListener('click', submitCustomer);

    function handleSuccess(data) {
        playSuccessChime();
        if (data.status === 'already_exists') {
            overlayIcon.className = 'overlay-icon info';
            overlayIcon.innerText = 'ℹ';
            overlayTitle.innerText = 'عضویت قبلی';
            overlayMessage.innerText = data.message;
            overlaySmsBadge.style.display = 'none';
        } else {
            overlayIcon.className = 'overlay-icon success';
            overlayIcon.innerText = '✓';
            overlayTitle.innerText = 'ثبت با موفقیت انجام شد';
            overlayMessage.innerText = data.greeting || 'به باشگاه مشتریان خوش آمدید!';
            overlaySmsBadge.style.display = 'inline-flex';
            overlaySmsText.innerText = data.sms_message || 'پیامک خوش‌آمدگویی ارسال شد';
        }

        if (data.today_count !== undefined && todayCountEl) {
            todayCountEl.innerText = data.today_count;
        }

        overlayEl.classList.add('visible');

        // 3-second Auto Reset Countdown
        var secondsLeft = 3;
        overlayTimerRing.innerText = 'بازگشت خودکار در ' + toPersianDigits(secondsLeft) + ' ثانیه...';

        if (autoResetTimer) clearInterval(autoResetTimer);
        autoResetTimer = setInterval(function() {
            secondsLeft--;
            if (secondsLeft <= 0) {
                clearInterval(autoResetTimer);
                resetTerminal();
            } else {
                overlayTimerRing.innerText = 'بازگشت خودکار در ' + toPersianDigits(secondsLeft) + ' ثانیه...';
            }
        }, 1000);
    }

    function handleError(msg) {
        playErrorTone();
        overlayIcon.className = 'overlay-icon error';
        overlayIcon.innerText = '✕';
        overlayTitle.innerText = 'خطا در ثبت';
        overlayMessage.innerText = msg;
        overlaySmsBadge.style.display = 'none';
        overlayTimerRing.innerText = '';
        overlayEl.classList.add('visible');
    }

    function resetTerminal() {
        if (autoResetTimer) clearInterval(autoResetTimer);
        overlayEl.classList.remove('visible');
        clearPhone();
        if (firstNameInput) firstNameInput.value = '';
        if (lastNameInput) lastNameInput.value = '';
        if (detailsDrawer) detailsDrawer.classList.remove('open');
        if (detailsIndicator) detailsIndicator.innerText = '▼';
    }

    overlayResetBtn.addEventListener('click', resetTerminal);

    // Sound toggle
    if (soundToggleBtn) {
        soundToggleBtn.addEventListener('click', function() {
            soundEnabled = !soundEnabled;
            soundToggleBtn.classList.toggle('active', soundEnabled);
            soundToggleBtn.innerText = soundEnabled ? '🔊' : '🔇';
        });
    }

    // Fullscreen toggle
    if (fullscreenToggleBtn) {
        fullscreenToggleBtn.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(function() {});
                fullscreenToggleBtn.classList.add('active');
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    fullscreenToggleBtn.classList.remove('active');
                }
            }
        });
    }

    // PIN Pad Login logic (if locked)
    var pinModal = document.getElementById('term-pin-modal');
    var pinKeypad = document.getElementById('pin-keypad');
    var pinDots = document.getElementById('pin-dots');
    var pinError = document.getElementById('pin-error');
    var enteredPin = '';

    if (pinKeypad) {
        pinKeypad.addEventListener('click', function(e) {
            var btn = e.target.closest('.term-key');
            if (!btn) return;
            var key = btn.getAttribute('data-pinkey');
            var action = btn.getAttribute('data-pinaction');

            if (key !== null && enteredPin.length < 4) {
                enteredPin += key;
                playBeep(700, 'sine', 0.05);
                updatePinDots();
                if (enteredPin.length === 4) {
                    verifyPin();
                }
            } else if (action === 'backspace') {
                if (enteredPin.length > 0) {
                    enteredPin = enteredPin.slice(0, -1);
                    playBeep(450, 'sine', 0.05);
                    updatePinDots();
                }
            } else if (action === 'clear') {
                enteredPin = '';
                playBeep(350, 'sine', 0.08);
                updatePinDots();
            }
        });

        function updatePinDots() {
            var dots = pinDots.querySelectorAll('.pin-dot');
            dots.forEach(function(dot, idx) {
                dot.classList.toggle('filled', idx < enteredPin.length);
            });
            pinError.innerText = '';
        }

        function verifyPin() {
            var formData = new FormData();
            formData.append('action', 'clubcore_terminal_verify_pin');
            formData.append('nonce', NONCE);
            formData.append('pin', enteredPin);

            fetch(AJAX_URL, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    playSuccessChime();
                    pinModal.remove();
                } else {
                    playErrorTone();
                    pinError.innerText = data.data ? data.data.message : 'پین اشتباه است.';
                    enteredPin = '';
                    updatePinDots();
                }
            })
            .catch(function() {
                pinError.innerText = 'خطا در بررسی پین.';
                enteredPin = '';
                updatePinDots();
            });
        }
    }

})();
</script>
<?php if (!$isEmbedded) : ?>
</body>
</html>
<?php endif; ?>

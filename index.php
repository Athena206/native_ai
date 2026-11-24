<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

startAuthSession();
$currentUser = getAuthenticatedUser();

$faqs = [];
$totalAnswered = 0;
$dbConnected = false;

try {
    $pdo = getDatabaseConnection();
    ensureUsersAuthSchema($pdo);
    $dbConnected = true;

    $faqStmt = $pdo->query('SELECT id, keyword, question, answer FROM faqs ORDER BY id ASC');
    if ($faqStmt !== false) {
        $faqs = $faqStmt->fetchAll();
    }

    $countStmt = $pdo->query("SELECT COUNT(*) FROM chats WHERE sender_type = 'bot'");
    if ($countStmt !== false) {
        $totalAnswered = (int) $countStmt->fetchColumn();
    }
} catch (Throwable $e) {
    $dbConnected = false;
}

function cleanFaqText(string $text): string
{
    $fixed = str_replace(["\xE2\x80\x99", "â€™", "├óÔé¼Ôäós"], ["'", "'", "'s"], $text);
    return htmlspecialchars($fixed, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#003366">
    <title>Native AI — Simple &amp; Safe Banking Portal</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop offset='0%25' stop-color='%23003366'/%3E%3Cstop offset='55%25' stop-color='%230b5cab'/%3E%3Cstop offset='100%25' stop-color='%2338bdf8'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='64' height='64' rx='16' fill='url(%23g)'/%3E%3Cpath d='M18 46V18h6l16 19V18h6v28h-6L24 27v19h-6z' fill='%23fff'/%3E%3Ccircle cx='49' cy='15' r='5' fill='%2338bdf8'/%3E%3C/svg%3E">
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('sbi_theme');
                if (saved === 'dark' || saved === 'light') {
                    document.documentElement.setAttribute('data-theme', saved);
                }
            } catch (e) {}
        })();
    </script>
    <style>
        :root {
            color-scheme: light;
            --bg-page: #F8FAFC;
            --bg-surface: #FFFFFF;
            --bg-elevated: #FFFFFF;
            --bg-surface-alt: #F1F5F9;
            --bg-header: rgba(248, 250, 252, 0.92);
            --bg-hero: linear-gradient(135deg, #0F294A 0%, #163C6B 58%, #0D9488 100%);
            --text-main: #0F172A;
            --text-muted: #475569;
            --text-inverse: #FFFFFF;
            --border-color: #E2E8F0;
            --brand-primary: #0F294A;
            --brand-dark: #091B33;
            --on-brand-primary: #FFFFFF;
            --brand-soft: #E6EEF8;
            --accent-green: #0D9488;
            --accent-green-bg: #E6F7F5;
            --accent-amber: #D97706;
            --accent-amber-bg: #FEF3C7;
            --accent-red: #DC2626;
            --accent-red-bg: #FEE2E2;
            --status-success: #15803D;
            --status-warning: #B45309;
            --shadow-sm: 0 2px 10px rgba(15, 23, 42, 0.05);
            --shadow-md: 0 10px 28px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 22px 48px rgba(15, 23, 42, 0.16);
            --radius-md: 14px;
            --radius-lg: 20px;
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --bg-page: #090D16;
            --bg-surface: #111827;
            --bg-elevated: #182234;
            --bg-surface-alt: #182234;
            --bg-header: rgba(17, 24, 39, 0.92);
            --bg-hero: linear-gradient(135deg, #090D16 0%, #111827 52%, #134E4A 100%);
            --text-main: #F1F5F9;
            --text-muted: #94A3B8;
            --text-inverse: #F1F5F9;
            --border-color: #1E293B;
            --brand-primary: #2DD4BF;
            --brand-dark: #14B8A6;
            --on-brand-primary: #090D16;
            --brand-soft: rgba(45, 212, 191, 0.14);
            --accent-green: #34D399;
            --accent-green-bg: rgba(52, 211, 153, 0.14);
            --accent-amber: #FBBF24;
            --accent-amber-bg: rgba(251, 191, 36, 0.14);
            --accent-red: #F87171;
            --accent-red-bg: rgba(248, 113, 113, 0.14);
            --status-success: #34D399;
            --status-warning: #FBBF24;
            --shadow-sm: 0 4px 14px rgba(0, 0, 0, 0.32);
            --shadow-md: 0 12px 32px rgba(0, 0, 0, 0.44);
            --shadow-lg: 0 24px 56px rgba(0, 0, 0, 0.62);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        html {
            scroll-behavior: smooth;
        }

        :root {
            --ease-smooth: cubic-bezier(0.22, 1, 0.36, 1);
        }

        body {
            margin: 0;
            font-family: Inter, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-page);
            color: var(--text-main);
            line-height: 1.55;
            transition: background-color 0.45s var(--ease-smooth), color 0.45s var(--ease-smooth);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button, input, select {
            font: inherit;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes dropdownReveal {
            from { opacity: 0; transform: translateY(-8px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes tabPanelReveal {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulseRing {
            0% { box-shadow: 0 0 0 0 rgba(78, 161, 255, 0.45); }
            70% { box-shadow: 0 0 0 10px rgba(78, 161, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(78, 161, 255, 0); }
        }

        .animate-in {
            animation: fadeSlideUp 0.65s var(--ease-smooth) both;
        }

        /* Header */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 120;
            background: var(--bg-header);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.25s ease, border-color 0.25s ease;
        }

        .header-inner {
            max-width: 1240px;
            margin: 0 auto;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            position: relative;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            flex-shrink: 0;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #003366, #0b5cab);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 6px 14px rgba(11, 92, 171, 0.28);
        }

        .brand-text strong {
            display: block;
            font-size: 17px;
            letter-spacing: -0.02em;
            color: var(--text-main);
        }

        .brand-text span {
            display: block;
            font-size: 12px;
            color: var(--text-muted);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .lang-select {
            padding: 7px 10px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        /* Icon-only theme button */
        .theme-icon-btn {
            width: 38px;
            height: 38px;
            padding: 0;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.18s ease, background-color 0.2s ease, border-color 0.2s ease;
        }

        .theme-icon-btn:hover {
            background: var(--brand-soft);
            border-color: var(--brand-primary);
            transform: translateY(-1px);
        }

        .theme-icon-btn svg {
            width: 19px;
            height: 19px;
            display: block;
        }

        .btn {
            border: 0;
            border-radius: 10px;
            padding: 9px 15px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: transform 0.16s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--brand-primary);
            color: var(--on-brand-primary);
            box-shadow: 0 6px 16px rgba(15, 41, 74, 0.22);
        }

        .btn-outline {
            background: var(--bg-surface);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-outline:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
        }

        .btn-soft {
            background: var(--brand-soft);
            color: var(--brand-primary);
        }

        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: var(--accent-green-bg);
            color: var(--status-success);
            font-size: 13px;
            font-weight: 700;
        }

        /* Top-Right Hamburger Menu Trigger */
        .hamburger-ai-btn {
            position: relative;
            height: 40px;
            padding: 0 13px;
            border-radius: 11px;
            border: 1px solid var(--brand-primary);
            background: var(--brand-primary);
            color: var(--on-brand-primary);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            animation: pulseRing 2.8s infinite;
            transition: transform 0.18s ease, background-color 0.2s ease;
        }

        .hamburger-ai-btn:hover {
            transform: translateY(-1px);
        }

        .hamburger-ai-btn svg {
            width: 20px;
            height: 20px;
        }

        /* Top-Right Corner Hamburger Dropdown Panel (shadcn DropdownMenu style) */
        .chatbot-dropdown {
            position: fixed;
            top: 66px;
            right: 16px;
            width: min(268px, calc(100vw - 24px));
            max-height: calc(100vh - 84px);
            background: var(--bg-elevated);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            z-index: 250;
            display: none;
            flex-direction: column;
            overflow: hidden;
            transform-origin: top right;
        }

        .chatbot-dropdown.open {
            display: flex;
            animation: dropdownReveal 0.36s var(--ease-smooth) both;
        }

        /* Side-by-Side Front AI Chatbot Panel */
        .ai-chat-panel {
            position: fixed;
            top: 80px;
            right: 16px;
            bottom: 16px;
            width: min(384px, calc(100vw - 24px));
            background: var(--bg-elevated);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            box-shadow: var(--shadow-lg);
            z-index: 220;
            display: none;
            flex-direction: column;
            overflow: hidden;
            transform-origin: bottom right;
        }

        .ai-chat-panel.open {
            display: flex;
            animation: dropdownReveal 0.42s var(--ease-smooth) both;
        }

        main.container,
        footer.site-footer {
            transition: max-width 0.42s var(--ease-smooth), margin-right 0.42s var(--ease-smooth);
        }

        @media (min-width: 1080px) {
            body.chat-docked-open main.container,
            body.chat-docked-open footer.site-footer {
                max-width: min(1160px, calc(100% - 420px));
                margin-left: auto;
                margin-right: 416px;
            }
            body.chat-docked-open .clarity-grid,
            body.chat-docked-open .faq-list,
            body.chat-docked-open .two-col,
            body.chat-docked-open .footer-inner {
                grid-template-columns: 1fr;
            }
            body.chat-docked-open .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1480px) {
            body.chat-docked-open .clarity-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            body.chat-docked-open .faq-list,
            body.chat-docked-open .two-col,
            body.chat-docked-open .footer-inner {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .chat-launcher-btn {
            position: fixed;
            bottom: 22px;
            right: 22px;
            z-index: 215;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: var(--bg-hero);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            box-shadow: var(--shadow-lg);
            cursor: pointer;
            transition: transform 0.28s var(--ease-smooth), box-shadow 0.28s var(--ease-smooth), opacity 0.28s var(--ease-smooth);
        }

        .chat-launcher-btn:hover {
            transform: translateY(-2px);
        }

        .chat-launcher-btn.hidden {
            opacity: 0;
            pointer-events: none;
            transform: translateY(10px);
        }

        .hamburger-nav-section {
            padding: 6px;
            background: transparent;
            display: flex;
            flex-direction: column;
            gap: 1px;
            max-height: calc(100vh - 86px);
            overflow-y: auto;
        }

        .dropdown-menu-group {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .dropdown-menu-label {
            padding: 4px 8px 2px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .dropdown-menu-separator {
            height: 1px;
            margin: 4px 2px;
            background: var(--border-color);
        }

        .hamburger-nav-links {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .hamburger-nav-link {
            width: 100%;
            padding: 6px 8px;
            border-radius: 7px;
            border: 0;
            background: transparent;
            color: var(--text-main);
            font-size: 13px;
            font-weight: 500;
            line-height: 1.3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            cursor: pointer;
            text-align: left;
            transition: background-color 0.25s var(--ease-smooth), color 0.25s var(--ease-smooth), transform 0.25s var(--ease-smooth);
        }

        .hamburger-nav-link:hover,
        .hamburger-nav-link:focus-visible {
            color: var(--brand-primary);
            background: var(--brand-soft);
            outline: none;
        }

        .dropdown-item-left {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .dropdown-item-icon {
            width: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            line-height: 1;
            flex-shrink: 0;
        }

        .dropdown-item-chevron {
            font-size: 11px;
            color: var(--text-muted);
            opacity: 0.7;
            flex-shrink: 0;
        }

        .menu-faq-dropdown {
            border-radius: 7px;
            background: transparent;
            overflow: hidden;
        }

        .menu-faq-dropdown summary {
            padding: 6px 8px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.3;
            cursor: pointer;
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: var(--brand-primary);
            border-radius: 7px;
            transition: background-color 0.25s var(--ease-smooth);
        }

        .menu-faq-dropdown summary:hover {
            background: var(--brand-soft);
        }

        .menu-faq-dropdown summary::-webkit-details-marker {
            display: none;
        }

        .menu-faq-items {
            padding: 6px 8px 8px;
            margin-top: 2px;
            border-top: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-faq-item {
            font-size: 12px;
            padding: 7px 8px;
            border-radius: 8px;
            background: var(--bg-surface-alt);
        }

        .menu-faq-item strong {
            display: block;
            margin-bottom: 3px;
            color: var(--text-main);
        }

        .menu-faq-item p {
            margin: 0 0 5px;
            color: var(--text-muted);
        }

        .chatbot-head {
            padding: 13px 16px;
            background: var(--bg-hero);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .chatbot-head-title strong {
            display: block;
            font-size: 15px;
        }

        .chatbot-head-title span {
            font-size: 12px;
            opacity: 0.88;
        }

        .chatbot-head-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .mini-pill-btn {
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            border-radius: 8px;
            padding: 5px 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .mini-pill-btn.active {
            background: #ffffff;
            color: #003366;
        }

        .chatbot-subbar {
            padding: 8px 14px;
            background: var(--bg-surface-alt);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .chatbot-subbar select {
            padding: 4px 8px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            font-size: 12px;
        }

        .chatbot-locked-box {
            padding: 20px 16px;
            text-align: center;
        }

        .chatbot-locked-box h4 {
            margin: 0 0 6px;
            font-size: 16px;
        }

        .chatbot-locked-box p {
            margin: 0 0 14px;
            font-size: 13px;
            color: var(--text-muted);
        }

        #messages {
            flex: 1;
            min-height: 210px;
            overflow-y: auto;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: var(--bg-page);
        }

        .message-row {
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }

        .message-row.user {
            flex-direction: row-reverse;
        }

        .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .avatar.bot {
            background: var(--brand-soft);
            color: var(--brand-primary);
        }

        .avatar.user {
            background: var(--brand-primary);
            color: var(--on-brand-primary);
        }

        .message-bubble {
            max-width: 82%;
            padding: 10px 13px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.45;
            white-space: pre-wrap;
            word-break: break-word;
            background: var(--bg-surface);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .message-row.user .message-bubble {
            background: var(--brand-primary);
            color: var(--on-brand-primary);
            border-color: transparent;
        }

        .message-row.error .message-bubble {
            background: var(--accent-red-bg);
            color: var(--accent-red);
            border-color: transparent;
        }

        .listen-btn {
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface-alt);
            color: var(--brand-primary);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .listen-btn:hover {
            background: var(--brand-soft);
            border-color: var(--brand-primary);
        }

        .quick-chips {
            padding: 8px 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            background: var(--bg-surface);
            border-top: 1px solid var(--border-color);
        }

        .chip-btn {
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface-alt);
            color: var(--text-main);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .chip-btn:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
        }

        .chat-form {
            padding: 10px 12px;
            background: var(--bg-surface);
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-form input {
            flex: 1;
            min-width: 0;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-page);
            color: var(--text-main);
            font-size: 14px;
        }

        .icon-square-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface-alt);
            color: var(--text-main);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .icon-square-btn.listening {
            background: var(--accent-red);
            color: #ffffff;
            border-color: var(--accent-red);
        }

        #voice-status {
            padding: 4px 12px 8px;
            font-size: 11px;
            color: var(--text-muted);
            background: var(--bg-surface);
            display: none;
        }

        #voice-status.visible {
            display: block;
        }

        /* Auth Modal (Hidden by default, opens only on button click) */
        .auth-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(6, 15, 29, 0.64);
            backdrop-filter: blur(5px);
            z-index: 300;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        .auth-modal-backdrop.open {
            display: flex;
            animation: dropdownReveal 0.2s ease-out both;
        }

        .auth-card {
            width: min(440px, 100%);
            background: var(--bg-elevated);
            color: var(--text-main);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            position: relative;
        }

        .developer-card {
            width: min(580px, 100%);
            max-height: calc(100vh - 36px);
            overflow-y: auto;
        }

        .dev-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand-primary);
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 10px;
        }

        .dev-headline {
            margin: 4px 0 14px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--accent-warm);
            line-height: 1.45;
        }

        .dev-bio {
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 13.5px;
            line-height: 1.6;
            color: var(--text-main);
        }

        .dev-bio p {
            margin: 0;
        }

        .dev-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid var(--border-color);
        }

        .dev-skill-pill {
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 11.5px;
            font-weight: 600;
        }

        .modal-close-btn {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface-alt);
            color: var(--text-main);
            display: grid;
            place-items: center;
            cursor: pointer;
            font-weight: 700;
        }

        .auth-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            padding: 5px;
            border-radius: 12px;
            background: var(--bg-surface-alt);
            margin: 8px 36px 18px 0;
        }

        .auth-tab {
            border: 0;
            border-radius: 9px;
            padding: 9px 12px;
            font-size: 14px;
            font-weight: 700;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: background-color 0.45s var(--ease-smooth), color 0.45s var(--ease-smooth), box-shadow 0.45s var(--ease-smooth), transform 0.35s var(--ease-smooth);
        }

        .auth-tab.active {
            background: var(--bg-surface);
            color: var(--brand-primary);
            box-shadow: var(--shadow-sm);
        }

        .auth-panel-animate {
            animation: tabPanelReveal 0.48s var(--ease-smooth) both;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-main);
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap input {
            width: 100%;
            padding: 11px 42px 11px 13px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-page);
            color: var(--text-main);
            font-size: 14px;
            transition: border-color 0.18s ease;
        }

        .input-wrap input:focus {
            outline: none;
            border-color: var(--brand-primary);
        }

        .eye-toggle-btn {
            position: absolute;
            right: 8px;
            width: 32px;
            height: 32px;
            border: 0;
            background: transparent;
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 8px;
        }

        .eye-toggle-btn:hover {
            background: var(--brand-soft);
            color: var(--brand-primary);
        }

        .eye-toggle-btn svg {
            width: 18px;
            height: 18px;
        }

        .auth-alert {
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 14px;
            display: none;
        }

        .auth-alert.show {
            display: block;
        }

        .auth-alert.success {
            background: var(--accent-green-bg);
            color: var(--accent-green);
        }

        .auth-alert.error {
            background: var(--accent-red-bg);
            color: var(--accent-red);
        }

        /* Main Container & Clean Hero */
        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 26px 20px 64px;
        }

        .hero {
            position: relative;
            overflow: hidden;
            background: var(--bg-hero);
            color: #ffffff;
            border-radius: var(--radius-lg);
            padding: 44px 36px;
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .hero > *:not(.hero-motion-bg) {
            position: relative;
            z-index: 2;
        }

        .hero-motion-bg {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 1;
        }

        .hero-orb {
            position: absolute;
            border-radius: 999px;
            filter: blur(2px);
            opacity: 0.45;
            will-change: transform;
        }

        .hero-orb-1 {
            width: 240px;
            height: 240px;
            top: -60px;
            right: -30px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.55) 0%, rgba(56, 189, 248, 0) 70%);
        }

        .hero-orb-2 {
            width: 180px;
            height: 180px;
            bottom: -50px;
            right: 22%;
            background: radial-gradient(circle, rgba(167, 243, 208, 0.35) 0%, rgba(167, 243, 208, 0) 70%);
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.16;
            letter-spacing: -0.02em;
        }

        .hero p {
            margin: 0 0 8px;
            font-size: 16px;
            color: rgba(255, 255, 255, 0.92);
            max-width: 68ch;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .hero-actions .btn-hero-light {
            background: #ffffff;
            color: #003366;
        }

        .hero-actions .btn-hero-ghost {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.34);
        }

        /* Clarity Strip */
        .clarity-grid {
            margin-top: 24px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .clarity-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 18px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: flex-start;
            gap: 14px;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .clarity-card:hover {
            transform: translateY(-2px);
            border-color: var(--brand-primary);
        }

        .clarity-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--brand-soft);
            color: var(--brand-primary);
            display: grid;
            place-items: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .clarity-card h3 {
            margin: 0 0 4px;
            font-size: 16px;
        }

        .clarity-card p {
            margin: 0 0 8px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .clarity-link {
            font-size: 13px;
            font-weight: 700;
            color: var(--brand-primary);
            cursor: pointer;
            background: none;
            border: 0;
            padding: 0;
        }

        /* Sections */
        .section {
            margin-top: 42px;
        }

        .section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .section-head h2 {
            margin: 0 0 4px;
            font-size: 24px;
            letter-spacing: -0.01em;
        }

        .section-head p {
            margin: 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        /* FAQ Dropdown Accordions */
        .faq-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            align-items: start;
        }

        .faq-dropdown {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: border-color 0.35s var(--ease-smooth), box-shadow 0.35s var(--ease-smooth);
            overflow: hidden;
        }

        .faq-dropdown:hover {
            border-color: var(--brand-primary);
        }

        .faq-dropdown summary {
            padding: 16px 18px;
            cursor: pointer;
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-weight: 700;
            font-size: 15px;
        }

        .faq-dropdown summary::-webkit-details-marker {
            display: none;
        }

        .faq-summary-left {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .faq-chevron {
            font-size: 14px;
            color: var(--brand-primary);
            transition: transform 0.42s var(--ease-smooth);
            flex-shrink: 0;
        }

        .faq-dropdown[open] .faq-chevron {
            transform: rotate(180deg);
        }

        .faq-body {
            padding: 0 18px 16px;
            border-top: 1px solid var(--border-color);
        }

        .faq-tag {
            display: inline-block;
            align-self: flex-start;
            padding: 3px 9px;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand-primary);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .faq-body p {
            margin: 12px 0 10px;
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Picture Banking Cards */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .service-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 18px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            cursor: pointer;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .service-card:hover, .service-card.active-card {
            transform: translateY(-3px);
            border-color: var(--brand-primary);
            box-shadow: var(--shadow-md);
        }

        .service-emoji {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .service-card h3 {
            margin: 0 0 6px;
            font-size: 16px;
        }

        .service-card p {
            margin: 0 0 12px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .service-detail {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            padding: 0 10px;
            margin-bottom: 0;
            border-radius: 10px;
            background: var(--bg-surface-alt);
            border: 0 solid var(--border-color);
            font-size: 13px;
            color: var(--text-main);
            transition: max-height 0.52s var(--ease-smooth), opacity 0.45s var(--ease-smooth), padding 0.45s var(--ease-smooth), margin-bottom 0.45s var(--ease-smooth);
        }

        .service-card.active-card .service-detail {
            max-height: 220px;
            opacity: 1;
            padding: 10px;
            margin-bottom: 12px;
            border-width: 1px;
        }

        .service-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Guides & Calculator */
        .two-col {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 18px;
        }

        .panel-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 22px;
            box-shadow: var(--shadow-sm);
        }

        .guide-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }

        .guide-tab {
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface-alt);
            color: var(--text-main);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.45s var(--ease-smooth), color 0.45s var(--ease-smooth), border-color 0.45s var(--ease-smooth), transform 0.35s var(--ease-smooth);
        }

        .guide-tab.active {
            background: var(--brand-primary);
            color: var(--on-brand-primary);
            border-color: var(--brand-primary);
            transform: translateY(-1px);
        }

        .guide-steps {
            margin: 0 0 14px;
            padding-left: 20px;
            transition: opacity 0.42s var(--ease-smooth), transform 0.42s var(--ease-smooth);
        }

        .guide-steps.is-fading {
            opacity: 0;
            transform: translateY(6px);
        }

        .guide-steps li {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .calc-row {
            margin-bottom: 14px;
        }

        .calc-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 6px;
        }

        .calc-controls input[type="range"] {
            flex: 1;
        }

        .calc-result {
            padding: 14px;
            border-radius: 12px;
            background: var(--brand-soft);
            margin-bottom: 14px;
        }

        .calc-result strong {
            display: block;
            font-size: 20px;
            color: var(--brand-primary);
        }

        /* Footer */
        .site-footer {
            background: var(--bg-surface);
            border-top: 1px solid var(--border-color);
            padding: 32px 20px 56px;
            margin-top: 48px;
        }

        .footer-inner {
            max-width: 1240px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr;
            gap: 24px;
        }

        .footer-inner h4 {
            margin: 0 0 10px;
            font-size: 15px;
        }

        .footer-inner p, .footer-inner li {
            font-size: 13px;
            color: var(--text-muted);
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 6px;
        }

        .fixed-brand-badge {
            position: fixed;
            left: 16px;
            bottom: 16px;
            z-index: 225;
            padding: 6px 12px;
            border-radius: 999px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.01em;
            box-shadow: var(--shadow-sm);
            pointer-events: none;
            user-select: none;
        }

        @media (max-width: 980px) {
            .two-col, .footer-inner, .clarity-grid, .faq-list {
                grid-template-columns: 1fr;
            }
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 560px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
            .hero {
                padding: 24px 20px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="#top" class="brand">
            <div class="brand-logo" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 48V16H24L40 36V16H48V48H40L24 28V48H16Z" fill="white"/>
                    <circle cx="48" cy="14" r="5" fill="#38BDF8"/>
                    <path d="M16 14C22 10 30 10 35 13" stroke="#93C5FD" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="brand-text">
                <strong data-i18n="siteName">Native AI</strong>
                <span data-i18n="siteTagline">Simple &amp; Safe Banking for Everyone</span>
            </div>
        </a>

        <div class="header-actions">
            <select id="site-language" class="lang-select" aria-label="Select Language">
                <option value="en-US">English</option>
                <option value="hi-IN">हिन्दी</option>
                <option value="bn-IN">বাংলা</option>
            </select>

            <!-- Icon-only theme toggle button (no text) -->
            <button type="button" id="theme-toggle" class="theme-icon-btn" aria-label="Toggle theme"></button>

            <?php if ($currentUser !== null): ?>
                <span class="user-chip" title="<?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?>">
                    👤 <?= htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <button type="button" class="btn btn-outline" id="logout-btn" data-i18n="logoutBtn">Log Out</button>
            <?php endif; ?>

            <!-- Always-visible Top-Right Hamburger Dropdown Trigger -->
            <button type="button" class="hamburger-ai-btn" id="hamburger-ai-toggle" aria-expanded="false" aria-controls="chatbot-dropdown">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
                <span data-i18n="menuLabel"><?= $currentUser ? 'Menu & AI' : 'Menu' ?></span>
            </button>
        </div>
    </div>
</header>

<!-- Top-Right Corner Hamburger Dropdown Menu (shadcn DropdownMenu structure) -->
<aside id="chatbot-dropdown" class="chatbot-dropdown" role="menu" aria-label="Navigation and AI Assistant Menu">
    <div class="hamburger-nav-section">
        <div class="dropdown-menu-group">
            <div class="dropdown-menu-label" data-i18n="menuNavLabel">Navigation</div>
            <div class="hamburger-nav-links">
                <a href="#faqs" class="hamburger-nav-link menu-jump-link" role="menuitem">
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">❓</span><span data-i18n="navFaqs">FAQs</span></span>
                    <span class="dropdown-item-chevron">→</span>
                </a>
                <a href="#services" class="hamburger-nav-link menu-jump-link" role="menuitem">
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">🖼️</span><span data-i18n="navServices">Picture Services</span></span>
                    <span class="dropdown-item-chevron">→</span>
                </a>
                <a href="#guides" class="hamburger-nav-link menu-jump-link" role="menuitem">
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">📘</span><span data-i18n="navGuides">Step Guides</span></span>
                    <span class="dropdown-item-chevron">→</span>
                </a>
                <a href="#calculator" class="hamburger-nav-link menu-jump-link" role="menuitem">
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">🧮</span><span data-i18n="navCalc">Calculator</span></span>
                    <span class="dropdown-item-chevron">→</span>
                </a>
            </div>
        </div>

        <div class="dropdown-menu-separator"></div>

        <div class="dropdown-menu-group">
            <div class="dropdown-menu-label" data-i18n="menuAccountLabel">My Account</div>
            <?php if ($currentUser === null): ?>
                <button type="button" class="hamburger-nav-link" id="menu-login-item" role="menuitem">
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">🔐</span><span data-i18n="loginBtn">Log In</span></span>
                    <span class="dropdown-item-chevron">→</span>
                </button>
                <button type="button" class="hamburger-nav-link" id="menu-signup-item" role="menuitem">
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">✨</span><span data-i18n="signupBtn">Sign Up</span></span>
                    <span class="dropdown-item-chevron">→</span>
                </button>
            <?php else: ?>
                <div class="hamburger-nav-link" style="cursor:default; opacity:0.85;">
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">👤</span><span><?= htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?></span></span>
                </div>
                <button type="button" class="hamburger-nav-link" id="menu-open-ai-item" role="menuitem">
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">🤖</span><span data-i18n="chatTitle">Native AI Assistant</span></span>
                    <span class="dropdown-item-chevron">→</span>
                </button>
                <button type="button" class="hamburger-nav-link" id="menu-logout-item" role="menuitem">
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">🚪</span><span data-i18n="logoutBtn">Log Out</span></span>
                </button>
            <?php endif; ?>
        </div>

        <div class="dropdown-menu-separator"></div>

        <div class="dropdown-menu-group">
            <div class="dropdown-menu-label" data-i18n="menuQuickFaqLabel">Quick Help</div>
            <details class="menu-faq-dropdown">
                <summary>
                    <span class="dropdown-item-left"><span class="dropdown-item-icon">💡</span><span data-i18n="menuFaqTitle">Frequently Asked Questions</span></span>
                    <span>▾</span>
                </summary>
                <div class="menu-faq-items">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <?php $mFaqId = 'menu-faq-' . (int) ($faq['id'] ?? $index); ?>
                        <div class="menu-faq-item" data-faq-index="<?= $index ?>">
                            <strong class="faq-q-text"><?= cleanFaqText((string) $faq['question']) ?></strong>
                            <p class="faq-a-text" id="<?= $mFaqId ?>"><?= cleanFaqText((string) $faq['answer']) ?></p>
                            <button type="button" class="listen-btn" data-speak-target="<?= $mFaqId ?>">🔊 Listen</button>
                        </div>
                    <?php endforeach; ?>
                    <div class="menu-faq-item" data-faq-key="rural">
                        <strong class="faq-q-text">How can I check my balance if I don't have a smartphone?</strong>
                        <p class="faq-a-text" id="menu-faq-extra">Give a missed call from your registered mobile number to the bank's official missed-call balance number, visit your nearest Bank Mitra (CSP), or update your passbook at the branch.</p>
                        <button type="button" class="listen-btn" data-speak-target="menu-faq-extra">🔊 Listen</button>
                    </div>
                </div>
            </details>
            <button type="button" class="hamburger-nav-link" id="menu-about-dev-item" role="menuitem">
                <span class="dropdown-item-left"><span class="dropdown-item-icon">👩‍💻</span><span>About Developer</span></span>
                <span class="dropdown-item-chevron">→</span>
            </button>
        </div>
    </div>
</aside>

<!-- Front-Facing Side-by-Side AI Chatbot Panel -->
<aside id="ai-chat-panel" class="ai-chat-panel<?= $currentUser !== null ? ' open' : '' ?>" aria-label="Native AI Banking Assistant">
    <?php if ($currentUser !== null): ?>
        <div class="chatbot-head">
            <div class="chatbot-head-title">
                <strong data-i18n="chatTitle">Native AI Assistant</strong>
                <span data-i18n="chatSubtitle">Ask by voice or simple tap</span>
            </div>
            <div class="chatbot-head-controls">
                <button type="button" id="auto-read-toggle" class="mini-pill-btn">🔊 Auto-Read</button>
                <button type="button" id="close-chatbot-btn" class="mini-pill-btn" aria-label="Minimize AI Assistant" title="Minimize">✕</button>
            </div>
        </div>

        <div class="chatbot-subbar">
            <span data-i18n="voiceLangLabel">Voice &amp; Answer Language:</span>
            <select id="voice-language" aria-label="Voice Language">
                <option value="en-US">English</option>
                <option value="hi-IN">हिन्दी (Hindi)</option>
                <option value="bn-IN">বাংলা (Bengali)</option>
            </select>
        </div>

        <div id="messages" aria-live="polite">
            <div class="message-row bot">
                <div class="avatar bot">AI</div>
                <div>
                    <div class="message-bubble" id="welcome-msg" data-user-name="<?= htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?>">Namaste <?= htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?>! How can I help you? Tap a question below, use the microphone to speak, or type anything about banking.</div>
                    <button type="button" class="listen-btn" data-speak-target="welcome-msg">🔊 Listen</button>
                </div>
            </div>
        </div>

        <div class="quick-chips">
            <button type="button" class="chip-btn" data-chip-key="balance">Check Balance?</button>
            <button type="button" class="chip-btn" data-chip-key="otp">OTP Safety?</button>
            <button type="button" class="chip-btn" data-chip-key="kcc">Kisan Loan</button>
            <button type="button" class="chip-btn" data-chip-key="upi">Send Money</button>
        </div>

        <form id="chat-form" class="chat-form">
            <input type="text" id="message-input" placeholder="Type or tap mic to ask..." autocomplete="off">
            <button type="button" id="voice-button" class="icon-square-btn" title="Speak your question" aria-label="Voice input">🎤</button>
            <button type="submit" id="send-button" class="btn btn-primary" style="padding: 9px 13px;">Send</button>
        </form>
        <div id="voice-status"></div>
    <?php endif; ?>
</aside>

<button type="button" id="chat-launcher-btn" class="chat-launcher-btn<?= $currentUser !== null ? ' hidden' : '' ?>" aria-label="Open Native AI Assistant">
    <span>💬</span>
    <span data-i18n="launcherLabel">Native AI · How can I help you?</span>
</button>

<?php if ($currentUser === null): ?>
<!-- Hidden Auth Modal: Opens ONLY on button click -->
<div id="auth-modal" class="auth-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
    <div class="auth-card" id="auth-section">
        <button type="button" class="modal-close-btn" id="close-auth-modal" aria-label="Close">✕</button>
        <h3 id="auth-modal-title" style="margin:0 0 8px; font-size:18px;" data-i18n="authModalTitle">Native AI Account</h3>

        <div class="auth-tabs" role="tablist">
            <button type="button" class="auth-tab active" id="tab-login" role="tab" aria-selected="true" data-i18n="loginBtn">Log In</button>
            <button type="button" class="auth-tab" id="tab-signup" role="tab" aria-selected="false" data-i18n="signupBtn">Sign Up</button>
        </div>

        <div id="auth-alert" class="auth-alert" role="alert"></div>

        <!-- Login Form -->
        <form id="login-form">
            <div class="form-group">
                <label for="login-email" data-i18n="emailLabel">Email ID</label>
                <div class="input-wrap">
                    <input type="email" id="login-email" required placeholder="you@example.com">
                </div>
            </div>
            <div class="form-group">
                <label for="login-password" data-i18n="passwordLabel">Password</label>
                <div class="input-wrap">
                    <input type="password" id="login-password" required placeholder="Enter your password">
                    <button type="button" class="eye-toggle-btn" data-toggle-pw="login-password" aria-label="Show or hide password">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;" data-i18n="loginSubmit">Log In to Open AI Assistant</button>
        </form>

        <!-- Register / Sign Up Form -->
        <form id="signup-form" style="display:none;">
            <div class="form-group">
                <label for="signup-name" data-i18n="nameLabel">Account Name</label>
                <div class="input-wrap">
                    <input type="text" id="signup-name" required minlength="2" placeholder="Enter your full name">
                </div>
            </div>
            <div class="form-group">
                <label for="signup-email" data-i18n="emailLabel">Email ID</label>
                <div class="input-wrap">
                    <input type="email" id="signup-email" required placeholder="you@example.com">
                </div>
            </div>
            <div class="form-group">
                <label for="signup-password" data-i18n="passwordLabel">Password (min 6 characters)</label>
                <div class="input-wrap">
                    <input type="password" id="signup-password" required minlength="6" placeholder="Create a password">
                    <button type="button" class="eye-toggle-btn" data-toggle-pw="signup-password" aria-label="Show or hide password">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;" data-i18n="signupSubmit">Create Account</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div id="developer-modal" class="auth-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="developer-modal-title">
    <div class="auth-card developer-card">
        <button type="button" class="modal-close-btn" id="close-developer-modal" aria-label="Close">✕</button>
        <div class="dev-badge">BCA 3rd Year · Hackathon Project</div>
        <h3 id="developer-modal-title" style="margin:0; font-size:20px;">Sangita Sinha</h3>
        <p class="dev-headline">Aspiring Tech Professional | BCA Undergraduate | Passionate About Software Development &amp; Innovation</p>
        <div class="dev-bio">
            <p>Writing code used to be the hard part. Now it’s knowing what to ask the machine, and whether the answer holds up.</p>
            <p>Most graduates can write a loop in Java or a query in SQL. Far fewer can explain why a bug happens, or trust the code an AI tool hands them without checking it first.</p>
            <p>That gap matters more every year. Developers who only memorise syntax are being outpaced by those who can direct AI tools, debug what those tools produce, and build systems that learn.</p>
            <p>I’m a final-year BCA student building from the foundations up: C, Java, Python, data structures, DBMS, and the web stack (HTML, CSS, JavaScript, PHP). Now I’m focusing on AI-first development (AIDLC): using AI models to write code, find bugs, and decode unfamiliar codebases, while learning prompt engineering so the results are reliable rather than lucky.</p>
        </div>
        <div class="dev-skills" aria-label="Core Skills and Focus">
            <span class="dev-skill-pill">C</span>
            <span class="dev-skill-pill">Java</span>
            <span class="dev-skill-pill">Python</span>
            <span class="dev-skill-pill">Data Structures</span>
            <span class="dev-skill-pill">DBMS</span>
            <span class="dev-skill-pill">HTML / CSS / JS</span>
            <span class="dev-skill-pill">PHP</span>
            <span class="dev-skill-pill">AIDLC &amp; Prompt Engineering</span>
        </div>
    </div>
</div>

<main id="top" class="container">
    <section class="hero animate-in">
        <div class="hero-motion-bg" aria-hidden="true">
            <div class="hero-orb hero-orb-1" data-motion-float="orb1"></div>
            <div class="hero-orb hero-orb-2" data-motion-float="orb2"></div>
        </div>
        <h1 data-i18n="heroTitle">Welcome to Native AI Banking</h1>
        <p data-i18n="heroSubtitle">
            Simple, voice-friendly banking help for everyone — whether you live in a village or city. Open the top-right ☰ menu for FAQs, Guides, Calculator, or log in to use the AI Banking Assistant.
        </p>
        <div class="hero-actions">
            <a href="#faqs" class="btn btn-hero-light" data-i18n="heroFaqBtn">Read Common FAQs</a>
            <a href="#services" class="btn btn-hero-ghost" data-i18n="heroServiceBtn">Picture Banking (No Typing)</a>
            <?php if ($currentUser === null): ?>
                <button type="button" class="btn btn-hero-ghost" id="hero-login-btn" data-i18n="heroLoginBtn">Log In for AI Assistant</button>
            <?php else: ?>
                <button type="button" class="btn btn-hero-light" id="open-ai-from-hero" data-i18n="heroOpenAiBtn">☰ Open AI Chatbot</button>
            <?php endif; ?>
        </div>
    </section>

    <!-- Clear Navigation Guide ("Where to check what") -->
    <section class="clarity-grid animate-in" aria-label="Where to find help">
        <div class="clarity-card">
            <div class="clarity-icon">❓</div>
            <div>
                <h3 data-i18n="clarityFaqTitle">1. Check FAQs Dropdown</h3>
                <p data-i18n="clarityFaqDesc">Tap any question below or in the top-right ☰ menu, and click Listen in English, Hindi, or Bengali.</p>
                <a href="#faqs" class="clarity-link" data-i18n="goFaqsLink">Go to FAQs →</a>
            </div>
        </div>
        <div class="clarity-card">
            <div class="clarity-icon">🖼️</div>
            <div>
                <h3 data-i18n="clarityPicTitle">2. Tap Pictures (No Typing)</h3>
                <p data-i18n="clarityPicDesc">Tap any Picture Banking card below to read, listen in your language, or ask AI.</p>
                <a href="#services" class="clarity-link" data-i18n="goServicesLink">View Picture Services →</a>
            </div>
        </div>
        <div class="clarity-card">
            <div class="clarity-icon">🤖</div>
            <div>
                <h3 data-i18n="clarityAiTitle">3. Chat Beside Home Page</h3>
                <p data-i18n="clarityAiDesc">After logging in, Native AI opens automatically beside the page saying "How can I help you?".</p>
                <button type="button" class="clarity-link" id="clarity-ai-btn">
                    <?= $currentUser ? 'Open AI Assistant →' : 'Log In to Use AI →' ?>
                </button>
            </div>
        </div>
    </section>

    <!-- Frequently Asked Questions (Dropdown Accordions) -->
    <section id="faqs" class="section animate-in">
        <div class="section-head">
            <div>
                <h2 data-i18n="faqHeading">Frequently Asked Questions</h2>
                <p data-i18n="faqSubheading">Tap any question to open its answer. Tap Listen to hear it in English, Hindi, or Bengali.</p>
            </div>
        </div>

        <div class="faq-list">
            <?php foreach ($faqs as $index => $faq): ?>
                <?php $faqId = 'faq-ans-' . (int) ($faq['id'] ?? $index); ?>
                <details class="faq-dropdown" data-faq-index="<?= $index ?>">
                    <summary>
                        <div class="faq-summary-left">
                            <span class="faq-tag"><?= htmlspecialchars((string) $faq['keyword'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="faq-q-text"><?= cleanFaqText((string) $faq['question']) ?></span>
                        </div>
                        <span class="faq-chevron">▾</span>
                    </summary>
                    <div class="faq-body">
                        <p class="faq-a-text" id="<?= $faqId ?>"><?= cleanFaqText((string) $faq['answer']) ?></p>
                        <button type="button" class="listen-btn" data-speak-target="<?= $faqId ?>">🔊 Listen</button>
                    </div>
                </details>
            <?php endforeach; ?>

            <details class="faq-dropdown" data-faq-key="rural">
                <summary>
                    <div class="faq-summary-left">
                        <span class="faq-tag" data-i18n="ruralTag">Village &amp; Rural Help</span>
                        <span class="faq-q-text">How can I check my balance if I don't have a smartphone?</span>
                    </div>
                    <span class="faq-chevron">▾</span>
                </summary>
                <div class="faq-body">
                    <p class="faq-a-text" id="faq-extra-1">Give a missed call from your registered mobile number to the bank's official missed-call balance number, visit your nearest Bank Mitra (CSP), or update your passbook at the branch.</p>
                    <button type="button" class="listen-btn" data-speak-target="faq-extra-1">🔊 Listen</button>
                </div>
            </details>
        </div>
    </section>

    <!-- Picture Banking Services -->
    <section id="services" class="section animate-in">
        <div class="section-head">
            <div>
                <h2 data-i18n="servicesHeading">Picture Banking (No Typing Needed)</h2>
                <p data-i18n="servicesSubheading">Tap any card to see simple steps, hear it in your language, or ask the AI Assistant.</p>
            </div>
        </div>

        <div class="services-grid">
            <div class="service-card" data-service-key="balance" tabindex="0">
                <div>
                    <div class="service-emoji">💰</div>
                    <h3 data-svc-title="balance">Check Balance</h3>
                    <p id="svc-1" data-svc-desc="balance">Check account money via missed call, passbook, or Bank Mitra.</p>
                    <div class="service-detail" id="svc-detail-1" data-svc-detail="balance">1. Call your bank's missed-call number from your registered mobile. 2. Or visit a Bank Mitra (CSP) with Aadhaar/passbook.</div>
                </div>
                <div class="service-actions">
                    <button type="button" class="listen-btn" data-speak-target="svc-detail-1">🔊 Listen</button>
                    <button type="button" class="btn btn-soft ask-ai-btn" data-service-key="balance">Ask AI</button>
                </div>
            </div>

            <div class="service-card" data-service-key="kcc" tabindex="0">
                <div>
                    <div class="service-emoji">🌾</div>
                    <h3 data-svc-title="kcc">Kisan Credit Card</h3>
                    <p id="svc-2" data-svc-desc="kcc">Low-interest farm loans for seeds, fertilizer, and equipment.</p>
                    <div class="service-detail" id="svc-detail-2" data-svc-detail="kcc">Take your Aadhaar, land record (Khatauni/Parcha), and 2 photos to your nearest SBI branch or Bank Mitra to apply for KCC.</div>
                </div>
                <div class="service-actions">
                    <button type="button" class="listen-btn" data-speak-target="svc-detail-2">🔊 Listen</button>
                    <button type="button" class="btn btn-soft ask-ai-btn" data-service-key="kcc">Ask AI</button>
                </div>
            </div>

            <div class="service-card" data-service-key="atm" tabindex="0">
                <div>
                    <div class="service-emoji">💳</div>
                    <h3 data-svc-title="atm">ATM &amp; PIN Safety</h3>
                    <p id="svc-3" data-svc-desc="atm">How to withdraw cash safely and keep your 4-digit PIN secret.</p>
                    <div class="service-detail" id="svc-detail-3" data-svc-detail="atm">Never share your 4-digit ATM PIN or OTP with anyone. Always cover the keypad with your hand while typing your PIN.</div>
                </div>
                <div class="service-actions">
                    <button type="button" class="listen-btn" data-speak-target="svc-detail-3">🔊 Listen</button>
                    <button type="button" class="btn btn-soft ask-ai-btn" data-service-key="atm">Ask AI</button>
                </div>
            </div>

            <div class="service-card" data-service-key="dbt" tabindex="0">
                <div>
                    <div class="service-emoji">🏛️</div>
                    <h3 data-svc-title="dbt">Govt Subsidy (DBT)</h3>
                    <p id="svc-4" data-svc-desc="dbt">Receive PM-KISAN, pension, and gas subsidy straight to your account.</p>
                    <div class="service-detail" id="svc-detail-4" data-svc-detail="dbt">Link your Aadhaar card with your bank account at the branch or CSP so PM-KISAN, pension, and gas subsidies arrive directly.</div>
                </div>
                <div class="service-actions">
                    <button type="button" class="listen-btn" data-speak-target="svc-detail-4">🔊 Listen</button>
                    <button type="button" class="btn btn-soft ask-ai-btn" data-service-key="dbt">Ask AI</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Guides + Calculator -->
    <section class="section two-col animate-in">
        <div id="guides" class="panel-card">
            <div class="section-head">
                <div>
                    <h2 data-i18n="guidesHeading">Step-by-Step Plain Guides</h2>
                    <p data-i18n="guidesSub">Simple instructions anyone can follow.</p>
                </div>
            </div>

            <div class="guide-tabs">
                <button type="button" class="guide-tab active" data-guide="otp" data-i18n="guideTabOtp">OTP &amp; PIN</button>
                <button type="button" class="guide-tab" data-guide="passbook" data-i18n="guideTabPassbook">Passbook &amp; IFSC</button>
                <button type="button" class="guide-tab" data-guide="atm" data-i18n="guideTabAtm">ATM Safety</button>
            </div>

            <ol id="guide-steps" class="guide-steps"></ol>
            <button type="button" class="listen-btn" id="listen-guide-btn">🔊 Listen to Guide</button>
        </div>

        <div id="calculator" class="panel-card">
            <div class="section-head">
                <div>
                    <h2 data-i18n="calcHeading">No-Typing Savings Estimator</h2>
                    <p data-i18n="calcSub">Slide or tap + / − to see how small monthly savings grow.</p>
                </div>
            </div>

            <div class="calc-row">
                <label for="calc-amount"><span data-i18n="calcMonthlyLabel">Monthly Saving:</span> <strong id="calc-amount-label">₹1,000</strong></label>
                <div class="calc-controls">
                    <button type="button" class="btn btn-outline" id="amt-minus">−</button>
                    <input type="range" id="calc-amount" min="500" max="10000" step="500" value="1000">
                    <button type="button" class="btn btn-outline" id="amt-plus">+</button>
                </div>
            </div>

            <div class="calc-row">
                <label for="calc-years"><span data-i18n="calcYearsLabel">Years:</span> <strong id="calc-years-label">3 Years</strong></label>
                <div class="calc-controls">
                    <button type="button" class="btn btn-outline" id="yr-minus">−</button>
                    <input type="range" id="calc-years" min="1" max="10" step="1" value="3">
                    <button type="button" class="btn btn-outline" id="yr-plus">+</button>
                </div>
            </div>

            <div class="calc-result" id="calc-result-box">
                <span data-i18n="calcEstimateLabel">Estimated Value (at ~6.5% p.a.):</span>
                <strong id="calc-total">₹39,700</strong>
            </div>

            <button type="button" class="listen-btn" data-speak-target="calc-result-box">🔊 Listen Result</button>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div>
            <h4 data-i18n="siteName">Native AI</h4>
            <p data-i18n="footerAbout">Designed for clarity and ease of use — helping village and first-time digital banking users stay safe and informed.</p>
        </div>
        <div>
            <h4 data-i18n="footerLinksTitle">Quick Links</h4>
            <ul class="footer-links">
                <li><a href="#faqs" data-i18n="navFaqs">Frequently Asked Questions</a></li>
                <li><a href="#services" data-i18n="navServices">Picture Banking Services</a></li>
                <li><a href="#guides" data-i18n="navGuides">Step-by-Step Guides</a></li>
                <li><a href="#calculator" data-i18n="navCalc">Savings Estimator</a></li>
            </ul>
        </div>
        <div>
            <h4 data-i18n="footerSafetyTitle">Banking Safety Rule</h4>
            <p data-i18n="footerSafetyText">Real bank staff will never call or message asking for your OTP, ATM PIN, CVV, or password. Never share them with anyone.</p>
        </div>
    </div>
</footer>

<div class="fixed-brand-badge" aria-label="Native AI">Native AI</div>

<script>
(function () {
    const isLoggedIn = <?= $currentUser !== null ? 'true' : 'false' ?>;

    // 1. Icon-only Theme Toggle (Light / Dark)
    const themeBtn = document.getElementById('theme-toggle');
    const moonSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';
    const sunSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';

    function renderThemeIcon() {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        themeBtn.innerHTML = current === 'dark' ? sunSvg : moonSvg;
    }
    renderThemeIcon();

    themeBtn.addEventListener('click', function () {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try { localStorage.setItem('sbi_theme', next); } catch (e) {}
        renderThemeIcon();
    });

    // 2. Password Eye Toggle (Show / Hide)
    const eyeOpenSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    const eyeOffSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';

    document.querySelectorAll('[data-toggle-pw]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(btn.getAttribute('data-toggle-pw'));
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.innerHTML = isHidden ? eyeOffSvg : eyeOpenSvg;
        });
    });

    // 3. Auth Modal (Hidden by default; opens only when clicking buttons)
    const authModal = document.getElementById('auth-modal');
    const closeAuthModalBtn = document.getElementById('close-auth-modal');
    const tabLogin = document.getElementById('tab-login');
    const tabSignup = document.getElementById('tab-signup');
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const authAlert = document.getElementById('auth-alert');

    function showAuthTab(mode) {
        if (!tabLogin || !tabSignup || !loginForm || !signupForm) return;
        const isLogin = mode === 'login';
        tabLogin.classList.toggle('active', isLogin);
        tabSignup.classList.toggle('active', !isLogin);
        tabLogin.setAttribute('aria-selected', isLogin ? 'true' : 'false');
        tabSignup.setAttribute('aria-selected', isLogin ? 'false' : 'true');

        const incoming = isLogin ? loginForm : signupForm;
        const outgoing = isLogin ? signupForm : loginForm;
        outgoing.style.display = 'none';
        outgoing.classList.remove('auth-panel-animate');
        incoming.style.display = 'block';
        incoming.classList.remove('auth-panel-animate');
        void incoming.offsetWidth;
        incoming.classList.add('auth-panel-animate');
        if (authAlert) {
            authAlert.className = 'auth-alert';
        }
    }

    function showAuthAlert(msg, type) {
        if (!authAlert) return;
        authAlert.textContent = msg;
        authAlert.className = 'auth-alert show ' + type;
    }

    function openAuthModal(mode, alertMessage) {
        if (!authModal) return;
        setChatbotOpen(false);
        showAuthTab(mode || 'login');
        if (alertMessage) {
            showAuthAlert(alertMessage, 'error');
        } else if (authAlert) {
            authAlert.className = 'auth-alert';
        }
        authModal.classList.add('open');
        const focusId = (mode === 'signup') ? 'signup-name' : 'login-email';
        const el = document.getElementById(focusId);
        if (el) setTimeout(function () { el.focus(); }, 60);
    }

    function closeAuthModal() {
        if (authModal) authModal.classList.remove('open');
    }

    if (closeAuthModalBtn) {
        closeAuthModalBtn.addEventListener('click', closeAuthModal);
    }
    if (authModal) {
        authModal.addEventListener('click', function (e) {
            if (e.target === authModal) closeAuthModal();
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAuthModal();
            setChatbotOpen(false);
        }
    });

    if (tabLogin && tabSignup) {
        tabLogin.addEventListener('click', function () { showAuthTab('login'); });
        tabSignup.addEventListener('click', function () { showAuthTab('signup'); });
    }

    const heroLoginBtn = document.getElementById('hero-login-btn');
    const dropdownLoginBtn = document.getElementById('dropdown-login-btn');
    const dropdownSignupBtn = document.getElementById('dropdown-signup-btn');

    if (heroLoginBtn) heroLoginBtn.addEventListener('click', function () { openAuthModal('login'); });
    if (dropdownLoginBtn) dropdownLoginBtn.addEventListener('click', function () { openAuthModal('login'); });
    if (dropdownSignupBtn) dropdownSignupBtn.addEventListener('click', function () { openAuthModal('signup'); });

    if (signupForm) {
        signupForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const name = document.getElementById('signup-name').value.trim();
            const email = document.getElementById('signup-email').value.trim();
            const password = document.getElementById('signup-password').value;

            try {
                const res = await fetch('auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'register', name, email, password })
                });
                const data = await res.json();
                if (!res.ok || !data.ok) {
                    showAuthAlert(data.error || 'Registration failed.', 'error');
                    return;
                }
                showAuthTab('login');
                document.getElementById('login-email').value = email;
                document.getElementById('login-password').value = '';
                showAuthAlert(data.message || 'Account created! Please log in now.', 'success');
                document.getElementById('login-password').focus();
            } catch (err) {
                showAuthAlert('Network error. Please try again.', 'error');
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const email = document.getElementById('login-email').value.trim();
            const password = document.getElementById('login-password').value;

            try {
                const res = await fetch('auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'login', email, password })
                });
                const data = await res.json();
                if (!res.ok || !data.ok) {
                    showAuthAlert(data.error || 'Login failed.', 'error');
                    return;
                }
                window.location.reload();
            } catch (err) {
                showAuthAlert('Network error. Please try again.', 'error');
            }
        });
    }

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function () {
            await fetch('auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'logout' })
            });
            window.location.reload();
        });
    }

    // 4. Complete Multilingual Support (English / हिन्दी / বাংলা) + Multilingual Listen
    const siteLang = document.getElementById('site-language');
    const voiceLang = document.getElementById('voice-language');

    const translations = {
        'en-US': {
            siteName: 'Native AI',
            siteTagline: 'Simple & Safe Banking for Everyone',
            loginBtn: 'Log In',
            signupBtn: 'Sign Up',
            logoutBtn: 'Log Out',
            menuLabel: isLoggedIn ? 'Menu & AI' : 'Menu',
            menuNavLabel: 'Navigation',
            menuAccountLabel: 'My Account',
            menuQuickFaqLabel: 'Quick Help',
            navFaqs: 'FAQs',
            navServices: 'Picture Services',
            navGuides: 'Step Guides',
            navCalc: 'Calculator',
            menuFaqTitle: 'Frequently Asked Questions',
            heroTitle: 'Welcome to Native AI Banking',
            heroSubtitle: 'Simple, voice-friendly banking help for everyone — whether you live in a village or city. Open the top-right ☰ menu for FAQs, Guides, Calculator, or log in to use the AI Banking Assistant.',
            heroFaqBtn: 'Read Common FAQs',
            heroServiceBtn: 'Picture Banking (No Typing)',
            heroLoginBtn: 'Log In for AI Assistant',
            heroOpenAiBtn: '💬 Open AI Assistant',
            clarityFaqTitle: '1. Check FAQs Dropdown',
            clarityFaqDesc: 'Tap any question below or in the top-right ☰ menu, and click Listen in English, Hindi, or Bengali.',
            clarityPicTitle: '2. Tap Pictures (No Typing)',
            clarityPicDesc: 'Tap any Picture Banking card below to read, listen in your language, or ask AI.',
            clarityAiTitle: '3. Chat Beside Home Page',
            clarityAiDesc: 'After logging in, Native AI opens automatically beside the page saying "How can I help you?".',
            goFaqsLink: 'Go to FAQs →',
            goServicesLink: 'View Picture Services →',
            faqHeading: 'Frequently Asked Questions',
            faqSubheading: 'Tap any question to open its answer. Tap Listen to hear it in English, Hindi, or Bengali.',
            ruralTag: 'Village & Rural Help',
            servicesHeading: 'Picture Banking (No Typing Needed)',
            servicesSubheading: 'Tap any card to see simple steps, hear it in your language, or ask the AI Assistant.',
            guidesHeading: 'Step-by-Step Plain Guides',
            guidesSub: 'Simple instructions anyone can follow.',
            guideTabOtp: 'OTP & PIN',
            guideTabPassbook: 'Passbook & IFSC',
            guideTabAtm: 'ATM Safety',
            calcHeading: 'No-Typing Savings Estimator',
            calcSub: 'Slide or tap + / − to see how small monthly savings grow.',
            calcMonthlyLabel: 'Monthly Saving:',
            calcYearsLabel: 'Years:',
            calcEstimateLabel: 'Estimated Value (at ~6.5% p.a.):',
            footerAbout: 'Designed for clarity and ease of use — helping village and first-time digital banking users stay safe and informed.',
            footerLinksTitle: 'Quick Links',
            footerSafetyTitle: 'Banking Safety Rule',
            footerSafetyText: 'Real bank staff will never call or message asking for your OTP, ATM PIN, CVV, or password. Never share them with anyone.',
            chatTitle: 'Native AI Assistant',
            chatSubtitle: 'Ask by voice or simple tap',
            voiceLangLabel: 'Voice & Answer Language:',
            aiLockedTitle: 'Native AI Banking Assistant',
            aiLockedDesc: 'Please log in or sign up to chat or speak with the Native AI Banking Assistant.',
            listenBtn: '🔊 Listen',
            listenGuideBtn: '🔊 Listen to Guide',
            listenResultBtn: '🔊 Listen Result',
            askAiBtn: 'Ask AI',
            launcherLabel: 'Native AI · How can I help you?',
            welcomeTemplate: 'Namaste {name}! How can I help you? Tap a question below, use the microphone to speak, or type anything about banking.'
        },
        'hi-IN': {
            siteName: 'नेটিভ AI',
            siteTagline: 'सबके लिए सरल और सुरक्षित बैंकिंग',
            loginBtn: 'लॉग इन',
            signupBtn: 'साइन अप',
            logoutBtn: 'लॉग आउट',
            menuLabel: isLoggedIn ? 'मेन्यू और AI' : 'मेन्यू',
            menuNavLabel: 'नेविगेशन',
            menuAccountLabel: 'मेरा खाता',
            menuQuickFaqLabel: 'त्वरित मदद',
            navFaqs: 'अक्सर पूछे जाने वाले सवाल',
            navServices: 'तस्वीरों से सेवाएं',
            navGuides: 'सरल गाइड',
            navCalc: 'बचत कैलकुलेटर',
            menuFaqTitle: 'अक्सर पूछे जाने वाले सवाल',
            heroTitle: 'नेটিভ AI बैंकिंग में आपका स्वागत है',
            heroSubtitle: 'गाँव और शहर के सभी लोगों के लिए सरल और बोलने वाली बैंकिंग मदद। ऊपर दाईं ओर ☰ मेन्यू से सवाल-जवाब, गाइड, कैलकुलेटर देखें या लॉग इन करके AI सहायक से पूछें।',
            heroFaqBtn: 'सामान्य सवाल पढ़ें',
            heroServiceBtn: 'तस्वीरों से बैंकिंग (बिना टाइप किए)',
            heroLoginBtn: 'AI सहायक के लिए लॉग इन करें',
            heroOpenAiBtn: '💬 AI सहायक खोलें',
            clarityFaqTitle: '1. सवाल-जवाब ड्रॉपडाउन देखें',
            clarityFaqDesc: 'नीचे या ऊपर दाईं ओर ☰ मेन्यू में किसी भी सवाल पर टैप करें और हिन्दी, बंगाली या अंग्रेजी में सुनें।',
            clarityPicTitle: '2. तस्वीरों पर टैप करें (बिना टाइप किए)',
            clarityPicDesc: 'सरल कदम जानने, अपनी भाषा में सुनने या AI से पूछने के लिए किसी भी कार्ड पर टैप करें।',
            clarityAiTitle: '3. होम पेज के साथ AI चैट',
            clarityAiDesc: 'लॉग इन करने के बाद नेটিভ AI अपने आप होम पेज के बगल में खुल जाता है।',
            goFaqsLink: 'सवाल-जवाब पर जाएं →',
            goServicesLink: 'तस्वीर सेवाएं देखें →',
            faqHeading: 'अक्सर पूछे जाने वाले सवाल (FAQs)',
            faqSubheading: 'जवाब देखने के लिए किसी भी सवाल पर टैप करें। हिन्दी, बंगाली या अंग्रेजी में सुनने के लिए Listen दबाएं।',
            ruralTag: 'गाँव और ग्रामीण मदद',
            servicesHeading: 'तस्वीरों से बैंकिंग (बिना टाइप किए)',
            servicesSubheading: 'सरल जानकारी देखने, अपनी भाषा में सुनने या AI से पूछने के लिए किसी भी कार्ड पर टैप करें।',
            guidesHeading: 'कदम-दर-कदम सरल गाइड',
            guidesSub: 'आसान निर्देश जिन्हें कोई भी समझ सकता है।',
            guideTabOtp: 'OTP और PIN',
            guideTabPassbook: 'पासबुक और IFSC',
            guideTabAtm: 'ATM सुरक्षा',
            calcHeading: 'बिना टाइप किए बचत कैलकुलेटर',
            calcSub: 'छोटी मासिक बचत कैसे बढ़ती है, यह देखने के लिए + / − दबाएं।',
            calcMonthlyLabel: 'मासिक बचत:',
            calcYearsLabel: 'साल:',
            calcEstimateLabel: 'अनुमानित राशि (~6.5% वार्षिक ब्याज पर):',
            footerAbout: 'गाँव और पहली बार डिजिटल बैंकिंग का उपयोग करने वाले लोगों को सुरक्षित और जागरूक रखने के लिए बनाया गया।',
            footerLinksTitle: 'त्वरित लिंक',
            footerSafetyTitle: 'बैंकिंग सुरक्षा नियम',
            footerSafetyText: 'बैंक के असली कर्मचारी कभी भी फोन या मैसेज पर आपका OTP, ATM PIN, CVV या पासवर्ड नहीं पूछते। इन्हें किसी को न बताएं।',
            chatTitle: 'नेটিভ AI सहायक',
            chatSubtitle: 'बोलकर या टैप करके पूछें',
            voiceLangLabel: 'आवाज़ और जवाब की भाषा:',
            aiLockedTitle: 'नेটিভ AI बैंकिंग सहायक',
            aiLockedDesc: 'AI सहायक से बात करने के लिए कृपया लॉग इन या साइन अप करें।',
            listenBtn: '🔊 सुनें',
            listenGuideBtn: '🔊 गाइड सुनें',
            listenResultBtn: '🔊 परिणाम सुनें',
            askAiBtn: 'AI से पूछें',
            launcherLabel: 'नेটিভ AI · मैं आपकी क्या मदद कर सकता हूँ?',
            welcomeTemplate: 'नमस्ते {name}! मैं आपकी क्या मदद कर सकता हूँ? नीचे दिए गए सवाल पर टैप करें, माइक से बोलें, या बैंकिंग से जुड़ा कोई भी सवाल लिखें।'
        },
        'bn-IN': {
            siteName: 'নেটিভ AI',
            siteTagline: 'সবার জন্য সহজ ও নিরাপদ ব্যাংকিং',
            loginBtn: 'লগ ইন',
            signupBtn: 'সাইন আপ',
            logoutBtn: 'লগ আউট',
            menuLabel: isLoggedIn ? 'মেনু ও AI' : 'মেনু',
            menuNavLabel: 'নেভিগেশন',
            menuAccountLabel: 'আমার অ্যাকাউন্ট',
            menuQuickFaqLabel: 'দ্রুত সাহায্য',
            navFaqs: 'সাধারণ প্রশ্ন',
            navServices: 'ছবির মাধ্যমে সেবা',
            navGuides: 'সহজ গাইড',
            navCalc: 'সঞ্চয় ক্যালকুলেটর',
            menuFaqTitle: 'সাধারণ জিজ্ঞাসিত প্রশ্ন',
            heroTitle: 'নেটিভ AI ব্যাংকিং-এ স্বাগতম',
            heroSubtitle: 'গ্রাম ও শহরের সবার জন্য সহজ ও ভয়েস-বান্ধব ব্যাংকিং সাহায্য। উপরের ডানদিকের ☰ মেনু থেকে প্রশ্ন-উত্তর, গাইড, ক্যালকুলেটর দেখুন অথবা AI সহকারীর জন্য লগ ইন করুন।',
            heroFaqBtn: 'সাধারণ প্রশ্ন পড়ুন',
            heroServiceBtn: 'ছবি দেখে ব্যাংকিং (টাইপ ছাড়াই)',
            heroLoginBtn: 'AI সহকারীর জন্য লগ ইন করুন',
            heroOpenAiBtn: '💬 AI সহকারী খুলুন',
            clarityFaqTitle: '১. প্রশ্ন-উত্তর ড্রপডাউন দেখুন',
            clarityFaqDesc: 'নিচে বা উপরের ডানদিকের ☰ মেনুতে যেকোনো প্রশ্নে ট্যাপ করুন এবং বাংলা, হিন্দি বা ইংরেজিতে শুনুন।',
            clarityPicTitle: '২. ছবিতে ট্যাপ করুন (টাইপ ছাড়াই)',
            clarityPicDesc: 'সহজ ধাপ জানতে, নিজের ভাষায় শুনতে বা AI-কে জিজ্ঞাসা করতে যেকোনো কার্ডে ট্যাপ করুন।',
            clarityAiTitle: '৩. হোম পেজের পাশেই AI চ্যাট',
            clarityAiDesc: 'লগ ইন করার পর নেটিভ AI নিজে থেকেই হোম পেজের পাশে খুলে যাবে।',
            goFaqsLink: 'প্রশ্ন-উত্তরে যান →',
            goServicesLink: 'ছবির সেবাগুলো দেখুন →',
            faqHeading: 'সাধারণ জিজ্ঞাসিত প্রশ্ন (FAQs)',
            faqSubheading: 'উত্তর দেখতে যেকোনো প্রশ্নে ট্যাপ করুন। বাংলা, হিন্দি বা ইংরেজিতে শুনতে Listen চাপুন।',
            ruralTag: 'গ্রাম ও গ্রামীণ সাহায্য',
            servicesHeading: 'ছবি দেখে ব্যাংকিং (টাইপ ছাড়াই)',
            servicesSubheading: 'সহজ ধাপ দেখতে, নিজের ভাষায় শুনতে বা AI-কে জিজ্ঞাসা করতে যেকোনো কার্ডে ট্যাপ করুন।',
            guidesHeading: 'ধাপে ধাপে সহজ গাইড',
            guidesSub: 'সহজ নির্দেশনা যা সবাই বুঝতে পারবেন।',
            guideTabOtp: 'OTP ও PIN',
            guideTabPassbook: 'পাসবুক ও IFSC',
            guideTabAtm: 'ATM নিরাপত্তা',
            calcHeading: 'টাইপ ছাড়াই সঞ্চয় ক্যালকুলেটর',
            calcSub: 'মাসিক ছোট সঞ্চয় কীভাবে বাড়ে তা দেখতে + / − চাপুন।',
            calcMonthlyLabel: 'মাসিক সঞ্চয়:',
            calcYearsLabel: 'বছর:',
            calcEstimateLabel: 'আনুমানিক মূল্য (~৬.৫% বার্ষিক সুদে):',
            footerAbout: 'গ্রাম ও প্রথমবার ডিজিটাল ব্যাংকিং ব্যবহারকারীদের নিরাপদ ও সচেতন রাখতে তৈরি।',
            footerLinksTitle: 'দ্রুত লিঙ্ক',
            footerSafetyTitle: 'ব্যাংকিং নিরাপত্তার নিয়ম',
            footerSafetyText: 'আসল ব্যাংক কর্মীরা কখনোই ফোন বা মেসেজে আপনার OTP, ATM PIN, CVV বা পাসওয়ার্ড চান না। এগুলো কাউকে বলবেন না।',
            chatTitle: 'নেটিভ AI সহকারী',
            chatSubtitle: 'কথা বলে বা ট্যাপ করে জিজ্ঞাসা করুন',
            voiceLangLabel: 'ভয়েস ও উত্তরের ভাষা:',
            aiLockedTitle: 'নেটিভ AI ব্যাংকিং সহকারী',
            aiLockedDesc: 'AI সহকারীর সাথে কথা বলতে অনুগ্রহ করে লগ ইন বা সাইন আপ করুন।',
            listenBtn: '🔊 শুনুন',
            listenGuideBtn: '🔊 গাইড শুনুন',
            listenResultBtn: '🔊 ফলাফল শুনুন',
            askAiBtn: 'AI-কে জিজ্ঞাসা করুন',
            launcherLabel: 'নেটিভ AI · কীভাবে সাহায্য করতে পারি?',
            welcomeTemplate: 'নমস্কার {name}! আমি আপনাকে কীভাবে সাহায্য করতে পারি? নিচের যেকোনো প্রশ্নে ট্যাপ করুন, মাইকে কথা বলুন, অথবা ব্যাংকিং সম্পর্কে কিছু লিখুন।'
        }
    };

    const faqTranslations = {
        0: {
            'hi-IN': {
                q: 'मैं अपने खाते का बैलेंस कैसे देख सकता हूँ?',
                a: 'आप अपने पंजीकृत मोबाइल नंबर से टोल-फ्री मिस्ड कॉल सेवा, YONO ऐप, या नजदीकी बैंक मित्र (CSP) पर पासबुक या आधार से बैलेंस देख सकते हैं।'
            },
            'bn-IN': {
                q: 'আমি কীভাবে আমার অ্যাকাউন্টের ব্যালেন্স চেক করতে পারি?',
                a: 'আপনি আপনার নিবন্ধিত মোবাইল নম্বর থেকে টোল-ফ্রি মিসড কল দিয়ে, YONO অ্যাপের মাধ্যমে, অথবা নিকটস্থ ব্যাংক মিত্র (CSP) কেন্দ্রে পাসবুক বা আধার দিয়ে ব্যালেন্স জানতে পারেন।'
            }
        },
        1: {
            'hi-IN': {
                q: 'SBI में नया खाता खोलने के लिए किन कागजातों की जरूरत होती है?',
                a: 'नया बचत खाता खोलने के लिए आपको अपना आधार कार्ड, पैन कार्ड (या फॉर्म 60) और दो पासपोर्ट साइज फोटो चाहिए।'
            },
            'bn-IN': {
                q: 'SBI-তে নতুন অ্যাকাউন্ট খুলতে কী কী নথি লাগে?',
                a: 'নতুন সেভিংস অ্যাকাউন্ট খুলতে আপনার আধার কার্ড, প্যান কার্ড (বা ফর্ম ৬০) এবং দুই কপি পাসপোর্ট সাইজের ছবি প্রয়োজন।'
            }
        },
        2: {
            'hi-IN': {
                q: 'अगर मेरा ATM कार्ड खो जाए तो क्या करूँ?',
                a: 'तुरंत SBI कस्टमर केयर (1800 11 2211 या 1800 425 3800) पर कॉल करके या YONO ऐप से अपना कार्ड ब्लॉक कराएं, ताकि कोई आपके पैसे न निकाल सके।'
            },
            'bn-IN': {
                q: 'আমার ATM কার্ড হারিয়ে গেলে কী করব?',
                a: 'সাথে সাথে SBI কাস্টমার কেয়ার (1800 11 2211 বা 1800 425 3800) নম্বরে ফোন করে অথবা YONO অ্যাপের মাধ্যমে কার্ডটি ব্লক করুন, যাতে কেউ টাকা তুলতে না পারে।'
            }
        },
        rural: {
            'hi-IN': {
                q: 'अगर मेरे पास स्मार्टफोन नहीं है तो मैं बैलेंस कैसे देखूँ?',
                a: 'अपने पंजीकृत मोबाइल नंबर से बैंक के आधिकारिक मिस्ड-कॉल नंबर पर मिस्ड कॉल दें, नजदीकी बैंक मित्र (CSP) पर जाएं, या शाखा में अपनी पासबुक अपडेट कराएं।'
            },
            'bn-IN': {
                q: 'আমার কাছে স্মার্টফোন না থাকলে কীভাবে ব্যালেন্স চেক করব?',
                a: 'আপনার নিবন্ধিত মোবাইল নম্বর থেকে ব্যাংকের অফিসিয়াল মিসড-কল নম্বরে মিসড কল দিন, নিকটস্থ ব্যাংক মিত্র (CSP) কেন্দ্রে যান, অথবা শাখায় গিয়ে পাসবুক আপডেট করুন।'
            }
        }
    };

    const servicesData = {
        balance: {
            'en-US': {
                title: 'Check Balance',
                desc: 'Check account money via missed call, passbook, or Bank Mitra.',
                detail: '1. Call your bank\'s missed-call number from your registered mobile. 2. Or visit a Bank Mitra (CSP) with Aadhaar/passbook.',
                prompt: 'How can I check my SBI account balance easily?'
            },
            'hi-IN': {
                title: 'बैलेंस चेक करें',
                desc: 'मिस्ड कॉल, पासबुक या बैंक मित्र से खाते के पैसे जानें।',
                detail: '1. अपने पंजीकृत मोबाइल से बैंक के मिस्ड-कॉल नंबर पर कॉल करें। 2. या आधार और पासबुक लेकर नजदीकी बैंक मित्र (CSP) पर जाएं।',
                prompt: 'कृपया सरल हिन्दी में बताएं कि मैं अपने SBI खाते का बैलेंस कैसे चेक कर सकता हूँ?'
            },
            'bn-IN': {
                title: 'ব্যালেন্স চেক করুন',
                desc: 'মিসড কল, পাসবুক বা ব্যাংক মিত্রের মাধ্যমে অ্যাকাউন্টের টাকা জানুন।',
                detail: '১. আপনার নিবন্ধিত মোবাইল থেকে ব্যাংকের মিসড-কল নম্বরে কল করুন। ২. অথবা আধার ও পাসবুক নিয়ে নিকটস্থ ব্যাংক মিত্র (CSP) কেন্দ্রে যান।',
                prompt: 'অনুগ্রহ করে সহজ বাংলায় বলুন কীভাবে আমি আমার SBI অ্যাকাউন্টের ব্যালেন্স চেক করতে পারি?'
            }
        },
        kcc: {
            'en-US': {
                title: 'Kisan Credit Card',
                desc: 'Low-interest farm loans for seeds, fertilizer, and equipment.',
                detail: 'Take your Aadhaar, land record (Khatauni/Parcha), and 2 photos to your nearest SBI branch or Bank Mitra to apply for KCC.',
                prompt: 'How do farmers apply for Kisan Credit Card (KCC) loan?'
            },
            'hi-IN': {
                title: 'किसान क्रेडिट कार्ड (KCC)',
                desc: 'बीज, खाद और खेती के सामान के लिए कम ब्याज पर कृषि लोन।',
                detail: 'KCC लोन के लिए अपना आधार कार्ड, जमीन के कागज (खतौनी/पर्चा) और 2 फोटो लेकर नजदीकी SBI शाखा या बैंक मित्र के पास जाएं।',
                prompt: 'कृपया सरल हिन्दी में बताएं कि किसान भाई किसान क्रेडिट कार्ड (KCC) लोन के लिए कैसे आवेदन करें?'
            },
            'bn-IN': {
                title: 'কিষান ক্রেডিট কার্ড (KCC)',
                desc: 'বীজ, সার ও কৃষি যন্ত্রপাতির জন্য কম সুদে কৃষি ঋণ।',
                detail: 'KCC ঋণের জন্য আপনার আধার কার্ড, জমির নথি (খতিয়ান/পর্চা) এবং ২ কপি ছবি নিয়ে নিকটস্থ SBI শাখা বা ব্যাংক মিত্রের কাছে যান।',
                prompt: 'অনুগ্রহ করে সহজ বাংলায় বলুন কৃষকরা কীভাবে কিষান ক্রেডিট কার্ড (KCC) ঋণের জন্য আবেদন করবেন?'
            }
        },
        atm: {
            'en-US': {
                title: 'ATM & PIN Safety',
                desc: 'How to withdraw cash safely and keep your 4-digit PIN secret.',
                detail: 'Never share your 4-digit ATM PIN or OTP with anyone. Always cover the keypad with your hand while typing your PIN.',
                prompt: 'How do I use an ATM safely and protect my PIN?'
            },
            'hi-IN': {
                title: 'ATM और PIN सुरक्षा',
                desc: 'सुरक्षित तरीके से पैसे निकालें और अपना 4-अंकों का PIN गुप्त रखें।',
                detail: 'अपना 4-अंकों का ATM PIN या OTP कभी किसी को न बताएं। मशीन में PIN डालते समय हमेशा हाथ से कीपैड को ढक लें।',
                prompt: 'कृपया सरल हिन्दी में बताएं कि ATM से सुरक्षित पैसे कैसे निकालें और PIN कैसे बचाएं?'
            },
            'bn-IN': {
                title: 'ATM ও PIN নিরাপত্তা',
                desc: 'নিরাপদে টাকা তুলুন এবং আপনার ৪-সংখ্যার PIN গোপন রাখুন।',
                detail: 'আপনার ৪-সংখ্যার ATM PIN বা OTP কখনো কাউকে বলবেন না। মেশিনে PIN দেওয়ার সময় সবসময় হাত দিয়ে কীপ্যাড ঢেকে রাখুন।',
                prompt: 'অনুগ্রহ করে সহজ বাংলায় বলুন কীভাবে নিরাপদে ATM ব্যবহার করব এবং PIN সুরক্ষিত রাখব?'
            }
        },
        dbt: {
            'en-US': {
                title: 'Govt Subsidy (DBT)',
                desc: 'Receive PM-KISAN, pension, and gas subsidy straight to your account.',
                detail: 'Link your Aadhaar card with your bank account at the branch or CSP so PM-KISAN, pension, and gas subsidies arrive directly.',
                prompt: 'How do I link Aadhaar for government subsidy DBT?'
            },
            'hi-IN': {
                title: 'सरकारी सब्सिडी (DBT)',
                desc: 'PM-किसान, पेंशन और गैस सब्सिडी सीधे अपने खाते में पाएं।',
                detail: 'शाखा या बैंक मित्र (CSP) पर जाकर अपने खाते को आधार से लिंक कराएं ताकि PM-किसान, पेंशन और गैस सब्सिडी सीधे खाते में आए।',
                prompt: 'कृपया सरल हिन्दी में बताएं कि सरकारी सब्सिडी (DBT) के लिए आधार कैसे लिंक करें?'
            },
            'bn-IN': {
                title: 'সরকারি ভর্তুকি (DBT)',
                desc: 'PM-KISAN, পেনশন ও গ্যাস ভর্তুকির টাকা সরাসরি অ্যাকাউন্টে পান।',
                detail: 'শাখা বা ব্যাংক মিত্র (CSP) কেন্দ্রে গিয়ে আপনার অ্যাকাউন্টের সাথে আধার লিঙ্ক করুন যাতে PM-KISAN, পেনশন ও ভর্তুকি সরাসরি আসে।',
                prompt: 'অনুগ্রহ করে সহজ বাংলায় বলুন সরকারি ভর্তুকির (DBT) জন্য কীভাবে আধার লিঙ্ক করব?'
            }
        }
    };

    const guidesData = {
        otp: {
            'en-US': [
                'OTP means One-Time Password — a temporary secret code sent to your phone.',
                'Your ATM PIN is your secret 4-digit key for cash withdrawal.',
                'Bank staff NEVER ask for OTP or PIN on phone calls. Hang up immediately if someone asks.'
            ],
            'hi-IN': [
                'OTP का मतलब वन-टाइम पासवर्ड है — यह आपके फोन पर आने वाला एक गुप्त कोड है।',
                'ATM PIN पैसे निकालने के लिए आपका 4-अंकों का गुप्त नंबर है।',
                'बैंक कर्मचारी फोन पर कभी भी OTP या PIN नहीं पूछते। कोई पूछे तो तुरंत फोन काट दें।'
            ],
            'bn-IN': [
                'OTP মানে ওয়ান-টাইম পাসওয়ার্ড — এটি আপনার ফোনে আসা একটি অস্থায়ী গোপন কোড।',
                'ATM PIN হলো টাকা তোলার জন্য আপনার ৪-সংখ্যার গোপন নম্বর।',
                'ব্যাংক কর্মীরা কখনোই ফোনে OTP বা PIN চান না। কেউ চাইলে সাথে সাথে ফোন কেটে দিন।'
            ]
        },
        passbook: {
            'en-US': [
                'Your Passbook records every rupee coming in and going out of your account.',
                'IFSC is an 11-character branch code printed on your passbook or cheque book.',
                'You need your Account Number and IFSC to receive money or government subsidies.'
            ],
            'hi-IN': [
                'आपकी पासबुक में खाते में आने और निकलने वाले हर रुपये का हिसाब लिखा होता है।',
                'IFSC एक 11-अक्षरों का शाखा कोड है जो आपकी पासबुक या चेकबुक पर छपा होता है।',
                'पैसे या सरकारी सब्सिडी मंगाने के लिए आपका खाता नंबर और IFSC कोड जरूरी होता है।'
            ],
            'bn-IN': [
                'আপনার পাসবুকে অ্যাকাউন্টে আসা ও খরচ হওয়া প্রতিটি টাকার হিসাব লেখা থাকে।',
                'IFSC হলো ১১ অক্ষরের একটি শাখা কোড যা আপনার পাসবুক বা চেকবুকে ছাপা থাকে।',
                'টাকা বা সরকারি ভর্তুকি পেতে আপনার অ্যাকাউন্ট নম্বর ও IFSC কোড প্রয়োজন।'
            ]
        },
        atm: {
            'en-US': [
                'Check that nobody is looking over your shoulder before typing your PIN.',
                'Cover the keypad with your other hand while entering your 4-digit PIN.',
                'Always take your ATM card, cash, and receipt before leaving the machine.'
            ],
            'hi-IN': [
                'PIN डालने से पहले देख लें कि कोई आपके पीछे से झांक तो नहीं रहा है।',
                'अपना 4-अंकों का PIN डालते समय दूसरे हाथ से कीपैड को ढक लें।',
                'मशीन छोड़ने से पहले हमेशा अपना ATM कार्ड, नकद पैसे और रसीद जरूर ले लें।'
            ],
            'bn-IN': [
                'PIN দেওয়ার আগে দেখে নিন কেউ আপনার পেছন থেকে উঁকি দিচ্ছে কি না।',
                '৪-সংখ্যার PIN দেওয়ার সময় অন্য হাত দিয়ে কীপ্যাড ঢেকে রাখুন।',
                'মেশিন ছাড়ার আগে সবসময় আপনার ATM কার্ড, টাকা এবং রসিদ সাথে নিন।'
            ]
        }
    };

    const chipPrompts = {
        balance: {
            'en-US': { label: 'Check Balance?', prompt: 'How do I check my bank balance without going to the branch?' },
            'hi-IN': { label: 'बैलेंस कैसे देखें?', prompt: 'बिना बैंक शाखा जाए मैं अपना बैंक बैलेंस कैसे चेक करूँ? सरल हिन्दी में बताएं।' },
            'bn-IN': { label: 'ব্যালেন্স চেক?', prompt: 'ব্যাংকের শাখায় না গিয়ে কীভাবে ব্যালেন্স চেক করব? সহজ বাংলায় বলুন।' }
        },
        otp: {
            'en-US': { label: 'OTP Safety?', prompt: 'What is OTP and why should I never share it?' },
            'hi-IN': { label: 'OTP सुरक्षा?', prompt: 'OTP क्या है और इसे किसी के साथ क्यों साझा नहीं करना चाहिए? सरल हिन्दी में बताएं।' },
            'bn-IN': { label: 'OTP নিরাপত্তা?', prompt: 'OTP কী এবং কেন এটি কারো সাথে শেয়ার করা উচিত নয়? সহজ বাংলায় বলুন।' }
        },
        kcc: {
            'en-US': { label: 'Kisan Loan', prompt: 'How can farmers apply for Kisan Credit Card (KCC) loan?' },
            'hi-IN': { label: 'किसान लोन', prompt: 'किसान क्रेडिट कार्ड (KCC) लोन के लिए आवेदन कैसे करें? सरल हिन्दी में बताएं।' },
            'bn-IN': { label: 'কিষান লোন', prompt: 'কৃষকরা কীভাবে কিষান ক্রেডিট কার্ড (KCC) ঋণের জন্য আবেদন করবেন? সহজ বাংলায় বলুন।' }
        },
        upi: {
            'en-US': { label: 'Send Money', prompt: 'How do I send money safely using UPI or YONO?' },
            'hi-IN': { label: 'पैसे भेजें', prompt: 'UPI या YONO से सुरक्षित तरीके से पैसे कैसे भेजें? सरल हिन्दी में बताएं।' },
            'bn-IN': { label: 'টাকা পাঠান', prompt: 'UPI বা YONO ব্যবহার করে নিরাপদে কীভাবে টাকা পাঠাব? সহজ বাংলায় বলুন।' }
        }
    };

    let currentGuideKey = 'otp';
    const guideStepsEl = document.getElementById('guide-steps');

    function getActiveLang() {
        return (siteLang && siteLang.value) || 'en-US';
    }

    function renderGuide(key) {
        currentGuideKey = key || currentGuideKey;
        const lang = getActiveLang();
        const guideSet = guidesData[currentGuideKey] || guidesData.otp;
        const list = guideSet[lang] || guideSet['en-US'];
        guideStepsEl.innerHTML = list.map(function (s) { return '<li>' + s + '</li>'; }).join('');
    }

    // Store original English FAQ texts from PHP so switching back to English works cleanly
    const originalFaqs = {};
    document.querySelectorAll('[data-faq-index], [data-faq-key]').forEach(function (el) {
        const key = el.hasAttribute('data-faq-index') ? el.getAttribute('data-faq-index') : el.getAttribute('data-faq-key');
        if (!originalFaqs[key]) {
            const qEl = el.querySelector('.faq-q-text');
            const aEl = el.querySelector('.faq-a-text');
            if (qEl && aEl) {
                originalFaqs[key] = { q: qEl.textContent, a: aEl.textContent };
            }
        }
    });

    function applyLanguage(lang) {
        const dict = translations[lang] || translations['en-US'];
        document.querySelectorAll('[data-i18n]').forEach(function (el) {
            const key = el.getAttribute('data-i18n');
            if (dict[key]) el.textContent = dict[key];
        });

        // Update Listen & Ask AI button labels
        document.querySelectorAll('.listen-btn').forEach(function (btn) {
            if (btn.id === 'listen-guide-btn') {
                btn.textContent = dict.listenGuideBtn;
            } else if (btn.getAttribute('data-speak-target') === 'calc-result-box') {
                btn.textContent = dict.listenResultBtn;
            } else {
                btn.textContent = dict.listenBtn;
            }
        });

        document.querySelectorAll('.ask-ai-btn').forEach(function (btn) {
            btn.textContent = dict.askAiBtn;
        });

        // Update FAQs (main section + hamburger menu)
        document.querySelectorAll('[data-faq-index], [data-faq-key]').forEach(function (el) {
            const key = el.hasAttribute('data-faq-index') ? el.getAttribute('data-faq-index') : el.getAttribute('data-faq-key');
            const qEl = el.querySelector('.faq-q-text');
            const aEl = el.querySelector('.faq-a-text');
            if (!qEl || !aEl) return;
            const tr = faqTranslations[key] && faqTranslations[key][lang];
            if (tr) {
                qEl.textContent = tr.q;
                aEl.textContent = tr.a;
            } else if (originalFaqs[key]) {
                qEl.textContent = originalFaqs[key].q;
                aEl.textContent = originalFaqs[key].a;
            }
        });

        // Update Picture Banking Cards
        Object.keys(servicesData).forEach(function (svcKey) {
            const item = servicesData[svcKey][lang] || servicesData[svcKey]['en-US'];
            const tEl = document.querySelector('[data-svc-title="' + svcKey + '"]');
            const dEl = document.querySelector('[data-svc-desc="' + svcKey + '"]');
            const detEl = document.querySelector('[data-svc-detail="' + svcKey + '"]');
            if (tEl) tEl.textContent = item.title;
            if (dEl) dEl.textContent = item.desc;
            if (detEl) detEl.textContent = item.detail;
        });

        // Update Quick Chips
        document.querySelectorAll('[data-chip-key]').forEach(function (chip) {
            const key = chip.getAttribute('data-chip-key');
            const cfg = chipPrompts[key] && (chipPrompts[key][lang] || chipPrompts[key]['en-US']);
            if (cfg) {
                chip.textContent = cfg.label;
                chip.setAttribute('data-prompt', cfg.prompt);
            }
        });

        // Update Welcome Message
        const welcomeEl = document.getElementById('welcome-msg');
        if (welcomeEl) {
            const uname = welcomeEl.getAttribute('data-user-name') || 'Friend';
            welcomeEl.textContent = dict.welcomeTemplate.replace('{name}', uname);
        }

        if (voiceLang) voiceLang.value = lang;
        renderGuide(currentGuideKey);
        updateCalc();
    }

    siteLang.addEventListener('change', function () {
        applyLanguage(siteLang.value);
    });
    if (voiceLang) {
        voiceLang.addEventListener('change', function () {
            siteLang.value = voiceLang.value;
            applyLanguage(voiceLang.value);
        });
    }

    // Multilingual Voice-Over: Server-side TTS (tts.php) for Hindi/Bengali & fallback, plus SpeechSynthesis
    let availableVoices = [];
    let currentTtsAudio = null;
    let ttsPlaybackToken = 0;

    function loadVoices() {
        if ('speechSynthesis' in window) {
            availableVoices = window.speechSynthesis.getVoices() || [];
        }
    }
    loadVoices();
    if ('speechSynthesis' in window && typeof window.speechSynthesis.onvoiceschanged !== 'undefined') {
        window.speechSynthesis.onvoiceschanged = loadVoices;
    }

    function pickVoiceForLang(langCode) {
        if (!availableVoices.length) loadVoices();
        const short = langCode.split('-')[0].toLowerCase();
        const exact = availableVoices.find(function (v) { return v.lang.toLowerCase() === langCode.toLowerCase(); });
        if (exact) return exact;
        return availableVoices.find(function (v) { return v.lang.toLowerCase().indexOf(short) === 0; }) || null;
    }

    function stopAllSpeech() {
        ttsPlaybackToken += 1;
        if (currentTtsAudio) {
            try {
                currentTtsAudio.pause();
                currentTtsAudio.src = '';
            } catch (e) {}
            currentTtsAudio = null;
        }
        if ('speechSynthesis' in window) {
            try {
                window.speechSynthesis.cancel();
            } catch (e) {}
        }
    }

    function splitTtsChunks(text, maxLen) {
        const clean = String(text || '').replace(/\s+/g, ' ').trim();
        if (!clean) return [];
        if (clean.length <= maxLen) return [clean];

        const sentences = clean.split(/(?<=[।.?!,;:])\s+/);
        const chunks = [];
        let current = '';

        sentences.forEach(function (part) {
            if (!part) return;
            if ((current + ' ' + part).trim().length <= maxLen) {
                current = (current + ' ' + part).trim();
            } else {
                if (current) chunks.push(current);
                if (part.length <= maxLen) {
                    current = part;
                } else {
                    const words = part.split(/\s+/);
                    let wBuf = '';
                    words.forEach(function (w) {
                        if ((wBuf + ' ' + w).trim().length <= maxLen) {
                            wBuf = (wBuf + ' ' + w).trim();
                        } else {
                            if (wBuf) chunks.push(wBuf);
                            wBuf = w.length > maxLen ? w.slice(0, maxLen) : w;
                        }
                    });
                    current = wBuf;
                }
            }
        });
        if (current) chunks.push(current);
        return chunks;
    }

    function speakWithBrowserSynthesis(text, langCode) {
        if (!('speechSynthesis' in window) || !text) return;
        const utter = new SpeechSynthesisUtterance(text);
        utter.lang = langCode;
        const matchedVoice = pickVoiceForLang(langCode);
        if (matchedVoice) utter.voice = matchedVoice;
        window.speechSynthesis.speak(utter);
    }

    function playTtsChunks(chunks, index, langCode, token, fullText) {
        if (token !== ttsPlaybackToken || index >= chunks.length) {
            currentTtsAudio = null;
            return;
        }
        const url = 'tts.php?lang=' + encodeURIComponent(langCode) + '&q=' + encodeURIComponent(chunks[index]);
        const audio = new Audio(url);
        currentTtsAudio = audio;
        audio.onended = function () {
            if (token === ttsPlaybackToken) {
                playTtsChunks(chunks, index + 1, langCode, token, fullText);
            }
        };
        audio.onerror = function () {
            if (token === ttsPlaybackToken) {
                currentTtsAudio = null;
                speakWithBrowserSynthesis(fullText, langCode);
            }
        };
        const playPromise = audio.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(function () {
                if (token === ttsPlaybackToken) {
                    currentTtsAudio = null;
                    speakWithBrowserSynthesis(fullText, langCode);
                }
            });
        }
    }

    function speakText(text, explicitLang) {
        const clean = String(text || '').replace(/\s+/g, ' ').trim();
        if (!clean) return;
        stopAllSpeech();

        const langCode = explicitLang || getActiveLang() || (voiceLang && voiceLang.value) || 'en-US';
        const short = langCode.split('-')[0].toLowerCase();
        const matchedVoice = pickVoiceForLang(langCode);

        // Always use tts.php for Hindi/Bengali (Windows/browsers often lack hi/bn TTS voices),
        // or when English browser voice is unavailable.
        if (short === 'bn' || short === 'hi' || !matchedVoice) {
            const chunks = splitTtsChunks(clean, 160);
            if (chunks.length) {
                playTtsChunks(chunks, 0, langCode, ttsPlaybackToken, clean);
                return;
            }
        }

        speakWithBrowserSynthesis(clean, langCode);
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-speak-target]');
        if (!btn) return;
        e.stopPropagation();
        const target = document.getElementById(btn.getAttribute('data-speak-target'));
        if (target) speakText(target.textContent);
    });

    // 5. Picture Banking Cards: Interactive Click, Listen & Ask AI
    document.querySelectorAll('.service-card').forEach(function (card) {
        card.addEventListener('click', function (e) {
            if (e.target.closest('button')) return;
            card.classList.toggle('active-card');
        });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.classList.toggle('active-card');
            }
        });
    });

    // 6. Guides & Calculator (Smooth, slow tab transition)
    let guideTabTimer = null;
    document.querySelectorAll('.guide-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            if (tab.classList.contains('active')) return;
            document.querySelectorAll('.guide-tab').forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            const key = tab.getAttribute('data-guide');
            if (guideTabTimer) clearTimeout(guideTabTimer);
            guideStepsEl.classList.add('is-fading');
            guideTabTimer = setTimeout(function () {
                renderGuide(key);
                requestAnimationFrame(function () {
                    guideStepsEl.classList.remove('is-fading');
                });
            }, 220);
        });
    });

    document.getElementById('listen-guide-btn').addEventListener('click', function () {
        speakText(guideStepsEl.textContent);
    });

    const amtRange = document.getElementById('calc-amount');
    const yrRange = document.getElementById('calc-years');
    function updateCalc() {
        const monthly = Number(amtRange.value);
        const years = Number(yrRange.value);
        const months = years * 12;
        const r = 0.065 / 12;
        const total = Math.round(monthly * ((Math.pow(1 + r, months) - 1) / r) * (1 + r));
        const lang = getActiveLang();
        const yearWord = lang === 'hi-IN' ? 'साल' : (lang === 'bn-IN' ? 'বছর' : (years === 1 ? 'Year' : 'Years'));
        document.getElementById('calc-amount-label').textContent = '₹' + monthly.toLocaleString('en-IN');
        document.getElementById('calc-years-label').textContent = years + ' ' + yearWord;
        document.getElementById('calc-total').textContent = '₹' + total.toLocaleString('en-IN');
    }
    amtRange.addEventListener('input', updateCalc);
    yrRange.addEventListener('input', updateCalc);
    document.getElementById('amt-minus').addEventListener('click', function () { amtRange.stepDown(); updateCalc(); });
    document.getElementById('amt-plus').addEventListener('click', function () { amtRange.stepUp(); updateCalc(); });
    document.getElementById('yr-minus').addEventListener('click', function () { yrRange.stepDown(); updateCalc(); });
    document.getElementById('yr-plus').addEventListener('click', function () { yrRange.stepUp(); updateCalc(); });

    // Initialize default language text
    applyLanguage(getActiveLang());

    // 7. Top-Right Hamburger Menu & Front-Facing Side-by-Side AI Chatbot Panel
    const hamburgerBtn = document.getElementById('hamburger-ai-toggle');
    const chatbotDropdown = document.getElementById('chatbot-dropdown');
    const aiChatPanel = document.getElementById('ai-chat-panel');
    const chatLauncherBtn = document.getElementById('chat-launcher-btn');
    const closeChatbotBtn = document.getElementById('close-chatbot-btn');
    const openHeroAiBtn = document.getElementById('open-ai-from-hero');
    const clarityAiBtn = document.getElementById('clarity-ai-btn');
    const menuOpenAiItem = document.getElementById('menu-open-ai-item');

    function setChatbotOpen(open) {
        if (!chatbotDropdown || !hamburgerBtn) return;
        chatbotDropdown.classList.toggle('open', open);
        hamburgerBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function setAiPanelOpen(open) {
        if (!isLoggedIn) {
            openAuthModal('login', 'Please log in first to chat with the Native AI Assistant.');
            return;
        }
        if (aiChatPanel) {
            aiChatPanel.classList.toggle('open', open);
        }
        document.body.classList.toggle('chat-docked-open', open);
        if (chatLauncherBtn) {
            chatLauncherBtn.classList.toggle('hidden', open);
        }
    }

    // Automatically open the chatbot beside the home page whenever logged in
    if (isLoggedIn && aiChatPanel) {
        setAiPanelOpen(true);
    }

    if (hamburgerBtn && chatbotDropdown) {
        hamburgerBtn.addEventListener('click', function () {
            setChatbotOpen(!chatbotDropdown.classList.contains('open'));
        });
    }
    if (closeChatbotBtn) {
        closeChatbotBtn.addEventListener('click', function () { setAiPanelOpen(false); });
    }
    if (chatLauncherBtn) {
        chatLauncherBtn.addEventListener('click', function () { setAiPanelOpen(true); });
    }
    if (menuOpenAiItem) {
        menuOpenAiItem.addEventListener('click', function () {
            setChatbotOpen(false);
            setAiPanelOpen(true);
        });
    }
    document.querySelectorAll('.menu-jump-link').forEach(function (link) {
        link.addEventListener('click', function () { setChatbotOpen(false); });
    });

    const menuLoginItem = document.getElementById('menu-login-item');
    const menuSignupItem = document.getElementById('menu-signup-item');
    const menuLogoutItem = document.getElementById('menu-logout-item');
    const menuAboutDevItem = document.getElementById('menu-about-dev-item');
    const developerModal = document.getElementById('developer-modal');
    const closeDeveloperModalBtn = document.getElementById('close-developer-modal');

    function openDeveloperModal() {
        if (!developerModal) return;
        developerModal.classList.add('open');
    }

    function closeDeveloperModal() {
        if (!developerModal) return;
        developerModal.classList.remove('open');
    }

    if (menuLoginItem) menuLoginItem.addEventListener('click', function () { setChatbotOpen(false); openAuthModal('login'); });
    if (menuSignupItem) menuSignupItem.addEventListener('click', function () { setChatbotOpen(false); openAuthModal('signup'); });
    if (menuLogoutItem && logoutBtn) menuLogoutItem.addEventListener('click', function () { logoutBtn.click(); });
    if (menuAboutDevItem) menuAboutDevItem.addEventListener('click', function () { setChatbotOpen(false); openDeveloperModal(); });
    if (closeDeveloperModalBtn) closeDeveloperModalBtn.addEventListener('click', closeDeveloperModal);
    if (developerModal) {
        developerModal.addEventListener('click', function (e) {
            if (e.target === developerModal) closeDeveloperModal();
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && developerModal && developerModal.classList.contains('open')) {
            closeDeveloperModal();
        }
    });

    document.addEventListener('click', function (e) {
        if (!chatbotDropdown || !hamburgerBtn || !chatbotDropdown.classList.contains('open')) return;
        if (!chatbotDropdown.contains(e.target) && !hamburgerBtn.contains(e.target)) {
            setChatbotOpen(false);
        }
    });
    if (openHeroAiBtn) {
        openHeroAiBtn.addEventListener('click', function () { setAiPanelOpen(true); });
    }
    if (clarityAiBtn) {
        clarityAiBtn.addEventListener('click', function () {
            setAiPanelOpen(true);
        });
    }

    // Chatbot Messaging & Voice Recognition
    const messagesEl = document.getElementById('messages');
    const chatForm = document.getElementById('chat-form');
    const msgInput = document.getElementById('message-input');
    const voiceBtn = document.getElementById('voice-button');
    const voiceStatus = document.getElementById('voice-status');
    const autoReadBtn = document.getElementById('auto-read-toggle');
    let autoRead = false;
    let msgCounter = 0;

    if (autoReadBtn) {
        autoReadBtn.addEventListener('click', function () {
            autoRead = !autoRead;
            autoReadBtn.classList.toggle('active', autoRead);
        });
    }

    function appendMessage(role, text) {
        if (!messagesEl) return;
        msgCounter += 1;
        const id = 'chat-msg-' + msgCounter;
        const row = document.createElement('div');
        row.className = 'message-row ' + role;

        const avatar = document.createElement('div');
        avatar.className = 'avatar ' + (role === 'user' ? 'user' : 'bot');
        avatar.textContent = role === 'user' ? 'You' : 'AI';

        const wrap = document.createElement('div');
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        bubble.id = id;
        bubble.textContent = text;
        wrap.appendChild(bubble);

        if (role === 'bot') {
            const dict = translations[getActiveLang()] || translations['en-US'];
            const listen = document.createElement('button');
            listen.type = 'button';
            listen.className = 'listen-btn';
            listen.textContent = dict.listenBtn;
            listen.setAttribute('data-speak-target', id);
            wrap.appendChild(listen);
            if (autoRead) speakText(text);
        }

        row.appendChild(avatar);
        row.appendChild(wrap);
        messagesEl.appendChild(row);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    async function sendChatMessage(text) {
        const clean = text.trim();
        if (!clean) return;
        appendMessage('user', clean);
        if (msgInput) msgInput.value = '';

        try {
            const res = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: clean })
            });
            const data = await res.json();
            if (!res.ok) {
                appendMessage('error', data.reply || data.error || 'Could not reach AI assistant.');
                return;
            }
            appendMessage('bot', data.reply || data.answer || 'No response received.');
        } catch (err) {
            appendMessage('error', 'Connection error. Please check if the server is running.');
        }
    }

    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            sendChatMessage(msgInput.value);
        });
    }

    document.querySelectorAll('.chip-btn').forEach(function (chip) {
        chip.addEventListener('click', function () {
            sendChatMessage(chip.getAttribute('data-prompt') || chip.textContent);
        });
    });

    document.querySelectorAll('.ask-ai-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const svcKey = btn.getAttribute('data-service-key');
            const lang = getActiveLang();
            const prompt = (servicesData[svcKey] && (servicesData[svcKey][lang] || servicesData[svcKey]['en-US']).prompt) || '';
            if (!isLoggedIn) {
                openAuthModal('login', 'Please log in to ask the AI Assistant.');
                return;
            }
            setAiPanelOpen(true);
            sendChatMessage(prompt);
        });
    });

    if (voiceBtn) {
        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition = null;
        if (SpeechRec) {
            recognition = new SpeechRec();
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;
            recognition.onresult = function (event) {
                const transcript = event.results[0][0].transcript;
                msgInput.value = transcript;
                voiceBtn.classList.remove('listening');
                voiceStatus.classList.remove('visible');
                sendChatMessage(transcript);
            };
            recognition.onerror = function () {
                voiceBtn.classList.remove('listening');
                voiceStatus.textContent = 'Voice input could not start. Check microphone permission in Chrome/Edge.';
                voiceStatus.classList.add('visible');
            };
            recognition.onend = function () {
                voiceBtn.classList.remove('listening');
            };
        }

        voiceBtn.addEventListener('click', function () {
            if (!recognition) {
                voiceStatus.textContent = 'Voice input requires Chrome or Edge browser with microphone access.';
                voiceStatus.classList.add('visible');
                return;
            }
            recognition.lang = (voiceLang && voiceLang.value) || 'en-US';
            voiceBtn.classList.add('listening');
            voiceStatus.textContent = 'Listening... please speak now.';
            voiceStatus.classList.add('visible');
            try {
                recognition.start();
            } catch (e) {
                voiceBtn.classList.remove('listening');
            }
        });
    }

    // Smooth non-jerky accordion expand/collapse for FAQ <details> cards
    document.querySelectorAll('details.faq-dropdown, details.menu-faq-dropdown').forEach(function (details) {
        const summary = details.querySelector('summary');
        const body = details.querySelector('.faq-body, .menu-faq-items');
        if (!summary || !body) return;

        let activeAnim = null;
        let bodyAnim = null;

        summary.addEventListener('click', function (e) {
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            if (!details.animate) return;
            e.preventDefault();

            if (activeAnim) activeAnim.cancel();
            if (bodyAnim) bodyAnim.cancel();

            const borderHeight = details.offsetHeight - details.clientHeight;
            const collapsedHeight = summary.offsetHeight + borderHeight;
            details.style.overflow = 'hidden';

            if (!details.open) {
                const startHeight = details.offsetHeight + 'px';
                details.open = true;
                const endHeight = details.offsetHeight + 'px';

                activeAnim = details.animate(
                    { height: [startHeight, endHeight] },
                    { duration: 420, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
                );
                bodyAnim = body.animate(
                    { opacity: [0, 1], transform: ['translateY(-6px)', 'translateY(0)'] },
                    { duration: 380, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
                );
                activeAnim.onfinish = function () {
                    details.style.height = '';
                    details.style.overflow = '';
                    activeAnim = null;
                    bodyAnim = null;
                };
                activeAnim.oncancel = function () {
                    details.style.height = '';
                    details.style.overflow = '';
                };
            } else {
                const startHeight = details.offsetHeight + 'px';
                const endHeight = collapsedHeight + 'px';

                activeAnim = details.animate(
                    { height: [startHeight, endHeight] },
                    { duration: 360, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
                );
                bodyAnim = body.animate(
                    { opacity: [1, 0] },
                    { duration: 240, easing: 'ease-out' }
                );
                activeAnim.onfinish = function () {
                    details.open = false;
                    details.style.height = '';
                    details.style.overflow = '';
                    activeAnim = null;
                    bodyAnim = null;
                };
                activeAnim.oncancel = function () {
                    details.style.height = '';
                    details.style.overflow = '';
                };
            }
        });
    });
})();
</script>

<!-- Motion.dev (https://motion.dev/) Smooth Animations & Moving Objects -->
<script type="module">
    try {
        const { animate, inView, stagger } = await import('https://cdn.jsdelivr.net/npm/motion@11/+esm');
        const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!prefersReduced) {
            // 1. Continuous gentle moving objects in Hero
            const orb1 = document.querySelector('[data-motion-float="orb1"]');
            const orb2 = document.querySelector('[data-motion-float="orb2"]');

            if (orb1) {
                animate(orb1, { x: [0, -28, 12, 0], y: [0, 22, -14, 0], scale: [1, 1.08, 0.96, 1] }, { duration: 12, repeat: Infinity, ease: 'easeInOut' });
            }
            if (orb2) {
                animate(orb2, { x: [0, 26, -18, 0], y: [0, -20, 14, 0], scale: [1, 0.94, 1.06, 1] }, { duration: 14, repeat: Infinity, ease: 'easeInOut' });
            }

            // 2. Subtle floating Native AI brand logo & icons
            const brandLogo = document.querySelector('.brand-logo');
            if (brandLogo) {
                animate(brandLogo, { y: [0, -2.5, 0] }, { duration: 4, repeat: Infinity, ease: 'easeInOut' });
            }

            document.querySelectorAll('.service-emoji').forEach(function (el, idx) {
                animate(el, { y: [0, -4, 0] }, { duration: 3.6 + (idx * 0.4), repeat: Infinity, ease: 'easeInOut' });
            });

            // 3. Scroll-triggered smooth staggered reveals (slow, non-jerky)
            inView('.clarity-grid', function (info) {
                animate(info.target.querySelectorAll('.clarity-card'), { opacity: [0, 1], y: [20, 0] }, { duration: 0.7, delay: stagger(0.12), ease: [0.22, 1, 0.36, 1] });
            });
            inView('#faqs .faq-list', function (info) {
                animate(info.target.querySelectorAll('.faq-dropdown'), { opacity: [0, 1], y: [18, 0] }, { duration: 0.65, delay: stagger(0.08), ease: [0.22, 1, 0.36, 1] });
            });
            inView('#services .services-grid', function (info) {
                animate(info.target.querySelectorAll('.service-card'), { opacity: [0, 1], y: [22, 0] }, { duration: 0.7, delay: stagger(0.1), ease: [0.22, 1, 0.36, 1] });
            });
        }
    } catch (e) {
        // Offline fallback: CSS animations remain active
    }
</script>
</body>
</html>

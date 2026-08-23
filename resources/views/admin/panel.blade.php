<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <title>Administración </title>
    <style>
        :root {
            --orange: #FFD700;
            --orange-soft: #FFE135;
            --orange-deep: #FFC700;
            --line: rgba(255, 215, 0, .18);
            --text: #FFFFFF;
            --bg: #FFD700;
            --panel: rgba(255, 255, 255, .94);
            --ink-soft: #000000;
            --panel-ink: #000000;
            --muted-ink: #000000;
            --shadow-soft: 0 16px 34px rgba(255, 215, 0, .08);
            --shadow-strong: 0 24px 52px rgba(255, 215, 0, .12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255, 215, 0, .16), transparent 24%),
                radial-gradient(circle at bottom right, rgba(255, 215, 0, .18), transparent 20%),
                linear-gradient(180deg, #FFD700 0%, #FFD700 48%, #FFD700 100%);
            color: #FFFFFF;
        }

        .container { max-width: 1260px; margin: 0 auto; padding: 0 18px; }

        header {
            position: sticky;
            top: 0;
            z-index: 30;
            backdrop-filter: blur(12px);
            background: #FFD700;
            border-bottom: 1px solid rgba(255, 215, 0, .9);
            box-shadow: 0 12px 30px rgba(255, 215, 0, .15);
        }

        .head {
            min-height: 84px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 12px 0;
        }

        .head-brand {
            display: grid;
            gap: 4px;
        }

        .head-kicker {
            font-size: 11px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #FFFFFF;
            font-weight: 900;
        }

        .title {
            color: #FFFFFF;
            font-weight: 900;
            font-size: clamp(20px, 3vw, 30px);
            line-height: 1;
        }

        .title-sub {
            color: #FFFFFF;
            font-size: 13px;
        }

        .head-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .user {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .88);
            background: rgba(255, 215, 0, .95);
            font-size: 13px;
            color: #FFFFFF;
            font-weight: 800;
        }

        .logout-btn {
            border-radius: 999px;
            padding: 10px 14px;
        }

        .layout {
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 16px;
            padding: 18px 0 34px;
        }

        .admin-menu {
            position: sticky;
            top: 92px;
            z-index: 29;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            margin-top: 14px;
            border-radius: 18px;
            border: 1px solid rgba(232, 121, 18, .88);
            background: #FF8A18;
            backdrop-filter: blur(12px);
            box-shadow: var(--shadow-soft);
        }

        .admin-menu .menu-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .menu-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .9);
            background: rgba(232, 121, 18, .96);
            color: #FFFFFF;
            font-weight: 900;
            font-size: 13px;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            cursor: pointer;
        }

        .tab-icon,
        .action-icon {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: #FFFFFF;
            font-size: 12px;
            line-height: 1;
            flex-shrink: 0;
        }

        .menu-tab:hover {
            transform: translateY(-1px);
            border-color: rgba(201, 106, 45, .42);
            box-shadow: 0 10px 22px rgba(201, 106, 45, .10);
        }

        .menu-tab.active {
            border-color: rgba(255, 255, 255, .28);
            background: #E87912;
            color: #FFFFFF;
        }

        #adminContent.tab-mode { grid-template-columns: 1fr; }
        #adminContent.tab-mode > section { grid-column: 1 / -1; }
        .tab-hidden { display: none !important; }

        .panel {
            background: #FFFFFF;
            border: 1px solid rgba(232, 121, 18, .86);
            border-radius: 24px;
            padding: 20px;
            box-shadow: var(--shadow-strong);
            backdrop-filter: blur(8px);
        }

        .panel h2, .panel h3 { margin-top: 0; color: #000000; }
        .section-subtitle {
            margin: -4px 0 18px;
            color: #000000;
            font-size: 14px;
            line-height: 1.55;
        }
        .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }

        input, select, textarea {
            width: 100%;
            border: 1px solid #FF8A18;
            border-radius: 14px;
            background: #FFFFFF;
            color: #000000;
            padding: 12px 13px;
            margin-top: 5px;
            margin-bottom: 10px;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #E87912;
            box-shadow: 0 0 0 4px rgba(232, 121, 18, .20);
            transform: translateY(-1px);
        }

        button {
            border: 1px solid #E87912;
            border-radius: 12px;
            background: #FF8A18;
            color: #FFFFFF;
            cursor: pointer;
            padding: 10px 13px;
            font-weight: 700;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        button:hover {
            transform: translateY(-1px);
            border-color: #FF6F1F;
            box-shadow: 0 10px 18px rgba(255, 138, 24, .20);
        }

        .btn-main {
            border: 0;
            background: linear-gradient(120deg, #FFD700, #FFE135);
            color: #FFFFFF;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(255, 138, 24, .18);
        }

        .list {
            display: grid;
            gap: 12px;
            max-height: 620px;
            overflow: auto;
            padding-right: 3px;
        }

        .card {
            position: relative;
            overflow: hidden;
            border: 1px solid #FF8A18;
            border-radius: 18px;
            padding: 14px;
            background: #FFFFFF;
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }

        .card::after {
            content: "";
            position: absolute;
            inset: auto -40px -40px auto;
            width: 110px;
            height: 110px;
            background: radial-gradient(circle, rgba(255, 138, 24, .11), transparent 68%);
            pointer-events: none;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
        }

        .tag {
            background: #FF8A18;
            border: 1px solid #E87912;
            color: #FFFFFF;
            border-radius: 999px;
            font-size: 11px;
            padding: 5px 9px;
            text-transform: capitalize;
            font-weight: 800;
        }

        .tag.active { color: #8c4508; background: #fff0e4; }
        .tag.inactive { color: #8a5b3c; background: #fff7f1; }
        .tag.sold-out { color: #a43f07; background: #fff2e8; }
        .tag.stock { background: #fff8f2; }
        .tag.payment-pending { color: #915400; background: #fff3dc; border-color: #ffd295; }
        .tag.payment-reported { color: #8b4304; background: #ffe9d6; border-color: #ffc492; }
        .tag.payment-verified { color: #11663f; background: #e9fff2; border-color: #98dfb7; }
        .tag.payment-rejected { color: #9a2517; background: #ffe5e2; border-color: #ffb8b0; }
        .order-proof-box {
            margin-top: 8px;
            padding: 10px;
            border: 1px dashed #ffd1b0;
            border-radius: 14px;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
        }
        .order-proof-preview {
            display: block;
            width: 100%;
            max-width: 220px;
            max-height: 160px;
            object-fit: contain;
            border-radius: 12px;
            border: 1px solid #ffd8bf;
            background: #fff;
            margin-top: 8px;
        }
        .proof-modal {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            inset: 0;
            width: 100vw;
            height: 100vh;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(34, 15, 4, .68);
            z-index: 90;
        }
        .proof-modal-card {
            width: 100%;
            max-width: 900px;
            max-height: 88vh;
            overflow: auto;
            border-radius: 20px;
            border: 1px solid #ffd7bd;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
            box-shadow: 0 24px 50px rgba(52, 17, 0, .20);
            padding: 16px;
        }
        .proof-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .proof-modal-title {
            margin: 0;
            color: #8d3d00;
        }
        .proof-modal-body {
            display: grid;
            gap: 12px;
        }
        .proof-modal-image {
            width: 100%;
            max-height: 72vh;
            object-fit: contain;
            border-radius: 14px;
            border: 1px solid #ffd8bf;
            background: #fff;
        }
        .proof-modal-frame {
            width: 100%;
            height: 72vh;
            border: 1px solid #ffd8bf;
            border-radius: 14px;
            background: #fff;
        }

        .read-only-admin form button[type="submit"],
        .read-only-admin [data-delete-order],
        .read-only-admin [data-delete],
        .read-only-admin [data-delete-user],
        .read-only-admin [data-toggle-user],
        .read-only-admin [data-einvoice-send],
        .read-only-admin [data-einvoice-email],
        .read-only-admin [data-save-role],
        .read-only-admin #runRecoveryCampaignBtn,
        .read-only-admin #removeProductImageBtn {
            pointer-events: none;
            opacity: .45;
            filter: grayscale(.4);
        }

        .side-panel-overlay {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            inset: 0;
            width: 100vw;
            height: 100vh;
            display: none;
            justify-content: flex-end;
            background: rgba(34, 15, 4, .5);
            z-index: 9999;
        }
        .side-panel-overlay.open { display: flex; }
        .side-panel {
            width: min(420px, 92vw);
            height: 100%;
            overflow-y: auto;
            border-left: 1px solid #ffd7bd;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
            box-shadow: -24px 0 50px rgba(52, 17, 0, .20);
            padding: 18px;
            transform: translateX(100%);
            transition: transform .28s ease;
        }
        .side-panel-overlay.open .side-panel { transform: translateX(0); }
        .side-panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }
        .side-panel-body { display: grid; gap: 12px; }
        .side-panel-preview {
            white-space: pre-wrap;
            word-break: break-word;
            background: #fff7f2;
            border: 1px solid #ffd8bf;
            border-radius: 14px;
            padding: 12px;
            font-size: 12px;
            color: #000000;
            max-height: 70vh;
            overflow: auto;
        }

        .img-shell {
            margin-top: 10px;
            margin-bottom: 8px;
            padding: 12px;
            border-radius: 16px;
            border: 1px solid #ffd9bf;
            background:
                radial-gradient(circle at top right, rgba(255, 111, 31, .12), transparent 35%),
                linear-gradient(180deg, #fffdfc 0%, #fff4ea 100%);
        }
        .img-thumb {
            width: 100%;
            aspect-ratio: 1 / 1;
            min-height: 180px;
            max-height: 180px;
            object-fit: contain;
            object-position: center;
            border-radius: 12px;
            background: #fff7f2;
            transition: transform .28s ease, box-shadow .28s ease, filter .28s ease;
        }

        .muted { font-size: 12px; opacity: .82; color: #6e4329; }
        .msg { font-size: 13px; min-height: 20px; }
        .msg.success { color: #2d7a48; font-weight: 800; }
        .msg.error { color: #a24022; font-weight: 800; }
        .product-form-grid {
            display: grid;
            gap: 14px;
        }
        .products-layout {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 22px;
            align-items: start;
        }
        @media (max-width: 860px) {
            .products-layout { grid-template-columns: 1fr; }
        }
        .toggle-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            border: 1px solid #ffd8bf;
            border-radius: 16px;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
            margin-bottom: 12px;
        }
        .toggle-main {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            color: #65320f;
        }
        .toggle-main input {
            width: 18px;
            height: 18px;
            margin: 0;
            accent-color: #ff6f1f;
        }
        .toggle-status-text {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 7px 12px;
            background: #fff0e4;
            border: 1px solid #ffc89d;
            color: #914406;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .toggle-status-text.inactive {
            background: #fff7f1;
            border-color: #ffd8bf;
            color: #8a5b3c;
        }
        .product-card-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
        }
        .product-card-title {
            margin: 0;
            font-size: 18px;
            color: #27160c;
        }
        .product-card-price {
            margin-top: 6px;
            font-size: 24px;
            font-weight: 900;
            color: #c35300;
        }
        .product-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .product-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .helper-text {
            margin: -4px 0 0;
            color: #8a5b3c;
            font-size: 12px;
        }
        .upload-box {
            display: grid;
            gap: 10px;
            padding: 14px;
            border: 1px dashed #ffc18f;
            border-radius: 18px;
            background: linear-gradient(180deg, #fffdfb 0%, #fff4ea 100%);
        }
        .upload-preview {
            display: none;
            width: 100%;
            max-width: 240px;
            aspect-ratio: 4 / 3;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid #ffd7bd;
            background: #fff;
        }
        .upload-preview.visible {
            display: block;
        }
        .upload-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .dashboard-grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
        .module-shell { display:grid; gap:16px; }
        .module-hero { display:grid; grid-template-columns: 1.1fr .9fr; gap:14px; margin-bottom:16px; }
        .module-summary {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid #ffd7bd;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
        }
        .metric-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap:10px; }
        .metric-card {
            padding: 14px;
            border-radius: 18px;
            border: 1px solid #ffd7bd;
            background: linear-gradient(180deg, #fff 0%, #fff8f2 100%);
        }
        .metric-card strong {
            display: block;
            margin-top: 6px;
            font-size: 26px;
            color: #a84800;
        }
        .section-grid-2 { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:14px; }
        .inline-note {
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px dashed #ffc18f;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
            color: #7a4520;
            font-size: 13px;
            line-height: 1.55;
        }
        .chart-card {
            position: relative;
            overflow: hidden;
            border:1px solid #ffd7bd;
            border-radius:20px;
            padding:14px;
            background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);
        }
        .chart-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 20%, rgba(255,255,255,.3) 50%, transparent 76%);
            transform: translateX(-140%);
            animation: chartSweep 4.4s ease-in-out infinite;
            pointer-events: none;
        }
        .chart-head { display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:8px; }
        .bars { display:grid; grid-template-columns: repeat(auto-fit, minmax(34px, 1fr)); gap:6px; min-height:140px; align-items:end; }
        .bar-col { display:grid; gap:5px; justify-items:center; font-size:10px; color:#7c4219; }
        .bar-fill {
            width:100%;
            max-width:28px;
            border-radius:10px 10px 5px 5px;
            background:linear-gradient(180deg,#ffb071,#ff6f1f);
            box-shadow: 0 8px 14px rgba(255, 111, 31, .18);
            animation: pulseBar 2.8s ease-in-out infinite;
            transform-origin: bottom;
        }

        .card:hover {
            transform: translateY(-3px);
            border-color: #ffbe92;
            box-shadow: 0 14px 26px rgba(255, 111, 31, .10);
        }

        .card:hover .img-thumb {
            transform: scale(1.04);
            filter: saturate(1.05);
            box-shadow: 0 10px 18px rgba(255, 111, 31, .10);
        }

        @keyframes chartSweep {
            0%, 15% { transform: translateX(-140%); }
            45%, 100% { transform: translateX(140%); }
        }

        @keyframes pulseBar {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(1.03); }
        }

        @media (max-width: 980px) {
            .head {
                flex-direction: column;
                align-items: flex-start;
            }
            .head-actions {
                width: 100%;
                justify-content: space-between;
            }
            .layout { grid-template-columns: 1fr; }
            .dashboard-grid { grid-template-columns: 1fr; }
            .module-hero,
            .section-grid-2 { grid-template-columns: 1fr; }
        }

        body[data-theme="dark"] {
            background:
                radial-gradient(circle at top left, rgba(199, 104, 42, .16), transparent 24%),
                radial-gradient(circle at bottom right, rgba(243, 187, 122, .08), transparent 22%),
                linear-gradient(180deg, #12100d 0%, #171411 48%, #1e1914 100%);
            color: #f8ecdf;
        }
        body[data-theme="dark"], body[data-theme="dark"] .title, body[data-theme="dark"] .title-sub, body[data-theme="dark"] .section-subtitle, body[data-theme="dark"] .muted, body[data-theme="dark"] .helper-text, body[data-theme="dark"] .proof-modal-title, body[data-theme="dark"] .proof-modal-meta {
            color: #e8cfb7;
        }
        body[data-theme="dark"] header,
        body[data-theme="dark"] .admin-menu,
        body[data-theme="dark"] .panel,
        body[data-theme="dark"] .card,
        body[data-theme="dark"] .chart-card,
        body[data-theme="dark"] .proof-modal-card,
        body[data-theme="dark"] .toggle-row,
        body[data-theme="dark"] .img-shell,
        body[data-theme="dark"] .upload-box {
            background: linear-gradient(180deg, rgba(27, 22, 18, .98) 0%, rgba(34, 28, 23, .98) 100%) !important;
            border-color: rgba(243, 187, 122, .16) !important;
            box-shadow: 0 22px 44px rgba(0,0,0,.22) !important;
        }
        body[data-theme="dark"] .menu-tab,
        body[data-theme="dark"] .user,
        body[data-theme="dark"] .tag,
        body[data-theme="dark"] .toggle-status-text,
        body[data-theme="dark"] button:not(.btn-main) {
            background: rgba(34, 28, 23, .96) !important;
            color: #f8ecdf !important;
            border-color: rgba(243, 187, 122, .18) !important;
        }
        body[data-theme="dark"] .menu-tab.active,
        body[data-theme="dark"] .btn-main {
            color: #2f180a !important;
        }
        body[data-theme="dark"] .tab-icon,
        body[data-theme="dark"] .action-icon {
            background: rgba(243, 187, 122, .16);
            color: #f3bb7a;
        }
        body[data-theme="dark"] input, body[data-theme="dark"] select, body[data-theme="dark"] textarea {
            background: #181410 !important;
            color: #f8ecdf !important;
            border-color: rgba(243, 187, 122, .14) !important;
        }
        body[data-theme="dark"] .product-card-title,
        body[data-theme="dark"] .product-card-price,
        body[data-theme="dark"] .label,
        body[data-theme="dark"] .head-kicker,
        body[data-theme="dark"] .section-subtitle,
        body[data-theme="dark"] .helper-text,
        body[data-theme="dark"] .muted {
            color: var(--panel-ink) !important;
        }
        body[data-theme="dark"] .img-thumb,
        body[data-theme="dark"] .order-proof-preview,
        body[data-theme="dark"] .proof-modal-image,
        body[data-theme="dark"] .proof-modal-frame,
        body[data-theme="dark"] .upload-preview {
            background: #0d0d0d !important;
        }

        body {
            background:
                linear-gradient(180deg, #fff7ea 0%, #fffaf5 46%, #fff1dc 100%);
            color: #21140d;
        }

        header {
            background:
                linear-gradient(90deg, #17110d 0%, #1d1510 58%, #ffc20e 58%, #ffc20e 100%);
            border-bottom: 1px solid rgba(255, 193, 15, .42);
            box-shadow: none;
        }

        .head-kicker,
        .title,
        .title-sub {
            color: #fff8ed;
        }

        .head-kicker {
            color: #ffc45d;
        }

        .menu-tab,
        .user,
        button:not(.btn-main) {
            min-height: 40px;
            border-radius: 999px;
            border-color: rgba(255,255,255,.38);
            background: rgba(255,255,255,.12);
            color: #fff8ed;
            box-shadow: 0 10px 18px rgba(32, 12, 0, .10);
        }

        .menu-tab.active,
        .btn-main {
            background: linear-gradient(135deg, #ff9f22, #d87525);
            color: #21140d;
            border-color: rgba(255,255,255,.52);
        }

        .tab-icon,
        .action-icon {
            background: rgba(255,255,255,.18);
            color: #fff8ed;
        }

        .admin-menu {
            top: 84px;
            border-radius: 8px;
            background: #18120e;
            border-color: rgba(255, 193, 15, .28);
            box-shadow: none;
        }

        .panel,
        .card,
        .module-summary,
        .metric-card,
        .chart-card,
        .toggle-row,
        .upload-box,
        .inline-note {
            border-radius: 8px;
            background: #fffaf4;
            border-color: rgba(255, 159, 34, .26);
            box-shadow: 0 14px 28px rgba(62, 24, 0, .08);
        }

        .panel h2,
        .panel h3 {
            letter-spacing: 0;
        }

        input,
        select,
        textarea {
            border-radius: 8px;
        }

        body[data-theme="dark"] {
            background: linear-gradient(180deg, #FFD700 0%, #FFD700 100%);
        }

        body[data-theme="dark"] header,
        body[data-theme="dark"] .admin-menu {
            background: linear-gradient(90deg, #E87912 0%, #E87912 58%, #FF6F1F 58%, #FF6F1F 100%) !important;
        }

        /* Tema final unificado: amarillo dorado. */
        body {
            --orange: #FFD700;
            --orange-soft: #FFE135;
            --orange-deep: #FFC700;
            --line: rgba(255, 215, 0, .34);
            --text: #FFFFFF;
            --panel: #FFFFFF;
            --panel-ink: #000000;
            --muted-ink: #000000;
            background:
                radial-gradient(circle at 12% 0%, rgba(255, 215, 0, .16), transparent 28%),
                linear-gradient(180deg, #FFD700 0%, #FFD700 45%, #FFD700 100%);
            color: #FFFFFF;
        }

        header {
            background:
                linear-gradient(90deg, #FFC700 0%, #FFC700 58%, #FFD700 58%, #FFD700 100%) !important;
            border-bottom: 1px solid rgba(255, 215, 0, .42);
            box-shadow: none;
        }

        .container {
            max-width: 1280px;
        }

        .title,
        .title-sub {
            color: #FFFFFF;
        }

        .head-kicker {
            color: #FFFFFF !important;
        }

        .menu-tab,
        .user,
        button:not(.btn-main) {
            min-height: 40px;
            border-radius: 999px;
            background: rgba(255, 215, 0, .9) !important;
            color: #FFFFFF !important;
            border-color: rgba(255, 255, 255, .34) !important;
            box-shadow: none;
        }

        .menu-tab.active,
        .btn-main {
            background: linear-gradient(135deg, #FFE135, #FFC700) !important;
            color: #FFFFFF !important;
            border-color: rgba(255, 255, 255, .48) !important;
        }

        .tab-icon,
        .action-icon {
            background: rgba(255, 255, 255, .22);
            color: #FFFFFF;
        }

        .admin-menu {
            top: 96px;
            border-radius: 8px;
            background: #FFD700 !important;
            border-color: rgba(255, 215, 0, .28) !important;
        }

        .panel,
        .card,
        .module-summary,
        .metric-card,
        .chart-card,
        .toggle-row,
        .upload-box,
        .inline-note {
            border-radius: 8px;
            background: #FFFFFF !important;
            color: #000000 !important;
            border-color: rgba(255, 215, 0, .30) !important;
            box-shadow: none !important;
        }

        .panel h2,
        .panel h3,
        .product-card-title,
        .product-card-price,
        .label,
        .section-subtitle,
        .helper-text,
        .muted {
            color: #000000 !important;
        }

        input,
        select,
        textarea {
            border-radius: 8px;
            background: #FFFFFF !important;
            color: #000000 !important;
            border-color: rgba(255, 215, 0, .34) !important;
        }

        /* Identidad final compartida con la tienda: el dorado queda solo como acento. */
        body,
        body[data-theme="dark"] {
            --orange: #FF6F1F;
            --orange-soft: #FF9D5A;
            --orange-deep: #C94700;
            --text: #25170F;
            --panel: #FFFDF9;
            --panel-ink: #25170F;
            --muted-ink: #68432E;
            background: #FFF8F2 !important;
            color: #25170F !important;
        }
        header,
        body[data-theme="dark"] header {
            background: #FFFDF9 !important;
            border-bottom: 1px solid #F0C9AA !important;
        }
        .title, .title-sub, .head-kicker { color: #25170F !important; }
        .head-kicker { color: #C94700 !important; }
        .admin-menu,
        body[data-theme="dark"] .admin-menu {
            background: #FFFDF9 !important;
            border: 1px solid #F0C9AA !important;
            box-shadow: 0 12px 28px rgba(37,23,15,.08) !important;
        }
        .menu-tab, .user, button:not(.btn-main),
        body[data-theme="dark"] .menu-tab {
            background: #FFFFFF !important;
            color: #25170F !important;
            border-color: #EAB68A !important;
        }
        .menu-tab:hover { background: #FF9D5A !important; color: #25170F !important; }
        .menu-tab.active, .btn-main,
        body[data-theme="dark"] .menu-tab.active {
            background: #FF6F1F !important;
            color: #FFFFFF !important;
            border-color: #C94700 !important;
        }
        .tab-icon, .action-icon { background: #FFF1E3 !important; color: #C94700 !important; }
        .menu-tab.active .tab-icon, .menu-tab.active .action-icon { background: rgba(255,255,255,.2) !important; color: #fff !important; }
        .panel, .card, .module-summary, .metric-card, .chart-card, .toggle-row, .upload-box, .inline-note {
            background: #FFFDF9 !important;
            color: #25170F !important;
            border-color: #F0C9AA !important;
            box-shadow: 0 12px 26px rgba(37,23,15,.08) !important;
        }
        .panel h2, .panel h3, .product-card-title, .product-card-price, .label { color: #25170F !important; }
        #adminOrderToast>div { background:#FFFDF9!important; border:1.5px solid #FFB37A!important; box-shadow:0 22px 50px rgba(255,111,31,.24)!important; }
        #adminOrderToast>div>div:first-child { background:#FFF1E3!important; border:0!important; border-bottom:1px solid #FFE4D2!important; }
        #adminOrderToastTitle { color:#7b3d11!important; }
        #adminOrderToastMessage { color:#25170f!important; }
        #adminOrderToastBody { color:#68432e!important; }
        .section-subtitle, .helper-text, .muted { color: #68432E !important; }
        input, select, textarea { background: #FFF4EB !important; color: #25170F !important; border-color: #EAB68A !important; }
        .dashboard-pies { grid-column: 1 / -1; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .pie-layout { display:grid; grid-template-columns:minmax(150px,210px) minmax(0,1fr); gap:18px; align-items:center; }
        .pie-chart { width:min(210px,100%); aspect-ratio:1; border-radius:50%; margin:auto; box-shadow:inset 0 0 0 1px #F0C9AA, 0 12px 28px rgba(37,23,15,.12); }
        .pie-legend { display:grid; gap:8px; min-width:0; }
        .pie-legend-row { display:grid; grid-template-columns:12px minmax(0,1fr) auto; gap:8px; align-items:center; color:#68432E; }
        .pie-dot { width:12px; height:12px; border-radius:50%; }

        @media (max-width: 860px) {
            .dashboard-pies, .pie-layout { grid-template-columns:1fr; }
            header {
                background: #FFFDF9 !important;
            }
            .admin-menu {
                top: 0;
            }
        }
    </style>
    <link rel="stylesheet" href="/css/brand-refresh.css?v=20260804-fullwidth">
</head>
<body>
<header>
    <div class="container head">
        <div class="head-brand">
            <div class="head-kicker">Centro de Control</div>
            <div class="title">Pollos y Parrillas "El Dorado"</div>
            <div class="title-sub">Operacion, pedidos y cobros en una sola vista.</div>
        </div>
        <div class="head-actions">
            <button class="menu-tab" type="button" data-header-target="sec-dashboard"><span class="action-icon">&#10022;</span>Dashboard</button>

            <button id="adminUnreadBtn" class="menu-tab" type="button"><span class="action-icon">&#9679;</span>Nuevos <span id="adminUnreadCount">0</span></button>
            <div class="user" id="adminUserLabel">Validando sesion...</div>
            <button id="adminLogoutBtn" class="logout-btn"><span class="action-icon">&#8617;</span>Cerrar sesion</button>
        </div>
    </div>
</header>

<div class="container">
    <div id="readOnlyBanner" style="display:none; align-items:center; gap:10px; margin-top:16px; padding:12px 16px; border-radius:16px; border:1px solid #E87912; background:#FFF3E0; color:#8a4b00; font-weight:800;">
        <span style="font-size:18px;">&#128065;</span>
        <span>Modo de solo revision: puedes ver toda la informacion, pero los botones que modifican datos estan desactivados.</span>
    </div>
    <div id="denyBox" class="panel" style="display:none; margin-top:16px;">
        <h2>Acceso denegado</h2>
        <p>Necesitas iniciar sesion como administrador.</p>
        <a href="/admin/login" style="color:#ffb387;">Ir a login admin</a>
    </div>

    <nav id="adminMenu" class="admin-menu" style="display:none;">
        <div class="menu-links">
            <button class="menu-tab" type="button" data-target="sec-dashboard"><span class="tab-icon">&#10022;</span>Dashboard</button>
            <button class="menu-tab" type="button" data-target="sec-offers"><span class="tab-icon">&#9993;</span>Promos</button>
            <button class="menu-tab" type="button" data-target="sec-jobs"><span class="tab-icon">&#9733;</span>Vacantes</button>
            <button class="menu-tab" type="button" data-target="sec-products"><span class="tab-icon">&#9638;</span>Productos</button>
            <button class="menu-tab" type="button" data-target="sec-orders"><span class="tab-icon">&#8811;</span>Pedidos</button>
            <button class="menu-tab" type="button" data-target="sec-manual-sale"><span class="tab-icon">&#9878;</span>Venta manual</button>
            <button class="menu-tab" type="button" data-target="sec-cash-closure"><span class="tab-icon">&#164;</span>Caja</button>
            <button class="menu-tab" type="button" data-target="sec-users"><span class="tab-icon">&#9675;</span>Cuentas</button>
        </div>
        <div class="helper-text" style="margin:0;">Panel ordenado por pestañas.</div>
    </nav>

    <div id="adminContent" class="layout" style="display:none;">
        <section id="sec-dashboard" class="panel" style="grid-column: 1 / -1;">
            <h2>Dashboard de ventas</h2>
            <p class="muted">Resumen visual de ventas por dia, mes y año a partir de los pedidos cargados.</p>
            <div class="module-hero" style="margin-bottom:14px;">
                <div class="module-summary">
                    <p class="head-kicker">Vision Ejecutiva</p>
                    <div class="section-subtitle" style="margin:0;">Prioriza operacion, cobros pendientes, productos activos y carga de clientes desde un solo bloque superior.</div>
                </div>
                <div class="metric-grid">
                    <article class="metric-card">
                        <span class="muted">Pedidos en vista</span>
                        <strong id="dashboardOrdersMetric">0</strong>
                    </article>
                    <article class="metric-card">
                        <span class="muted">Pagos pendientes</span>
                        <strong id="dashboardPendingPaymentsMetric">0</strong>
                    </article>
                    <article class="metric-card">
                        <span class="muted">Activos hoy</span>
                        <strong id="dashboardProductsMetric">0</strong>
                    </article>
                    <article class="metric-card">
                        <span class="muted">Clientes registrados</span>
                        <strong id="dashboardUsersMetric">0</strong>
                    </article>
                </div>
            </div>
            <div id="salesDashboard" class="dashboard-grid">
                <div class="chart-card">
                    <div class="chart-head">
                        <strong>Ventas por dia</strong>
                        <span class="tag">0</span>
                    </div>
                    <div class="muted">Aun sin datos.</div>
                </div>
                <div class="chart-card">
                    <div class="chart-head">
                        <strong>Ventas por mes</strong>
                        <span class="tag">0</span>
                    </div>
                    <div class="muted">Aun sin datos.</div>
                </div>
                <div class="chart-card">
                    <div class="chart-head">
                        <strong>Ventas por año</strong>
                        <span class="tag">0</span>
                    </div>
                    <div class="muted">Aun sin datos.</div>
                </div>
            </div>
        </section>

        <section id="sec-offers" class="panel">
            <h2>Promociones</h2>
            <p class="section-subtitle">Elige de forma sencilla donde quieres avisar la promocion. La imagen se sube desde aqui y se guarda en el servidor.</p>
            <form id="offerForm">
                <input type="hidden" name="target" value="all">
                <input type="hidden" name="cta_label" value="">
                <div class="toggle-row">
                    <label class="toggle-main">
                        <input type="checkbox" id="offerSendAll" checked> Enviar a todos (app abierta, app cerrada, web y correo)
                    </label>
                </div>
                <div class="toggle-row">
                    <label class="toggle-main">
                        <input type="checkbox" name="send_realtime" checked> Enviar a app abierta y web (tiempo real)
                    </label>
                </div>
                <div class="toggle-row">
                    <label class="toggle-main">
                        <input type="checkbox" name="send_push" checked> Enviar a app cerrada (notificacion push)
                    </label>
                </div>
                <div class="toggle-row">
                    <label class="toggle-main">
                        <input type="checkbox" name="send_email"> Enviar por correo
                    </label>
                </div>
                <div class="row">
                    <div>
                        <label>Asunto del correo (opcional)</label>
                        <input name="email_subject" placeholder="Ej: Promo de hoy en El Dorado">
                    </div>
                    <div>
                        <label>Platillo al que dirigirá "Lo quiero"</label>
                        <select id="offerProductSelect" name="product_id" required>
                            <option value="">Catálogo general</option>
                        </select>
                        <div class="helper-text">Incluye los platillos nuevos creados desde Productos.</div>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Descuento porcentual</label>
                        <input id="offerDiscountPercent" name="discount_percent" type="number" min="0.01" max="99.99" step="0.01" placeholder="Ej: 20">
                        <div class="helper-text">El sistema calcula el precio normal menos este porcentaje.</div>
                    </div>
                    <div>
                        <label>Precio final de promoción</label>
                        <input id="offerPromoPrice" name="promo_price" type="number" min="0.01" step="0.01" placeholder="Ej: 39.90">
                        <div id="offerPriceHelp" class="helper-text">Puedes indicar porcentaje o precio final.</div>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Duracion de la promocion</label>
                        <select id="offerDurationSelect" name="duration_hours">
                            <option value="">Sin fecha de fin (hasta que la desactives)</option>
                            <option value="24" selected>24 horas</option>
                            <option value="48">48 horas</option>
                            <option value="72">72 horas</option>
                            <option value="168">7 dias</option>
                            <option value="custom">Elegir fecha y hora de fin...</option>
                        </select>
                        <div class="helper-text">Hora Peru. Al vencer, el precio vuelve a la normal automaticamente en web, correo y app.</div>
                    </div>
                    <div id="offerEndsAtWrap" style="display:none;">
                        <label>Termina el</label>
                        <input id="offerEndsAtInput" type="datetime-local">
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Ti­tulo</label>
                        <input name="title" required maxlength="120" placeholder="Ej: Combo familiar al 20%">
                    </div>
                    <div>
                        <label>Mensaje corto</label>
                        <input name="message" required maxlength="255" placeholder="Ej: Solo hoy, delivery gratis en tu zona">
                    </div>
                </div>
                <label>Contenido (opcional)</label>
                <textarea name="body" rows="3" maxlength="255" placeholder="Describe la promo con mas detalle..."></textarea>
                <div class="upload-box">
                    <label>Imagen de la promocion</label>
                    <input id="offerImageInput" name="image" type="file" accept="image/*">
                    <div class="helper-text">Sube la imagen desde este panel. El sistema la guarda y usa la ruta automaticamente.</div>
                    <img id="offerImagePreview" class="upload-preview" alt="Vista previa de promocion">
                </div>

                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px;">
                    <button type="submit" class="btn-main">Enviar promocion</button>
                </div>
                <div id="offerMsg" class="msg"></div>
            </form>
            <div class="upload-box" style="margin-top:18px;">
                <strong>Promociones programadas / activas / vencidas</strong>
                <div class="helper-text">Vencidas y desactivadas se ocultan solas de la caja del inicio (web y app). Puedes cortar una activa antes de tiempo.</div>
                <div id="promotionsList" class="list" style="margin-top:10px;"></div>
            </div>
            <div class="upload-box" style="margin-top:18px;">
                <strong>Reactivacion automatica</strong>
                <div class="helper-text">Lanza manualmente la campana para clientes inactivos y carritos abandonados. El cron diario queda en 5 dias de inactividad.</div>
                <div class="row">
                    <div>
                        <label>Dias sin login</label>
                        <input id="inactiveDaysInput" type="number" min="1" max="30" value="5">
                    </div>
                    <div>
                        <label>Horas de carrito abandonado</label>
                        <input id="abandonedHoursInput" type="number" min="1" max="72" value="3">
                    </div>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button" id="runRecoveryCampaignBtn" class="btn-main">Ejecutar campanas</button>
                </div>
                <div id="recoveryCampaignMsg" class="msg"></div>
            </div>
        </section>

        <section id="sec-jobs" class="panel">
            <h2>Vacantes laborales</h2>
            <p class="section-subtitle">Publica puestos como lavandero, delivery, cocina o atención. El cliente podrá postular por WhatsApp.</p>
            <form id="jobForm"><div class="row"><label>Puesto<input name="title" required maxlength="120" placeholder="Ej: Se necesita lavandero"></label><label>Descripción<input name="description" maxlength="500" placeholder="Horario, experiencia o requisitos"></label></div><button class="btn-main" type="submit">Publicar vacante</button><div id="jobMsg" class="msg"></div></form>
            <div id="adminJobsList" class="list" style="margin-top:14px"></div>
        </section>

        <section id="sec-products" class="panel">
            <h2>Gestion de Productos</h2>
            <p class="section-subtitle">Administra catalogo, estado visible y stock interno sin mostrar existencias al cliente final.</p>
            <div class="products-layout">
            <div class="products-form-col">
            <form id="productForm">
                <input type="hidden" name="product_id">
                <div class="product-form-grid">
                <div class="row">
                    <div>
                        <label>Nombre</label>
                        <input name="name" required>
                    </div>
                    <div>
                        <label>Precio (S/)</label>
                        <input name="price" type="number" min="0" step="0.10" required>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Categoria</label>
                        <select name="category" id="categorySelect" required></select>
                    </div>
                    <div>
                        <label>Nueva categoria (opcional)</label>
                        <input id="newCategoryInput" placeholder="Ej: postres">
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Stock interno</label>
                        <input name="stock" type="number" min="0" step="1" value="0" required>
                        <div class="helper-text">Solo visible en admin. Si llega a 0 el cliente vera "Platillo agotado".</div>
                    </div>
                    <div class="upload-box">
                        <label>Imagen del producto</label>
                        <input id="productImageInput" name="image" type="file" accept="image/*">
                        <div class="helper-text">El administrador solo sube la imagen y el sistema conserva la ruta correcta en la base de datos.</div>
                        <img id="productImagePreview" class="upload-preview" alt="Vista previa del producto">
                        <div class="upload-actions">
                            <button type="button" id="removeProductImageBtn">Quitar imagen</button>
                        </div>
                    </div>
                </div>
                <label>Descripcion</label>
                <textarea name="description" rows="3"></textarea>
                <div class="toggle-row">
                    <label class="toggle-main"><input type="checkbox" name="is_available" checked> Visible en la tienda</label>
                    <span id="productStatusText" class="toggle-status-text">Producto activo</span>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="submit" class="btn-main">Guardar producto</button>
                    <button type="button" id="cancelEditBtn">Cancelar edicion</button>
                </div>
                <div id="productMsg" class="msg"></div>
                </div>
            </form>
            </div>

            <div class="products-list-col">
            <h3>Lista de productos</h3>
            <p class="section-subtitle">Aqui ves activos, inactivos y agotados, con el stock real para control interno.</p>
            <div id="productsList" class="list"></div>
            </div>
            </div>
        </section>

        <section id="sec-orders" class="panel">
            <h2>Pedidos recientes</h2>
            <div class="row">
                <div>
                    <label>Estado pedido</label>
                    <select id="filterStatus">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="confirmed">Confirmado</option>
                        <option value="preparing">Preparando</option>
                        <option value="on_the_way">En camino</option>
                        <option value="delivered">Entregado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
                <div>
                    <label>Metodo de pago</label>
                    <select id="filterPaymentMethod">
                        <option value="">Todos</option>
                        <option value="izipay">Pago con tarjeta</option>
                        <option value="yape">Yape</option>
                        <option value="cod">Contraentrega</option>
                    </select>
                </div>
                <div>
                    <label>Estado pago</label>
                    <select id="filterPaymentStatus">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="reported">Reportado</option>
                        <option value="verified">Verificado</option>
                        <option value="rejected">Rechazado</option>
                    </select>
                </div>
                <div>
                    <label>Desde</label>
                    <input id="filterDateFrom" type="date">
                </div>
                <div>
                    <label>Hasta</label>
                    <input id="filterDateTo" type="date">
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-bottom:8px;">
                <button id="applyFiltersBtn">Aplicar filtros</button>
                <button id="clearFiltersBtn">Limpiar</button>
                <button id="exportCsvBtn" class="btn-main">Exportar Excel</button>
            </div>
            <div id="ordersList" class="list"></div>
            <div id="orderActionsMsg" class="msg"></div>
        </section>

        <section id="sec-manual-sale" class="panel" style="grid-column: 1 / -1;">
            <h2>Registrar venta manual</h2>
            <p class="section-subtitle">Para ventas cobradas en el mostrador (efectivo, tarjeta fisica o Yape). No pasa por Izipay y no pide datos personales de contacto del cliente.</p>

            <div class="row">
                <div>
                    <label>Producto</label>
                    <select id="manualSaleProductSelect"></select>
                </div>
                <div>
                    <label>Cantidad</label>
                    <input id="manualSaleQty" type="number" min="1" value="1">
                </div>
                <div style="display:flex; align-items:flex-end;">
                    <button type="button" id="manualSaleAddItemBtn">Agregar producto</button>
                </div>
            </div>

            <div id="manualSaleItemsList" class="list"></div>
            <div style="font-weight:900; margin:8px 0 14px;">Total: S/ <span id="manualSaleTotal">0.00</span></div>

            <div>
                <label>Nombre (para el comprobante, opcional)</label>
                <input id="manualSaleCustomerName" maxlength="120" placeholder="Ej: Juan Perez">
            </div>

            <div class="row">
                <div>
                    <label>Metodo de pago</label>
                    <select id="manualSalePaymentMethod">
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="yape">Yape</option>
                    </select>
                </div>
                <div>
                    <label>Comprobante</label>
                    <select id="manualSaleReceiptType">
                        <option value="">Comprobante simple (sin DNI/RUC)</option>
                        <option value="boleta">Boleta con DNI</option>
                        <option value="factura">Factura con RUC</option>
                    </select>
                </div>
            </div>

            <div id="manualSaleDocumentWrap" class="row" style="display:none;">
                <div>
                    <label id="manualSaleDocumentLabel">DNI del cliente</label>
                    <div style="display:flex; gap:6px;">
                        <input id="manualSaleDocumentNumber" inputmode="numeric">
                        <button type="button" id="manualSaleLookupBtn">Consultar</button>
                    </div>
                </div>
                <div>
                    <label id="manualSaleNameLabel">Nombre del cliente</label>
                    <input id="manualSaleBillingName" readonly>
                </div>
            </div>

            <div id="manualSaleDeliveryWrap" style="display:none;">
                <label>Entrega del comprobante</label>
                <div style="display:flex; gap:16px; margin:6px 0;">
                    <label><input type="radio" name="manualSaleDelivery" value="persona" checked> En persona</label>
                    <label><input type="radio" name="manualSaleDelivery" value="correo"> Por correo</label>
                </div>
                <div id="manualSaleEmailWrap" style="display:none;">
                    <label>Correo destino</label>
                    <input id="manualSaleEmail" type="email">
                </div>
            </div>

            <div>
                <label>Nota (opcional)</label>
                <input id="manualSaleNote" maxlength="255">
            </div>

            <button type="button" id="manualSaleSubmitBtn" class="btn-main" style="margin-top:10px;">Registrar venta</button>
            <div id="manualSaleMsg" class="msg"></div>

            <div id="manualSaleResult" style="display:none; margin-top:10px;">
                <div id="manualSaleResultInfo" class="msg"></div>
                <div style="display:flex; gap:8px; margin-top:8px; flex-wrap:wrap;">
                    <button type="button" id="manualSaleDownloadBtn">Descargar comprobante simple</button>
                    <button type="button" id="manualSaleSendEinvoiceBtn" style="display:none;">Emitir comprobante SUNAT</button>
                    <button type="button" id="manualSaleDownloadOfficialBtn" style="display:none;">Descargar comprobante oficial (SUNAT)</button>
                    <button type="button" id="manualSaleSendEmailBtn" style="display:none;">Enviar por correo</button>
                </div>
                <div id="manualSaleOfficialHint" class="helper-text" style="display:none; margin-top:6px;">Este es el documento real emitido con Nubefact. Descargalo para entregarlo en persona.</div>
            </div>
        </section>

        <section id="sec-cash-closure" class="panel" style="grid-column: 1 / -1;">
            <h2>Cierre de caja</h2>
            <p class="section-subtitle">Resume ventas del dia, separa efectivo contra pagos digitales y guarda el cierre operativo para auditoria.</p>
            <div class="row" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <section class="panel" style="padding:16px;">
                    <h3>Registrar cierre</h3>
                    <form id="cashClosureForm">
                        <label>Fecha operativa</label>
                        <input name="business_date" id="cashClosureDate" type="date" required>
                        <label>Efectivo contado en caja (S/)</label>
                        <input name="declared_cash" type="number" min="0" step="0.01" required>
                        <label>Observaciones (opcional)</label>
                        <textarea name="notes" rows="3" maxlength="500" placeholder="Ej: faltante por vuelto, caja inicial, observaciones del turno"></textarea>
                        <div class="muted">Politica: solo 1 cierre por dia y se habilita desde las 11:00 PM hora Peru Lima.</div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <button type="button" id="refreshCashSummaryBtn">Actualizar resumen</button>
                            <button type="button" id="exportCashClosuresBtn">Exportar Excel</button>
                            <button type="submit" class="btn-main">Guardar cierre</button>
                        </div>
                        <div id="cashClosureMsg" class="msg"></div>
                    </form>
                </section>

                <section class="panel" style="padding:16px;">
                    <h3>Resumen del dia</h3>
                    <div id="cashClosureSummary" class="list">
                        <div class="card">Selecciona una fecha para ver el resumen de caja.</div>
                    </div>
                </section>
            </div>

            <hr style="border-color:#ffd7bd; margin:18px 0;">
            <h3>Ultimos cierres guardados</h3>
            <div id="cashClosureHistory" class="list">
                <div class="card">Aun no hay cierres registrados.</div>
            </div>
        </section>

        <section id="sec-users" class="panel" style="grid-column: 1 / -1;">
            <h2>Gestion de cuentas</h2>
            <p class="muted">Controla cuentas registradas, tiempo de creación y estado activo.</p>
            <div id="usersList" class="list"></div>
        </section>
    </div>
</div>

<div id="proofModal" class="proof-modal">
    <div class="proof-modal-card">
        <div class="proof-modal-head">
            <h3 id="proofModalTitle" class="proof-modal-title">Comprobante</h3>
            <button id="proofModalCloseBtn" type="button">Cerrar</button>
        </div>
        <div class="proof-modal-body">
            <div id="proofModalMeta" class="muted"></div>
            <div id="proofModalContent"></div>
        </div>
    </div>
</div>

<div id="orderActionPanel" class="side-panel-overlay">
    <aside class="side-panel">
        <div class="side-panel-head">
            <h3 id="orderActionPanelTitle" class="proof-modal-title">Accion de pedido</h3>
            <button id="orderActionPanelCloseBtn" type="button">Cerrar</button>
        </div>
        <div class="side-panel-body">
            <div id="orderActionPanelStatus">
                <form id="statusForm">
                    <label>Pedido ID</label>
                    <input name="order_id" required>
                    <label>Nuevo estado</label>
                    <select name="status" required>
                        <option value="pending">Pendiente</option>
                        <option value="confirmed">Confirmado</option>
                        <option value="preparing">Preparando</option>
                        <option value="on_the_way">En camino</option>
                        <option value="delivered">Entregado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                    <label>Nota (opcional)</label>
                    <input name="note">
                    <button type="submit" class="btn-main">Actualizar estado</button>
                    <div id="statusMsg" class="msg"></div>
                </form>
            </div>
            <div id="orderActionPanelPreview" style="display:none;">
                <pre id="orderActionPanelPreviewContent" class="side-panel-preview"></pre>
            </div>
        </div>
    </aside>
</div>

<div id="orderDetailModal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; padding:18px; background:rgba(24,15,8,.55);">
    <div style="width:min(94vw,480px); max-height:86vh; overflow-y:auto; background:#FFFDF9; border:1.5px solid #FFB37A; border-radius:26px; box-shadow:0 30px 60px rgba(255,111,31,.28); padding:26px 24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px;">
            <strong id="orderDetailModalTitle" style="font-size:18px; color:#25170f; line-height:1.3;">Ver pedido</strong>
            <button id="orderDetailModalCloseBtn" type="button" class="pill-btn" style="flex-shrink:0; padding:8px 14px; background:#FFF1E3; color:#7b3d11; border-color:#FFD9B0;">Cerrar</button>
        </div>
        <div id="orderDetailModalContent"></div>
    </div>
</div>

<div id="adminOrderToast" style="display:none; position:fixed; right:18px; bottom:18px; z-index:9998; width:min(400px, calc(100vw - 36px));">
    <div style="background:#FFFDF9; border:1.5px solid #FFB37A; border-radius:22px; box-shadow: 0 26px 60px rgba(255,111,31,.24); overflow:hidden;">
        <div style="padding:12px 14px; background:#FFF1E3; border-bottom:1px solid #FFE4D2; display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <strong id="adminOrderToastTitle" style="font-size:13px; line-height:1.2; color:#7b3d11;">Nuevo pedido</strong>
            <button id="adminOrderToastCloseBtn" type="button" class="pill-btn" style="padding:8px 10px; background:#FF6F1F; color:#fff; border-color:#FF6F1F;">X</button>
        </div>
        <div style="padding:12px 14px;">
            <div id="adminOrderToastMessage" style="color:#25170f; line-height:1.45; font-weight:800;"></div>
            <div id="adminOrderToastBody" style="margin-top:8px; color:#68432e;"></div>
        </div>
    </div>
</div>

<div id="adminConfirmOverlay" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; padding:18px; background:rgba(24,15,8,.55);">
    <div style="width:min(92vw,380px); background:#FFFDF9; border:1.5px solid #FFB37A; border-radius:26px; box-shadow:0 30px 60px rgba(255,111,31,.28); padding:26px 24px; text-align:center;">
        <div style="width:44px; height:44px; border-radius:14px; background:#FFE4D2; color:#FF6F1F; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:900; margin:0 auto 14px;">!</div>
        <strong id="adminConfirmTitle" style="display:block; font-size:18px; color:#25170f; margin-bottom:8px;">Confirmar accion</strong>
        <p id="adminConfirmMessage" style="margin:0 0 18px; color:#68432e; font-size:14.5px; line-height:1.5;"></p>
        <div style="display:flex; gap:10px;">
            <button id="adminConfirmCancelBtn" type="button" class="pill-btn" style="flex:1; padding:12px; background:#FFF1E3; color:#7b3d11; border-color:#FFD9B0;">Cancelar</button>
            <button id="adminConfirmOkBtn" type="button" class="pill-btn" style="flex:1; padding:12px; background:#FF6F1F; color:#fff; border-color:#FF6F1F;">Aceptar</button>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
(() => {
    const apiBase = @json(config('app.api_base_url'));
    const base = (apiBase || '').toString().replace(/\/+$/, '');

    const originalFetch = window.fetch.bind(window);
    window.fetch = (input, init) => {
        if (typeof input === 'string' && input.startsWith('/api/')) {
            if (base) input = `${base}${input}`;
            const headers = new Headers((init && init.headers) ? init.headers : undefined);
            headers.set('Accept', 'application/json');
            init = { ...(init || {}), headers };
        }
        return originalFetch(input, init);
    };
})();

function showAdminConfirm(message, title = 'Confirmar accion') {
    return new Promise((resolve) => {
        const overlay = document.getElementById('adminConfirmOverlay');
        if (!overlay) { resolve(window.confirm(message)); return; }
        document.getElementById('adminConfirmTitle').textContent = title;
        document.getElementById('adminConfirmMessage').textContent = message;
        overlay.style.display = 'flex';
        const okBtn = document.getElementById('adminConfirmOkBtn');
        const cancelBtn = document.getElementById('adminConfirmCancelBtn');
        const cleanup = (result) => {
            overlay.style.display = 'none';
            okBtn.removeEventListener('click', onOk);
            cancelBtn.removeEventListener('click', onCancel);
            resolve(result);
        };
        const onOk = () => cleanup(true);
        const onCancel = () => cleanup(false);
        okBtn.addEventListener('click', onOk);
        cancelBtn.addEventListener('click', onCancel);
    });
}

const denyBox = document.getElementById('denyBox');
const readOnlyBanner = document.getElementById('readOnlyBanner');
const adminContent = document.getElementById('adminContent');
const adminUserLabel = document.getElementById('adminUserLabel');
const adminLogoutBtn = document.getElementById('adminLogoutBtn');
const adminMenu = document.getElementById('adminMenu');
const adminMenuTabs = Array.from(document.querySelectorAll('#adminMenu .menu-tab'));
const adminSections = [
    document.getElementById('sec-dashboard'),
    document.getElementById('sec-offers'),
    document.getElementById('sec-jobs'),
    document.getElementById('sec-products'),
    document.getElementById('sec-orders'),
    document.getElementById('sec-manual-sale'),
    document.getElementById('sec-cash-closure'),
    document.getElementById('sec-users'),
].filter(Boolean);

function showAdminTab(targetId) {
    adminSections.forEach(section => {
        section.classList.toggle('tab-hidden', section.id !== targetId);
    });
    adminMenuTabs.forEach(tab => {
        tab.classList.toggle('active', tab.dataset.target === targetId);
    });
    if (adminContent) adminContent.classList.add('tab-mode');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function loadAdminTabData(targetId) {
    if (!canUseAdmin()) return;

    try {
        if (targetId === 'sec-dashboard') {
            await Promise.allSettled([
                fetchProducts(),
                fetchOrders(),
                fetchCashClosureSummary(),
                fetchUsers(),
            ]);
            return;
        }

        if (targetId === 'sec-products') {
            await fetchProducts();
            return;
        }

        if (targetId === 'sec-orders') {
            await fetchOrders();
            return;
        }

        if (targetId === 'sec-manual-sale') {
            await fetchProducts();
            return;
        }

        if (targetId === 'sec-offers') {
            await Promise.allSettled([fetchProducts(), fetchPromotions()]);
            return;
        }

        if (targetId === 'sec-cash-closure') {
            await Promise.allSettled([
                fetchCashClosureSummary(),
                fetchCashClosureHistory(),
            ]);
            return;
        }

        if (targetId === 'sec-users') {
            await fetchUsers();
            return;
        }

        if (targetId === 'sec-jobs') {
            await fetchJobs();
        }
    } catch (error) {
        console.error('No se pudo cargar la pestaña admin', targetId, error);
    }
}

const productForm = document.getElementById('productForm');
const productMsg = document.getElementById('productMsg');
const categorySelect = document.getElementById('categorySelect');
const newCategoryInput = document.getElementById('newCategoryInput');
const cancelEditBtn = document.getElementById('cancelEditBtn');
const productsList = document.getElementById('productsList');
const offerProductSelect = document.getElementById('offerProductSelect');
const offerDiscountPercent = document.getElementById('offerDiscountPercent');
const offerPromoPrice = document.getElementById('offerPromoPrice');
const offerPriceHelp = document.getElementById('offerPriceHelp');
const productStatusText = document.getElementById('productStatusText');
const productImageInput = document.getElementById('productImageInput');
const productImagePreview = document.getElementById('productImagePreview');
const removeProductImageBtn = document.getElementById('removeProductImageBtn');

const offerForm = document.getElementById('offerForm');
const jobForm = document.getElementById('jobForm');
const jobMsg = document.getElementById('jobMsg');
const adminJobsList = document.getElementById('adminJobsList');
const offerMsg = document.getElementById('offerMsg');
const inactiveDaysInput = document.getElementById('inactiveDaysInput');
const abandonedHoursInput = document.getElementById('abandonedHoursInput');
const runRecoveryCampaignBtn = document.getElementById('runRecoveryCampaignBtn');
const recoveryCampaignMsg = document.getElementById('recoveryCampaignMsg');
const offerImageInput = document.getElementById('offerImageInput');
const offerImagePreview = document.getElementById('offerImagePreview');

const statusForm = document.getElementById('statusForm');
const statusMsg = document.getElementById('statusMsg');
const orderActionsMsg = document.getElementById('orderActionsMsg');
const ordersList = document.getElementById('ordersList');
const filterStatus = document.getElementById('filterStatus');
const filterPaymentMethod = document.getElementById('filterPaymentMethod');
const filterPaymentStatus = document.getElementById('filterPaymentStatus');
const filterDateFrom = document.getElementById('filterDateFrom');
const filterDateTo = document.getElementById('filterDateTo');
const applyFiltersBtn = document.getElementById('applyFiltersBtn');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');
const exportCsvBtn = document.getElementById('exportCsvBtn');
const cashClosureForm = document.getElementById('cashClosureForm');
const cashClosureDate = document.getElementById('cashClosureDate');
const cashClosureMsg = document.getElementById('cashClosureMsg');
const cashClosureSummary = document.getElementById('cashClosureSummary');
const cashClosureHistory = document.getElementById('cashClosureHistory');
const refreshCashSummaryBtn = document.getElementById('refreshCashSummaryBtn');
const exportCashClosuresBtn = document.getElementById('exportCashClosuresBtn');
const usersList = document.getElementById('usersList');
const salesDashboard = document.getElementById('salesDashboard');
const orderActionPanel = document.getElementById('orderActionPanel');
const orderActionPanelTitle = document.getElementById('orderActionPanelTitle');
const orderActionPanelCloseBtn = document.getElementById('orderActionPanelCloseBtn');
const orderActionPanelStatus = document.getElementById('orderActionPanelStatus');
const orderActionPanelPreview = document.getElementById('orderActionPanelPreview');
const orderActionPanelPreviewContent = document.getElementById('orderActionPanelPreviewContent');
const orderDetailModal = document.getElementById('orderDetailModal');
const orderDetailModalTitle = document.getElementById('orderDetailModalTitle');
const orderDetailModalContent = document.getElementById('orderDetailModalContent');
orderDetailModal.addEventListener('click', (event) => {
    if (event.target === orderDetailModal) orderDetailModal.style.display = 'none';
});
document.getElementById('orderDetailModalCloseBtn').addEventListener('click', () => {
    orderDetailModal.style.display = 'none';
});

function openOrderActionPanel(title) {
    orderActionPanelTitle.textContent = title;
    orderActionPanel.classList.add('open');
    orderActionPanel.scrollTop = 0;
    orderActionPanel.querySelector('.side-panel').scrollTop = 0;
}

function closeOrderActionPanel() {
    orderActionPanel.classList.remove('open');
}

orderActionPanelCloseBtn.addEventListener('click', closeOrderActionPanel);
orderActionPanel.addEventListener('click', (event) => {
    if (event.target === orderActionPanel) closeOrderActionPanel();
});

const proofModal = document.getElementById('proofModal');
const proofModalTitle = document.getElementById('proofModalTitle');
const proofModalMeta = document.getElementById('proofModalMeta');
const proofModalContent = document.getElementById('proofModalContent');
const proofModalCloseBtn = document.getElementById('proofModalCloseBtn');
const adminOrderToast = document.getElementById('adminOrderToast');
const adminOrderToastTitle = document.getElementById('adminOrderToastTitle');
const adminOrderToastMessage = document.getElementById('adminOrderToastMessage');
const adminOrderToastBody = document.getElementById('adminOrderToastBody');
const adminOrderToastCloseBtn = document.getElementById('adminOrderToastCloseBtn');
const adminUnreadBtn = document.getElementById('adminUnreadBtn');
const adminUnreadCount = document.getElementById('adminUnreadCount');
const dashboardOrdersMetric = document.getElementById('dashboardOrdersMetric');
const dashboardPendingPaymentsMetric = document.getElementById('dashboardPendingPaymentsMetric');
const dashboardProductsMetric = document.getElementById('dashboardProductsMetric');
const dashboardUsersMetric = document.getElementById('dashboardUsersMetric');

const ADMIN_TIMEOUT_MS = 30 * 60 * 1000;
const BASE_CATEGORIES = ['pollos', 'parrillas', 'bebidas'];
let productsCache = [];
let refreshTimer = null;
let productImageRemoved = false;
let adminUnreadOrders = 0;

const STATUS_ES = {
    pending: 'Pendiente',
    confirmed: 'Confirmado',
    preparing: 'Preparando',
    on_the_way: 'En camino',
    delivered: 'Entregado',
    cancelled: 'Cancelado',
};

const PAYMENT_STATUS_ES = {
    pending: 'Pendiente',
    reported: 'Reportado',
    verified: 'Verificado',
    rejected: 'Rechazado',
};

function getToken() { return localStorage.getItem('ed_token'); }
function getUser() {
    const raw = localStorage.getItem('ed_user');
    if (!raw) return null;
    try { return JSON.parse(raw); } catch { return null; }
}

function setUploadPreview(previewEl, value) {
    if (!previewEl) return;
    if (!value) {
        previewEl.removeAttribute('src');
        previewEl.classList.remove('visible');
        return;
    }
    previewEl.src = value;
    previewEl.classList.add('visible');
}

function bindImagePreview(inputEl, previewEl) {
    if (!inputEl || !previewEl) return;
    inputEl.addEventListener('change', () => {
        const file = inputEl.files?.[0];
        if (!file) {
            if (!previewEl.dataset.persisted) setUploadPreview(previewEl, '');
            return;
        }
        previewEl.dataset.persisted = '';
        setUploadPreview(previewEl, URL.createObjectURL(file));
    });
}

async function sendOffer(formData, targetValue) {
    const token = getToken();
    offerMsg.textContent = 'Enviando...';
    offerMsg.classList.remove('success');
    offerMsg.classList.remove('error');

    const res = await fetch('/api/v1/admin/notifications/offers', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: formData,
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        offerMsg.textContent = data?.message || 'No se pudo enviar la promo.';
        offerMsg.classList.add('error');
        return;
    }

    const openAppStatus = offerForm.send_realtime?.checked
        ? (data?.broadcast?.ok === true
            ? ` App abierta: OK`
            : ` App abierta: ${data?.broadcast?.message || 'ERROR'}`)
        : '';
    const pushStatus = data?.push?.ok === true
        ? ` App cerrada: OK`
        : (data?.push?.ok === false ? ` App cerrada: ${data.push.message || 'ERROR'}` : '');
    const emailStatus = data?.email ? ` Correos enviados: ${data.email.sent || 0}` : '';
    offerMsg.textContent = `Promo enviada.${openAppStatus}${pushStatus}${emailStatus}`;
    offerMsg.classList.add('success');
    offerForm.reset();
    const offerSendAllEl = document.getElementById('offerSendAll');
    if (offerSendAllEl) {
        offerSendAllEl.checked = Boolean(
            offerForm.send_realtime?.checked && offerForm.send_push?.checked && offerForm.send_email?.checked
        );
    }
    setUploadPreview(offerImagePreview, '');
    const offerEndsAtWrapEl = document.getElementById('offerEndsAtWrap');
    if (offerEndsAtWrapEl) offerEndsAtWrapEl.style.display = 'none';
    fetchPromotions();
}

const promotionStatusLabels = {
    programada: { label: 'Programada', color: '#946200' },
    activa: { label: 'Activa', color: '#166534' },
    vencida: { label: 'Vencida', color: '#6b7280' },
    desactivada: { label: 'Desactivada', color: '#b42318' },
};

function formatPromoDate(iso) {
    if (!iso) return 'Sin fecha';
    return new Date(iso).toLocaleString('es-PE', { dateStyle: 'short', timeStyle: 'short' });
}

async function fetchPromotions() {
    const list = document.getElementById('promotionsList');
    if (!list) return;
    const token = getToken();
    try {
        const res = await fetch('/api/v1/admin/promotions', {
            headers: { 'Authorization': `Bearer ${token}` },
        });
        const data = await res.json().catch(() => ({}));
        const offers = Array.isArray(data?.data) ? data.data : [];
        if (!offers.length) {
            list.innerHTML = '<div class="muted">Todavia no has creado ninguna promocion.</div>';
            return;
        }
        list.innerHTML = offers.map(offer => {
            const statusInfo = promotionStatusLabels[offer.status] || { label: offer.status, color: '#6b7280' };
            const canCut = offer.status === 'activa' || offer.status === 'programada';
            return `<div class="list-row" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;border-bottom:1px solid #f0d5bd;padding:10px 0;">
                <div>
                    <strong>${escapeHtml(offer.title)}</strong> · ${escapeHtml(offer.product_name || 'Producto eliminado')}
                    <div class="muted" style="font-size:12px;">S/ ${money(offer.original_price)} &rarr; S/ ${money(offer.promo_price)} (-${Number(offer.discount_percent).toFixed(0)}%) · ${offer.orders_count} pedidos</div>
                    <div class="muted" style="font-size:12px;">Inicio: ${formatPromoDate(offer.starts_at)} · Fin: ${formatPromoDate(offer.ends_at)}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="tag" style="background:${statusInfo.color};color:#fff;">${statusInfo.label}</span>
                    ${canCut ? `<button type="button" class="btn-secondary" onclick="cutPromotionShort(${offer.id})">Cortar ahora</button>` : ''}
                </div>
            </div>`;
        }).join('');
    } catch (error) {
        list.innerHTML = '<div class="muted">No se pudo cargar la lista de promociones.</div>';
    }
}

async function cutPromotionShort(offerId) {
    if (!confirm('¿Cortar esta promocion ahora? El precio volvera a la normal de inmediato en web, app y correo.')) return;
    const token = getToken();
    await fetch(`/api/v1/admin/promotions/${offerId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({ end_now: true }),
    });
    fetchPromotions();
}

async function runRecoveryCampaigns() {
    const token = getToken();
    recoveryCampaignMsg.textContent = 'Ejecutando campanas...';
    const res = await fetch('/api/v1/admin/notifications/recovery-campaigns', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
            inactive_days: Number(inactiveDaysInput?.value || 5),
            abandoned_hours: Number(abandonedHoursInput?.value || 3),
            send_push: true,
        }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        recoveryCampaignMsg.textContent = data?.message || 'No se pudieron ejecutar las campanas.';
        return;
    }
    recoveryCampaignMsg.textContent = `OK. Inactivos: ${data.inactive?.sent || 0} correos, ${data.inactive?.pushSent || 0} push. Carrito: ${data.abandoned?.sent || 0} correos, ${data.abandoned?.pushSent || 0} push.`;
}

function parseSession() {
    const raw = localStorage.getItem('ed_session');
    if (!raw) return null;
    try { return JSON.parse(raw); } catch { return null; }
}

function saveSession(session) {
    localStorage.setItem('ed_session', JSON.stringify(session));
}

function touchAdminSession() {
    const session = parseSession();
    if (!session || (session.role !== 'admin' && session.role !== 'reviewer')) return;
    session.lastActivity = Date.now();
    session.expiresAt = Date.now() + ADMIN_TIMEOUT_MS;
    saveSession(session);
}

function clearAuth() {
    [
        'ed_token',
        'ed_user',
        'ed_session',
        'ed_cart',
        'ed_last_tracking',
        'ed_recent_trackings',
        'ed_order_statuses',
        'ed_pollia_checkout_prefill_v1',
        'ed_pollia_pending_cart_v1',
        'ed_pollia_guest_session',
        'ed_checkout_data',
        'ed_customer_data',
        'ed_delivery_data',
        'ed_payment_method',
        'ed_payment_draft',
        'ed_order_draft',
        'ed_izipay_data',
        'ed_pending_order',
        'ed_order_alert_count',
    ].forEach(key => localStorage.removeItem(key));
    sessionStorage.removeItem('ed_receipt_preview');
    sessionStorage.removeItem('ed_checkout_draft');
}

function statusEs(code) {
    return STATUS_ES[code] || code || 'n/a';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function paymentStatusEs(code) {
    return PAYMENT_STATUS_ES[code] || code || 'n/a';
}

function paymentMethodEs(code) {
    return String(code || '').toLowerCase() === 'izipay' ? 'Pago con tarjeta' : String(code || '').toLowerCase() === 'yape' ? 'Yape' : String(code || '').toLowerCase() === 'cod' ? 'Pago contraentrega' : code || 'n/a';
}

function paymentStatusClass(code) {
    const normalized = String(code || '').toLowerCase();
    return {
        pending: 'payment-pending',
        reported: 'payment-reported',
        verified: 'payment-verified',
        rejected: 'payment-rejected',
    }[normalized] || '';
}

function isImageProof(path) {
    return /\.(jpg|jpeg|png|webp)$/i.test(String(path || ''));
}

function isPdfProof(path) {
    return /\.pdf$/i.test(String(path || ''));
}

function closeProofModal() {
    proofModal.style.display = 'none';
    proofModalMeta.textContent = '';
    proofModalContent.innerHTML = '';
}

function hideAdminOrderToast() {
    if (adminOrderToast) adminOrderToast.style.display = 'none';
}

function renderAdminUnread() {
    if (adminUnreadCount) adminUnreadCount.textContent = String(adminUnreadOrders);
}

function syncDashboardMetrics() {
    if (dashboardOrdersMetric) {
        dashboardOrdersMetric.textContent = String(Array.isArray(window.__adminOrdersCache) ? window.__adminOrdersCache.length : 0);
    }
    if (dashboardPendingPaymentsMetric) {
        const pending = Array.isArray(window.__adminOrdersCache)
            ? window.__adminOrdersCache.filter(order => ['pending', 'reported'].includes(String(order.payment_status || ''))).length
            : 0;
        dashboardPendingPaymentsMetric.textContent = String(pending);
    }
    if (dashboardProductsMetric) {
        dashboardProductsMetric.textContent = String(Array.isArray(productsCache) ? productsCache.filter(product => Boolean(product.is_available)).length : 0);
    }
    if (dashboardUsersMetric) {
        dashboardUsersMetric.textContent = String(Number(window.__adminUsersCount || 0));
    }
}

function playAdminSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'triangle';
        osc.frequency.value = 740;
        gain.gain.value = 0.04;
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.22);
    } catch {}
}

let productToastTimer = null;
function showProductToast(message) {
    if (!adminOrderToast) return;
    adminOrderToastTitle.textContent = 'Catalogo';
    adminOrderToastMessage.textContent = message;
    adminOrderToastBody.textContent = '';
    adminOrderToast.style.display = 'block';
    clearTimeout(productToastTimer);
    productToastTimer = setTimeout(hideAdminOrderToast, 3500);
}

function showAdminOrderToast(payload) {
    if (!adminOrderToast) return;
    adminOrderToastTitle.textContent = payload?.title || 'Nuevo pedido';
    adminOrderToastMessage.textContent = payload?.message || 'Se registro un nuevo pedido.';
    adminOrderToastBody.textContent = payload?.body || '';
    adminOrderToast.style.display = 'block';
    adminUnreadOrders += 1;
    renderAdminUnread();
    playAdminSound();
}

function openProofModal(order) {
    if (!order?.payment_proof_path) return;

    proofModalTitle.textContent = `Comprobante ${order.tracking_code}`;
    proofModalMeta.textContent = `Pedido ${order.id} | ${order.customer_name} | ${paymentMethodEs(order.payment_method)} | ${paymentStatusEs(order.payment_status)}`;

    if (isImageProof(order.payment_proof_path)) {
        proofModalContent.innerHTML = `
            <img src="${order.payment_proof_path}" alt="Comprobante ${order.tracking_code}" class="proof-modal-image">
            <div><a href="${order.payment_proof_path}" target="_blank">Abrir archivo en otra pestaña</a></div>
        `;
    } else if (isPdfProof(order.payment_proof_path)) {
        proofModalContent.innerHTML = `
            <iframe src="${order.payment_proof_path}" class="proof-modal-frame"></iframe>
            <div><a href="${order.payment_proof_path}" target="_blank">Abrir PDF en otra pestaña</a></div>
        `;
    } else {
        proofModalContent.innerHTML = `
            <div class="muted">No hay vista previa integrada para este archivo.</div>
            <div><a href="${order.payment_proof_path}" target="_blank">Abrir archivo</a></div>
        `;
    }

    proofModal.style.display = 'flex';
}

function money(value) {
    return `S/ ${Number(value || 0).toFixed(2)}`;
}

function todayDateValue() {
    const now = new Date();
    const local = new Date(now.getTime() - (now.getTimezoneOffset() * 60000));
    return local.toISOString().slice(0, 10);
}

function canUseAdmin() {
    const user = getUser();
    return Boolean(user && (user.role === 'admin' || user.role === 'reviewer') && getToken());
}

function isReadOnlyAdmin() {
    const user = getUser();
    return Boolean(user && user.role === 'reviewer');
}

function upsertCategoryOptions() {
    const categories = new Set(BASE_CATEGORIES);
    productsCache.forEach(product => {
        if (product.category) categories.add(String(product.category).toLowerCase());
    });

    const selected = categorySelect.value;
    categorySelect.innerHTML = [...categories]
        .sort()
        .map(category => `<option value="${category}">${category}</option>`)
        .join('');

    if (selected && [...categories].includes(selected)) categorySelect.value = selected;
}

function clearProductForm() {
    productForm.reset();
    productForm.product_id.value = '';
    productForm.is_available.checked = true;
    productForm.stock.value = 0;
    newCategoryInput.value = '';
    productImageRemoved = false;
    if (productImagePreview) productImagePreview.dataset.persisted = '';
    setUploadPreview(productImagePreview, '');
    syncProductAvailabilityLabel();
}

function productStatusMeta(product) {
    if (!product.is_available) {
        return { text: 'Producto inactivo', className: 'inactive' };
    }

    if (Number(product.stock || 0) <= 0) {
        return { text: 'Platillo agotado', className: 'sold-out' };
    }

    return { text: 'Producto activo', className: 'active' };
}

function productCard(product) {
    const image = product.image_url || '/images/products/default.svg';
    const status = productStatusMeta(product);
    return `
        <article class="card">
            <div class="product-card-header">
                <div>
                    <h4 class="product-card-title">${product.name}</h4>
                    <div class="product-card-price">S/ ${Number(product.price).toFixed(2)}</div>
                </div>
                <span class="tag">${product.category || 'general'}</span>
            </div>
            <div class="img-shell">
                <img src="${image}" alt="${product.name}" class="img-thumb">
            </div>
            <div class="product-chip-row">
                <span class="tag ${status.className}">${status.text}</span>
                <span class="tag stock">Stock: ${Number(product.stock || 0)}</span>
                <span class="tag">ID ${product.id}</span>
            </div>
            <div class="muted">${product.description || 'Sin descripcion'}</div>
            <div class="product-card-actions">
                <button data-edit="${product.id}">Editar</button>
                <button data-delete="${product.id}" style="border-color:#ffc1b5; color:#a53216;">Eliminar</button>
            </div>
        </article>`;
}

function formatBucketLabel(date, mode) {
    if (mode === 'day') {
        return date.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit' });
    }
    if (mode === 'month') {
        return date.toLocaleDateString('es-PE', { month: 'short', year: '2-digit' }).replace('.', '');
    }
    return String(date.getFullYear());
}

function buildBuckets(orders, mode) {
    const map = new Map();
    orders.forEach(order => {
        const sourceDate = new Date(order.created_at);
        if (Number.isNaN(sourceDate.getTime())) return;
        const key = mode === 'day'
            ? sourceDate.toISOString().slice(0, 10)
            : mode === 'month'
                ? `${sourceDate.getFullYear()}-${String(sourceDate.getMonth() + 1).padStart(2, '0')}`
                : `${sourceDate.getFullYear()}`;
        const current = map.get(key) || { total: 0, count: 0, date: sourceDate };
        current.total += Number(order.total_amount || 0);
        current.count += 1;
        current.date = sourceDate;
        map.set(key, current);
    });

    return [...map.entries()]
        .sort((a, b) => a[0].localeCompare(b[0]))
        .slice(-6)
        .map(([, value]) => ({
            label: formatBucketLabel(value.date, mode),
            total: value.total,
            count: value.count,
        }));
}

function renderChart(title, rows) {
    if (!rows.length) {
        return `
            <div class="chart-card">
                <div class="chart-head">
                    <strong>${title}</strong>
                    <span class="tag">0</span>
                </div>
                <div class="muted">Sin pedidos para mostrar.</div>
            </div>`;
    }

    const max = Math.max(...rows.map(item => item.total), 1);

    return `
        <div class="chart-card">
            <div class="chart-head">
                <strong>${title}</strong>
                <span class="tag">${money(rows.reduce((sum, item) => sum + item.total, 0))}</span>
            </div>
            <div class="bars">
                ${rows.map(item => `
                    <div class="bar-col" title="${item.label}: ${money(item.total)} en ${item.count} pedidos">
                        <div class="bar-fill" style="height:${Math.max(16, Math.round((item.total / max) * 110))}px;"></div>
                        <strong>${item.label}</strong>
                        <span>${money(item.total)}</span>
                    </div>
                `).join('')}
            </div>
        </div>`;
}

function renderPieChart(title, rows, labelKey, valueKey) {
    const palette = ['#FF6F1F', '#F7B801', '#FF9D5A', '#C94700', '#EAB68A', '#17683A', '#205A84'];
    const cleanRows = (rows || [])
        .map(row => ({ label: String(row[labelKey] || 'Otros'), value: Number(row[valueKey] || 0) }))
        .filter(row => row.value > 0);
    const total = cleanRows.reduce((sum, row) => sum + row.value, 0);
    if (!total) {
        return `<div class="chart-card"><div class="chart-head"><strong>${title}</strong><span class="tag">0</span></div><div class="muted">Sin datos para mostrar.</div></div>`;
    }
    let cursor = 0;
    const stops = cleanRows.map((row, index) => {
        const start = cursor;
        cursor += (row.value / total) * 360;
        return `${palette[index % palette.length]} ${start.toFixed(2)}deg ${cursor.toFixed(2)}deg`;
    });
    return `<div class="chart-card">
        <div class="chart-head"><strong>${title}</strong><span class="tag">${total}</span></div>
        <div class="pie-layout">
            <div class="pie-chart" role="img" aria-label="${escapeHtml(title)}" style="background:conic-gradient(${stops.join(',')})"></div>
            <div class="pie-legend">${cleanRows.map((row, index) => {
                const percentage = ((row.value / total) * 100).toFixed(1);
                return `<div class="pie-legend-row" title="${escapeHtml(row.label)}: ${row.value} (${percentage}%)"><span class="pie-dot" style="background:${palette[index % palette.length]}"></span><span>${escapeHtml(row.label)}</span><strong>${row.value} · ${percentage}%</strong></div>`;
            }).join('')}</div>
        </div>
    </div>`;
}

function renderDashboard(stats) {
    if (!salesDashboard) return;
    const dayRows = stats?.buckets?.day || [];
    const monthRows = stats?.buckets?.month || [];
    const yearRows = stats?.buckets?.year || [];
    const payments = stats?.payments || [];
    const statuses = stats?.statuses || [];
    const promotions = stats?.promotions || [];
    const summary = stats?.summary || {};
    const bestDay = summary.best_day;
    const worstDay = summary.worst_day;

    salesDashboard.innerHTML = [
        renderChart('Ventas por dia', dayRows.slice(-6).map(item => ({
            label: item.label,
            total: Number(item.total || 0),
            count: Number(item.count || 0),
        }))),
        renderChart('Ventas por mes', monthRows.slice(-6).map(item => ({
            label: item.label,
            total: Number(item.total || 0),
            count: Number(item.count || 0),
        }))),
        renderChart('Ventas por ano', yearRows.slice(-6).map(item => ({
            label: item.label,
            total: Number(item.total || 0),
            count: Number(item.count || 0),
        }))),
        `
        <div class="chart-card">
            <div class="chart-head">
                <strong>Indicadores utiles</strong>
                <span class="tag">${Number(summary.orders_count || 0)} pedidos</span>
            </div>
            <div class="muted">Venta total: <strong>S/ ${money(summary.total_sales || 0)}</strong></div>
            <div class="muted">Ticket promedio: <strong>S/ ${money(summary.average_ticket || 0)}</strong></div>
            <div class="muted">Mejor dia: <strong>${bestDay ? `${bestDay.label} | S/ ${money(bestDay.total)}` : 'Sin datos'}</strong></div>
            <div class="muted">Dia mas bajo: <strong>${worstDay ? `${worstDay.label} | S/ ${money(worstDay.total)}` : 'Sin datos'}</strong></div>
        </div>`,
        `
        <div class="chart-card">
            <div class="chart-head">
                <strong>Pagos digitales</strong>
                <span class="tag">${payments.length}</span>
            </div>
            ${payments.length ? payments.map(payment => `
                <div class="muted" style="margin-bottom:6px;">
                    <strong>${payment.method}</strong>: S/ ${money(payment.total || 0)} | ${payment.count} pedidos
                    <br>Verificados: ${payment.verified_count} | Reportados: ${payment.reported_count} | Pendientes: ${payment.pending_count}
                </div>
            `).join('') : '<div class="muted">Sin datos de pago.</div>'}
        </div>`,
        `<div class="dashboard-pies">
            ${renderPieChart('Pedidos por estado', statuses.map(item => ({ ...item, status: statusEs(item.status) })), 'status', 'count')}
            ${renderPieChart('Ventas por metodo de pago', payments, 'method', 'count')}
        </div>`,
        `<div class="chart-card" style="grid-column:1/-1">
            <div class="chart-head"><strong>Compras realizadas por promociones</strong><span class="tag">${promotions.reduce((sum,row)=>sum+Number(row.orders_count||0),0)} pedidos</span></div>
            ${promotions.length ? promotions.map(row => `<div class="muted" style="padding:9px 0;border-bottom:1px solid #f0d5bd"><strong>${escapeHtml(row.title)}</strong> · ${row.orders_count} pedidos · ${row.units} unidades · Ventas S/ ${money(row.sales)} · Descuentos S/ ${money(row.discount_total)}</div>`).join('') : '<div class="muted">Todavía no hay compras originadas por promociones.</div>'}
        </div>`,
    ].join('');
}

async function fetchOrderStats() {
    const token = getToken();
    const params = new URLSearchParams();
    if (filterStatus.value) params.set('status', filterStatus.value);
    if (filterPaymentMethod.value) params.set('payment_method', filterPaymentMethod.value);
    if (filterPaymentStatus.value) params.set('payment_status', filterPaymentStatus.value);
    if (filterDateFrom.value) params.set('date_from', filterDateFrom.value);
    if (filterDateTo.value) params.set('date_to', filterDateTo.value);
    const query = params.toString() ? `?${params.toString()}` : '';

    const res = await fetch(`/api/v1/admin/orders/stats${query}`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        renderDashboard(null);
        return;
    }
    renderDashboard(data);
}

async function fetchProducts() {
    const res = await fetch('/api/v1/admin/products', {
        headers: { 'Authorization': `Bearer ${getToken()}` },
    });
    const data = await res.json();
    productsCache = Array.isArray(data) ? data : [];
    if (offerProductSelect) {
        const selected = offerProductSelect.value;
        offerProductSelect.innerHTML = '<option value="">Catálogo general</option>' + productsCache.map(product => `<option value="${Number(product.id)}">${escapeHtml(product.name || 'Producto')}</option>`).join('');
        if ([...offerProductSelect.options].some(option => option.value === selected)) offerProductSelect.value = selected;
    }
    upsertCategoryOptions();
    syncDashboardMetrics();
    renderProducts();
    renderManualSaleProductOptions();
}

function renderProducts() {
    if (!productsCache.length) {
        productsList.innerHTML = '<div class="card">No hay productos.</div>';
        return;
    }
    productsList.innerHTML = productsCache.map(productCard).join('');

    productsList.querySelectorAll('[data-edit]').forEach(btn => {
        btn.addEventListener('click', () => editProduct(Number(btn.getAttribute('data-edit'))));
    });
    productsList.querySelectorAll('[data-delete]').forEach(btn => {
        btn.addEventListener('click', () => deleteProduct(Number(btn.getAttribute('data-delete'))));
    });
}

function editProduct(productId) {
    const product = productsCache.find(item => item.id === productId);
    if (!product) return;
    productForm.product_id.value = product.id;
    productForm.name.value = product.name || '';
    productForm.price.value = product.price || '';
    productForm.description.value = product.description || '';
    productForm.is_available.checked = Boolean(product.is_available);
    productForm.stock.value = Number(product.stock || 0);
    categorySelect.value = String(product.category || 'pollos').toLowerCase();
    productImageRemoved = false;
    if (productImageInput) productImageInput.value = '';
    if (productImagePreview) productImagePreview.dataset.persisted = product.image_url || '';
    setUploadPreview(productImagePreview, product.image_url || '');
    syncProductAvailabilityLabel();
    productMsg.textContent = `Editando producto ID ${product.id}`;
}

async function deleteProduct(productId) {
    if (!(await showAdminConfirm(`Eliminar producto ID ${productId}?`, 'Eliminar producto'))) return;
    const token = getToken();
    const res = await fetch(`/api/v1/products/${productId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        productMsg.textContent = data.message || 'No se pudo eliminar';
        return;
    }
    productMsg.textContent = 'Producto eliminado';
    await fetchProducts();
}

async function saveProduct(e) {
    e.preventDefault();
    const token = getToken();

    const customCategory = newCategoryInput.value.trim().toLowerCase();
    const category = customCategory || categorySelect.value;
    if (!category) {
        productMsg.textContent = 'Selecciona o crea una categoria';
        return;
    }

    const formData = new FormData();
    formData.append('name', productForm.name.value.trim());
    formData.append('price', String(Number(productForm.price.value)));
    formData.append('category', category);
    formData.append('description', productForm.description.value.trim() || '');
    formData.append('is_available', productForm.is_available.checked ? '1' : '0');
    formData.append('stock', String(Number(productForm.stock.value || 0)));
    if (productImageRemoved) formData.append('remove_image', '1');
    if (productImageInput?.files?.[0]) formData.append('image', productImageInput.files[0]);

    const editingId = productForm.product_id.value.trim();
    const url = editingId ? `/api/v1/products/${editingId}` : '/api/v1/products';
    const method = editingId ? 'PUT' : 'POST';
    if (editingId) formData.append('_method', 'PUT');

    const res = await fetch(url, {
        method: editingId ? 'POST' : method,
        headers: {
            'Authorization': `Bearer ${token}`,
        },
        body: formData,
    });
    const raw = await res.text();
    let data = {};
    try {
        data = raw ? JSON.parse(raw) : {};
    } catch {
        data = { message: raw || 'Respuesta invalida del servidor' };
    }

    if (!res.ok) {
        const validationErrors = data.errors ? Object.values(data.errors).flat().join(' | ') : '';
        productMsg.textContent = validationErrors || data.message || 'No se pudo guardar el producto';
        return;
    }

    productMsg.textContent = editingId ? 'Producto actualizado' : 'Producto creado';
    showProductToast(editingId ? 'Producto actualizado correctamente.' : 'Producto agregado al catalogo.');
    clearProductForm();
    await fetchProducts();
}

function syncProductAvailabilityLabel() {
    if (!productStatusText) return;
    const active = Boolean(productForm.is_available.checked);
    productStatusText.textContent = active ? 'Producto activo' : 'Producto inactivo';
    productStatusText.className = `toggle-status-text${active ? '' : ' inactive'}`;
}

async function fetchOrders() {
    const token = getToken();
    const params = new URLSearchParams();
    if (filterStatus.value) params.set('status', filterStatus.value);
    if (filterPaymentMethod.value) params.set('payment_method', filterPaymentMethod.value);
    if (filterPaymentStatus.value) params.set('payment_status', filterPaymentStatus.value);
    if (filterDateFrom.value) params.set('date_from', filterDateFrom.value);
    if (filterDateTo.value) params.set('date_to', filterDateTo.value);
    const query = params.toString() ? `?${params.toString()}` : '';

    const res = await fetch(`/api/v1/admin/orders${query}`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        renderDashboard(null);
        ordersList.innerHTML = '<div class="card">No se pudieron cargar pedidos.</div>';
        return;
    }

    const orders = data.data || [];
    window.__adminOrdersCache = orders;
    syncDashboardMetrics();
    if (!orders.length) {
        ordersList.innerHTML = '<div class="card">Sin pedidos recientes.</div>';
    } else {
        ordersList.innerHTML = orders.map(order => `
            <article class="card">
                <div class="card-top">
                    <strong>${order.tracking_code}</strong>
                    <span class="tag">${statusEs(order.status)}</span>
                </div>
                <div class="muted">ID: ${order.id} | ${order.customer_name}</div>
                <div class="muted">Fecha/Hora: ${new Date(order.created_at).toLocaleString()}</div>
                <div class="muted">
                    Pago: ${paymentMethodEs(order.payment_method)}
                    <span class="tag ${paymentStatusClass(order.payment_status)}" style="margin-left:6px;">${paymentStatusEs(order.payment_status)}</span>
                </div>
                <div class="muted">Operacion: ${order.payment_reference || 'sin codigo'}</div>
                <div class="muted">Tributario: ${order.billing_receipt_type ? `${order.billing_receipt_type} ${order.billing_document_number || ''}` : 'sin boleta/factura'}</div>
                ${order.billing_receipt_type ? `<div class="muted">Envio: ${escapeHtml(order.billing_metadata?.einvoice?.status || 'pending')} · Ultimo intento: ${escapeHtml(order.billing_metadata?.einvoice?.last_attempt_at || 'sin intentos')}</div>` : ''}
                ${order.billing_metadata?.einvoice?.delivery ? `<div class="muted">Correo comprobante: ${order.billing_metadata.einvoice.delivery.status || 'sin estado'}${order.billing_metadata.einvoice.delivery.recipient ? ` (${order.billing_metadata.einvoice.delivery.recipient})` : ''}</div>` : ''}
                <div style="margin-top:6px;">Total: <strong>S/ ${Number(order.total_amount).toFixed(2)}</strong></div>
                <div style="display:flex; gap:8px; margin-top:8px;">
                    <button data-view-order="${order.id}">Ver pedido</button>
                    <button data-fill="${order.id}">Usar en actualizar estado</button>
                    ${order.payment_proof_path ? `<button data-proof-modal="${order.id}">Ver comprobante</button>` : ''}
                    ${order.billing_receipt_type ? `<button data-einvoice-preview="${order.id}">Preview SUNAT</button>${order.payment_status === 'verified' ? `<button data-einvoice-send="${order.id}">${order.billing_metadata?.einvoice?.sent_at ? 'Reenviar comprobante' : order.billing_metadata?.einvoice?.status === 'failed' ? 'Reintentar envio' : 'Enviar comprobante'}</button><button data-einvoice-email="${order.id}">Reenviar correo</button>` : ''}` : ''}
                    <button data-delete-order="${order.id}" style="border-color:#ffc1b5; color:#a53216;">Eliminar pedido</button>
                </div>
            </article>
        `).join('');
    }
    await fetchOrderStats();

    ordersList.querySelectorAll('[data-fill]').forEach(btn => {
        btn.addEventListener('click', () => {
            const orderId = btn.getAttribute('data-fill');
            const order = orders.find(item => item.id === Number(orderId));
            statusForm.order_id.value = orderId;
            // Siempre se parte del estado real de ESTE pedido y una nota
            // vacia, para no arrastrar la seleccion o nota que haya quedado
            // de haber editado otro pedido antes y confundir al admin.
            statusForm.status.value = order ? order.status : 'pending';
            statusForm.note.value = '';
            statusMsg.textContent = `Pedido ID ${orderId} seleccionado`;
            orderActionPanelPreview.style.display = 'none';
            orderActionPanelStatus.style.display = '';
            openOrderActionPanel(`Actualizar estado - Pedido ${orderId}`);
        });
    });
    ordersList.querySelectorAll('[data-view-order]').forEach(btn => {
        btn.addEventListener('click', () => {
            const order = orders.find(item => item.id === Number(btn.getAttribute('data-view-order')));
            if (order) viewOrderDetail(order);
        });
    });
    ordersList.querySelectorAll('[data-proof-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const order = orders.find(item => item.id === Number(btn.getAttribute('data-proof-modal')));
            openProofModal(order);
        });
    });
    ordersList.querySelectorAll('[data-einvoice-preview]').forEach(btn => {
        btn.addEventListener('click', () => previewEinvoice(Number(btn.getAttribute('data-einvoice-preview'))));
    });
    ordersList.querySelectorAll('[data-einvoice-send]').forEach(btn => {
        btn.addEventListener('click', () => sendEinvoice(Number(btn.getAttribute('data-einvoice-send')), btn));
    });
    ordersList.querySelectorAll('[data-einvoice-email]').forEach(btn => {
        btn.addEventListener('click', () => sendEinvoiceEmail(Number(btn.getAttribute('data-einvoice-email'))));
    });
    ordersList.querySelectorAll('[data-delete-order]').forEach(btn => {
        btn.addEventListener('click', () => deleteOrder(Number(btn.getAttribute('data-delete-order'))));
    });
}

async function previewEinvoice(orderId) {
    const token = getToken();
    orderActionsMsg.textContent = `Generando preview SUNAT para pedido ${orderId}...`;
    const res = await fetch(`/api/v1/admin/orders/${orderId}/einvoice/preview`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        orderActionsMsg.textContent = data.message || 'No se pudo generar preview SUNAT.';
        return;
    }
    orderActionsMsg.textContent = `Preview SUNAT listo para pedido ${orderId}.`;
    orderActionPanelPreviewContent.textContent = JSON.stringify(data, null, 2);
    orderActionPanelStatus.style.display = 'none';
    orderActionPanelPreview.style.display = '';
    openOrderActionPanel(`Preview SUNAT - Pedido ${orderId}`);
}

function viewOrderDetail(order) {
    const items = (order.items || []).map(item => `
        <div style="display:flex; justify-content:space-between; gap:10px; padding:10px 0; border-bottom:1px solid #FFE4D2;">
            <span style="color:#25170f; font-weight:700;">Pidio: ${escapeHtml(item.product_name)} × ${item.quantity}</span>
            <span style="color:#8d3d00; font-weight:900; flex-shrink:0;">S/ ${Number(item.line_total).toFixed(2)}</span>
        </div>
    `).join('') || '<div class="muted">Sin items registrados.</div>';

    const extras = [];
    if (order.salad_type) extras.push(`Ensalada tipo: ${escapeHtml(order.salad_type)}`);
    if (order.drink_note) extras.push(`Sugerencias del cliente: "${escapeHtml(order.drink_note)}"`);

    const delivery = order.delivery_type === 'delivery'
        ? `
            <div style="margin-top:14px; font-size:13px; color:#68432e; line-height:1.6;">
                <div><strong style="color:#25170f;">Direccion:</strong> ${escapeHtml(order.address || 'sin direccion')}</div>
                <div><strong style="color:#25170f;">Referencia:</strong> ${escapeHtml(order.reference || 'sin referencia')}</div>
            </div>
        `
        : '<div style="margin-top:14px; font-size:13px; color:#68432e;">Recojo en local</div>';

    orderDetailModalTitle.textContent = `Ver pedido - ${order.tracking_code}`;
    orderDetailModalContent.innerHTML = `
        <div style="margin-bottom:14px; padding-bottom:14px; border-bottom:1px solid #FFE4D2;">
            <div style="font-size:13px; color:#68432e;">Cliente: <strong style="color:#25170f;">${escapeHtml(order.customer_name)}</strong> · ${escapeHtml(order.customer_phone || '')}</div>
        </div>
        <div style="margin-bottom:14px;">${items}</div>
        ${extras.length ? `<div style="margin-bottom:14px; padding:14px; background:#FFF7EF; border:1px solid #FFD4B1; border-radius:16px; font-size:13.5px; color:#68432e; display:grid; gap:6px;">${extras.map(e => `<div>${e}</div>`).join('')}</div>` : ''}
        ${delivery}
        <div style="margin-top:16px; padding-top:14px; border-top:1px solid #FFE4D2; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; color:#68432e; text-transform:uppercase; letter-spacing:.06em;">Total</span>
            <strong style="font-size:20px; color:#25170f;">S/ ${Number(order.total_amount).toFixed(2)}</strong>
        </div>
    `;
    orderDetailModal.style.display = 'flex';
}

async function sendEinvoice(orderId, button = null) {
    if (!(await showAdminConfirm(`Emitir comprobante electronico para pedido ID ${orderId}?`, 'Emitir comprobante'))) return;
    if (button?.disabled) return;
    const originalLabel = button?.textContent || '';
    if (button) {
        button.disabled = true;
        button.textContent = 'Enviando...';
        button.setAttribute('aria-busy', 'true');
    }
    const token = getToken();
    orderActionsMsg.textContent = `Enviando comprobante SUNAT para pedido ${orderId}...`;
    try {
        const res = await fetch(`/api/v1/admin/orders/${orderId}/einvoice/send`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
        });
        const data = await res.json();
        if (!res.ok) {
            orderActionsMsg.textContent = data.message || 'No se pudo emitir comprobante SUNAT.';
            return null;
        }
        orderActionsMsg.textContent = data.already_sent
            ? `El pedido ${orderId} ya tenia comprobante emitido.`
            : `Comprobante electronico enviado para pedido ${orderId}.`;
        await fetchOrders();
        return data;
    } catch {
        orderActionsMsg.textContent = 'No se pudo conectar con el servicio de comprobantes.';
        return null;
    } finally {
        if (button?.isConnected) {
            button.disabled = false;
            button.textContent = originalLabel;
            button.removeAttribute('aria-busy');
        }
    }
}

async function sendEinvoiceEmail(orderId) {
    if (!(await showAdminConfirm(`Reenviar el comprobante por correo para el pedido ID ${orderId}?`, 'Reenviar comprobante'))) return;
    const token = getToken();
    orderActionsMsg.textContent = `Enviando correo del comprobante para pedido ${orderId}...`;
    const res = await fetch(`/api/v1/admin/orders/${orderId}/einvoice/send-customer-copy`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    orderActionsMsg.textContent = res.ok
        ? `${data.message} Destino: ${data.recipient}`
        : (data.message || 'No se pudo enviar el correo del comprobante.');
    if (res.ok) await fetchOrders();
}

async function exportCsv() {
    const token = getToken();
    const params = new URLSearchParams();
    if (filterStatus.value) params.set('status', filterStatus.value);
    if (filterPaymentMethod.value) params.set('payment_method', filterPaymentMethod.value);
    if (filterPaymentStatus.value) params.set('payment_status', filterPaymentStatus.value);
    if (filterDateFrom.value) params.set('date_from', filterDateFrom.value);
    if (filterDateTo.value) params.set('date_to', filterDateTo.value);
    const query = params.toString() ? `?${params.toString()}` : '';

    const res = await fetch(`/api/v1/admin/orders/export${query}`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    if (!res.ok) {
        statusMsg.textContent = 'No se pudo exportar Excel';
        return;
    }
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'reporte-pedidos-admin.xls';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}

function renderCashSummary(data) {
    if (!cashClosureSummary) return;
    const totals = data?.totals || {};
    const payments = data?.payments || [];
    const policy = data?.policy || {};
    cashClosureSummary.innerHTML = `
        <article class="card">
            <div class="card-top">
                <strong>Fecha ${data?.business_date || '-'}</strong>
                <span class="tag">${totals.orders_count || 0} pedidos</span>
            </div>
            <div class="muted">Politica: ${policy.message || 'Sin politica disponible.'}</div>
            <div class="muted">Horario habilitado: desde ${policy.cutoff_hour || '23:00'} (${policy.timezone || 'America/Lima'})</div>
            <div class="muted">Venta bruta: <strong>${money(totals.gross_sales || 0)}</strong></div>
            <div class="muted">Ventas verificadas: <strong>${money(totals.verified_sales || 0)}</strong></div>
            <div class="muted">Efectivo esperado: <strong>${money(totals.cash_sales || 0)}</strong></div>
            <div class="muted">Pagos digitales: <strong>${money(totals.digital_sales || 0)}</strong></div>
            <div class="muted" style="margin-top:8px;">Desglose:</div>
            ${payments.length ? payments.map(payment => `
                <div class="muted"><strong>${payment.method}</strong>: ${money(payment.total || 0)} en ${payment.orders_count || 0} pedidos</div>
            `).join('') : '<div class="muted">Sin movimientos para esa fecha.</div>'}
        </article>
    `;
}

function renderCashClosureHistory(rows) {
    if (!cashClosureHistory) return;
    if (!rows.length) {
        cashClosureHistory.innerHTML = '<div class="card">Aun no hay cierres registrados.</div>';
        return;
    }

    cashClosureHistory.innerHTML = rows.map(row => `
        <article class="card">
            <div class="card-top">
                <strong>${row.business_date}</strong>
                <span class="tag ${Number(row.difference_amount || 0) === 0 ? 'payment-verified' : 'payment-reported'}">
                    Dif. ${money(row.difference_amount || 0)}
                </span>
            </div>
            <div class="muted">Pedidos: ${row.orders_count} | Bruto: ${money(row.gross_sales || 0)}</div>
            <div class="muted">Esperado en caja: ${money(row.expected_cash || 0)} | Declarado: ${money(row.declared_cash || 0)}</div>
            <div class="muted">Registrado por: ${row.closer?.name || 'Sistema'} | Cierre: ${row.closed_at ? new Date(row.closed_at).toLocaleString() : '-'}</div>
            <div class="muted">${row.notes || 'Sin observaciones.'}</div>
        </article>
    `).join('');
}

async function fetchCashClosureSummary() {
    const token = getToken();
    const selectedDate = cashClosureDate?.value || todayDateValue();
    const res = await fetch(`/api/v1/admin/cash-closures/summary?date=${encodeURIComponent(selectedDate)}`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        cashClosureSummary.innerHTML = `<div class="card">${data.message || 'No se pudo cargar el resumen de caja.'}</div>`;
        return;
    }
    renderCashSummary(data);
}

async function fetchCashClosureHistory() {
    const token = getToken();
    const res = await fetch('/api/v1/admin/cash-closures', {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        cashClosureHistory.innerHTML = '<div class="card">No se pudieron cargar los cierres registrados.</div>';
        return;
    }
    renderCashClosureHistory(Array.isArray(data) ? data : []);
}

async function exportCashClosures() {
    const token = getToken();
    const res = await fetch('/api/v1/admin/cash-closures/export', {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    if (!res.ok) {
        cashClosureMsg.textContent = 'No se pudo exportar el Excel de cierres de caja.';
        return;
    }
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'cierres-caja-admin.xls';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}

async function saveCashClosure(event) {
    event.preventDefault();
    const token = getToken();
    const payload = {
        business_date: cashClosureForm.business_date.value,
        declared_cash: Number(cashClosureForm.declared_cash.value || 0),
        notes: cashClosureForm.notes.value.trim() || null,
    };

    cashClosureMsg.textContent = 'Guardando cierre...';
    const res = await fetch('/api/v1/admin/cash-closures', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok) {
        const validationErrors = data.errors ? Object.values(data.errors).flat().join(' | ') : '';
        cashClosureMsg.textContent = validationErrors || data.message || 'No se pudo guardar el cierre de caja.';
        return;
    }

    cashClosureMsg.textContent = `Cierre guardado para ${data.business_date}. Diferencia: ${money(data.difference_amount || 0)}`;
    await fetchCashClosureSummary();
    await fetchCashClosureHistory();
}

function bootRealtimeOrders() {
    const key = @json(config('broadcasting.connections.pusher.key'));
    const cluster = @json(config('broadcasting.connections.pusher.options.cluster'));
    const host = @json(config('broadcasting.connections.pusher.options.host'));
    const port = @json(config('broadcasting.connections.pusher.options.port'));
    const scheme = @json(config('broadcasting.connections.pusher.options.scheme'));
    const channelName = 'mi-canal';
    const eventName = 'mi-evento';

    if (!key || typeof Pusher === 'undefined') return;

    const pusherOptions = {
        forceTLS: scheme === 'https',
    };
    if (cluster) pusherOptions.cluster = cluster;
    if (host) pusherOptions.wsHost = host;
    if (port) {
        pusherOptions.wsPort = Number(port);
        pusherOptions.wssPort = Number(port);
    }

    const pusher = new Pusher(key, pusherOptions);
    const channel = pusher.subscribe(channelName);

    const handleRealtimeEvent = async (data) => {
        const payload = data && data.data ? data.data : data;
        if ((payload?.type || '') !== 'order_created') return;
        if ((payload?.target || '').toString().toLowerCase() !== 'admin') return;

        showAdminOrderToast(payload || {});

        try {
            await fetchOrders();
            await fetchCashClosureSummary();
        } catch {}
    };

    [eventName, `.${eventName}`, 'App\\Events\\OrderCreatedAlertSent'].forEach((name) => {
        channel.bind(name, handleRealtimeEvent);
    });
}

async function fetchUsers() {
    const token = getToken();
    const res = await fetch('/api/v1/admin/users', {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        usersList.innerHTML = '<div class="card">No se pudieron cargar usuarios.</div>';
        return;
    }

    const users = data.data || [];
    window.__adminUsersCount = users.length;
    syncDashboardMetrics();
    if (!users.length) {
        usersList.innerHTML = '<div class="card">Sin cuentas registradas.</div>';
        return;
    }

    usersList.innerHTML = users.map(user => {
        const days = Math.floor((Date.now() - new Date(user.created_at).getTime()) / (1000 * 60 * 60 * 24));
        return `
            <article class="card">
                <div class="card-top">
                    <strong>${user.name}</strong>
                    <span class="tag">${user.role}</span>
                </div>
                <div class="muted">${user.email}</div>
                <div class="muted">Telefono: ${user.phone || '-'}</div>
                <div class="muted">Creada: ${new Date(user.created_at).toLocaleString()} (${days} dias)</div>
                <div class="muted">Estado: ${user.is_active ? 'Activa' : 'Desactivada'}</div>
                <div style="display:flex; gap:8px; margin-top:8px; align-items:center; flex-wrap:wrap;">
                    <select data-role-select="${user.id}" style="width:auto; margin:0;">
                        <option value="customer" ${user.role === 'customer' ? 'selected' : ''}>Cliente</option>
                        <option value="reviewer" ${user.role === 'reviewer' ? 'selected' : ''}>Revisor (solo lectura)</option>
                        <option value="delivery" ${user.role === 'delivery' ? 'selected' : ''}>Repartidor</option>
                        <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Administrador</option>
                    </select>
                    <button type="button" data-save-role="${user.id}" class="btn-main">Guardar rol</button>
                    <button data-toggle-user="${user.id}" data-next="${user.is_active ? '0' : '1'}">
                        ${user.is_active ? 'Dar de baja' : 'Reactivar'}
                    </button>
                    <button data-delete-user="${user.id}" style="border-color:#ffc1b5; color:#a53216;">Eliminar</button>
                </div>
                <div data-role-msg="${user.id}" class="msg"></div>
            </article>`;
    }).join('');

    usersList.querySelectorAll('[data-toggle-user]').forEach(btn => {
        btn.addEventListener('click', () => toggleUserActive(
            Number(btn.getAttribute('data-toggle-user')),
            btn.getAttribute('data-next') === '1'
        ));
    });

    usersList.querySelectorAll('[data-delete-user]').forEach(btn => {
        btn.addEventListener('click', () => deleteUser(Number(btn.getAttribute('data-delete-user'))));
    });

    usersList.querySelectorAll('[data-save-role]').forEach(btn => {
        btn.addEventListener('click', () => {
            const userId = Number(btn.getAttribute('data-save-role'));
            const select = usersList.querySelector(`[data-role-select="${userId}"]`);
            if (select) changeUserRole(userId, select.value, btn);
        });
    });
}

async function changeUserRole(userId, role, btn) {
    const msgEl = usersList.querySelector(`[data-role-msg="${userId}"]`);
    if (msgEl) { msgEl.textContent = 'Guardando...'; msgEl.className = 'msg'; }
    if (btn) btn.disabled = true;

    const token = getToken();
    try {
        const res = await fetch(`/api/v1/admin/users/${userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ role }),
        });
        const data = await res.json();
        if (!res.ok) {
            if (msgEl) { msgEl.textContent = data.message || 'No se pudo cambiar el rol'; msgEl.className = 'msg error'; }
            if (btn) btn.disabled = false;
            return;
        }
        if (msgEl) { msgEl.textContent = `Rol actualizado a "${role}" correctamente.`; msgEl.className = 'msg success'; }
    } catch {
        if (msgEl) { msgEl.textContent = 'Error de conexion al cambiar el rol.'; msgEl.className = 'msg error'; }
        if (btn) btn.disabled = false;
        return;
    }

    await fetchUsers();
}

async function toggleUserActive(userId, isActive) {
    const token = getToken();
    const res = await fetch(`/api/v1/admin/users/${userId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({ is_active: isActive }),
    });
    const data = await res.json();
    if (!res.ok) {
        statusMsg.textContent = data.message || 'No se pudo actualizar cuenta';
        return;
    }
    statusMsg.textContent = `Cuenta ${data.name} ${data.is_active ? 'activada' : 'desactivada'}`;
    await fetchUsers();
}

async function deleteUser(userId) {
    if (!(await showAdminConfirm(`Eliminar usuario ID ${userId}?`, 'Eliminar usuario'))) return;
    const token = getToken();
    const res = await fetch(`/api/v1/admin/users/${userId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        statusMsg.textContent = data.message || 'No se pudo eliminar usuario';
        return;
    }
    statusMsg.textContent = `Usuario ${userId} eliminado`;
    await fetchUsers();
}

async function fetchJobs() {
    if (!adminJobsList) return;
    const res=await fetch('/api/v1/admin/jobs',{headers:{'Authorization':`Bearer ${getToken()}`}}),jobs=await res.json();
    if(!res.ok){adminJobsList.textContent=jobs.message||'No se pudieron cargar las vacantes.';return}
    adminJobsList.innerHTML=jobs.length?jobs.map(job=>`<article class="card"><strong>${escapeHtml(job.title)}</strong><div class="muted">${escapeHtml(job.description||'Sin descripción')}</div><button type="button" data-delete-job="${job.id}">Eliminar</button></article>`).join(''):'<div class="card">No hay vacantes publicadas.</div>';
    adminJobsList.querySelectorAll('[data-delete-job]').forEach(button=>button.onclick=async()=>{if(!(await showAdminConfirm('Eliminar esta vacante?','Eliminar vacante')))return;await fetch(`/api/v1/admin/jobs/${button.dataset.deleteJob}`,{method:'DELETE',headers:{'Authorization':`Bearer ${getToken()}`}});fetchJobs()});
}

async function saveJob(event) {
    event.preventDefault();jobMsg.textContent='Publicando...';
    const res=await fetch('/api/v1/admin/jobs',{method:'POST',headers:{'Content-Type':'application/json','Authorization':`Bearer ${getToken()}`},body:JSON.stringify({title:jobForm.title.value.trim(),description:jobForm.description.value.trim()||null,is_active:true})}),data=await res.json();
    if(!res.ok){jobMsg.textContent=data.message||'No se pudo publicar.';return}jobForm.reset();jobMsg.textContent='Vacante publicada.';fetchJobs();
}

async function updateOrderStatus(e) {
    e.preventDefault();
    const token = getToken();
    const orderId = statusForm.order_id.value.trim();
    if (!orderId) return;

    const payload = {
        status: statusForm.status.value,
        note: statusForm.note.value.trim() || null,
    };

    const res = await fetch(`/api/v1/admin/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok) {
        statusMsg.textContent = data.message || 'No se pudo actualizar estado';
        return;
    }
    statusMsg.textContent = `Estado actualizado a ${statusEs(data.status)}`;
    statusForm.note.value = '';
    await fetchOrders();
}

async function deleteOrder(orderId) {
    if (!(await showAdminConfirm(`Eliminar pedido ID ${orderId}? Esta accion lo quitara de la vista del cliente.`, 'Eliminar pedido'))) return;
    const token = getToken();
    const res = await fetch(`/api/v1/admin/orders/${orderId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        statusMsg.textContent = data.message || 'No se pudo eliminar pedido';
        return;
    }
    statusMsg.textContent = `Pedido ${orderId} eliminado`;
    orderActionsMsg.textContent = `Pedido ${orderId} eliminado`;
    await fetchOrders();
}

async function boot() {
    const user = getUser();
    const session = parseSession();
    const allowedRoles = ['admin', 'reviewer'];
    if (!canUseAdmin() || !session || !allowedRoles.includes(session.role) || Date.now() > Number(session.expiresAt || 0)) {
        clearAuth();
        denyBox.style.display = 'block';
        adminUserLabel.textContent = 'Sin permisos admin';
        setTimeout(() => { window.location.href = '/admin/login'; }, 800);
        return;
    }

    try {
        const meRes = await fetch('/api/v1/auth/me', {
            headers: { 'Authorization': `Bearer ${getToken()}` },
        });
        const meData = await meRes.json();
        if (!meRes.ok || !meData.user || !allowedRoles.includes(meData.user.role) || !meData.user.is_active) {
            clearAuth();
            window.location.href = '/admin/login';
            return;
        }
        localStorage.setItem('ed_user', JSON.stringify(meData.user));
    } catch {
        clearAuth();
        window.location.href = '/admin/login';
        return;
    }

    touchAdminSession();
    const activeUser = getUser() || user;
    const readOnly = isReadOnlyAdmin();
    adminUserLabel.textContent = `${readOnly ? 'Revisor' : 'Admin'}: ${activeUser.name}`;
    if (readOnly) {
        document.body.classList.add('read-only-admin');
        if (readOnlyBanner) readOnlyBanner.style.display = 'flex';
    }
    adminContent.style.display = 'grid';
    if (adminMenu) adminMenu.style.display = 'flex';
    showAdminTab('sec-dashboard');

    upsertCategoryOptions();
    if (cashClosureDate && !cashClosureDate.value) cashClosureDate.value = todayDateValue();
    await Promise.allSettled([
        fetchProducts(),
        fetchOrders(),
        fetchCashClosureSummary(),
        fetchCashClosureHistory(),
        fetchUsers(),
        fetchJobs(),
    ]);

    refreshTimer = setInterval(async () => {
        if (Date.now() > Number((parseSession() || {}).expiresAt || 0)) {
            clearAuth();
            window.location.href = '/admin/login';
            return;
        }
        await Promise.allSettled([
            fetchProducts(),
            fetchOrders(),
            fetchCashClosureSummary(),
            fetchCashClosureHistory(),
            fetchUsers(),
        ]);
    }, 20000);
}

cancelEditBtn.addEventListener('click', clearProductForm);
productForm.is_available.addEventListener('change', syncProductAvailabilityLabel);
productForm.addEventListener('submit', saveProduct);
if (offerForm) {
    const chargePrice = (raw) => Math.max(0.99, Math.round(raw) - 0.01);
    const calculateOfferPrice = () => {
        const product = productsCache.find(item => Number(item.id) === Number(offerProductSelect?.value));
        const percent = Number(offerDiscountPercent?.value || 0);
        if (!product || percent <= 0 || !offerPromoPrice) return;
        const rawPrice = Number(product.price) * (1 - percent / 100);
        offerPromoPrice.value = chargePrice(rawPrice).toFixed(2);
        if (offerPriceHelp) offerPriceHelp.textContent = `Precio normal S/ ${Number(product.price).toFixed(2)} · ahorro S/ ${(Number(product.price)-Number(offerPromoPrice.value)).toFixed(2)}`;
    };
    offerDiscountPercent?.addEventListener('input', calculateOfferPrice);
    offerProductSelect?.addEventListener('change', calculateOfferPrice);
    offerPromoPrice?.addEventListener('input', () => {
        const product=productsCache.find(item=>Number(item.id)===Number(offerProductSelect?.value)),price=Number(offerPromoPrice.value||0);
        if(!product||price<=0)return;
        const effective=(1-price/Number(product.price))*100,announced=Number(offerDiscountPercent?.value||0),expected=Number(product.price)*(1-announced/100),allowed=Math.max(1,Number(product.price)*.02),valid=!announced||Math.abs(price-expected)<=allowed;
        if(offerPriceHelp){offerPriceHelp.textContent=`Descuento real: ${effective.toFixed(2)}% · precio calculado S/ ${expected.toFixed(2)}${valid?' · ajuste permitido':' · ajuste demasiado grande'}`;offerPriceHelp.style.color=valid?'#166534':'#b42318'}
    });
    const offerSendAll = document.getElementById('offerSendAll');
    const offerChannelInputs = [offerForm.send_realtime, offerForm.send_push, offerForm.send_email].filter(Boolean);
    const syncOfferSendAll = () => {
        offerSendAll.checked = offerChannelInputs.every(input => input.checked);
    };
    offerSendAll?.addEventListener('change', () => {
        offerChannelInputs.forEach(input => { input.checked = offerSendAll.checked; });
    });
    offerChannelInputs.forEach(input => input.addEventListener('change', syncOfferSendAll));
    syncOfferSendAll();
    const offerDurationSelect = document.getElementById('offerDurationSelect');
    const offerEndsAtWrap = document.getElementById('offerEndsAtWrap');
    offerDurationSelect?.addEventListener('change', () => {
        if (offerEndsAtWrap) offerEndsAtWrap.style.display = offerDurationSelect.value === 'custom' ? '' : 'none';
    });
    offerForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData();
        formData.append('target', offerForm.target.value);
        formData.append('send_realtime', offerForm.send_realtime?.checked ? '1' : '0');
        formData.append('send_push', offerForm.send_push?.checked ? '1' : '0');
        formData.append('send_email', offerForm.send_email?.checked ? '1' : '0');
        formData.append('email_subject', offerForm.email_subject.value.trim() || '');
        formData.append('title', offerForm.title.value.trim());
        formData.append('message', offerForm.message.value.trim());
        formData.append('body', offerForm.body.value.trim() || '');
        formData.append('cta_label', offerForm.cta_label.value.trim() || '');
        formData.append('product_id', offerForm.product_id.value);
        if (offerForm.promo_price.value) formData.append('promo_price', offerForm.promo_price.value);
        if (offerForm.discount_percent.value) formData.append('discount_percent', offerForm.discount_percent.value);
        if (offerImageInput?.files?.[0]) formData.append('image', offerImageInput.files[0]);
        const durationValue = offerDurationSelect?.value || '';
        if (durationValue === 'custom' && document.getElementById('offerEndsAtInput')?.value) {
            formData.append('ends_at', document.getElementById('offerEndsAtInput').value);
        } else if (durationValue && durationValue !== 'custom') {
            formData.append('duration_hours', durationValue);
        }
        sendOffer(formData, offerForm.target.value);
    });
}
jobForm?.addEventListener('submit',saveJob);
if (runRecoveryCampaignBtn) {
    runRecoveryCampaignBtn.addEventListener('click', runRecoveryCampaigns);
}
statusForm.addEventListener('submit', updateOrderStatus);
applyFiltersBtn.addEventListener('click', fetchOrders);
clearFiltersBtn.addEventListener('click', () => {
    filterStatus.value = '';
    filterPaymentMethod.value = '';
    filterPaymentStatus.value = '';
    filterDateFrom.value = '';
    filterDateTo.value = '';
    fetchOrders();
});
exportCsvBtn.addEventListener('click', exportCsv);
if (exportCashClosuresBtn) {
    exportCashClosuresBtn.addEventListener('click', exportCashClosures);
}
if (cashClosureForm) {
    cashClosureForm.addEventListener('submit', saveCashClosure);
}
if (refreshCashSummaryBtn) {
    refreshCashSummaryBtn.addEventListener('click', fetchCashClosureSummary);
}
if (cashClosureDate) {
    cashClosureDate.addEventListener('change', fetchCashClosureSummary);
}
proofModalCloseBtn.addEventListener('click', closeProofModal);
if (adminOrderToastCloseBtn) {
    adminOrderToastCloseBtn.addEventListener('click', hideAdminOrderToast);
}
if (adminUnreadBtn) {
    adminUnreadBtn.addEventListener('click', () => {
        adminUnreadOrders = 0;
        renderAdminUnread();
        showAdminTab('sec-orders');
    });
}
proofModal.addEventListener('click', (event) => {
    if (event.target === proofModal) closeProofModal();
});
adminLogoutBtn.addEventListener('click', async () => {
    const token = getToken();
    if (token) {
        try {
            await fetch('/api/v1/auth/logout', {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
                cache: 'no-store',
            });
        } catch {}
    }
    clearAuth();
    window.location.replace('/admin/login');
});
adminMenuTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        showAdminTab(tab.dataset.target);
        loadAdminTabData(tab.dataset.target);
    });
});
document.querySelectorAll('[data-header-target]').forEach(btn => {
    btn.addEventListener('click', () => {
        showAdminTab(btn.dataset.headerTarget);
        loadAdminTabData(btn.dataset.headerTarget);
    });
});
['click', 'keydown', 'mousemove', 'touchstart', 'scroll'].forEach(evt => {
    window.addEventListener(evt, touchAdminSession, { passive: true });
});

bootRealtimeOrders();
renderAdminUnread();
boot();
syncProductAvailabilityLabel();
bindImagePreview(productImageInput, productImagePreview);
bindImagePreview(offerImageInput, offerImagePreview);
if (removeProductImageBtn) {
    removeProductImageBtn.addEventListener('click', () => {
        productImageRemoved = true;
        if (productImageInput) productImageInput.value = '';
        if (productImagePreview) productImagePreview.dataset.persisted = '';
        setUploadPreview(productImagePreview, '');
    });
}

// --- Registrar venta manual (mostrador) ---
const manualSaleProductSelect = document.getElementById('manualSaleProductSelect');
const manualSaleQty = document.getElementById('manualSaleQty');
const manualSaleAddItemBtn = document.getElementById('manualSaleAddItemBtn');
const manualSaleItemsList = document.getElementById('manualSaleItemsList');
const manualSaleTotal = document.getElementById('manualSaleTotal');
const manualSaleCustomerName = document.getElementById('manualSaleCustomerName');
const manualSalePaymentMethod = document.getElementById('manualSalePaymentMethod');
const manualSaleReceiptType = document.getElementById('manualSaleReceiptType');
const manualSaleDocumentWrap = document.getElementById('manualSaleDocumentWrap');
const manualSaleDocumentLabel = document.getElementById('manualSaleDocumentLabel');
const manualSaleNameLabel = document.getElementById('manualSaleNameLabel');
const manualSaleDocumentNumber = document.getElementById('manualSaleDocumentNumber');
const manualSaleLookupBtn = document.getElementById('manualSaleLookupBtn');
const manualSaleBillingName = document.getElementById('manualSaleBillingName');
const manualSaleDeliveryWrap = document.getElementById('manualSaleDeliveryWrap');
const manualSaleEmailWrap = document.getElementById('manualSaleEmailWrap');
const manualSaleEmail = document.getElementById('manualSaleEmail');
const manualSaleNote = document.getElementById('manualSaleNote');
const manualSaleSubmitBtn = document.getElementById('manualSaleSubmitBtn');
const manualSaleMsg = document.getElementById('manualSaleMsg');
const manualSaleResult = document.getElementById('manualSaleResult');
const manualSaleResultInfo = document.getElementById('manualSaleResultInfo');
const manualSaleDownloadBtn = document.getElementById('manualSaleDownloadBtn');
const manualSaleSendEinvoiceBtn = document.getElementById('manualSaleSendEinvoiceBtn');
const manualSaleDownloadOfficialBtn = document.getElementById('manualSaleDownloadOfficialBtn');
const manualSaleOfficialHint = document.getElementById('manualSaleOfficialHint');
const manualSaleSendEmailBtn = document.getElementById('manualSaleSendEmailBtn');

let manualSaleItems = [];
let manualSaleLastOrderId = null;

function renderManualSaleProductOptions() {
    if (!manualSaleProductSelect) return;
    const selected = manualSaleProductSelect.value;
    manualSaleProductSelect.innerHTML = productsCache.map(p => `<option value="${Number(p.id)}">${escapeHtml(p.name || 'Producto')} - S/ ${Number(p.price).toFixed(2)}</option>`).join('');
    if ([...manualSaleProductSelect.options].some(option => option.value === selected)) manualSaleProductSelect.value = selected;
}

function renderManualSaleItems() {
    if (!manualSaleItemsList) return;
    if (!manualSaleItems.length) {
        manualSaleItemsList.innerHTML = '<div class="card">Sin productos agregados.</div>';
        manualSaleTotal.textContent = '0.00';
        return;
    }
    let total = 0;
    manualSaleItemsList.innerHTML = manualSaleItems.map((item, index) => {
        const lineTotal = item.price * item.quantity;
        total += lineTotal;
        return `<div class="card" style="display:flex; justify-content:space-between; align-items:center;">
            <span>${escapeHtml(item.name)} x${item.quantity}</span>
            <span>S/ ${lineTotal.toFixed(2)} <button type="button" data-remove-manual-item="${index}" style="margin-left:8px;">Quitar</button></span>
        </div>`;
    }).join('');
    manualSaleTotal.textContent = total.toFixed(2);
    manualSaleItemsList.querySelectorAll('[data-remove-manual-item]').forEach(btn => {
        btn.addEventListener('click', () => {
            manualSaleItems.splice(Number(btn.getAttribute('data-remove-manual-item')), 1);
            renderManualSaleItems();
        });
    });
}

manualSaleAddItemBtn?.addEventListener('click', () => {
    const productId = Number(manualSaleProductSelect.value);
    const product = productsCache.find(p => Number(p.id) === productId);
    const qty = Math.max(1, Number(manualSaleQty.value) || 1);
    if (!product) return;
    const existing = manualSaleItems.find(item => item.productId === productId);
    if (existing) existing.quantity += qty;
    else manualSaleItems.push({ productId, name: product.name, price: Number(product.price), quantity: qty });
    manualSaleQty.value = 1;
    renderManualSaleItems();
});

function updateManualSaleReceiptUi() {
    const type = manualSaleReceiptType.value;
    const isFactura = type === 'factura';
    const needsDocument = type === 'boleta' || type === 'factura';
    manualSaleDocumentWrap.style.display = needsDocument ? 'grid' : 'none';
    manualSaleDeliveryWrap.style.display = needsDocument ? 'block' : 'none';
    manualSaleDocumentLabel.textContent = isFactura ? 'RUC del cliente' : 'DNI del cliente';
    manualSaleNameLabel.textContent = isFactura ? 'Razon social' : 'Nombre del cliente';
    manualSaleDocumentNumber.placeholder = isFactura ? 'Ej: 20131312955' : 'Ej: 12345678';
    if (!needsDocument) {
        manualSaleDocumentNumber.value = '';
        manualSaleBillingName.value = '';
    }
}
manualSaleReceiptType?.addEventListener('change', updateManualSaleReceiptUi);

document.querySelectorAll('input[name="manualSaleDelivery"]').forEach(radio => {
    radio.addEventListener('change', () => {
        manualSaleEmailWrap.style.display = document.querySelector('input[name="manualSaleDelivery"]:checked')?.value === 'correo' ? 'block' : 'none';
    });
});

manualSaleLookupBtn?.addEventListener('click', async () => {
    const token = getToken();
    const isFactura = manualSaleReceiptType.value === 'factura';
    const docType = isFactura ? 'ruc' : 'dni';
    const number = manualSaleDocumentNumber.value.replace(/\D/g, '');
    const needed = isFactura ? 11 : 8;
    if (number.length !== needed) {
        manualSaleMsg.textContent = `El ${docType.toUpperCase()} debe tener ${needed} digitos.`;
        return;
    }
    manualSaleMsg.textContent = 'Consultando documento...';
    try {
        const endpoint = isFactura ? '/api/v1/lookups/ruc' : '/api/v1/lookups/dni';
        const body = isFactura ? { ruc: number } : { dni: number };
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) {
            manualSaleMsg.textContent = data.message || 'No se pudo consultar el documento.';
            return;
        }
        const normalized = data.normalized || {};
        manualSaleBillingName.value = isFactura ? (normalized.business_name || '') : (normalized.full_name || '');
        manualSaleMsg.textContent = manualSaleBillingName.value ? 'Documento identificado.' : 'No se encontraron datos para ese documento.';
    } catch {
        manualSaleMsg.textContent = 'No se pudo conectar para validar el documento.';
    }
});

manualSaleSubmitBtn?.addEventListener('click', async () => {
    if (!manualSaleItems.length) {
        manualSaleMsg.textContent = 'Agrega al menos un producto.';
        return;
    }
    const receiptType = manualSaleReceiptType.value || null;
    const isFactura = receiptType === 'factura';
    const needsDocument = receiptType === 'boleta' || receiptType === 'factura';
    if (needsDocument && (!manualSaleDocumentNumber.value.trim() || !manualSaleBillingName.value.trim())) {
        manualSaleMsg.textContent = 'Consulta el documento antes de registrar la venta.';
        return;
    }
    const deliveryMode = document.querySelector('input[name="manualSaleDelivery"]:checked')?.value || 'persona';
    if (needsDocument && deliveryMode === 'correo' && !manualSaleEmail.value.trim()) {
        manualSaleMsg.textContent = 'Ingresa el correo de destino.';
        return;
    }

    const payload = {
        payment_method: manualSalePaymentMethod.value,
        customer_name: manualSaleCustomerName.value.trim() || null,
        note: manualSaleNote.value.trim() || null,
        items: manualSaleItems.map(item => ({ product_id: item.productId, quantity: item.quantity })),
        billing_receipt_type: receiptType,
        billing_document_type: needsDocument ? (isFactura ? 'ruc' : 'dni') : null,
        billing_document_number: needsDocument ? manualSaleDocumentNumber.value.trim() : null,
        billing_name: needsDocument ? manualSaleBillingName.value.trim() : null,
        billing_email: needsDocument && deliveryMode === 'correo' ? manualSaleEmail.value.trim() : null,
    };

    manualSaleSubmitBtn.disabled = true;
    manualSaleMsg.textContent = 'Registrando venta...';
    try {
        const token = getToken();
        const res = await fetch('/api/v1/admin/orders/manual', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok) {
            manualSaleMsg.textContent = data.message || 'No se pudo registrar la venta.';
            return;
        }
        manualSaleLastOrderId = data.id;
        manualSaleMsg.textContent = `Venta registrada: ${data.tracking_code}`;
        manualSaleResultInfo.textContent = `Pedido ${data.tracking_code} - Total S/ ${Number(data.total_amount).toFixed(2)}`;
        manualSaleResult.style.display = 'block';
        manualSaleSendEinvoiceBtn.style.display = needsDocument ? 'inline-block' : 'none';
        manualSaleSendEmailBtn.style.display = (needsDocument && deliveryMode === 'correo') ? 'inline-block' : 'none';
        manualSaleDownloadOfficialBtn.style.display = 'none';
        manualSaleOfficialHint.style.display = 'none';
        manualSaleItems = [];
        manualSaleCustomerName.value = '';
        manualSaleNote.value = '';
        manualSaleDocumentNumber.value = '';
        manualSaleBillingName.value = '';
        manualSaleEmail.value = '';
        manualSaleReceiptType.value = '';
        updateManualSaleReceiptUi();
        renderManualSaleItems();
        await fetchOrders();
    } catch {
        manualSaleMsg.textContent = 'No se pudo conectar al servidor.';
    } finally {
        manualSaleSubmitBtn.disabled = false;
    }
});

manualSaleDownloadBtn?.addEventListener('click', async () => {
    if (!manualSaleLastOrderId) return;
    const token = getToken();
    const res = await fetch(`/api/v1/orders/${manualSaleLastOrderId}/receipt`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    if (!res.ok) {
        manualSaleMsg.textContent = 'No se pudo descargar el comprobante.';
        return;
    }
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `comprobante-${manualSaleLastOrderId}.pdf`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
});

manualSaleSendEinvoiceBtn?.addEventListener('click', async () => {
    if (!manualSaleLastOrderId) return;
    const result = await sendEinvoice(manualSaleLastOrderId, manualSaleSendEinvoiceBtn);
    if (result) {
        manualSaleDownloadOfficialBtn.style.display = 'inline-block';
        manualSaleOfficialHint.style.display = 'block';
    }
});
manualSaleDownloadOfficialBtn?.addEventListener('click', async () => {
    if (!manualSaleLastOrderId) return;
    const token = getToken();
    manualSaleMsg.textContent = 'Descargando comprobante oficial...';
    const res = await fetch(`/api/v1/admin/orders/${manualSaleLastOrderId}/einvoice/official-pdf`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        manualSaleMsg.textContent = data.message || 'No se pudo descargar el comprobante oficial.';
        return;
    }
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `comprobante-oficial-${manualSaleLastOrderId}.pdf`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
    manualSaleMsg.textContent = 'Comprobante oficial descargado.';
});
manualSaleSendEmailBtn?.addEventListener('click', () => {
    if (manualSaleLastOrderId) sendEinvoiceEmail(manualSaleLastOrderId);
});

renderManualSaleItems();
</script>
</body>
</html>

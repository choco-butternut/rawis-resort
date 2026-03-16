<?php
require_once __DIR__ . '/php/config.php';

$room_types_result = $conn->query("SELECT DISTINCT room_type FROM rooms WHERE room_status='available' ORDER BY room_type ASC");
$room_types = [];
while ($rt = $room_types_result->fetch_assoc()) {
    $room_types[] = $rt['room_type'];
}

$price_range = $conn->query("SELECT MIN(price_per_night) as min_price, MAX(price_per_night) as max_price FROM rooms WHERE room_status='available'")->fetch_assoc();
$min_price = (int) ($price_range['min_price'] ?? 0);
$max_price = (int) ($price_range['max_price'] ?? 10000);

$rooms = $conn->query("SELECT * FROM rooms WHERE room_status='available' ORDER BY price_per_night ASC");
$all_rooms = [];
while ($r = $rooms->fetch_assoc()) {
    $all_rooms[] = $r;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms | Rawis Resort</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* ══════════════════════════════════════
           FILTER BAR
        ══════════════════════════════════════ */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            background: #fff;
            border: 1px solid #ede8e1;
            border-radius: 12px;
            padding: 18px 24px;
            margin: 20px auto;
            max-width: 1100px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 140px;
        }
        .filter-group.price-group {
            flex: 1.8;
            min-width: 220px;
        }
        .filter-group label {
            font-family: Poppins, sans-serif;
            font-size: 11px;
            font-weight: 700;
            color: #531e07;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .filter-group select,
        .filter-group input[type="number"] {
            padding: 9px 12px;
            border: 1.5px solid #e2ddd8;
            border-radius: 8px;
            font-family: Poppins, sans-serif;
            font-size: 14px;
            background: #faf8f6;
            color: #341f0c;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .filter-group select:focus,
        .filter-group input[type="number"]:focus {
            border-color: #bbcc81;
            box-shadow: 0 0 0 3px rgba(187,204,129,0.18);
        }
        .price-range-row {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .price-range-row span {
            color: #aaa;
            font-size: 13px;
            flex-shrink: 0;
        }
        .price-range-row input {
            width: 90px;
            min-width: 0;
            flex: 1;
        }
        .btn-filter-reset {
            padding: 9px 20px;
            background: #faf8f6;
            border: 1.5px solid #e2ddd8;
            border-radius: 8px;
            font-family: Poppins, sans-serif;
            font-size: 14px;
            cursor: pointer;
            color: #531e07;
            font-weight: 500;
            transition: background 0.2s, border-color 0.2s;
            height: fit-content;
            align-self: flex-end;
        }
        .btn-filter-reset:hover {
            background: #ede8e1;
            border-color: #bbcc81;
        }
        .filter-results-count {
            font-family: Poppins, sans-serif;
            font-size: 13px;
            color: #888;
            margin: 0 auto 10px;
            max-width: 1100px;
            padding: 0 4px;
        }

        /* ══════════════════════════════════════
           MULTI-STEP RESERVATION MODAL
        ══════════════════════════════════════ */
        .rm-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(52,31,12,0.60);
            backdrop-filter: blur(5px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .rm-overlay.show { display: flex; }

        .rm-shell {
            background: #fff;
            border-radius: 22px;
            width: 100%;
            max-width: 720px;
            max-height: 93vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 40px 100px rgba(52,31,12,0.28);
            animation: rmSlide 0.3s cubic-bezier(.22,.68,0,1.18);
        }
        @keyframes rmSlide {
            from { transform: translateY(44px) scale(0.96); opacity: 0; }
            to   { transform: none; opacity: 1; }
        }

        /* Header */
        .rm-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 26px;
            border-bottom: 1px solid #ede8e1;
            background: #faf8f5;
            flex-shrink: 0;
        }
        .rm-header-left { display: flex; align-items: center; gap: 14px; }
        .rm-badge {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #bbcc81, #334937);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 17px; flex-shrink: 0;
        }
        .rm-title {
            font-family: 'The Seasons', serif;
            margin: 0; font-size: 20px; font-weight: 400; color: #341f0c;
        }
        .rm-subtitle {
            font-family: Poppins, sans-serif;
            margin: 2px 0 0; font-size: 12px; color: #8a7060;
        }
        .rm-close {
            width: 34px; height: 34px;
            border: 1px solid #e2ddd8; border-radius: 8px;
            background: #fff; cursor: pointer; font-size: 14px; color: #8a7060;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .rm-close:hover { background: #fdf0ee; border-color: #c0392b; color: #c0392b; }

        /* Stepper */
        .rm-stepper {
            display: flex;
            align-items: center;
            padding: 16px 30px;
            background: #faf8f5;
            border-bottom: 1px solid #ede8e1;
            flex-shrink: 0;
        }
        .rm-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }
        .rm-step-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #e8e3dc;
            color: #a89080;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            transition: background 0.3s, color 0.3s, box-shadow 0.3s;
        }
        .rm-step span {
            font-family: Poppins, sans-serif;
            font-size: 10px;
            color: #a89080;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: color 0.3s;
            white-space: nowrap;
        }
        .rm-step.active .rm-step-circle {
            background: linear-gradient(135deg, #bbcc81, #334937);
            color: #fff;
            box-shadow: 0 4px 14px rgba(187,204,129,0.45);
        }
        .rm-step.active span { color: #334937; }
        .rm-step.done .rm-step-circle { background: #334937; color: #fff; }
        .rm-step.done span { color: #334937; }
        .rm-step-line {
            flex: 1;
            height: 2px;
            background: #e8e3dc;
            margin: 0 6px;
            margin-bottom: 18px;
            transition: background 0.4s;
        }
        .rm-step-line.done { background: #334937; }

        /* Steps wrapper */
        .rm-steps-wrap {
            position: relative;
            flex: 1;
            overflow: hidden;
        }
        .rm-step-panel {
            display: none;
            flex-direction: column;
            height: 100%;
        }
        .rm-step-panel.active {
            display: flex;
            animation: panelIn 0.28s ease;
        }
        @keyframes panelIn {
            from { opacity: 0; transform: translateX(18px); }
            to   { opacity: 1; transform: none; }
        }
        @keyframes panelInBack {
            from { opacity: 0; transform: translateX(-18px); }
            to   { opacity: 1; transform: none; }
        }
        .rm-step-panel.slide-back { animation: panelInBack 0.28s ease; }

        .rm-panel-inner {
            flex: 1;
            overflow-y: auto;
            padding: 22px 28px;
        }
        .rm-panel-inner::-webkit-scrollbar { width: 5px; }
        .rm-panel-inner::-webkit-scrollbar-track { background: transparent; }
        .rm-panel-inner::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

        /* Nav footer */
        .rm-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 28px;
            border-top: 1px solid #ede8e1;
            background: #faf8f5;
            flex-shrink: 0;
        }
        .rm-nav-confirm { justify-content: flex-start; }
        .rm-btn-next {
            padding: 11px 24px;
            background: linear-gradient(135deg, #bbcc81 0%, #334937 100%);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-family: Poppins, sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            transition: opacity 0.2s, transform 0.15s;
        }
        .rm-btn-next:hover { opacity: 0.88; transform: translateY(-1px); }
        .rm-btn-ghost {
            padding: 10px 18px;
            background: transparent;
            border: 1.5px solid #ddd;
            border-radius: 50px;
            font-family: Poppins, sans-serif;
            font-size: 13px;
            color: #8a7060;
            cursor: pointer;
            display: flex; align-items: center; gap: 7px;
            transition: background 0.15s;
        }
        .rm-btn-ghost:hover { background: #f0ece6; }

        /* Section labels */
        .rm-section-label {
            font-family: Poppins, sans-serif;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #8a7060;
            margin: 0 0 14px;
            display: flex; align-items: center; gap: 7px;
        }
        .rm-section-label i { color: #bbcc81; }

        /* Fields */
        .rm-fields-wrap { margin-top: 16px; }
        .rm-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .rm-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 12px; }
        .rm-field label {
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: #5a4030;
        }
        .rm-field input,
        .rm-field select,
        .rm-field textarea {
            padding: 9px 12px;
            border: 1.5px solid #e2ddd8;
            border-radius: 9px;
            font-family: Poppins, sans-serif;
            font-size: 13px;
            color: #341f0c;
            background: #faf8f5;
            transition: border-color 0.2s;
            outline: none;
            box-sizing: border-box;
        }
        .rm-field input:focus,
        .rm-field select:focus,
        .rm-field textarea:focus { border-color: #bbcc81; background: #fff; }
        .rm-field textarea { resize: vertical; }
        .req { color: #e53e3e; }

        /* Room preview (step 1) */
        .rm-panel-hero {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            height: 120px;
            margin-bottom: 20px;
        }
        .rm-room-preview { width: 100%; height: 100%; object-fit: cover; }
        .rm-room-chip {
            position: absolute;
            bottom: 10px; left: 12px;
            background: rgba(52,31,12,0.75);
            color: #fff;
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }

        /* Amenity grid (step 2) */
        .rm-amenities-header { margin-bottom: 16px; }
        .rm-amenity-hint { font-family: Poppins, sans-serif; font-size: 13px; color: #8a7060; margin: -8px 0 0; }
        .rm-amenity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .rm-amenity-card {
            border: 1.5px solid #e2ddd8;
            border-radius: 12px;
            overflow: hidden;
            background: #faf8f5;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .rm-amenity-card:hover { border-color: #bbcc81; box-shadow: 0 4px 14px rgba(187,204,129,0.2); }
        .rm-amenity-img { width: 100%; height: 80px; object-fit: cover; }
        .rm-amenity-img-placeholder {
            width: 100%; height: 80px;
            background: linear-gradient(135deg, #ede8e1, #e2ddd8);
            display: flex; align-items: center; justify-content: center;
            color: #bbb; font-size: 22px;
        }
        .rm-amenity-info { padding: 8px 10px 4px; }
        .rm-amenity-name { font-family: Poppins, sans-serif; font-size: 13px; font-weight: 600; color: #341f0c; margin: 0 0 2px; }
        .rm-amenity-price { font-family: Poppins, sans-serif; font-size: 11px; color: #8a7060; margin: 0; }
        .rm-amenity-qty {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 10px;
            border-top: 1px solid #ede8e1;
        }
        .qty-btn {
            width: 26px; height: 26px;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            background: #fff;
            font-size: 15px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #334937;
            transition: background 0.15s;
        }
        .qty-btn:hover { background: #bbcc81; color: #fff; border-color: #bbcc81; }
        .qty-input {
            width: 38px;
            text-align: center;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-family: Poppins, sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #341f0c;
            padding: 3px 0;
        }
        .rm-no-amenities {
            font-family: Poppins, sans-serif;
            font-size: 13px; color: #aaa;
            grid-column: 1/-1; text-align: center; padding: 30px 0;
        }
        .rm-amenity-summary-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            background: #f0ece6;
            border-radius: 10px;
            font-family: Poppins, sans-serif;
            font-size: 13px;
            color: #5a4030;
        }
        .rm-amenity-summary-bar strong { color: #334937; font-size: 15px; }

        /* Payment options (step 3) */
        .rm-pay-options { display: flex; flex-direction: column; gap: 10px; margin-bottom: 18px; }
        .rm-pay-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border: 2px solid #e2ddd8;
            border-radius: 12px;
            cursor: pointer;
            background: #faf8f5;
            transition: border-color 0.2s, background 0.2s;
        }
        .rm-pay-card input[type="radio"] { display: none; }
        .rm-pay-card.rm-pay-selected { border-color: #334937; background: #f0f5ee; }
        .rm-pay-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .rm-pay-icon.cash  { background: #ecfdf5; color: #059669; }
        .rm-pay-icon.gcash { background: #eff6ff; color: #1d4ed8; }
        .rm-pay-icon.card  { background: #f5f3ff; color: #7c3aed; }
        .rm-pay-text { flex: 1; }
        .rm-pay-text strong { font-family: Poppins, sans-serif; font-size: 14px; color: #341f0c; display: block; }
        .rm-pay-text span   { font-family: Poppins, sans-serif; font-size: 12px; color: #8a7060; }
        .rm-pay-check { color: #ccc; font-size: 20px; transition: color 0.2s; }
        .rm-pay-card.rm-pay-selected .rm-pay-check { color: #334937; }

        /* Payment detail boxes */
        .rm-pay-detail {
            display: none;
            padding: 14px 16px;
            border-radius: 12px;
            font-family: Poppins, sans-serif;
            font-size: 13px;
            margin-top: 4px;
            line-height: 1.6;
        }
        .rm-pay-detail.show { display: block; }
        .rm-pay-note-cash  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .rm-pay-note-gcash { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .rm-pay-note-card  { background: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe; }
        .rm-pay-note-cash i,
        .rm-pay-note-gcash i,
        .rm-pay-note-card i { margin-right: 6px; }

        /* GCash box */
        .rm-gcash-box { text-align: center; margin-bottom: 12px; }
        .rm-gcash-title { font-size: 13px; font-weight: 700; margin: 0 0 10px; }
        .rm-qr-wrapper { margin: 0 auto 10px; width: 110px; }
        .rm-qr-img {
            width: 110px; height: 110px;
            border-radius: 10px; border: 2px solid #93c5fd;
            object-fit: contain; background: #fff;
        }
        .rm-qr-fallback {
            width: 110px; height: 110px;
            border-radius: 10px; border: 2px dashed #93c5fd;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; color: #93c5fd;
        }
        .rm-gcash-number { font-size: 13px; font-weight: 600; }
        .rm-ref-field { display: flex; flex-direction: column; gap: 5px; }
        .rm-ref-field label { font-size: 12px; font-weight: 600; color: #1e40af; }
        .rm-ref-field input {
            padding: 9px 12px;
            border: 1.5px solid #bfdbfe;
            border-radius: 9px;
            font-family: Poppins, sans-serif;
            font-size: 13px;
            background: #fff;
        }

        /* Card fields */
        .rm-card-fields { display: flex; flex-direction: column; gap: 10px; }
        .rm-card-num-wrap { position: relative; display: flex; align-items: center; }
        .rm-card-num-wrap input {
            flex: 1; padding: 9px 12px;
            border: 1.5px solid #ddd6fe;
            border-radius: 9px;
            font-family: Poppins, sans-serif;
            font-size: 13px; background: #fff;
        }
        #rm-card-brand { position: absolute; right: 10px; font-size: 22px; }

        /* Confirm step (step 4) */
        .rm-confirm-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }
        .rm-confirm-left, .rm-confirm-right { display: flex; flex-direction: column; }
        .rm-summary-room-img {
            position: relative; border-radius: 12px;
            overflow: hidden; height: 110px; margin-bottom: 16px;
        }
        .rm-summary-room-img img { width: 100%; height: 100%; object-fit: cover; }
        .confirm-room-chip {
            position: absolute; bottom: 8px; left: 10px;
            background: rgba(52,31,12,0.75); color: #fff;
            font-family: Poppins, sans-serif; font-size: 11px; font-weight: 600;
            padding: 3px 10px; border-radius: 20px; backdrop-filter: blur(4px);
        }
        .rm-confirm-block { display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px; }
        .rm-confirm-row {
            display: flex; justify-content: space-between; align-items: center;
            font-family: Poppins, sans-serif; font-size: 13px;
        }
        .rm-confirm-row span { color: #8a7060; }
        .rm-confirm-row strong { color: #341f0c; font-size: 13px; text-align: right; max-width: 60%; }
        .rm-total-row strong { font-size: 16px; color: #334937; }
        .rm-summary-divider { height: 1px; background: #ede8e1; margin: 8px 0; }

        /* Pay recap */
        .rm-pay-recap {
            padding: 14px;
            border: 1.5px solid #e2ddd8;
            border-radius: 12px;
            background: #faf8f5;
            font-family: Poppins, sans-serif;
            font-size: 13px;
            color: #341f0c;
            margin-bottom: 16px;
        }
        .rm-pay-recap .method-pill {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 700;
            padding: 5px 14px; border-radius: 20px; margin-bottom: 8px;
        }
        .method-pill.cash  { background: #ecfdf5; color: #059669; }
        .method-pill.gcash { background: #eff6ff; color: #1d4ed8; }
        .method-pill.card  { background: #f5f3ff; color: #7c3aed; }
        .rm-pay-recap p { margin: 6px 0 0; font-size: 12px; color: #8a7060; line-height: 1.6; }
        .rm-pay-recap .ref-line { margin-top: 8px; font-size: 12px; color: #334937; font-weight: 600; }

        /* Confirm CTA */
        .rm-confirm-cta { display: flex; flex-direction: column; gap: 8px; margin-top: auto; }
        .rm-btn-confirm {
            width: 100%;
            padding: 13px;
            background: linear-gradient(to right, #bbcc81 10%, #334937 80%);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-family: Poppins, sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.2);
            box-shadow: 0 4px 16px rgba(51,73,55,0.3);
            transition: opacity 0.2s, transform 0.15s;
        }
        .rm-btn-confirm:hover { opacity: 0.9; transform: translateY(-1px); }
        .rm-btn-cancel {
            width: 100%;
            padding: 10px;
            background: transparent;
            color: #8a7060;
            border: 1px solid #ddd;
            border-radius: 50px;
            font-family: Poppins, sans-serif;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .rm-btn-cancel:hover { background: #f1ece6; }

        /* ══════════════════════════════════════
           ROOM DETAIL MODAL
        ══════════════════════════════════════ */
        #roomDetailModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(52,31,12,0.58);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        #roomDetailModal.show { display: flex; }
        .room-detail-content {
            background: #fff;
            border-radius: 18px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(52,31,12,0.25);
            animation: slideUp 0.25s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }
        .room-detail-image {
            width: 100%; height: 260px;
            object-fit: cover;
            border-radius: 14px 14px 0 0;
        }
        .room-detail-body { padding: 28px 32px; }
        .room-detail-header {
            display: flex; justify-content: space-between;
            align-items: flex-start; margin-bottom: 16px;
        }
        .room-detail-header h2 {
            font-family: 'The Seasons', serif;
            margin: 0; font-size: 26px; font-weight: 400; color: #341f0c;
        }
        .room-detail-price { text-align: right; }
        .room-detail-price .big-price {
            font-family: 'The Seasons', serif;
            font-size: 28px; font-weight: 400; color: #334937;
        }
        .room-detail-price .per-night {
            font-family: Poppins, sans-serif;
            font-size: 12px; color: #888;
        }
        .room-meta {
            display: flex; gap: 20px; flex-wrap: wrap;
            margin-bottom: 20px; padding: 16px;
            background: #faf8f5; border-radius: 10px;
            border: 1px solid #ede8e1;
        }
        .room-meta-item {
            display: flex; align-items: center; gap: 8px;
            font-family: Poppins, sans-serif;
            font-size: 14px; color: #531e07;
        }
        .room-meta-item i { color: #bbcc81; width: 16px; }
        .detail-status-pill {
            display: inline-block; padding: 4px 14px;
            border-radius: 20px;
            font-family: Poppins, sans-serif;
            font-size: 12px; font-weight: 600; text-transform: capitalize;
        }
        .detail-status-pill.available   { background: #e8f0d8; color: #2d5a27; }
        .detail-status-pill.occupied    { background: #fde8e8; color: #9b2226; }
        .detail-status-pill.maintenance { background: #fff8f0; color: #8e4a0f; }
        .room-detail-divider { border: none; border-top: 1px solid #ede8e1; margin: 20px 0; }
        .detail-section-title {
            font-family: Poppins, sans-serif;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: #aaa; margin-bottom: 10px;
        }
        #detailDescription {
            font-family: Poppins, sans-serif;
            color: #555; font-size: 14px; line-height: 1.75;
        }
        .room-detail-actions { display: flex; gap: 12px; margin-top: 24px; }
        .btn-detail-book {
            flex: 1; padding: 12px;
            background: linear-gradient(to right, #bbcc81 10%, #334937 80%);
            color: #fff; border: none; border-radius: 50px;
            font-family: Poppins, sans-serif;
            font-size: 15px; font-weight: 700; cursor: pointer;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
            box-shadow: 0 4px 14px rgba(51,73,55,0.25);
            transition: opacity 0.2s, transform 0.15s;
        }
        .btn-detail-book:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-detail-close {
            padding: 12px 24px;
            background: #faf8f5; color: #531e07;
            border: 1.5px solid #e2ddd8; border-radius: 50px;
            font-family: Poppins, sans-serif;
            font-size: 15px; cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-detail-close:hover { background: #ede8e1; border-color: #bbcc81; }
        .modal-x-btn {
            position: absolute; top: 14px; right: 18px;
            background: rgba(52,31,12,0.4);
            border: none; color: #fff; border-radius: 50%;
            width: 32px; height: 32px; font-size: 16px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.15s;
        }
        .modal-x-btn:hover { background: rgba(52,31,12,0.65); }
        .room-detail-image-wrap { position: relative; }

        /* ══════════════════════════════════════
           NO RESULTS
        ══════════════════════════════════════ */
        .no-results {
            text-align: center; padding: 60px 20px;
            color: #aaa; display: none;
            font-family: Poppins, sans-serif;
        }
        .no-results i { font-size: 48px; margin-bottom: 12px; color: #bbcc81; }

        /* ══════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════ */
        @media (max-width: 640px) {
            .rm-shell {
                max-width: 100%;
                border-radius: 16px 16px 0 0;
                position: fixed; bottom: 0; top: auto;
                max-height: 95vh;
            }
            .rm-overlay { align-items: flex-end; }
            .rm-confirm-layout { grid-template-columns: 1fr; }
            .rm-stepper { padding: 12px 16px; }
            .rm-stepper .rm-step span { display: none; }
            .rm-panel-inner { padding: 16px 18px; }
            .rm-nav { padding: 12px 18px; }
            .rm-field-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="customer-page">
<div class="rooms-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <!-- ══════════════════════════════════════
         MULTI-STEP RESERVATION MODAL
    ══════════════════════════════════════ -->
    <div id="reserveModal" class="rm-overlay">
        <div class="rm-shell">

            <div class="rm-header">
                <div class="rm-header-left">
                    <span class="rm-badge"><i class="fas fa-calendar-check"></i></span>
                    <div>
                        <h2 class="rm-title">Reserve Your Room</h2>
                        <p class="rm-subtitle" id="rm-room-label">Loading room details…</p>
                    </div>
                </div>
                <button class="rm-close" onclick="closeModal()" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="rm-stepper">
                <div class="rm-step active" id="step-dot-1">
                    <div class="rm-step-circle"><i class="fas fa-user"></i></div>
                    <span>Guest Info</span>
                </div>
                <div class="rm-step-line" id="line-1"></div>
                <div class="rm-step" id="step-dot-2">
                    <div class="rm-step-circle"><i class="fas fa-concierge-bell"></i></div>
                    <span>Amenities</span>
                </div>
                <div class="rm-step-line" id="line-2"></div>
                <div class="rm-step" id="step-dot-3">
                    <div class="rm-step-circle"><i class="fas fa-credit-card"></i></div>
                    <span>Payment</span>
                </div>
                <div class="rm-step-line" id="line-3"></div>
                <div class="rm-step" id="step-dot-4">
                    <div class="rm-step-circle"><i class="fas fa-check"></i></div>
                    <span>Confirm</span>
                </div>
            </div>

            <form method="POST" action="/php/reserve.php" id="reserveForm">
                <input type="hidden" name="room_id"          id="room_id">
                <input type="hidden" name="payment_method"   id="hidden_pay_method" value="Cash">
                <input type="hidden" name="reference_number" id="hidden_ref_number" value="">

                <div class="rm-steps-wrap">

                    <!-- STEP 1 — Guest Information -->
                    <div class="rm-step-panel active" id="panel-1">
                        <div class="rm-panel-inner">
                            <div class="rm-panel-hero">
                                <img id="rm-room-img" src="" alt="Room" class="rm-room-preview">
                                <div class="rm-room-chip" id="rm-room-chip">—</div>
                            </div>
                            <div class="rm-fields-wrap">
                                <p class="rm-section-label"><i class="fas fa-user-circle"></i> Your Details</p>
                                <div class="rm-field-row">
                                    <div class="rm-field">
                                        <label>First Name <span class="req">*</span></label>
                                        <input type="text" name="first_name" id="f_first" placeholder="Juan" required>
                                    </div>
                                    <div class="rm-field">
                                        <label>Last Name <span class="req">*</span></label>
                                        <input type="text" name="last_name" id="f_last" placeholder="dela Cruz" required>
                                    </div>
                                </div>
                                <div class="rm-field-row">
                                    <div class="rm-field">
                                        <label>Email <span class="req">*</span></label>
                                        <input type="email" name="email" id="f_email" placeholder="you@email.com" required>
                                    </div>
                                    <div class="rm-field">
                                        <label>Phone <span class="req">*</span></label>
                                        <input type="text" name="phone_number" id="f_phone" placeholder="09XX XXX XXXX" required>
                                    </div>
                                </div>
                                <div class="rm-field">
                                    <label>Address</label>
                                    <input type="text" name="address" placeholder="City, Province">
                                </div>
                                <p class="rm-section-label" style="margin-top:18px"><i class="fas fa-calendar-alt"></i> Stay Dates</p>
                                <div class="rm-field-row">
                                    <div class="rm-field">
                                        <label>Check-in <span class="req">*</span></label>
                                        <input type="date" name="check_in_date" id="modal_checkin" required>
                                    </div>
                                    <div class="rm-field">
                                        <label>Check-out <span class="req">*</span></label>
                                        <input type="date" name="check_out_date" id="modal_checkout" required>
                                    </div>
                                </div>
                                <div class="rm-field">
                                    <label>Special Requests</label>
                                    <textarea name="extra_requests" placeholder="Allergies, preferences, early check-in…" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="rm-nav">
                            <button type="button" class="rm-btn-ghost" onclick="closeModal()">Cancel</button>
                            <button type="button" class="rm-btn-next" onclick="goStep(2)">
                                Next: Amenities <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2 — Amenities -->
                    <div class="rm-step-panel" id="panel-2">
                        <div class="rm-panel-inner">
                            <div class="rm-amenities-header">
                                <p class="rm-section-label"><i class="fas fa-concierge-bell"></i> Add-on Amenities</p>
                                <p class="rm-amenity-hint">Select any extras you'd like included in your stay.</p>
                            </div>
                            <div class="rm-amenity-grid" id="amenity-grid">
                                <?php
                                $amenityRes = $conn->query("SELECT * FROM amenities WHERE amenity_status='Available' ORDER BY amenity_name");
                                if ($amenityRes && $amenityRes->num_rows > 0):
                                    while ($amenity = $amenityRes->fetch_assoc()):
                                ?>
                                <div class="rm-amenity-card" data-price="<?= $amenity['price']; ?>" data-id="<?= $amenity['amenity_id']; ?>">
                                    <?php if (!empty($amenity['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($amenity['image_path']); ?>" alt="<?= htmlspecialchars($amenity['amenity_name']); ?>" class="rm-amenity-img">
                                    <?php else: ?>
                                        <div class="rm-amenity-img-placeholder"><i class="fas fa-star"></i></div>
                                    <?php endif; ?>
                                    <div class="rm-amenity-info">
                                        <p class="rm-amenity-name"><?= htmlspecialchars($amenity['amenity_name']); ?></p>
                                        <p class="rm-amenity-price">₱<?= number_format($amenity['price'], 2); ?> / use</p>
                                    </div>
                                    <div class="rm-amenity-qty">
                                        <button type="button" class="qty-btn" onclick="changeQty(<?= $amenity['amenity_id']; ?>, -1)">−</button>
                                        <input type="number"
                                               name="amenity_id[<?= $amenity['amenity_id']; ?>]"
                                               id="qty_<?= $amenity['amenity_id']; ?>"
                                               value="0" min="0" max="20"
                                               class="qty-input"
                                               onchange="updateSummary()">
                                        <button type="button" class="qty-btn" onclick="changeQty(<?= $amenity['amenity_id']; ?>, 1)">+</button>
                                    </div>
                                </div>
                                <?php endwhile; else: ?>
                                    <p class="rm-no-amenities">No amenities available right now.</p>
                                <?php endif; ?>
                            </div>
                            <div class="rm-amenity-summary-bar">
                                <span>Amenities subtotal:</span>
                                <strong id="amenity-subtotal">₱0.00</strong>
                            </div>
                        </div>
                        <div class="rm-nav">
                            <button type="button" class="rm-btn-ghost" onclick="goStep(1)"><i class="fas fa-arrow-left"></i> Back</button>
                            <button type="button" class="rm-btn-next" onclick="goStep(3)">
                                Next: Payment <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3 — Payment -->
                    <div class="rm-step-panel" id="panel-3">
                        <div class="rm-panel-inner">
                            <p class="rm-section-label"><i class="fas fa-wallet"></i> Choose Payment Method</p>
                            <div class="rm-pay-options">
                                <label class="rm-pay-card rm-pay-selected" id="pay-opt-cash">
                                    <input type="radio" name="_pay_ui" value="Cash" checked onchange="selectPayMethod('Cash')">
                                    <div class="rm-pay-icon cash"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="rm-pay-text">
                                        <strong>Cash</strong>
                                        <span>Pay on arrival</span>
                                    </div>
                                    <div class="rm-pay-check"><i class="fas fa-check-circle"></i></div>
                                </label>
                                <label class="rm-pay-card" id="pay-opt-gcash">
                                    <input type="radio" name="_pay_ui" value="GCash" onchange="selectPayMethod('GCash')">
                                    <div class="rm-pay-icon gcash"><i class="fas fa-mobile-alt"></i></div>
                                    <div class="rm-pay-text">
                                        <strong>GCash</strong>
                                        <span>Send via e-wallet</span>
                                    </div>
                                    <div class="rm-pay-check"><i class="fas fa-check-circle"></i></div>
                                </label>
                                <label class="rm-pay-card" id="pay-opt-card">
                                    <input type="radio" name="_pay_ui" value="Card" onchange="selectPayMethod('Card')">
                                    <div class="rm-pay-icon card"><i class="fas fa-credit-card"></i></div>
                                    <div class="rm-pay-text">
                                        <strong>Credit / Debit Card</strong>
                                        <span>Visa, Mastercard, Amex</span>
                                    </div>
                                    <div class="rm-pay-check"><i class="fas fa-check-circle"></i></div>
                                </label>
                            </div>

                            <div id="pay-detail-cash" class="rm-pay-detail rm-pay-note-cash show">
                                <i class="fas fa-info-circle"></i>
                                Your booking will be <strong>pending</strong> until you pay at the front desk on check-in.
                            </div>

                            <div id="pay-detail-gcash" class="rm-pay-detail rm-pay-note-gcash">
                                <div class="rm-gcash-box">
                                    <p class="rm-gcash-title"><i class="fas fa-mobile-alt"></i> Send via GCash</p>
                                    <div class="rm-qr-wrapper">
                                        <img src="assets/images/gcash-qr.png" alt="GCash QR" class="rm-qr-img"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="rm-qr-fallback" style="display:none">
                                            <i class="fas fa-qrcode"></i>
                                        </div>
                                    </div>
                                    <p class="rm-gcash-number"><strong>0977 183 7288</strong> — Rawis Resort Hotel</p>
                                </div>
                                <div class="rm-ref-field" style="margin-top:14px">
                                    <label>GCash Reference Number <span class="req">*</span></label>
                                    <input type="text" id="gcash-ref-input" placeholder="e.g. 2024031512345678">
                                </div>
                            </div>

                            <div id="pay-detail-card" class="rm-pay-detail rm-pay-note-card">
                                <div class="rm-card-fields">
                                    <div class="rm-field">
                                        <label>Cardholder Name <span class="req">*</span></label>
                                        <input type="text" id="card-name-input" placeholder="Full name on card">
                                    </div>
                                    <div class="rm-field">
                                        <label>Card Number <span class="req">*</span></label>
                                        <div class="rm-card-num-wrap">
                                            <input type="text" id="rm-card-number" placeholder="•••• •••• •••• ••••"
                                                   maxlength="19" oninput="rmFormatCard(this)">
                                            <span id="rm-card-brand"></span>
                                        </div>
                                    </div>
                                    <div class="rm-field-row">
                                        <div class="rm-field">
                                            <label>Expiry <span class="req">*</span></label>
                                            <input type="text" id="card-expiry-input" placeholder="MM / YY" maxlength="7"
                                                   oninput="rmFormatExpiry(this)">
                                        </div>
                                        <div class="rm-field">
                                            <label>CVV <span class="req">*</span></label>
                                            <input type="text" id="card-cvv-input" placeholder="•••" maxlength="4">
                                        </div>
                                    </div>
                                    <div class="rm-ref-field">
                                        <label>Transaction / Approval Code <span class="req">*</span></label>
                                        <input type="text" id="card-ref-input" placeholder="e.g. AUTH123456">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="rm-nav">
                            <button type="button" class="rm-btn-ghost" onclick="goStep(2)"><i class="fas fa-arrow-left"></i> Back</button>
                            <button type="button" class="rm-btn-next" onclick="goStep(4)">
                                Review Booking <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 4 — Confirm -->
                    <div class="rm-step-panel" id="panel-4">
                        <div class="rm-panel-inner rm-confirm-layout">
                            <div class="rm-confirm-left">
                                <p class="rm-section-label"><i class="fas fa-receipt"></i> Booking Summary</p>
                                <div class="rm-summary-room-img">
                                    <img id="confirm-room-img" src="" alt="Room">
                                    <div class="confirm-room-chip" id="confirm-room-chip">—</div>
                                </div>
                                <div class="rm-confirm-block">
                                    <div class="rm-confirm-row"><span>Guest</span><strong id="c-guest">—</strong></div>
                                    <div class="rm-confirm-row"><span>Email</span><strong id="c-email">—</strong></div>
                                    <div class="rm-confirm-row"><span>Phone</span><strong id="c-phone">—</strong></div>
                                </div>
                                <div class="rm-summary-divider"></div>
                                <div class="rm-confirm-block">
                                    <div class="rm-confirm-row"><span>Check-in</span><strong id="c-checkin">—</strong></div>
                                    <div class="rm-confirm-row"><span>Check-out</span><strong id="c-checkout">—</strong></div>
                                    <div class="rm-confirm-row"><span>Nights</span><strong id="c-nights">—</strong></div>
                                </div>
                                <div class="rm-summary-divider"></div>
                                <div class="rm-confirm-block">
                                    <div class="rm-confirm-row"><span>Room Cost</span><strong id="c-room-cost">₱0.00</strong></div>
                                    <div id="c-amenities-list"></div>
                                    <div class="rm-summary-divider"></div>
                                    <div class="rm-confirm-row rm-total-row">
                                        <span>Total</span>
                                        <strong id="c-total">₱0.00</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="rm-confirm-right">
                                <p class="rm-section-label"><i class="fas fa-wallet"></i> Payment</p>
                                <div class="rm-pay-recap" id="pay-recap"></div>
                                <div class="rm-confirm-cta">
                                    <button type="submit" class="rm-btn-confirm">
                                        <i class="fas fa-check-circle"></i> Confirm Reservation
                                    </button>
                                    <button type="button" class="rm-btn-cancel" onclick="closeModal()">Cancel</button>
                                </div>
                            </div>
                        </div>
                        <div class="rm-nav rm-nav-confirm">
                            <button type="button" class="rm-btn-ghost" onclick="goStep(3)"><i class="fas fa-arrow-left"></i> Back</button>
                        </div>
                    </div>

                </div><!-- /.rm-steps-wrap -->
            </form>
        </div><!-- /.rm-shell -->
    </div><!-- /.rm-overlay -->

    <!-- ══════════════════════════════════════
         ROOM DETAIL MODAL
    ══════════════════════════════════════ -->
    <div id="roomDetailModal">
        <div class="room-detail-content">
            <div class="room-detail-image-wrap">
                <img id="detailImage" src="" alt="Room" class="room-detail-image">
                <button class="modal-x-btn" onclick="closeDetailModal()">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="room-detail-body">
                <div class="room-detail-header">
                    <div>
                        <h2 id="detailType"></h2>
                        <span id="detailStatus" class="detail-status-pill"></span>
                    </div>
                    <div class="room-detail-price">
                        <div class="big-price"><span id="detailPrice"></span></div>
                        <div class="per-night">per night</div>
                    </div>
                </div>
                <div class="room-meta">
                    <div class="room-meta-item">
                        <i class="fas fa-door-open"></i>
                        <span>Room <strong id="detailNumber"></strong></span>
                    </div>
                    <div class="room-meta-item">
                        <i class="fas fa-users"></i>
                        <span>Up to <strong id="detailCapacity"></strong> guests</span>
                    </div>
                    <div class="room-meta-item">
                        <i class="fas fa-tag"></i>
                        <span id="detailType2"></span>
                    </div>
                </div>
                <hr class="room-detail-divider">
                <p class="detail-section-title">About this room</p>
                <p id="detailDescription"></p>
                <div class="room-detail-actions">
                    <button class="btn-detail-book" id="detailBookBtn" onclick="bookFromDetail()">
                        <i class="fas fa-calendar-check"></i> Book This Room
                    </button>
                    <button class="btn-detail-close" onclick="closeDetailModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         PAGE CONTENT
    ══════════════════════════════════════ -->
    <div class="page-header">
        <h1>Room Options</h1>
    </div>

    <div class="filter-bar">
        <div class="filter-group">
            <label><i class="fas fa-bed"></i> Room Type</label>
            <select id="filterType">
                <option value="">All Types</option>
                <?php foreach ($room_types as $rt): ?>
                    <option value="<?= htmlspecialchars($rt); ?>"><?= htmlspecialchars($rt); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-users"></i> Min Capacity</label>
            <select id="filterCapacity">
                <option value="0">Any</option>
                <option value="1">1+ Guest</option>
                <option value="2">2+ Guests</option>
                <option value="3">3+ Guests</option>
                <option value="4">4+ Guests</option>
                <option value="5">5+ Guests</option>
            </select>
        </div>
        <div class="filter-group price-group">
            <label><i class="fas fa-peso-sign"></i> Price Range (per night)</label>
            <div class="price-range-row">
                <input type="number" id="filterMinPrice" placeholder="Min" min="0" value="">
                <span>–</span>
                <input type="number" id="filterMaxPrice" placeholder="Max" min="0" value="">
            </div>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-sort"></i> Sort By</label>
            <select id="filterSort">
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="capacity_asc">Capacity: Low to High</option>
                <option value="capacity_desc">Capacity: High to Low</option>
            </select>
        </div>
        <button class="btn-filter-reset" onclick="resetFilters()">
            <i class="fas fa-rotate-left"></i> Reset
        </button>
    </div>

    <p class="filter-results-count" id="resultsCount"></p>

    <div class="room-cards" id="roomGrid">
        <?php foreach ($all_rooms as $room):
            $name_parts  = explode(' ', $room["room_type"], 2);
            $first_word  = $name_parts[0];
            $second_word = $name_parts[1] ?? '';
            $description = "Experience comfort in our " . htmlspecialchars($room["room_type"]) . ". This room accommodates up to " . $room["max_capacity"] . " guests and provides a relaxing stay with all essential amenities. Perfect for both leisure and business travelers.";
        ?>
        <div class="room-card"
             data-type="<?= htmlspecialchars($room["room_type"]); ?>"
             data-capacity="<?= (int)$room["max_capacity"]; ?>"
             data-price="<?= (float)$room["price_per_night"]; ?>"
             data-room-id="<?= $room["room_id"]; ?>"
             data-room-number="<?= htmlspecialchars($room["room_number"]); ?>"
             data-image="<?= htmlspecialchars($room["image_path"]); ?>"
             data-status="<?= htmlspecialchars($room["room_status"]); ?>"
             data-description="<?= htmlspecialchars($description); ?>">

            <img src="<?= htmlspecialchars($room["image_path"]); ?>" alt="Room Image" />

            <div class="room-info">
                <div class="room-price-tag">
                    <span class="currency">PHP</span>
                    <span class="price"><?= number_format($room["price_per_night"], 0); ?></span>
                    <span class="per-night">/night</span>
                </div>
                <h3 class="room-title">
                    <span class="main-name"><?= htmlspecialchars($first_word); ?></span>
                    <span class="sub-name"><?= htmlspecialchars($second_word); ?></span>
                </h3>
                <div class="room-details">
                    <p><i class="fa-solid fa-bed"></i> Room: <?= htmlspecialchars($room["room_number"]); ?></p>
                    <p><i class="fa-solid fa-users"></i> <?= $room["max_capacity"]; ?> Guests</p>
                </div>
                <div class="room-card-actions">
                    <a href="#" class="details-link" onclick="openDetailModal(event, this.closest('.room-card'))">See more details</a>
                    <button onclick="openModal(<?= $room['room_id']; ?>, <?= $room['price_per_night']; ?>, '<?= addslashes(htmlspecialchars($room['room_type'])); ?>', '<?= addslashes(htmlspecialchars($room['room_number'])); ?>', '<?= addslashes(htmlspecialchars($room['image_path'])); ?>')">Book</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="no-results" id="noResults">
        <i class="fas fa-search"></i>
        <p>No rooms match your filters. Try adjusting your criteria.</p>
    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>

    <script>
    /* ══════════════════════════════════════════════
       MULTI-STEP RESERVATION MODAL
    ══════════════════════════════════════════════ */
    let currentRoomPrice  = 0;
    let currentRoomType   = '';
    let currentRoomNumber = '';
    let currentRoomImage  = '';
    let currentStep       = 1;

    function openModal(roomId, price, type, number, image) {
        if (price === undefined) {
            const card = document.querySelector(`.room-card[data-room-id="${roomId}"]`);
            if (card) {
                price  = parseFloat(card.dataset.price);
                type   = card.dataset.type;
                number = card.dataset.roomNumber;
                image  = card.dataset.image;
            }
        }
        currentRoomPrice  = parseFloat(price)  || 0;
        currentRoomType   = type   || 'Room';
        currentRoomNumber = number || '';
        currentRoomImage  = image  || '';

        document.getElementById('room_id').value = roomId;

        const label = currentRoomType + (currentRoomNumber ? ' — Room ' + currentRoomNumber : '');
        document.getElementById('rm-room-label').textContent = label;
        document.getElementById('rm-room-img').src           = currentRoomImage;
        document.getElementById('rm-room-chip').textContent  = label;

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('modal_checkin').min  = today;
        document.getElementById('modal_checkout').min = today;

        showStep(1, false);
        document.getElementById('reserveModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('reserveModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    function goStep(n) {
        if (n > currentStep && !validateStep(currentStep)) return;
        const back = n < currentStep;
        showStep(n, back);
        if (n === 4) buildConfirmPanel();
    }

    function showStep(n, back) {
        document.querySelectorAll('.rm-step-panel').forEach(p => p.classList.remove('active', 'slide-back'));
        const panel = document.getElementById('panel-' + n);
        if (back) panel.classList.add('slide-back');
        panel.classList.add('active');
        currentStep = n;
        updateStepper(n);
    }

    function updateStepper(n) {
        for (let i = 1; i <= 4; i++) {
            const dot  = document.getElementById('step-dot-' + i);
            const line = document.getElementById('line-' + i);
            dot.classList.remove('active', 'done');
            if (i < n)  dot.classList.add('done');
            if (i === n) dot.classList.add('active');
            if (line) line.classList.toggle('done', i < n);
        }
    }

    function validateStep(step) {
        if (step === 1) {
            const fields = ['f_first', 'f_last', 'f_email', 'f_phone', 'modal_checkin', 'modal_checkout'];
            for (const id of fields) {
                const el = document.getElementById(id);
                if (!el || !el.value.trim()) {
                    el && el.focus();
                    if (el) { el.style.borderColor = '#e53e3e'; setTimeout(() => { el.style.borderColor = ''; }, 2000); }
                    showToast('Please fill in all required fields.');
                    return false;
                }
            }
            const ci = new Date(document.getElementById('modal_checkin').value);
            const co = new Date(document.getElementById('modal_checkout').value);
            if (co <= ci) {
                showToast('Check-out must be after check-in.');
                document.getElementById('modal_checkout').focus();
                return false;
            }
        }
        if (step === 3) {
            const method = document.getElementById('hidden_pay_method').value;
            if (method === 'GCash') {
                const ref = document.getElementById('gcash-ref-input').value.trim();
                if (!ref) { showToast('Please enter your GCash reference number.'); return false; }
                document.getElementById('hidden_ref_number').value = ref;
            }
            if (method === 'Card') {
                const name   = document.getElementById('card-name-input').value.trim();
                const num    = document.getElementById('rm-card-number').value.trim();
                const expiry = document.getElementById('card-expiry-input').value.trim();
                const cvv    = document.getElementById('card-cvv-input').value.trim();
                const ref    = document.getElementById('card-ref-input').value.trim();
                if (!name || !num || !expiry || !cvv || !ref) {
                    showToast('Please complete all card details.');
                    return false;
                }
                document.getElementById('hidden_ref_number').value = ref;
            }
        }
        return true;
    }

    function updateSummary() {
        let total = 0;
        document.querySelectorAll('.rm-amenity-card').forEach(card => {
            const qty = parseInt(document.getElementById('qty_' + card.dataset.id)?.value) || 0;
            total += qty * (parseFloat(card.dataset.price) || 0);
        });
        document.getElementById('amenity-subtotal').textContent =
            '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function changeQty(id, delta) {
        const inp = document.getElementById('qty_' + id);
        if (!inp) return;
        let v = Math.max(0, (parseInt(inp.value) || 0) + delta);
        inp.value = v;
        updateSummary();
        const card = inp.closest('.rm-amenity-card');
        if (card) card.style.borderColor = v > 0 ? '#bbcc81' : '';
    }

    function selectPayMethod(method) {
        document.getElementById('hidden_pay_method').value = method;
        ['cash', 'gcash', 'card'].forEach(m => {
            document.getElementById('pay-opt-' + m)?.classList.remove('rm-pay-selected');
            document.getElementById('pay-detail-' + m)?.classList.remove('show');
        });
        const key = { Cash: 'cash', GCash: 'gcash', Card: 'card' }[method];
        document.getElementById('pay-opt-' + key)?.classList.add('rm-pay-selected');
        document.getElementById('pay-detail-' + key)?.classList.add('show');
    }

    function buildConfirmPanel() {
        const ci = document.getElementById('modal_checkin').value;
        const co = document.getElementById('modal_checkout').value;
        const nights   = Math.max(1, Math.round((new Date(co + 'T00:00:00') - new Date(ci + 'T00:00:00')) / 86400000));
        const roomCost = currentRoomPrice * nights;

        document.getElementById('confirm-room-img').src          = currentRoomImage;
        document.getElementById('confirm-room-chip').textContent  = currentRoomType + (currentRoomNumber ? ' — Room ' + currentRoomNumber : '');
        document.getElementById('c-guest').textContent    = document.getElementById('f_first').value + ' ' + document.getElementById('f_last').value;
        document.getElementById('c-email').textContent    = document.getElementById('f_email').value;
        document.getElementById('c-phone').textContent    = document.getElementById('f_phone').value;
        document.getElementById('c-checkin').textContent  = fmtDate(ci);
        document.getElementById('c-checkout').textContent = fmtDate(co);
        document.getElementById('c-nights').textContent   = nights + ' night' + (nights > 1 ? 's' : '');
        document.getElementById('c-room-cost').textContent = fmt(roomCost);

        let amTotal = 0, amHTML = '';
        document.querySelectorAll('.rm-amenity-card').forEach(card => {
            const qty = parseInt(document.getElementById('qty_' + card.dataset.id)?.value) || 0;
            if (qty > 0) {
                const sub  = (parseFloat(card.dataset.price) || 0) * qty;
                amTotal   += sub;
                amHTML    += `<div class="rm-confirm-row"><span>${card.querySelector('.rm-amenity-name')?.textContent} ×${qty}</span><strong>${fmt(sub)}</strong></div>`;
            }
        });
        document.getElementById('c-amenities-list').innerHTML = amHTML;
        document.getElementById('c-total').textContent = fmt(roomCost + amTotal);

        const method  = document.getElementById('hidden_pay_method').value;
        const keyMap  = { Cash: 'cash', GCash: 'gcash', Card: 'card' };
        const iconMap = { Cash: 'fas fa-money-bill-wave', GCash: 'fas fa-mobile-alt', Card: 'fas fa-credit-card' };
        const noteMap = {
            Cash:  `Pay ${fmt(roomCost + amTotal)} in cash at the front desk on check-in.`,
            GCash: `Send ${fmt(roomCost + amTotal)} to 0977 183 7288 via GCash.`,
            Card:  'Card payment recorded. Our team will verify the transaction.'
        };
        const ref     = document.getElementById('hidden_ref_number').value;
        document.getElementById('pay-recap').innerHTML =
            `<div class="method-pill ${keyMap[method]}"><i class="${iconMap[method]}"></i> ${method}</div>
             <p>${noteMap[method]}</p>
             ${ref ? `<p class="ref-line"><i class="fas fa-tag"></i> Ref: ${ref}</p>` : ''}`;
    }

    function fmt(n) {
        return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtDate(str) {
        if (!str) return '—';
        return new Date(str + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function showToast(msg) {
        let t = document.getElementById('rm-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'rm-toast';
            t.style.cssText = 'position:fixed;bottom:28px;left:50%;transform:translateX(-50%);background:#341f0c;color:#fff;padding:10px 22px;border-radius:50px;font-family:Poppins,sans-serif;font-size:13px;z-index:9999;box-shadow:0 6px 24px rgba(0,0,0,0.25);pointer-events:none;transition:opacity 0.3s;';
            document.body.appendChild(t);
        }
        t.textContent  = msg;
        t.style.opacity = '1';
        clearTimeout(t._to);
        t._to = setTimeout(() => { t.style.opacity = '0'; }, 2800);
    }

    function rmFormatCard(input) {
        let v = input.value.replace(/\D/g, '').substring(0, 16);
        input.value = v.replace(/(.{4})/g, '$1 ').trim();
        const brand = document.getElementById('rm-card-brand');
        brand.innerHTML = /^4/.test(v)      ? '<i class="fab fa-cc-visa" style="color:#1a1f71"></i>'
                        : /^5[1-5]/.test(v) ? '<i class="fab fa-cc-mastercard" style="color:#eb001b"></i>'
                        : /^3[47]/.test(v)  ? '<i class="fab fa-cc-amex" style="color:#2e77bc"></i>'
                        : '';
    }

    function rmFormatExpiry(input) {
        let v = input.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 2) v = v.substring(0, 2) + ' / ' + v.substring(2);
        input.value = v;
    }

    document.getElementById('reserveModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    /* ══════════════════════════════════════════════
       ROOM DETAIL MODAL
    ══════════════════════════════════════════════ */
    function openDetailModal(e, card) {
        e.preventDefault();
        const d = card.dataset;
        document.getElementById('detailImage').src                = d.image;
        document.getElementById('detailType').textContent         = d.type;
        document.getElementById('detailType2').textContent        = d.type;
        document.getElementById('detailNumber').textContent       = d.roomNumber;
        document.getElementById('detailCapacity').textContent     = d.capacity;
        document.getElementById('detailPrice').textContent        = 'PHP ' + Number(d.price).toLocaleString();
        document.getElementById('detailDescription').textContent  = d.description;

        const pill = document.getElementById('detailStatus');
        pill.textContent = d.status.charAt(0).toUpperCase() + d.status.slice(1);
        pill.className   = 'detail-status-pill ' + d.status.toLowerCase();

        const btn = document.getElementById('detailBookBtn');
        btn.dataset.roomId = d.roomId;
        btn.dataset.price  = d.price;
        btn.dataset.type   = d.type;
        btn.dataset.number = d.roomNumber;
        btn.dataset.image  = d.image;

        document.getElementById('roomDetailModal').classList.add('show');
    }

    function closeDetailModal() {
        document.getElementById('roomDetailModal').classList.remove('show');
    }

    function bookFromDetail() {
        const btn = document.getElementById('detailBookBtn');
        closeDetailModal();
        openModal(btn.dataset.roomId, btn.dataset.price, btn.dataset.type, btn.dataset.number, btn.dataset.image);
    }

    document.getElementById('roomDetailModal').addEventListener('click', function(e) {
        if (e.target === this) closeDetailModal();
    });

    /* ══════════════════════════════════════════════
       FILTER & SORT
    ══════════════════════════════════════════════ */
    function applyFilters() {
        const type     = document.getElementById('filterType').value.toLowerCase();
        const capacity = parseInt(document.getElementById('filterCapacity').value) || 0;
        const minP     = parseFloat(document.getElementById('filterMinPrice').value) || 0;
        const maxP     = parseFloat(document.getElementById('filterMaxPrice').value) || Infinity;
        const sort     = document.getElementById('filterSort').value;

        const cards   = Array.from(document.querySelectorAll('#roomGrid .room-card'));
        let visible   = cards.filter(c => {
            if (type && c.dataset.type.toLowerCase() !== type) return false;
            if (parseInt(c.dataset.capacity) < capacity) return false;
            const p = parseFloat(c.dataset.price);
            if (p < minP || p > maxP) return false;
            return true;
        });

        visible.sort((a, b) => {
            const ap = parseFloat(a.dataset.price), bp = parseFloat(b.dataset.price);
            const ac = parseInt(a.dataset.capacity), bc = parseInt(b.dataset.capacity);
            if (sort === 'price_asc')     return ap - bp;
            if (sort === 'price_desc')    return bp - ap;
            if (sort === 'capacity_asc')  return ac - bc;
            if (sort === 'capacity_desc') return bc - ac;
            return 0;
        });

        const grid = document.getElementById('roomGrid');
        cards.forEach(c => c.style.display = 'none');
        visible.forEach(c => { c.style.display = ''; grid.appendChild(c); });

        document.getElementById('resultsCount').textContent =
            visible.length + ' room' + (visible.length !== 1 ? 's' : '') + ' found';
        document.getElementById('noResults').style.display = visible.length === 0 ? 'block' : 'none';
    }

    function resetFilters() {
        document.getElementById('filterType').value     = '';
        document.getElementById('filterCapacity').value = '0';
        document.getElementById('filterMinPrice').value = '';
        document.getElementById('filterMaxPrice').value = '';
        document.getElementById('filterSort').value     = 'price_asc';
        applyFilters();
    }

    ['filterType', 'filterCapacity', 'filterSort'].forEach(id =>
        document.getElementById(id).addEventListener('change', applyFilters)
    );
    ['filterMinPrice', 'filterMaxPrice'].forEach(id =>
        document.getElementById(id).addEventListener('input', applyFilters)
    );

    applyFilters();
    </script>
</div>
</body>
</html>
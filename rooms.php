<?php
require_once __DIR__ . '/php/config.php';

// Get distinct room types for filter dropdown
$room_types_result = $conn->query("SELECT DISTINCT room_type FROM rooms WHERE room_status='available' ORDER BY room_type ASC");
$room_types = [];
while ($rt = $room_types_result->fetch_assoc()) {
    $room_types[] = $rt['room_type'];
}

// Get min/max price for filter
$price_range = $conn->query("SELECT MIN(price_per_night) as min_price, MAX(price_per_night) as max_price FROM rooms WHERE room_status='available'")->fetch_assoc();
$min_price = (int) ($price_range['min_price'] ?? 0);
$max_price = (int) ($price_range['max_price'] ?? 10000);

$amenities = $conn->query(
    "SELECT * FROM amenities WHERE amenity_status='Available'"
);

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
        /* ── Filter Panel ── */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px 24px;
            margin: 20px auto;
            max-width: 1100px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
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
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .filter-group select,
        .filter-group input[type="number"] {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: #f9fafb;
            color: #111827;
            outline: none;
            transition: border-color 0.2s;
        }
        .filter-group select:focus,
        .filter-group input[type="number"]:focus {
            border-color: #3b82f6;
        }
        .price-range-row {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .price-range-row span {
            color: #9ca3af;
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
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            color: #374151;
            transition: background 0.2s;
            height: fit-content;
            align-self: flex-end;
        }
        .btn-filter-reset:hover { background: #e5e7eb; }

        .filter-results-count {
            font-size: 13px;
            color: #6b7280;
            margin: 0 auto 10px;
            max-width: 1100px;
            padding: 0 4px;
        }

        /* ── Room Detail Modal ── */
        #roomDetailModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        #roomDetailModal.show {
            display: flex;
        }
        .room-detail-content {
            background: #fff;
            border-radius: 16px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            animation: slideUp 0.25s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .room-detail-image {
            width: 100%;
            height: 260px;
            object-fit: cover;
            border-radius: 16px 16px 0 0;
        }
        .room-detail-body {
            padding: 28px 32px;
        }
        .room-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .room-detail-header h2 {
            margin: 0;
            font-size: 24px;
            color: #1f2937;
        }
        .room-detail-price {
            text-align: right;
        }
        .room-detail-price .big-price {
            font-size: 26px;
            font-weight: 700;
            color: #1d4ed8;
        }
        .room-detail-price .per-night {
            font-size: 13px;
            color: #6b7280;
        }
        .room-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 10px;
        }
        .room-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #374151;
        }
        .room-meta-item i {
            color: #3b82f6;
            width: 16px;
        }
        .detail-status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .detail-status-pill.available { background: #d1fae5; color: #065f46; }
        .detail-status-pill.occupied  { background: #fee2e2; color: #991b1b; }
        .detail-status-pill.maintenance { background: #fef3c7; color: #92400e; }

        .room-detail-divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
        }
        .detail-section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin-bottom: 10px;
        }
        .room-detail-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn-detail-book {
            flex: 1;
            padding: 12px;
            background: #1d4ed8;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-detail-book:hover { background: #1e40af; }
        .btn-detail-close {
            padding: 12px 24px;
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-detail-close:hover { background: #e5e7eb; }
        .modal-x-btn {
            position: absolute;
            top: 14px;
            right: 18px;
            background: rgba(0,0,0,0.35);
            border: none;
            color: #fff;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .room-detail-image-wrap { position: relative; }

        /* no results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
            display: none;
        }
        .no-results i { font-size: 48px; margin-bottom: 12px; }
    </style>
</head>
<body class="customer-page">
<div class="rooms-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <!-- ══════════════ RESERVATION MODAL ══════════════ -->
    <div id="reserveModal" class="rm-overlay">
        <div class="rm-shell">

            <!-- Header -->
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

            <form method="POST" action="/php/reserve.php" id="reserveForm">
                <input type="hidden" name="room_id" id="room_id">

                <div class="rm-body">

                    <!-- ── COL 1 : Guest Info ── -->
                    <div class="rm-col rm-col-guest">
                        <p class="rm-col-heading"><i class="fas fa-user"></i> Guest Information</p>

                        <div class="rm-field-row">
                            <div class="rm-field">
                                <label>First Name <span class="req">*</span></label>
                                <input type="text" name="first_name" placeholder="Juan" required>
                            </div>
                            <div class="rm-field">
                                <label>Last Name <span class="req">*</span></label>
                                <input type="text" name="last_name" placeholder="dela Cruz" required>
                            </div>
                        </div>

                        <div class="rm-field">
                            <label>Email <span class="req">*</span></label>
                            <input type="email" name="email" placeholder="you@email.com" required>
                        </div>

                        <div class="rm-field">
                            <label>Phone Number <span class="req">*</span></label>
                            <input type="text" name="phone_number" placeholder="09XX XXX XXXX" required>
                        </div>

                        <div class="rm-field">
                            <label>Address</label>
                            <input type="text" name="address" placeholder="City, Province">
                        </div>

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
                            <textarea name="extra_requests" placeholder="Any special requests or notes…" rows="2"></textarea>
                        </div>

                        <div class="rm-field">
                            <label>Payment Method <span class="req">*</span></label>
                            <select name="payment_method" id="modal_pay_method" onchange="toggleRefField()" required>
                                <option value="Cash">Cash — Pay on arrival</option>
                                <option value="GCash">GCash</option>
                                <option value="Card">Credit / Debit Card</option>
                            </select>
                        </div>

                        <div class="rm-field" id="ref-field" style="display:none">
                            <label>Reference Number <span style="color:#94a3b8;font-weight:400">(optional)</span></label>
                            <input type="text" name="reference_number"
                                   placeholder="Transaction/approval code">
                            <small style="color:#64748b;font-size:11px;margin-top:4px;display:block">
                                You can also submit this on your confirmation page.
                            </small>
                        </div>

                        <div id="cash-note" class="rm-pay-note rm-pay-note-cash">
                            <i class="fas fa-info-circle"></i>
                            Your booking will be <strong>pending</strong> until you pay at the front desk on check-in.
                        </div>
                        <div id="gcash-note" class="rm-pay-note rm-pay-note-gcash" style="display:none">
                            <i class="fas fa-mobile-alt"></i>
                            Send payment to GCash <strong>0977 183 7288</strong>. Submit your reference number to get confirmed.
                        </div>
                        <div id="card-note" class="rm-pay-note rm-pay-note-card" style="display:none">
                            <i class="fas fa-credit-card"></i>
                            Provide your card transaction reference. Our team will verify and confirm your booking.
                        </div>
                    </div>

                    <!-- ── COL 2 : Amenities ── -->
                    <div class="rm-col rm-col-amenities">
                        <p class="rm-col-heading"><i class="fas fa-concierge-bell"></i> Add-on Amenities</p>
                        <div class="rm-amenities-list">
                            <?php
                            // Re-query amenities since the pointer was already used
                            $amenities2 = $conn->query("SELECT * FROM amenities WHERE amenity_status='Available'");
                            while($amenity = $amenities2->fetch_assoc()): ?>
                                <div class="rm-amenity-card" id="acard_<?= $amenity['amenity_id']; ?>">
                                    <div class="rm-amenity-top">
                                        <label class="rm-amenity-check-label">
                                            <input type="checkbox"
                                                class="rm-amenity-cb"
                                                id="amenity_<?= $amenity['amenity_id']; ?>"
                                                name="amenities[<?= $amenity['amenity_id']; ?>]"
                                                value="<?= $amenity['price']; ?>"
                                                data-price="<?= $amenity['price']; ?>"
                                                data-id="<?= $amenity['amenity_id']; ?>"
                                                onchange="recalc()">
                                            <span class="rm-amenity-name"><?= htmlspecialchars($amenity['amenity_name']); ?></span>
                                        </label>
                                        <span class="rm-amenity-price">₱<?= number_format($amenity['price'], 2); ?></span>
                                    </div>
                                    <div class="rm-amenity-qty" id="qty-row-<?= $amenity['amenity_id']; ?>">
                                        <button type="button" class="qty-btn" onclick="changeQty(<?= $amenity['amenity_id']; ?>,-1)">−</button>
                                        <input type="number"
                                            id="qty_<?= $amenity['amenity_id']; ?>"
                                            name="quantity[<?= $amenity['amenity_id']; ?>]"
                                            min="1" value="1"
                                            onchange="recalc()">
                                        <button type="button" class="qty-btn" onclick="changeQty(<?= $amenity['amenity_id']; ?>,1)">+</button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- ── COL 3 : Live Summary ── -->
                    <div class="rm-col rm-col-summary">
                        <p class="rm-col-heading"><i class="fas fa-receipt"></i> Reservation Summary</p>

                        <div class="rm-summary-room-img">
                            <img id="rm-room-img" src="" alt="Room">
                        </div>

                        <div class="rm-summary-block">
                            <div class="rm-summary-row">
                                <span>Room</span>
                                <strong id="sum-room-name">—</strong>
                            </div>
                            <div class="rm-summary-row">
                                <span>Check-in</span>
                                <strong id="sum-checkin">—</strong>
                            </div>
                            <div class="rm-summary-row">
                                <span>Check-out</span>
                                <strong id="sum-checkout">—</strong>
                            </div>
                            <div class="rm-summary-row">
                                <span>Nights</span>
                                <strong id="sum-nights">—</strong>
                            </div>
                        </div>

                        <div class="rm-summary-divider"></div>

                        <div class="rm-summary-block">
                            <div class="rm-summary-row">
                                <span>Room Cost</span>
                                <strong id="sum-room-cost">₱0.00</strong>
                            </div>
                            <div id="sum-amenities-list"></div>
                            <div class="rm-summary-divider"></div>
                            <div class="rm-summary-row rm-total-row">
                                <span>Total</span>
                                <strong id="sum-total">₱0.00</strong>
                            </div>
                        </div>

                        <button type="submit" class="rm-btn-confirm">
                            <i class="fas fa-check-circle"></i> Confirm Reservation
                        </button>
                        <button type="button" class="rm-btn-cancel" onclick="closeModal()">Cancel</button>
                    </div>

                </div><!-- /.rm-body -->
            </form>
        </div>
    </div>
    <!-- ══════════════ END RESERVATION MODAL ══════════════ -->

    <style>
    /* ════════════════ RESERVATION MODAL STYLES ════════════════ */
    .rm-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.65);
        backdrop-filter: blur(4px);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .rm-overlay.show { display: flex; }

    .rm-shell {
        background: #fff;
        border-radius: 20px;
        width: 100%;
        max-width: 1060px;
        max-height: 92vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 32px 80px rgba(0,0,0,0.22);
        animation: rmSlide 0.28s cubic-bezier(.22,.68,0,1.2);
    }
    @keyframes rmSlide {
        from { transform: translateY(40px) scale(0.97); opacity: 0; }
        to   { transform: none; opacity: 1; }
    }

    /* Header */
    .rm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 28px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
        flex-shrink: 0;
    }
    .rm-header-left { display: flex; align-items: center; gap: 14px; }
    .rm-badge {
        width: 44px; height: 44px;
        background: linear-gradient(135deg,#1d4ed8,#3b82f6);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 18px; flex-shrink: 0;
    }
    .rm-title { margin: 0; font-size: 18px; font-weight: 700; color: #0f172a; }
    .rm-subtitle { margin: 2px 0 0; font-size: 13px; color: #64748b; }
    .rm-close {
        width: 36px; height: 36px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        font-size: 15px;
        color: #64748b;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.15s;
    }
    .rm-close:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }

    /* Body — 3 columns */
    .rm-body {
        display: grid;
        grid-template-columns: 1.1fr 1fr 0.85fr;
        overflow-y: auto;
        flex: 1;
    }
    .rm-col {
        padding: 24px 22px;
        overflow-y: auto;
    }
    .rm-col + .rm-col {
        border-left: 1px solid #e5e7eb;
    }
    .rm-col-summary {
        background: #f8fafc;
    }
    .rm-col-heading {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .rm-col-heading i { color: #3b82f6; }

    /* Fields */
    .rm-field { margin-bottom: 14px; }
    .rm-field label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 5px;
    }
    .req { color: #ef4444; }
    .rm-field input,
    .rm-field textarea,
    .rm-field select {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        color: #0f172a;
        background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
        outline: none;
    }
    .rm-field input:focus,
    .rm-field textarea:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
    }
    .rm-field textarea { resize: vertical; }
    .rm-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    /* Amenity cards */
    .rm-amenities-list { display: flex; flex-direction: column; gap: 10px; }
    .rm-amenity-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
        background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .rm-amenity-card.selected {
        border-color: #3b82f6;
        background: #eff6ff;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    .rm-amenity-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .rm-amenity-check-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        flex: 1;
    }
    .rm-amenity-check-label input[type="checkbox"] {
        width: 16px; height: 16px;
        accent-color: #3b82f6;
        cursor: pointer;
        flex-shrink: 0;
    }
    .rm-amenity-name { font-size: 14px; font-weight: 500; color: #1e293b; }
    .rm-amenity-price { font-size: 13px; font-weight: 600; color: #3b82f6; white-space: nowrap; margin-left: 10px; }
    .rm-amenity-qty {
        display: none;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }
    .rm-amenity-qty.visible { display: flex; }
    .qty-btn {
        width: 28px; height: 28px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
        font-size: 16px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #374151;
        transition: background 0.15s;
        flex-shrink: 0;
    }
    .qty-btn:hover { background: #f3f4f6; }
    .rm-amenity-qty input[type="number"] {
        width: 52px;
        text-align: center;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 4px 6px;
        font-size: 14px;
        font-weight: 600;
    }

    /* Summary panel */
    .rm-summary-room-img {
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 16px;
        aspect-ratio: 16/9;
        background: #e2e8f0;
    }
    .rm-summary-room-img img {
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
    }
    .rm-summary-block { margin-bottom: 4px; }
    .rm-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        font-size: 13px;
        color: #475569;
        border-bottom: 1px solid #f1f5f9;
    }
    .rm-summary-row strong { color: #0f172a; font-weight: 600; text-align: right; max-width: 60%; }
    .rm-total-row {
        margin-top: 4px;
        padding-top: 10px;
        border-top: 2px solid #e2e8f0;
        border-bottom: none;
    }
    .rm-total-row span { font-size: 15px; font-weight: 700; color: #0f172a; }
    .rm-total-row strong { font-size: 18px; font-weight: 800; color: #1d4ed8; }
    .rm-summary-divider {
        border: none;
        border-top: 1px dashed #e2e8f0;
        margin: 10px 0;
    }
    .sum-amenity-line {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #64748b;
        padding: 4px 0;
    }

    /* Buttons */
    .rm-btn-confirm {
        width: 100%;
        margin-top: 18px;
        padding: 13px;
        background: linear-gradient(135deg,#1d4ed8,#2563eb);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        letter-spacing: 0.02em;
        transition: opacity 0.2s, transform 0.15s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .rm-btn-confirm:hover { opacity: 0.92; transform: translateY(-1px); }
    .rm-btn-cancel {
        width: 100%;
        margin-top: 8px;
        padding: 10px;
        background: transparent;
        color: #94a3b8;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.15s;
    }
    .rm-btn-cancel:hover { background: #f1f5f9; color: #475569; }

    @media (max-width: 900px) {
        .rm-body { grid-template-columns: 1fr; }
        .rm-col + .rm-col { border-left: none; border-top: 1px solid #e5e7eb; }
        .rm-shell { max-width: 540px; }
    }

    /* Payment notes */
    .rm-pay-note {
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        margin-top: 10px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        line-height: 1.5;
    }
    .rm-pay-note i { flex-shrink: 0; margin-top: 2px; }
    .rm-pay-note-cash  { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
    .rm-pay-note-gcash { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }
    .rm-pay-note-card  { background:#f5f3ff; color:#5b21b6; border:1px solid #ddd6fe; }
    /* ════════════════ END MODAL STYLES ════════════════ */
    </style>

    <!-- Room Detail Modal -->
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
                <p id="detailDescription" style="color:#4b5563;font-size:14px;line-height:1.7;"></p>

                <div class="room-detail-actions">
                    <button class="btn-detail-book" id="detailBookBtn" onclick="bookFromDetail()">
                        <i class="fas fa-calendar-check"></i> Book This Room
                    </button>                    <button class="btn-detail-close" onclick="closeDetailModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-header">
        <h1>Room Options</h1>
    </div>

    <!-- Filter Bar -->
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
            $name_parts = explode(' ', $room["room_type"], 2);
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
    function toggleRefField() {
        const method = document.getElementById('modal_pay_method').value;
        document.getElementById('ref-field').style.display   = method !== 'Cash' ? '' : 'none';
        document.getElementById('cash-note').style.display   = method === 'Cash'  ? '' : 'none';
        document.getElementById('gcash-note').style.display  = method === 'GCash' ? '' : 'none';
        document.getElementById('card-note').style.display   = method === 'Card'  ? '' : 'none';
    }

    // ── Reserve Modal ──
    let currentRoomPrice  = 0;
    let currentRoomType   = '';
    let currentRoomNumber = '';
    let currentRoomImage  = '';

    function openModal(roomId, price, type, number, image) {
        // Support calling from detail modal (passes all args) or from card (looks up data)
        if (price === undefined) {
            const card = document.querySelector(`.room-card[data-room-id="${roomId}"]`);
            if (card) {
                price  = parseFloat(card.dataset.price);
                type   = card.dataset.type;
                number = card.dataset.roomNumber;
                image  = card.dataset.image;
            }
        }
        currentRoomPrice  = parseFloat(price) || 0;
        currentRoomType   = type   || 'Room';
        currentRoomNumber = number || '';
        currentRoomImage  = image  || '';

        document.getElementById("room_id").value = roomId;
        document.getElementById("rm-room-label").textContent =
            currentRoomType + (currentRoomNumber ? ' — Room ' + currentRoomNumber : '');

        // Set summary room image
        const img = document.getElementById('rm-room-img');
        img.src = currentRoomImage;

        document.getElementById("rm-room-label").textContent =
            currentRoomType + (currentRoomNumber ? ' · Room ' + currentRoomNumber : '');

        // Reset checkboxes & qty
        document.querySelectorAll('.rm-amenity-cb').forEach(cb => {
            cb.checked = false;
            const id = cb.dataset.id;
            document.getElementById('qty-row-' + id).classList.remove('visible');
            document.querySelector(`#acard_${id}`).classList.remove('selected');
            document.getElementById('qty_' + id).value = 1;
        });

        // Reset date inputs
        document.getElementById('modal_checkin').value  = '';
        document.getElementById('modal_checkout').value = '';

        recalc();
        document.getElementById("reserveModal").classList.add("show");
    }

    function closeModal() {
        document.getElementById("reserveModal").classList.remove("show");
    }

    document.getElementById("reserveModal").addEventListener("click", function(e) {
        if (e.target === this) closeModal();
    });

    // Toggle qty row visibility when checkbox changes
    document.querySelectorAll('.rm-amenity-cb').forEach(cb => {
        cb.addEventListener('change', function() {
            const id = this.dataset.id;
            const qtyRow = document.getElementById('qty-row-' + id);
            const card   = document.getElementById('acard_' + id);
            if (this.checked) {
                qtyRow.classList.add('visible');
                card.classList.add('selected');
            } else {
                qtyRow.classList.remove('visible');
                card.classList.remove('selected');
            }
        });
    });

    function changeQty(id, delta) {
        const input = document.getElementById('qty_' + id);
        const newVal = Math.max(1, parseInt(input.value || 1) + delta);
        input.value = newVal;
        recalc();
    }

    function formatPHP(n) {
        return '₱' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function recalc() {
        const checkin  = document.getElementById('modal_checkin').value;
        const checkout = document.getElementById('modal_checkout').value;

        let nights = 0;
        if (checkin && checkout) {
            const d1 = new Date(checkin), d2 = new Date(checkout);
            nights = Math.max(0, Math.round((d2 - d1) / 86400000));
        }

        // Summary dates
        document.getElementById('sum-room-name').textContent =
            currentRoomType + (currentRoomNumber ? ' · Rm ' + currentRoomNumber : '');
        document.getElementById('sum-checkin').textContent  = checkin  ? formatDate(checkin)  : '—';
        document.getElementById('sum-checkout').textContent = checkout ? formatDate(checkout) : '—';
        document.getElementById('sum-nights').textContent   = nights > 0 ? nights + ' night' + (nights > 1 ? 's' : '') : '—';

        const roomCost = currentRoomPrice * nights;
        document.getElementById('sum-room-cost').textContent = formatPHP(roomCost);

        // Amenities
        let amenitiesTotal = 0;
        let amenityHTML = '';
        document.querySelectorAll('.rm-amenity-cb:checked').forEach(cb => {
            const aid   = cb.dataset.id;
            const price = parseFloat(cb.dataset.price);
            const qty   = parseInt(document.getElementById('qty_' + aid).value || 1);
            const sub   = price * qty;
            amenitiesTotal += sub;
            const name = document.querySelector(`#acard_${aid} .rm-amenity-name`).textContent;
            amenityHTML += `<div class="sum-amenity-line">
                <span>${name} ×${qty}</span>
                <span>${formatPHP(sub)}</span>
            </div>`;
        });
        document.getElementById('sum-amenities-list').innerHTML = amenityHTML;

        const total = roomCost + amenitiesTotal;
        document.getElementById('sum-total').textContent = formatPHP(total);
    }

    function formatDate(str) {
        const d = new Date(str + 'T00:00:00');
        return d.toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
    }

    // Live recalc on date change
    document.getElementById('modal_checkin').addEventListener('change', recalc);
    document.getElementById('modal_checkout').addEventListener('change', recalc);
    document.querySelectorAll('.rm-amenity-qty input[type="number"]').forEach(inp => {
        inp.addEventListener('input', recalc);
    });

    // ── Room Detail Modal ──
    function openDetailModal(e, card) {
        e.preventDefault();
        const d = card.dataset;

        document.getElementById('detailImage').src      = d.image;
        document.getElementById('detailType').textContent  = d.type;
        document.getElementById('detailType2').textContent = d.type;
        document.getElementById('detailNumber').textContent = d.roomNumber;
        document.getElementById('detailCapacity').textContent = d.capacity;
        document.getElementById('detailPrice').textContent = 'PHP ' + Number(d.price).toLocaleString();
        document.getElementById('detailDescription').textContent = d.description;

        const pill = document.getElementById('detailStatus');
        pill.textContent = d.status.charAt(0).toUpperCase() + d.status.slice(1);
        pill.className = 'detail-status-pill ' + d.status.toLowerCase();

        document.getElementById('detailBookBtn').dataset.roomId = d.roomId;
        document.getElementById('detailBookBtn').dataset.price  = d.price;
        document.getElementById('detailBookBtn').dataset.type   = d.type;
        document.getElementById('detailBookBtn').dataset.number = d.roomNumber;
        document.getElementById('detailBookBtn').dataset.image  = d.image;

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

    // ── Filter & Sort ──
    function applyFilters() {
        const type     = document.getElementById('filterType').value.toLowerCase();
        const capacity = parseInt(document.getElementById('filterCapacity').value) || 0;
        const minP     = parseFloat(document.getElementById('filterMinPrice').value) || 0;
        const maxP     = parseFloat(document.getElementById('filterMaxPrice').value) || Infinity;
        const sort     = document.getElementById('filterSort').value;

        const cards = Array.from(document.querySelectorAll('#roomGrid .room-card'));

        let visible = cards.filter(card => {
            const cType     = card.dataset.type.toLowerCase();
            const cCap      = parseInt(card.dataset.capacity);
            const cPrice    = parseFloat(card.dataset.price);

            if (type && cType !== type) return false;
            if (cCap < capacity) return false;
            if (cPrice < minP || cPrice > maxP) return false;
            return true;
        });

        // Sort
        visible.sort((a, b) => {
            const aPrice = parseFloat(a.dataset.price);
            const bPrice = parseFloat(b.dataset.price);
            const aCap   = parseInt(a.dataset.capacity);
            const bCap   = parseInt(b.dataset.capacity);
            if (sort === 'price_asc')     return aPrice - bPrice;
            if (sort === 'price_desc')    return bPrice - aPrice;
            if (sort === 'capacity_asc')  return aCap - bCap;
            if (sort === 'capacity_desc') return bCap - aCap;
            return 0;
        });

        // Hide all, then re-append sorted visible ones
        cards.forEach(c => c.style.display = 'none');
        const grid = document.getElementById('roomGrid');
        visible.forEach(c => {
            c.style.display = '';
            grid.appendChild(c);
        });

        const count = visible.length;
        document.getElementById('resultsCount').textContent =
            count + ' room' + (count !== 1 ? 's' : '') + ' found';
        document.getElementById('noResults').style.display = count === 0 ? 'block' : 'none';
    }

    function resetFilters() {
        document.getElementById('filterType').value = '';
        document.getElementById('filterCapacity').value = '0';
        document.getElementById('filterMinPrice').value = '';
        document.getElementById('filterMaxPrice').value = '';
        document.getElementById('filterSort').value = 'price_asc';
        applyFilters();
    }

    // Attach listeners
    ['filterType','filterCapacity','filterSort'].forEach(id => {
        document.getElementById(id).addEventListener('change', applyFilters);
    });
    ['filterMinPrice','filterMaxPrice'].forEach(id => {
        document.getElementById(id).addEventListener('input', applyFilters);
    });

    // Init count
    applyFilters();
    </script>
</div>
</body>
</html>
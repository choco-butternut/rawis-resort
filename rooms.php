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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rawis Resort Hotel</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="customer-page">
<div class="rooms-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <!-- ══════════════════════════════════════
         MULTI-STEP RESERVATION MODAL
    ══════════════════════════════════════ -->
    <!-- <div id="reserveModal" class="rm-overlay">
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

                    # STEP 1 — Guest Information #
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

                    # STEP 2 — Amenities #
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

                    # STEP 3 — Payment #
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
                                        <img src="/assets/gcash-qr.jpeg" alt="GCash QR" class="rm-qr-img"
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

                    # STEP 4 — Confirm #
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

                </div>
            </form>
        </div>
    </div> -->  

    <!-- STEP 1 — Reservation Form (NEW STRUCTURE) -->
    <div id="reserveModal" class="rm-overlay">
            <div class="rm-step-panel active" id="panel-1">

                <div class="reservation-container">

                    <!-- HEADER -->
                    <div class="reservation-header">
                        <h2>Reservation Form</h2>
                        <p>Please complete the form below. Your registration will be verified prior to your arrival.</p>
                        <button type="button" class="rm-close" onclick="closeModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="reservation-body">

                        <!-- LEFT SIDE -->
                        <div class="reservation-left">

                            <h3>Guest Information</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" required>
                                </div>

                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" required>
                            </div>

                            <div class="form-group">
                                <label>Special Request</label>
                                <textarea name="extra_requests" placeholder="Any special requests or notes..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Payment Method</label>
                                <select id="payment_method_ui" onchange="selectPayMethod(this.value)">
                                    <option value="Cash">Cash - Pay on arrival</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Card">Card</option>
                                </select>
                            </div>

                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="reservation-right">

                            <h3>Reservation Summary</h3>

                            <div class="summary-card">

                                <h4 id="rm-room-label">Pakto Butlaw</h4>

                                <img id="rm-room-img" src="" alt="pakigawing responsive nlng po here">

                                <div class="summary-details">
                                    <div><span>Check-in</span><span id="sum-checkin">—</span></div>
                                    <div><span>Check-out</span><span id="sum-checkout">—</span></div>
                                    <div><span>Nights</span><span id="sum-nights">—</span></div>
                                    <div><span>Room Price</span><span id="sum-room-cost">₱0</span></div>
                                    <div><span>(1x) Extra Guest</span><span>₱0</span></div>
                                    <div><span>(2x) Extra Bed</span><span>₱0</span></div>
                                </div>

                                <div class="summary-total">
                                    <span>Total</span>
                                    <strong id="sum-total">₱0</strong>
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="reservation-footer">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                        <button type="button" class="btn-confirm" onclick="goStep(2)">
                            <i class="fas fa-check-circle"></i> Confirm Reservation
                        </button>
                    </div>

                </div>

            </div>
    </div>        

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
                        
                        <div class="room-meta">
                            <div class="room-meta-item">
                                <i class="fas fa-door-open"></i> <span>Room <strong id="detailNumber"></strong></span>
                            </div>
                            <div class="room-meta-item">
                                <i class="fas fa-users"></i> <span>Up to <strong id="detailCapacity"></strong> guests</span>
                            </div>
                        </div>
                    </div>
                    <div class="room-detail-price">
                        <div class="big-price"><span id="detailPrice"></span></div>
                        <div class="per-night">per night</div>
                    </div>
                </div>

                <div class="room-amenities-container">
                    <h4 class="amenities-title">ROOM AMENITIES</h4>
                    <div class="amenities-grid">
                        <div class="amenity-col">
                            <h5><i class="fas fa-bed"></i> Bedroom</h5>
                            <ul>
                                <li>Air conditioning</li>
                                <li>Bed sheets</li>
                                <li>Rollaway/extra bed <br><small>(available upon request)</small></li>
                            </ul>
                        </div>
                        <div class="amenity-col">
                            <h5><i class="fas fa-bath"></i> Bathroom</h5>
                            <ul>
                                <li>Free toiletries</li>
                                <li>Hair dryer (on request)</li>
                                <li>Private bathroom</li>
                                <li>Shower | Slippers | Towels</li>
                            </ul>
                        </div>
                        <div class="amenity-col">
                            <h5><i class="fas fa-concierge-bell"></i> Services</h5>
                            <ul>
                                <li>Parking Area</li>
                                <li>Laundry Services</li>
                                <li>Housekeeping (daily)</li>
                            </ul>
                        </div>
                        <div class="amenity-col">
                            <h5><i class="fas fa-utensils"></i> Food and Drinks</h5>
                            <ul>
                                <li>Coffee/tea maker</li>
                                <li>Electric kettle</li>
                                <li>Free bottled water</li>
                                <li>Room service (limited)</li>
                                <li>Plated Breakfast</li>
                            </ul>
                        </div>
                        <div class="amenity-col">
                            <h5><i class="fas fa-tv"></i> Entertainment</h5>
                            <ul>
                                <li>Smart TV</li>
                            </ul>
                        </div>
                        <div class="amenity-col">
                            <h5><i class="fas fa-swimming-pool"></i> Free Access Facilities</h5>
                            <ul>
                                <li>Swimming Pool</li>
                                <li>Night Acoustic Band</li>
                            </ul>
                        </div>
                        <div class="amenity-col">
                            <h5><i class="fas fa-wifi"></i> Connectivity</h5>
                            <ul>
                                <li>Free Wi-Fi</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="detail-main-layout">

                    <div class="stay-details-card">
                        
                        <div class="card-section">
                            <h4><i class="fas fa-calendar-alt"></i> STAY DETAILS</h4>
                            <div class="date-row">
                                <label>Check-in:</label>
                                <input type="date" id="inputCheckIn" class="date-input">
                            </div>
                            <div class="date-row">
                                <label>Check-out:</label>
                                <input type="date" id="inputCheckOut" class="date-input">
                            </div>
                        </div>

                        <div class="card-section">
                            <h4><i class="fas fa-user-plus"></i> CUSTOMIZE YOUR STAY</h4>
                            <div class="counter-row">
                                <span>Extra Guest</span>
                                <div class="counter-ctrl">
                                    <button type="button" onclick="changeQty('extraGuest', -1)">-</button>
                                    <input type="number" id="extraGuest" value="0" readonly>
                                    <button type="button" onclick="changeQty('extraGuest', 1)">+</button>
                                </div>
                            </div>
                            <div class="counter-row">
                                <span>Extra Bed</span>
                                <div class="counter-ctrl">
                                    <button type="button" onclick="changeQty('extraBed', -1)">-</button>
                                    <input type="number" id="extraBed" value="0" readonly>
                                    <button type="button" onclick="changeQty('extraBed', 1)">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="price-footer">
                            <div class="price-divider"></div>
                            <div class="price-row">
                                <span>Estimated Total</span>
                                <span class="total-amount">₱<span id="estimatedTotalPrice">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="room-detail-actions">
                    <button class="btn-close" onclick="closeDetailModal()">Close</button>
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

    // function openModal(roomId, price, type, number, image) {
    //     if (price === undefined) {
    //         const card = document.querySelector(`.room-card[data-room-id="${roomId}"]`);
    //         if (card) {
    //             price  = parseFloat(card.dataset.price);
    //             type   = card.dataset.type;
    //             number = card.dataset.roomNumber;
    //             image  = card.dataset.image;
    //         }
    //     }
    //     currentRoomPrice  = parseFloat(price)  || 0;
    //     currentRoomType   = type   || 'Room';
    //     currentRoomNumber = number || '';
    //     currentRoomImage  = image  || '';

    //     document.getElementById('room_id').value = roomId;

    //     const label = currentRoomType + (currentRoomNumber ? ' — Room ' + currentRoomNumber : '');
    //     document.getElementById('rm-room-label').textContent = label;
    //     document.getElementById('rm-room-img').src           = currentRoomImage;

    //     const today = new Date().toISOString().split('T')[0];
    //     document.getElementById('modal_checkin').min  = today;
    //     document.getElementById('modal_checkout').min = today;

    //     showStep(1, false);
    //     document.getElementById('reserveModal').classList.add('show');
    //     document.body.style.overflow = 'hidden';

    //     setTimeout(() => {
    //         updateReservationSummary();
    //     }, 100);
    // }

    // ultra mega vibe coded kay dire nagana an modal han ginliwat ko an entire rm modal
    function openModal(roomId, price, type, number, image) {

        currentRoomPrice  = parseFloat(price) || 0;
        currentRoomType   = type || 'Room';
        currentRoomNumber = number || '';
        currentRoomImage  = image || '';

        // Set hidden input if exists
        const roomInput = document.getElementById('room_id');
        if (roomInput) roomInput.value = roomId;

        // Update UI
        const label = currentRoomType + (currentRoomNumber ? ' — Room ' + currentRoomNumber : '');

        const labelEl = document.getElementById('rm-room-label');
        const imgEl   = document.getElementById('rm-room-img');

        if (labelEl) labelEl.textContent = label;
        if (imgEl) imgEl.src = currentRoomImage;

        // Show modal
        const modal = document.getElementById('reserveModal');
        if (modal) modal.classList.add('show');

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
        updateReservationSummary();
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
        let input;
        let minVal = 1;
        if (typeof id === 'string') {
            input = document.getElementById(id);
            minVal = 0;
        } else {
            input = document.getElementById('qty_' + id);
            minVal = 1;
        }
        const newVal = Math.max(minVal, parseInt(input.value || minVal) + delta);
        input.value = newVal;
        if (typeof id === 'string') {
            recalcDetail();
        } else {
            recalc();
        }
    }

    // function formatPHP(n) {
    //     return '₱' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    // }

    function formatPHP(n) {
        return '₱' + n.toLocaleString('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    // function recalc() {
    //     const checkin  = document.getElementById('modal_checkin').value;
    //     const checkout = document.getElementById('modal_checkout').value;

    //     let nights = 0;
    //     if (checkin && checkout) {
    //         const d1 = new Date(checkin), d2 = new Date(checkout);
    //         nights = Math.max(0, Math.round((d2 - d1) / 86400000));
    //     }

    //     document.getElementById('sum-room-name').textContent =
    //         currentRoomType + (currentRoomNumber ? ' · Rm ' + currentRoomNumber : '');
    //     document.getElementById('sum-checkin').textContent  = checkin  ? formatDate(checkin)  : '-';
    //     document.getElementById('sum-checkout').textContent = checkout ? formatDate(checkout) : '-';
    //     document.getElementById('sum-nights').textContent   = nights > 0 ? nights + ' night' + (nights > 1 ? 's' : '') : '-';

    //     const roomCost = currentRoomPrice * nights;
    //     document.getElementById('sum-room-cost').textContent = formatPHP(roomCost);

    //     let amenitiesTotal = 0;
    //     let amenityHTML = '';
    //     document.querySelectorAll('.rm-amenity-cb:checked').forEach(cb => {
    //         const aid   = cb.dataset.id;
    //         const price = parseFloat(cb.dataset.price);
    //         const qty   = parseInt(document.getElementById('qty_' + aid).value || 1);
    //         const sub   = price * qty;
    //         amenitiesTotal += sub;
    //         const name = document.querySelector(`#acard_${aid} .rm-amenity-name`).textContent;
    //         amenityHTML += `<div class="sum-amenity-line">
    //             <span>${name} ×${qty}</span>
    //             <span>${formatPHP(sub)}</span>
    //         </div>`;
    //     });
    //     document.getElementById('sum-amenities-list').innerHTML = amenityHTML;

    //     const total = roomCost + amenitiesTotal;
    //     document.getElementById('sum-total').textContent = formatPHP(total);
    // }

    function updateReservationSummary() {
        const checkin  = document.getElementById('modal_checkin').value;
        const checkout = document.getElementById('modal_checkout').value;

        let nights = 0;

        if (checkin && checkout) {
            const d1 = new Date(checkin);
            const d2 = new Date(checkout);
            nights = Math.max(0, Math.round((d2 - d1) / 86400000));
        }

        // DISPLAY DATES
        document.getElementById('sum-checkin').textContent  = checkin ? formatDate(checkin) : '—';
        document.getElementById('sum-checkout').textContent = checkout ? formatDate(checkout) : '—';
        document.getElementById('sum-nights').textContent   = nights ? nights + ' night' + (nights > 1 ? 's' : '') : '—';

        // ROOM COST
        const roomCost = currentRoomPrice * nights;
        document.getElementById('sum-room-cost').textContent = formatPHP(roomCost);

        // TOTAL (for now = room only)
        document.getElementById('sum-total').textContent = formatPHP(roomCost);
    }

    function formatDate(str) {
        const d = new Date(str + 'T00:00:00');
        return d.toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
    }

    // document.getElementById('modal_checkin').addEventListener('change', recalc);
    // document.getElementById('modal_checkout').addEventListener('change', recalc);
    document.getElementById('modal_checkin').addEventListener('change', updateReservationSummary);
    document.getElementById('modal_checkout').addEventListener('change', updateReservationSummary);
    document.querySelectorAll('.rm-amenity-qty input[type="number"]').forEach(inp => {
        inp.addEventListener('input', recalc);
    });

    // ── Room Detail Modal ── gab gin ai ko la in an mga additional sensya na agad kun may nahibang hihih
    let detailRoomPrice = 0;

    function recalcDetail() {
        const checkin  = document.getElementById('inputCheckIn').value;
        const checkout = document.getElementById('inputCheckOut').value;
        let nights = 0;
        if (checkin && checkout) {
            const d1 = new Date(checkin), d2 = new Date(checkout);
            nights = Math.max(0, Math.round((d2 - d1) / 86400000));
        }

        const total = detailRoomPrice * nights;
        document.getElementById('estimatedTotalPrice').textContent =
            total ? total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
    }

    // function openDetailModal(e, card) {
    //     e.preventDefault();
    //     const d = card.dataset;

    //     detailRoomPrice = parseFloat(d.price) || 0;

    //     document.getElementById('detailImage').src          = d.image;
    //     document.getElementById('detailType').textContent   = d.type;
    //     document.getElementById('detailNumber').textContent = d.roomNumber;
    //     document.getElementById('detailCapacity').textContent = d.capacity;
    //     document.getElementById('detailPrice').textContent   = 'PHP ' + Number(d.price).toLocaleString();

    //     const today = new Date();
    //     const tomorrow = new Date(today);
    //     tomorrow.setDate(today.getDate() + 1);
    //     document.getElementById('inputCheckIn').value = today.toISOString().split('T')[0];
    //     document.getElementById('inputCheckOut').value = tomorrow.toISOString().split('T')[0];

    //     document.getElementById('extraGuest').value = 0;
    //     document.getElementById('extraBed').value = 0;

    //     recalcDetail();
    //     document.getElementById('roomDetailModal').classList.add('show');
    // }
    
    // e2 rin
    function openDetailModal(e, card) {
        e.preventDefault();

        const modal = document.getElementById('roomDetailModal');

        // Get data from clicked card
        const type     = card.dataset.type;
        const number   = card.dataset.roomNumber;
        const price    = card.dataset.price;
        const image    = card.dataset.image;
        const capacity = card.dataset.capacity;

        // Populate modal UI
        document.getElementById('detailType').textContent = type || '';
        document.getElementById('detailNumber').textContent = number || '';
        document.getElementById('detailCapacity').textContent = capacity || '';
        document.getElementById('detailPrice').textContent =
            "₱" + parseFloat(price || 0).toLocaleString();
        document.getElementById('detailImage').src = image || '';

        // Store data for booking
        modal.dataset.roomId = card.dataset.roomId;
        modal.dataset.price  = price;
        modal.dataset.type   = type;
        modal.dataset.number = number;
        modal.dataset.image  = image;

        // Show modal
        modal.classList.add('show');
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
    document.getElementById('inputCheckIn').addEventListener('change', recalcDetail);
    document.getElementById('inputCheckOut').addEventListener('change', recalcDetail);

    function bookFromDetail() {
        const modal = document.getElementById('roomDetailModal');

        const roomId = modal.dataset.roomId;
        const price  = modal.dataset.price;
        const type   = modal.dataset.type;
        const number = modal.dataset.number;
        const image  = modal.dataset.image;

        closeDetailModal();
        openModal(roomId, price, type, number, image);
    }

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
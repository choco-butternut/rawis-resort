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

    <!-- STEP 1 — Reservation Form -->
    <div id="reserveModal" class="rm-overlay">
            <div class="rm-step-panel active" id="panel-1">

                <div class="reservation-container">

                    <div class="reservation-header">
                        <h2>Reservation Form</h2>
                        <p>Please complete the form below. Your registration will be verified prior to your arrival.</p>
                        <button type="button" class="rm-close" onclick="closeModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form method="POST" action="/php/reserve.php" id="reserveForm">
                        <input type="hidden" name="room_id" id="room_id">
                        <input type="hidden" name="payment_method" id="hidden_pay_method" value="Cash">
                        <input type="hidden" name="reference_number" id="hidden_ref_number" value="">

                        <div class="reservation-body">

                            <!-- LEFT SIDE -->
                            <div class="reservation-left">

                                <h3>Guest Information</h3>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" id="f_first_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" name="last_name" id="f_last_name" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" id="f_email" required>
                                </div>

                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone_number" id="f_phone" required>
                                </div>

                                <div class="form-group">
                                    <label>Check-in Date</label>
                                    <input type="date" name="check_in_date" id="modal_checkin" required>
                                </div>

                                <div class="form-group">
                                    <label>Check-out Date</label>
                                    <input type="date" name="check_out_date" id="modal_checkout" required>
                                </div>

                                 <div class="form-group">
                                    <label>Extra Guests</label>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <button type="button" onclick="changeModalQty('modal_extra_guest',-1)" style="width:32px;height:32px;border:1px solid #ddd;border-radius:6px;background:#fff;font-size:18px;cursor:pointer;line-height:1">−</button>
                                        <input type="number" name="extra_guests" id="modal_extra_guest" value="0" min="0" readonly style="width:50px;text-align:center;border:1px solid #ddd;border-radius:6px;padding:6px">
                                        <button type="button" onclick="changeModalQty('modal_extra_guest',1)" style="width:32px;height:32px;border:1px solid #ddd;border-radius:6px;background:#fff;font-size:18px;cursor:pointer;line-height:1">+</button>
                                        <span id="modal-guest-fee-hint" style="font-size:12px;color:#aaa"></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Extra Beds</label>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <button type="button" onclick="changeModalQty('modal_extra_bed',-1)" style="width:32px;height:32px;border:1px solid #ddd;border-radius:6px;background:#fff;font-size:18px;cursor:pointer;line-height:1">−</button>
                                        <input type="number" name="extra_beds" id="modal_extra_bed" value="0" min="0" readonly style="width:50px;text-align:center;border:1px solid #ddd;border-radius:6px;padding:6px">
                                        <button type="button" onclick="changeModalQty('modal_extra_bed',1)" style="width:32px;height:32px;border:1px solid #ddd;border-radius:6px;background:#fff;font-size:18px;cursor:pointer;line-height:1">+</button>
                                        <span id="modal-bed-fee-hint" style="font-size:12px;color:#aaa"></span>
                                    </div>
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

                                    <h4 id="rm-room-label">—</h4>
                                    <img id="rm-room-img" src="" alt="" style="display:none">

                                    <div class="summary-details">
                                        <div><span>Check-in</span><span id="sum-checkin">—</span></div>
                                        <div><span>Check-out</span><span id="sum-checkout">—</span></div>
                                        <div><span>Nights</span><span id="sum-nights">—</span></div>
                                        <div><span>Room Price</span><span id="sum-room-cost">₱0</span></div>
                                        <div id="sum-extra-guest-row" style="display:none"><span id="sum-extra-guest-label">Extra Guest</span><span id="sum-extra-guest-cost">₱0</span></div>
                                        <div id="sum-extra-bed-row" style="display:none"><span id="sum-extra-bed-label">Extra Bed</span><span id="sum-extra-bed-cost">₱0</span></div>
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
                            <button type="submit" class="btn-confirm">
                                <i class="fas fa-check-circle"></i> Confirm Reservation
                            </button>
                        </div>

                    </form>

                </div>

            </div>
    </div>        

    <!-- ROOM DETAIL MODAL -->
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
                                <input type="date" id="inputCheckIn" class="date-input" oninput="recalcDetail()">
                            </div>
                            <div class="date-row">
                                <label>Check-out:</label>
                                <input type="date" id="inputCheckOut" class="date-input" oninput="recalcDetail()">
                            </div>
                        </div>

                        <div class="card-section">
                            <h4><i class="fas fa-user-plus"></i> CUSTOMIZE YOUR STAY</h4>
                            <div class="counter-row">
                                <span>Extra Guest</span>
                                <div class="counter-ctrl">
                                    <button type="button" onclick="changeQty('extraGuest', -1)">-</button>
                                    <input type="number" id="extraGuest" value="0" min="0" readonly>
                                    <button type="button" onclick="changeQty('extraGuest', 1)">+</button>
                                </div>
                            </div>
                            <!-- fee hint shown only when extra_guest_fee > 0 -->
                            <div id="extraGuestFeeHint" style="font-family:Poppins,sans-serif;font-size:12px;color:#888;text-align:right;margin:-6px 0 8px;display:none"></div>

                            <div class="counter-row">
                                <span>Extra Bed</span>
                                <div class="counter-ctrl">
                                    <button type="button" onclick="changeQty('extraBed', -1)">-</button>
                                    <input type="number" id="extraBed" value="0" min="0" readonly>
                                    <button type="button" onclick="changeQty('extraBed', 1)">+</button>
                                </div>
                            </div>
                            <div id="extraBedFeeHint" style="font-family:Poppins,sans-serif;font-size:12px;color:#888;text-align:right;margin:-6px 0 8px;display:none"></div>
                        </div>

                        <div class="price-footer">
                            <div class="price-divider"></div>
                            <div style="font-family:Poppins,sans-serif;font-size:12px;color:#aaa;text-align:center;margin-bottom:6px" id="detailNightsLabel"></div>
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
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE CONTENT -->
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

    <!-- <p class="filter-results-count" id="resultsCount"></p> -->

    <div class="room-cards" id="roomGrid">
        <?php foreach ($all_rooms as $room):
            $name_parts  = explode(' ', $room["room_type"], 2);
            $first_word  = $name_parts[0];
            $second_word = $name_parts[1] ?? '';
        ?>
        <div class="room-card"
             data-type="<?= htmlspecialchars($room["room_type"]); ?>"
             data-capacity="<?= (int)$room["max_capacity"]; ?>"
             data-price="<?= (float)$room["price_per_night"]; ?>"
             data-extra-guest-fee="<?= (float)($room["extra_guest_fee"] ?? 0); ?>"
             data-extra-bed-fee="<?= (float)($room["extra_bed_fee"] ?? 0); ?>"
             data-room-id="<?= $room["room_id"]; ?>"
             data-room-number="<?= htmlspecialchars($room["room_number"]); ?>"
             data-image="<?= htmlspecialchars($room["image_path"]); ?>"
             data-status="<?= htmlspecialchars($room["room_status"]); ?>">

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
                    <button onclick="openModal(<?= $room['room_id']; ?>, <?= $room['price_per_night']; ?>, '<?= addslashes(htmlspecialchars($room['room_type'])); ?>', '<?= addslashes(htmlspecialchars($room['room_number'])); ?>', '<?= addslashes(htmlspecialchars($room['image_path'])); ?>', <?= (float)($room['extra_guest_fee'] ?? 0); ?>, <?= (float)($room['extra_bed_fee'] ?? 0); ?>)">Book</button>                </div>
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
    /* ── Shared state ── */
    let currentRoomId        = 0;
    let currentRoomPrice     = 0;
    let currentRoomType      = '';
    let currentRoomNumber    = '';
    let currentRoomImage     = '';
    let currentExtraGuestFee = 0;
    let currentExtraBedFee   = 0;

    /* ══════════════════════════════════════
       RESERVATION MODAL
    ══════════════════════════════════════ */
    function openModal(roomId, price, type, number, image, extraGuestFee, extraBedFee, checkIn, checkOut, extraGuests, extraBeds) {
        currentRoomId        = roomId;
        currentRoomPrice     = parseFloat(price)          || 0;
        currentRoomType      = type   || 'Room';
        currentRoomNumber    = number || '';
        currentRoomImage     = image  || '';
        currentExtraGuestFee = parseFloat(extraGuestFee)  || 0;
        currentExtraBedFee   = parseFloat(extraBedFee)    || 0;

        document.getElementById('room_id').value = roomId;


        const label = currentRoomType + (currentRoomNumber ? ' — Room ' + currentRoomNumber : '');
        document.getElementById('rm-room-label').textContent = label;

        const imgEl = document.getElementById('rm-room-img');
        if (currentRoomImage) { imgEl.src = currentRoomImage; imgEl.style.display = ''; }
        else { imgEl.style.display = 'none'; }

        document.getElementById('modal_extra_guest').value = extraGuests || 0;
        document.getElementById('modal_extra_bed').value   = extraBeds || 0;

        // Show fee hints
        const guestHint = document.getElementById('modal-guest-fee-hint');
        const bedHint   = document.getElementById('modal-bed-fee-hint');
        guestHint.textContent = extraGuestFee > 0 ? formatPHP(extraGuestFee) + '/night each' : 'No extra fee';
        bedHint.textContent   = extraBedFee   > 0 ? formatPHP(extraBedFee)   + '/night each' : 'No extra fee';


        // Pre-fill dates if passed from detail modal
        const today    = new Date().toISOString().split('T')[0];
        const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
        document.getElementById('modal_checkin').value  = checkIn  || today;
        document.getElementById('modal_checkout').value = checkOut || tomorrow;
        document.getElementById('modal_checkin').min    = today;
        document.getElementById('modal_checkout').min   = today;

        updateReservationSummary();
        document.getElementById('reserveModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('reserveModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    document.getElementById('reserveModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    function updateReservationSummary() {
        const ci = document.getElementById('modal_checkin').value;
        const co = document.getElementById('modal_checkout').value;

        let nights = 0;
        if (ci && co) nights = Math.max(0, Math.round((new Date(co) - new Date(ci)) / 86400000));

        document.getElementById('sum-checkin').textContent  = ci ? formatDate(ci) : '—';
        document.getElementById('sum-checkout').textContent = co ? formatDate(co) : '—';
        document.getElementById('sum-nights').textContent   = nights ? nights + ' night' + (nights > 1 ? 's' : '') : '—';

        const roomCost = currentRoomPrice * nights;
        document.getElementById('sum-room-cost').textContent = formatPHP(roomCost);

        let total = roomCost;

        const extraGuests = parseInt(document.getElementById('modal_extra_guest').value) || 0;
        const extraBeds   = parseInt(document.getElementById('modal_extra_bed').value)   || 0;

        const guestRow = document.getElementById('sum-extra-guest-row');
        const bedRow   = document.getElementById('sum-extra-bed-row');

        if (currentExtraGuestFee > 0 && extraGuests > 0) {
            const gc = currentExtraGuestFee * extraGuests * nights;
            document.getElementById('sum-extra-guest-label').textContent = 'Extra Guest ×' + extraGuests;
            document.getElementById('sum-extra-guest-cost').textContent  = formatPHP(gc);
            guestRow.style.display = '';
            total += gc;
        } else { guestRow.style.display = 'none'; }

        if (currentExtraBedFee > 0 && extraBeds > 0) {
            const bc = currentExtraBedFee * extraBeds * nights;
            document.getElementById('sum-extra-bed-label').textContent = 'Extra Bed ×' + extraBeds;
            document.getElementById('sum-extra-bed-cost').textContent  = formatPHP(bc);
            bedRow.style.display = '';
            total += bc;
        } else { bedRow.style.display = 'none'; }

        document.getElementById('sum-total').textContent = formatPHP(total);
    }

    function changeModalQty(id, delta) {
        const input  = document.getElementById(id);
        input.value  = Math.max(0, (parseInt(input.value) || 0) + delta);
        updateReservationSummary();
    }

    document.getElementById('modal_checkin').addEventListener('change', updateReservationSummary);
    document.getElementById('modal_checkout').addEventListener('change', updateReservationSummary);

    function selectPayMethod(method) {
        document.getElementById('hidden_pay_method').value = method;
    }

    /* ══════════════════════════════════════
       ROOM DETAIL MODAL
    ══════════════════════════════════════ */
    let detailRoomPrice     = 0;
    let detailExtraGuestFee = 0;
    let detailExtraBedFee   = 0;

    function openDetailModal(e, card) {
        e.preventDefault();

        detailRoomPrice     = parseFloat(card.dataset.price)          || 0;
        detailExtraGuestFee = parseFloat(card.dataset.extraGuestFee)   || 0;
        detailExtraBedFee   = parseFloat(card.dataset.extraBedFee)     || 0;

        document.getElementById('detailType').textContent     = card.dataset.type     || '';
        document.getElementById('detailNumber').textContent   = card.dataset.roomNumber || '';
        document.getElementById('detailCapacity').textContent = card.dataset.capacity  || '';
        document.getElementById('detailPrice').textContent    = '₱' + parseFloat(card.dataset.price || 0).toLocaleString();
        document.getElementById('detailImage').src            = card.dataset.image     || '';

        // Fee hints
        const guestHint = document.getElementById('extraGuestFeeHint');
        const bedHint   = document.getElementById('extraBedFeeHint');
        if (detailExtraGuestFee > 0) {
            guestHint.textContent    = formatPHP(detailExtraGuestFee) + ' / night per extra guest';
            guestHint.style.display  = '';
        } else {
            guestHint.style.display  = 'none';
        }
        if (detailExtraBedFee > 0) {
            bedHint.textContent    = formatPHP(detailExtraBedFee) + ' / night per extra bed';
            bedHint.style.display  = '';
        } else {
            bedHint.style.display  = 'none';
        }

        // Store for bookFromDetail
        const modal = document.getElementById('roomDetailModal');
        modal.dataset.roomId        = card.dataset.roomId;
        modal.dataset.price         = card.dataset.price;
        modal.dataset.type          = card.dataset.type;
        modal.dataset.number        = card.dataset.roomNumber;
        modal.dataset.image         = card.dataset.image;
        modal.dataset.extraGuestFee = card.dataset.extraGuestFee || 0;
        modal.dataset.extraBedFee   = card.dataset.extraBedFee   || 0;

        // Set default dates
        const today    = new Date().toISOString().split('T')[0];
        const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
        document.getElementById('inputCheckIn').min   = today;
        document.getElementById('inputCheckOut').min  = today;
        if (!document.getElementById('inputCheckIn').value)  document.getElementById('inputCheckIn').value  = today;
        if (!document.getElementById('inputCheckOut').value) document.getElementById('inputCheckOut').value = tomorrow;

        // Reset counters
        document.getElementById('extraGuest').value = 0;
        document.getElementById('extraBed').value   = 0;

        recalcDetail();
        document.getElementById('roomDetailModal').classList.add('show');
    }

    function closeDetailModal() {
        document.getElementById('roomDetailModal').classList.remove('show');
    }

    document.getElementById('roomDetailModal').addEventListener('click', function(e) {
        if (e.target === this) closeDetailModal();
    });

    document.getElementById('inputCheckIn').addEventListener('change',  recalcDetail);
    document.getElementById('inputCheckOut').addEventListener('change', recalcDetail);

    function changeQty(id, delta) {
        const input  = document.getElementById(id);
        const newVal = Math.max(0, (parseInt(input.value) || 0) + delta);
        input.value  = newVal;
        recalcDetail();
    }

    function recalcDetail() {
        const ci = document.getElementById('inputCheckIn').value;
        const co = document.getElementById('inputCheckOut').value;

        let nights = 0;
        if (ci && co) {
            nights = Math.max(0, Math.round((new Date(co) - new Date(ci)) / 86400000));
        }

        const extraGuests = parseInt(document.getElementById('extraGuest').value) || 0;
        const extraBeds   = parseInt(document.getElementById('extraBed').value)   || 0;

        const roomCost        = detailRoomPrice     * nights;
        const extraGuestCost  = detailExtraGuestFee * extraGuests * nights;
        const extraBedCost    = detailExtraBedFee   * extraBeds   * nights;
        const total           = roomCost + extraGuestCost + extraBedCost;

        const nightsLabel = document.getElementById('detailNightsLabel');
        if (nights > 0) {
            let breakdown = detailRoomPrice.toLocaleString() + ' × ' + nights + ' night' + (nights > 1 ? 's' : '');
            if (extraGuests > 0 && detailExtraGuestFee > 0) breakdown += ' + extra guest';
            if (extraBeds   > 0 && detailExtraBedFee   > 0) breakdown += ' + extra bed';
            nightsLabel.textContent = breakdown;
        } else {
            nightsLabel.textContent = 'Select dates to see price';
        }

        document.getElementById('estimatedTotalPrice').textContent =
            total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function bookFromDetail() {
        const modal       = document.getElementById('roomDetailModal');
        const checkIn     = document.getElementById('inputCheckIn').value;
        const checkOut    = document.getElementById('inputCheckOut').value;
        const extraGuests  = parseInt(document.getElementById('extraGuest').value) || 0;
        const extraBeds    = parseInt(document.getElementById('extraBed').value)   || 0;

        closeDetailModal();

        openModal(
            modal.dataset.roomId,
            modal.dataset.price,
            modal.dataset.type,
            modal.dataset.number,
            modal.dataset.image,
            modal.dataset.extraGuestFee,
            modal.dataset.extraBedFee,
            checkIn,
            checkOut,
            extraGuests,
            extraBeds
        );
    }

    /* ══════════════════════════════════════
       HELPERS
    ══════════════════════════════════════ */
    function formatPHP(n) {
        return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function formatDate(str) {
        const d = new Date(str + 'T00:00:00');
        return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    /* ══════════════════════════════════════
       FILTER & SORT
    ══════════════════════════════════════ */
    function applyFilters() {
        const type     = document.getElementById('filterType').value.toLowerCase();
        const capacity = parseInt(document.getElementById('filterCapacity').value) || 0;
        const minP     = parseFloat(document.getElementById('filterMinPrice').value) || 0;
        const maxP     = parseFloat(document.getElementById('filterMaxPrice').value) || Infinity;
        const sort     = document.getElementById('filterSort').value;

        const cards  = Array.from(document.querySelectorAll('#roomGrid .room-card'));
        let visible  = cards.filter(c => {
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
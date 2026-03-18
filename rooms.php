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

    <!-- RESERVATION MODAL -->
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

            <form method="POST" action="/php/reserve.php" id="reserveForm">
                <input type="hidden" name="room_id" id="room_id">

                <div class="rm-body">

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
                                <option value="Cash">Cash - Pay on arrival</option>
                                <option value="GCash">GCash</option>
                                <option value="Card">Credit / Debit Card</option>
                            </select>
                        </div>

                        <div class="rm-field" id="ref-field" style="display:none">
                            <label>Reference Number <span style="color:#aaa;font-weight:400">(optional)</span></label>
                            <input type="text" name="reference_number" placeholder="Transaction/approval code">
                            <small style="color:#888;font-size:11px;margin-top:4px;display:block">
                                You can also submit this on your confirmation page.
                            </small>
                        </div>

                        <div id="cash-note"  class="rm-pay-note rm-pay-note-cash">
                            <i class="fas fa-info-circle"></i>
                            Your booking will be <strong>pending</strong> until you pay at the front desk on check-in.
                        </div>
                        <div id="gcash-note" class="rm-pay-note rm-pay-note-gcash" style="display:none">
                            <i class="fas fa-mobile-alt"></i>
                            Send payment to GCash <strong>0977 183 7288</strong>. Submit your reference number to get confirmed.
                        </div>
                        <div id="card-note"  class="rm-pay-note rm-pay-note-card" style="display:none">
                            <i class="fas fa-credit-card"></i>
                            Provide your card transaction reference. Our team will verify and confirm your booking.
                        </div>
                    </div>

                    <div class="rm-col rm-col-amenities">
                        <p class="rm-col-heading"><i class="fas fa-concierge-bell"></i> Add-on Amenities</p>
                        <div class="rm-amenities-list">
                            <?php
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

                    <div class="rm-col rm-col-summary">
                        <p class="rm-col-heading"><i class="fas fa-receipt"></i> Reservation Summary</p>

                        <div class="rm-summary-room-img">
                            <img id="rm-room-img" src="" alt="Room">
                        </div>

                        <div class="rm-summary-block">
                            <div class="rm-summary-row">
                                <span>Room</span>
                                <strong id="sum-room-name">-</strong>
                            </div>
                            <div class="rm-summary-row">
                                <span>Check-in</span>
                                <strong id="sum-checkin">-</strong>
                            </div>
                            <div class="rm-summary-row">
                                <span>Check-out</span>
                                <strong id="sum-checkout">-</strong>
                            </div>
                            <div class="rm-summary-row">
                                <span>Nights</span>
                                <strong id="sum-nights">-</strong>
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

                </div>
            </form>
        </div>
    </div>
    
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
                            <h5><i class="fas fa-concierge-bell"></i> Services & Facilities</h5>
                            <ul>
                                <li>Parking Area</li>
                                <li>Laundry Services</li>
                                <li>Swimming Pool Access</li>
                                <li>Free Wi-Fi</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="detail-main-layout">

                    <div class="stay-details-card">
                        
                        <div class="card-section">
                            <h4><i class="fas fa-calendar-alt"></i> STAY DETAILS</h4>
                            <div class="input-group">
                                <label>Check-in:</label>
                                <input type="date" id="inputCheckIn" class="date-input">
                            </div>
                            <div class="input-group">
                                <label>Check-out:</label>
                                <input type="date" id="inputCheckOut" class="date-input">
                            </div>
                        </div>

                        <div class="card-section">
                            <h4><i class="fas fa-user-plus"></i> CUSTOMIZE</h4>
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
                            <span>Estimated Total</span>
                            <span class="total-amount">₱<span id="estimatedTotalPrice">0.00</span></span>
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
            currentRoomType + (currentRoomNumber ? ' - Room ' + currentRoomNumber : '');

        const img = document.getElementById('rm-room-img');
        img.src = currentRoomImage;

        document.getElementById("rm-room-label").textContent =
            currentRoomType + (currentRoomNumber ? ' · Room ' + currentRoomNumber : '');

        document.querySelectorAll('.rm-amenity-cb').forEach(cb => {
            cb.checked = false;
            const id = cb.dataset.id;
            document.getElementById('qty-row-' + id).classList.remove('visible');
            document.querySelector(`#acard_${id}`).classList.remove('selected');
            document.getElementById('qty_' + id).value = 1;
        });

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

        document.getElementById('sum-room-name').textContent =
            currentRoomType + (currentRoomNumber ? ' · Rm ' + currentRoomNumber : '');
        document.getElementById('sum-checkin').textContent  = checkin  ? formatDate(checkin)  : '-';
        document.getElementById('sum-checkout').textContent = checkout ? formatDate(checkout) : '-';
        document.getElementById('sum-nights').textContent   = nights > 0 ? nights + ' night' + (nights > 1 ? 's' : '') : '-';

        const roomCost = currentRoomPrice * nights;
        document.getElementById('sum-room-cost').textContent = formatPHP(roomCost);

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

    document.getElementById('modal_checkin').addEventListener('change', recalc);
    document.getElementById('modal_checkout').addEventListener('change', recalc);
    document.querySelectorAll('.rm-amenity-qty input[type="number"]').forEach(inp => {
        inp.addEventListener('input', recalc);
    });

    // ── Room Detail Modal ── gab gin ai ko la in an mga additional sensya na agad kun may nahibang hihih
    let detailRoomPrice = 0;
    function openDetailModal(e, card) {
        e.preventDefault();
        const d = card.dataset;

        detailRoomPrice = parseFloat(d.price);

        document.getElementById('detailImage').src      = d.image;
        document.getElementById('detailType').textContent  = d.type;
        document.getElementById('detailNumber').textContent = d.roomNumber;
        document.getElementById('detailCapacity').textContent = d.capacity;
        document.getElementById('detailPrice').textContent = 'PHP ' + Number(d.price).toLocaleString();
        // document.getElementById('detailDescription').textContent = d.description;

        // const pill = document.getElementById('detailStatus');
        // pill.textContent = d.status.charAt(0).toUpperCase() + d.status.slice(1);
        // pill.className = 'detail-status-pill ' + d.status.toLowerCase();

        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        document.getElementById('inputCheckIn').value = today.toISOString().split('T')[0];
        document.getElementById('inputCheckOut').value = tomorrow.toISOString().split('T')[0];

        document.getElementById('extraGuest').value = 0;
        document.getElementById('extraBed').value = 0;

        document.getElementById('detailBookBtn').dataset.roomId = d.roomId;
        document.getElementById('detailBookBtn').dataset.price  = d.price;
        document.getElementById('detailBookBtn').dataset.type   = d.type;
        document.getElementById('detailBookBtn').dataset.number = d.roomNumber;
        document.getElementById('detailBookBtn').dataset.image  = d.image;

        recalcDetail();
        document.getElementById('roomDetailModal').classList.add('show');
    }
    function closeDetailModal() {
        document.getElementById('roomDetailModal').classList.remove('show');
    }
    function recalcDetail() {
        const checkin = document.getElementById('inputCheckIn').value;
        const checkout = document.getElementById('inputCheckOut').value;
        let nights = 0;
        if (checkin && checkout) {
            const d1 = new Date(checkin), d2 = new Date(checkout);
            nights = Math.max(0, Math.round((d2 - d1) / 86400000));
        }
        const extraGuest = parseInt(document.getElementById('extraGuest').value) || 0;
        const extraBed = parseInt(document.getElementById('extraBed').value) || 0;
        const extraCost = extraGuest * 500 + extraBed * 1000;
        const total = (detailRoomPrice * nights) + extraCost;
        document.getElementById('estimatedTotalPrice').textContent = total.toFixed(2);
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

    ['filterType','filterCapacity','filterSort'].forEach(id => {
        document.getElementById(id).addEventListener('change', applyFilters);
    });
    ['filterMinPrice','filterMaxPrice'].forEach(id => {
        document.getElementById(id).addEventListener('input', applyFilters);
    });

    applyFilters();
    </script>
</div>
</body>
</html>
<?php
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";

$today = date('Y-m-d');

$arrivals_today = $conn->query("
    SELECT COUNT(*) AS c FROM reservations
    WHERE check_in_date = '$today'
    AND reservation_status = 'Confirmed'
")->fetch_assoc()["c"];

$departures_today = $conn->query("
    SELECT COUNT(*) AS c FROM reservations
    WHERE check_out_date = '$today'
    AND reservation_status IN ('Confirmed', 'Completed')
")->fetch_assoc()["c"];

$pending_count = $conn->query("
    SELECT COUNT(*) AS c FROM reservations WHERE reservation_status='Pending'
")->fetch_assoc()["c"];

$available_rooms = $conn->query("
    SELECT COUNT(*) AS c FROM rooms WHERE room_status='available'
")->fetch_assoc()["c"];

$occupied_rooms = $conn->query("
    SELECT COUNT(*) AS c FROM rooms WHERE room_status='occupied'
")->fetch_assoc()["c"];

$maintenance_rooms = $conn->query("
    SELECT COUNT(*) AS c FROM rooms WHERE room_status='maintenance'
")->fetch_assoc()["c"];

$sales_today = $conn->query("
    SELECT COALESCE(SUM(p.amount_paid), 0) AS t
    FROM payments p
    JOIN reservations r ON r.reservation_id = p.reservation_id
    WHERE DATE(p.payment_date) = '$today'
      AND p.payment_status = 'Completed'
      AND r.reservation_status != 'Cancelled'
")->fetch_assoc()["t"];

$sales_week = $conn->query("
    SELECT COALESCE(SUM(p.amount_paid), 0) AS t
    FROM payments p
    JOIN reservations r ON r.reservation_id = p.reservation_id
    WHERE p.payment_date >= DATE_SUB('$today', INTERVAL 7 DAY)
      AND p.payment_status = 'Completed'
      AND r.reservation_status != 'Cancelled'
")->fetch_assoc()["t"];

$sales_month = $conn->query("
    SELECT COALESCE(SUM(p.amount_paid), 0) AS t
    FROM payments p
    JOIN reservations r ON r.reservation_id = p.reservation_id
    WHERE YEAR(p.payment_date)  = YEAR('$today')
      AND MONTH(p.payment_date) = MONTH('$today')
      AND p.payment_status = 'Completed'
      AND r.reservation_status != 'Cancelled'
")->fetch_assoc()["t"];

$sales_all = $conn->query("
    SELECT COALESCE(SUM(p.amount_paid), 0) AS t
    FROM payments p
    JOIN reservations r ON r.reservation_id = p.reservation_id
    WHERE p.payment_status = 'Completed'
      AND r.reservation_status != 'Cancelled'
")->fetch_assoc()["t"];

$recent_res = $conn->query("
    SELECT r.reservation_id, u.first_name, u.last_name,
           rm.room_type, r.check_in_date, r.check_out_date, r.reservation_status
    FROM reservations r
    JOIN users u  ON r.guest_id = u.id
    JOIN rooms rm ON r.room_id  = rm.room_id
    ORDER BY r.created_at DESC
    LIMIT 5
");

$awaiting_count = $conn->query("
    SELECT COUNT(*) AS c FROM payments WHERE payment_status='Awaiting Verification'
")->fetch_assoc()["c"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Admin</title>
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="section-title">
            <h1>DASHBOARD</h1>
            <hr class="header-line">
        </div>

        <div class="dashboard-grid">

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-sign-in-alt"></i></div>
                <div class="stat-label">Arrivals<br><span style="font-weight:400">Today</span></div>
                <div class="stat-value"><?= $arrivals_today; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
                <div class="stat-label">Departures<br><span style="font-weight:400">Today</span></div>
                <div class="stat-value"><?= $departures_today; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-label">Pending<br><span style="font-weight:400">Reservations</span></div>
                <div class="stat-value"><?= $pending_count; ?></div>
            </div>

            <?php if ($awaiting_count > 0): ?>
            <div class="stat-card" style="cursor:pointer;border:2px solid #93c5fd"
                 onclick="window.location.href='reservation.php'">
                <div class="stat-icon" style="color:#1d4ed8"><i class="fas fa-credit-card"></i></div>
                <span class="stat-label">Awaiting Payment Verification</span>
                <span class="stat-value" style="color:#1d4ed8"><?= $awaiting_count; ?></span>
            </div>
            <?php endif; ?>

            <div class="status-summary-card">
                <h3>Room Status</h3>
                <hr>
                <ul>
                    <li>Available Rooms: <strong><?= $available_rooms; ?></strong></li>
                    <li>Occupied Rooms: <strong><?= $occupied_rooms; ?></strong></li>
                    <li>Cleaning: <strong>1</strong></li>
                    <li>Maintenance: <strong><?= $maintenance_rooms; ?></strong></li>
                </ul>
            </div>
        </div>

        <div class="reservations-wrapper">

            <div class="table-header-row">
                <div class="title-add">
                    <h2 class="reservations-head">Reservations</h2>

                    <button class="btn-add" onclick="openBookingModal()">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            
                <div class="search-bar">
                    <input type="text" id="dashSearchInput" placeholder="Search guest, room…" oninput="dashFilterTable()">
                    <i class="fas fa-search"></i>
                </div>
            </div>

            <div class="status-filters">
                <a href="#" class="active" onclick="dashFilterStatus('all',this);return false;">All</a>
                <a href="#" onclick="dashFilterStatus('Confirmed',this);return false;">Confirmed</a>
                <a href="#" onclick="dashFilterStatus('Pending',this);return false;">Pending</a>
                <a href="#" onclick="dashFilterStatus('Completed',this);return false;">Completed</a>
                <a href="#" onclick="dashFilterStatus('Cancelled',this);return false;">Cancelled</a>
            </div>

            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Contact</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Room Type</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="dashTableBody">

                        <?php
                        $all_res = $conn->query("
                            SELECT r.*, u.first_name, u.last_name, u.phone_number,
                                rm.room_type
                            FROM reservations r
                            JOIN users u ON r.guest_id = u.id
                            JOIN rooms rm ON r.room_id = rm.room_id
                            ORDER BY r.reservation_id DESC
                            LIMIT 6
                        ");
                        ?>

                        <?php while ($row = $all_res->fetch_assoc()): ?>
                        <tr data-status="<?= htmlspecialchars($row['reservation_status']); ?>">
                            <td class="id-column">#<?= $row["reservation_id"]; ?></td>
                            <td><?= htmlspecialchars($row["first_name"]); ?></td>
                            <td><?= htmlspecialchars($row["last_name"]); ?></td>
                            <td><?= htmlspecialchars($row["phone_number"]); ?></td>
                            <td><?= date("M d, Y", strtotime($row["check_in_date"])); ?></td>
                            <td><?= date("M d, Y", strtotime($row["check_out_date"])); ?></td>
                            <td><?= htmlspecialchars($row["room_type"]); ?></td>
                            <td>₱<?= number_format($row["total_amount"] ?? 0); ?></td>
                            <td>
                                <span class="status-text <?= strtolower($row["reservation_status"]); ?>">
                                    <?= $row["reservation_status"]; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-wrapper">
                                    <button class="action-btn" onclick="toggleMenu(event, this)">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>

                                    <div class="action-menu">
                                        <div class="action-item"><i class="fas fa-eye"></i> View details</div>
                                        <div class="action-item"><i class="fas fa-edit"></i> Edit</div>
                                        <div class="action-item"><i class="fas fa-envelope"></i> Send Message</div>
                                        <div class="action-item delete"><i class="fas fa-trash"></i> Delete</div>
                                        <div class="action-item"><i class="fas fa-sign-in-alt"></i> Mark as Checked-in</div>
                                        <div class="action-item"><i class="fas fa-sign-out-alt"></i> Mark as Checked-out</div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
    function dashFilterTable() {
        const q = document.getElementById('dashSearchInput').value.toLowerCase();
        document.querySelectorAll('#dashTableBody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    let dashCurrentStatus = 'all';
    function dashFilterStatus(status, el) {
        dashCurrentStatus = status;
        document.querySelectorAll('.status-filters a').forEach(a => a.classList.remove('active'));
        el.classList.add('active');
        document.querySelectorAll('#dashTableBody tr').forEach(row => {
            row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
        });
        return false;
    }

    function openBookingModal(data = null) {
        populateRoomSelect();
        if (data) {
            document.getElementById('bm-title').textContent = 'Edit Reservation';
            document.getElementById('bm-res-id').value      = data.id;
            document.getElementById('bm-first').value       = data.firstName;
            document.getElementById('bm-last').value        = data.lastName;
            document.getElementById('bm-phone').value       = data.phone;
            document.getElementById('bm-requests').value    = data.requests;
            document.getElementById('bm-pay').value         = data.payMethod;
            document.getElementById('bm-room').value        = data.roomId;
            document.getElementById('bm-checkin').value     = data.checkIn;
            document.getElementById('bm-checkout').value    = data.checkOut;
            document.getElementById('bm-extra-guest').value = data.extraGuests;
            document.getElementById('bm-extra-bed').value   = data.extraBeds;
            document.getElementById('bm-delete-btn').style.display = '';
            document.getElementById('bm-submit-btn').textContent   = 'UPDATE';
            recalcBookingModal();
        } else {
            document.getElementById('bm-title').textContent = 'Add Reservation';
            document.getElementById('bm-res-id').value = '';
            document.getElementById('bm-form').reset();
            document.getElementById('bm-extra-guest').value = 0;
            document.getElementById('bm-extra-bed').value   = 0;
            const today    = new Date().toISOString().split('T')[0];
            const tomorrow = new Date(Date.now()+86400000).toISOString().split('T')[0];
            document.getElementById('bm-checkin').value  = today;
            document.getElementById('bm-checkout').value = tomorrow;
            document.getElementById('bm-delete-btn').style.display = 'none';
            document.getElementById('bm-submit-btn').textContent   = 'CONFIRM';
            recalcBookingModal();
        }
        document.getElementById('bookingModal').classList.add('show');
    }

    function closeBookingModal() {
        document.getElementById('bookingModal').classList.remove('show');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const bm = document.getElementById('bookingModal');
        if (bm) bm.addEventListener('click', function(e){ if(e.target===this) closeBookingModal(); });
    });

    function populateRoomSelect() {
        const sel = document.getElementById('bm-room');
        sel.innerHTML = '<option value="">Select a room…</option>';
        (window.adminRooms || []).forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id;
            opt.textContent = r.type + ' — Room ' + r.number;
            opt.dataset.price         = r.price;
            opt.dataset.extraGuestFee = r.extraGuestFee;
            opt.dataset.extraBedFee   = r.extraBedFee;
            sel.appendChild(opt);
        });
    }

    function recalcBookingModal() {
        const sel      = document.getElementById('bm-room');
        const opt      = sel.options[sel.selectedIndex];
        const price    = opt ? parseFloat(opt.dataset.price || 0) : 0;
        const egFee    = opt ? parseFloat(opt.dataset.extraGuestFee || 0) : 0;
        const ebFee    = opt ? parseFloat(opt.dataset.extraBedFee   || 0) : 0;
        const ci       = document.getElementById('bm-checkin').value;
        const co       = document.getElementById('bm-checkout').value;
        const nights   = (ci && co) ? Math.max(0, Math.round((new Date(co)-new Date(ci))/86400000)) : 0;
        const eg       = parseInt(document.getElementById('bm-extra-guest').value) || 0;
        const eb       = parseInt(document.getElementById('bm-extra-bed').value)   || 0;
        const total    = price*nights + egFee*eg*nights + ebFee*eb*nights;

        document.getElementById('bm-room-price').textContent  = '₱' + price.toLocaleString('en-PH');
        document.getElementById('bm-nights').textContent      = nights;
        document.getElementById('bm-eg-cost').textContent     = '₱' + (egFee*eg*nights).toLocaleString('en-PH');
        document.getElementById('bm-eb-cost').textContent     = '₱' + (ebFee*eb*nights).toLocaleString('en-PH');
        document.getElementById('bm-total').textContent       = '₱' + total.toLocaleString('en-PH');
    }

    function changeBMQty(id, delta) {
        const el = document.getElementById(id);
        el.value = Math.max(0, (parseInt(el.value)||0) + delta);
        recalcBookingModal();
    }

    function toggleMenu(event, btn) {
        event.stopPropagation();
        document.querySelectorAll('.action-menu').forEach(m => m.classList.remove('show'));
        btn.nextElementSibling.classList.toggle('show');
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-wrapper'))
            document.querySelectorAll('.action-menu').forEach(m => m.classList.remove('show'));
    });
</script>
    <script>
    window.adminRooms = <?= json_encode(array_map(fn($r) => [
        'id'           => $r['room_id'],
        'number'       => $r['room_number'],
        'type'         => $r['room_type'],
        'price'        => $r['price_per_night'],
        'extraGuestFee'=> $r['extra_guest_fee'],
        'extraBedFee'  => $r['extra_bed_fee'],
    ], $conn->query("SELECT * FROM rooms WHERE room_status='available' ORDER BY room_type")->fetch_all(MYSQLI_ASSOC))); ?>;
    </script>

    <?php include __DIR__ . '/booking_modal.php'; ?>
</body>
</html>
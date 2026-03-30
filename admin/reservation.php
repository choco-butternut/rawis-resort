<?php
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";

if (isset($_POST["verify_payment"])) {
    $reservation_id = (int) $_POST["reservation_id"];

    $stmt = $conn->prepare(
        "UPDATE payments SET payment_status='Completed' WHERE reservation_id=? ORDER BY payment_id DESC LIMIT 1"
    );
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->close();

    $stmt2 = $conn->prepare(
        "UPDATE reservations SET reservation_status='Confirmed' WHERE reservation_id=?"
    );
    $stmt2->bind_param("i", $reservation_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: reservation.php?msg=verified");
    exit();
}

if (isset($_POST["reject_payment"])) {
    $reservation_id = (int) $_POST["reservation_id"];
    $reject_reason  = sanitize_input($_POST["reject_reason"] ?? "Payment could not be verified.");

    $stmt = $conn->prepare(
        "UPDATE payments SET payment_status='Rejected', notes=? WHERE reservation_id=? ORDER BY payment_id DESC LIMIT 1"
    );
    $stmt->bind_param("si", $reject_reason, $reservation_id);
    if (!$stmt->execute()) {
        $stmt->close();
        $stmt = $conn->prepare(
            "UPDATE payments SET payment_status='Rejected' WHERE reservation_id=? ORDER BY payment_id DESC LIMIT 1"
        );
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
    }
    $stmt->close();

    $stmt2 = $conn->prepare(
        "UPDATE reservations SET reservation_status='Cancelled' WHERE reservation_id=?"
    );
    $stmt2->bind_param("i", $reservation_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: reservation.php?msg=rejected");
    exit();
}

if (isset($_POST["update_status"])) {
    $reservation_id = (int) $_POST["reservation_id"];
    $new_status     = sanitize_input($_POST["reservation_status"]);

    $allowed = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
    if (!in_array($new_status, $allowed)) {
        header("Location: reservation.php"); exit();
    }

    $info = $conn->prepare("
        SELECT p.payment_status, r.room_id
        FROM reservations r
        LEFT JOIN payments p ON p.reservation_id = r.reservation_id
        WHERE r.reservation_id = ?
        ORDER BY p.payment_id DESC LIMIT 1
    ");
    $info->bind_param("i", $reservation_id);
    $info->execute();
    $row = $info->get_result()->fetch_assoc();
    $info->close();

    $room_id    = $row["room_id"]       ?? null;
    $pay_status = $row["payment_status"] ?? "Pending";

    $stmt = $conn->prepare("UPDATE reservations SET reservation_status=? WHERE reservation_id=?");
    $stmt->bind_param("si", $new_status, $reservation_id);
    $stmt->execute(); $stmt->close();

    if ($new_status === 'Confirmed') {
        $sp = $conn->prepare(
            "UPDATE payments SET payment_status='Completed'
            WHERE reservation_id=? ORDER BY payment_id DESC LIMIT 1"
        );
        $sp->bind_param("i", $reservation_id);
        $sp->execute(); $sp->close();

        if ($room_id) {
            $sr = $conn->prepare("UPDATE rooms SET room_status='occupied' WHERE room_id=?");
            $sr->bind_param("i", $room_id);
            $sr->execute(); $sr->close();
        }
    }

    if ($new_status === 'Completed') {
        $sp = $conn->prepare(
            "UPDATE payments SET payment_status='Completed'
             WHERE reservation_id=? AND payment_status != 'Completed'
             ORDER BY payment_id DESC LIMIT 1"
        );
        $sp->bind_param("i", $reservation_id);
        $sp->execute(); $sp->close();

        if ($room_id) {
            $sr = $conn->prepare("UPDATE rooms SET room_status='available' WHERE room_id=?");
            $sr->bind_param("i", $room_id);
            $sr->execute(); $sr->close();
        }
    }

    if ($new_status === 'Cancelled') {
        $new_pay = ($pay_status === 'Completed') ? 'Refunded' : 'Rejected';
        $sp = $conn->prepare(
            "UPDATE payments SET payment_status=?
             WHERE reservation_id=? ORDER BY payment_id DESC LIMIT 1"
        );
        $sp->bind_param("si", $new_pay, $reservation_id);
        $sp->execute(); $sp->close();

        if ($room_id) {
            $sr = $conn->prepare("UPDATE rooms SET room_status='available' WHERE room_id=?");
            $sr->bind_param("i", $room_id);
            $sr->execute(); $sr->close();
        }
    }

    if ($new_status === 'Pending') {
        if ($room_id) {
            $sr = $conn->prepare("UPDATE rooms SET room_status='available' WHERE room_id=?");
            $sr->bind_param("i", $room_id);
            $sr->execute(); $sr->close();
        }

        $sp = $conn->prepare(
            "UPDATE payments SET payment_status='Pending'
             WHERE reservation_id=? ORDER BY payment_id DESC LIMIT 1"
        );
        $sp->bind_param("i", $reservation_id);
        $sp->execute(); $sp->close();
    }

    header("Location: reservation.php"); exit();
}

if (isset($_GET["delete"])) {
    $reservation_id = (int) $_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM reservations WHERE reservation_id=?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->close();
    header("Location: reservation.php");
    exit();
}

$reservations = $conn->query("
    SELECT r.*,
           u.first_name, u.last_name, u.phone_number,
           rm.room_number, rm.room_type, rm.price_per_night,
           (rm.price_per_night * DATEDIFF(r.check_out_date, r.check_in_date)) AS base_amount,
           p.payment_id, p.payment_method, p.payment_status, p.reference_number, p.amount_paid
    FROM reservations r
    JOIN users u  ON r.guest_id = u.id
    JOIN rooms rm ON r.room_id  = rm.room_id
    LEFT JOIN payments p ON p.reservation_id = r.reservation_id
    ORDER BY
        CASE p.payment_status
            WHEN 'Awaiting Verification' THEN 1
            WHEN 'Pending' THEN 2
            ELSE 3
        END,
        r.created_at DESC
");

function payBadge($s) {
    $map = [
        'Pending'               => 'pay-pending',
        'Awaiting Verification' => 'pay-awaiting',
        'Completed'             => 'pay-completed',
        'Rejected'              => 'pay-rejected',
        'Refunded'              => 'pay-refunded',
    ];
    $cls = $map[$s] ?? 'pay-pending';
    return "<span class='pay-badge {$cls}'>" . htmlspecialchars($s) . "</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations | Admin</title>

    <!-- grabe namain nga styles, tuhaya nala kathlyn hehe -->
    <style>
        .pay-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .pay-pending    { background:#fff8f0; color:#8e4a0f; border:1px solid #8e4a0f; }
        .pay-awaiting   { background:#f0f7e6; color:#334937; border:1px solid #334937; }
        .pay-completed  { background:#e8f0d8; color:#2d5a27; border:1px solid #2d5a27; }
        .pay-rejected   { background:#fdf0ee; color:#9b2226; border:1px solid #9b2226; }
        .pay-refunded   { background:#fdf0ee; color:#9b2226; border:1px solid #9b2226; }
        .pm-pill { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .pm-cash  { background:#f0f7e6; color:#334937; }
        .pm-gcash { background:#eff6ff; color:#1d4ed8; }
        .pm-card  { background:#fdf6f0; color:#8e4a0f; }
        .row-awaiting { background:#fffbeb; }
        .verify-actions { display:flex; gap:10px; margin-top:16px; }
        .btn-verify { padding:10px 22px; background:linear-gradient(to right,#5d330f,#dbb595); color:#fff; border:none; border-radius:50px; font-family:Poppins,sans-serif; font-size:14px; font-weight:700; cursor:pointer; }
        .btn-reject { padding:10px 22px; background:#e74c3c; color:#fff; border:none; border-radius:50px; font-family:Poppins,sans-serif; font-size:14px; font-weight:700; cursor:pointer; }
        .pay-detail-box { background:#faf8f5; border:1px solid #ede8e1; border-radius:10px; padding:16px; margin-top:16px; }
        .pay-detail-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px dashed #ede8e1; font-size:14px; }
        .pay-detail-row:last-child { border-bottom:none; }
        .pd-label { color:#7c746b; }
        .pd-value { font-weight:600; color:#341f0c; }
        .admin-alert { padding:12px 16px; border-radius:10px; font-family:Poppins,sans-serif; font-size:14px; margin-bottom:16px; display:flex; align-items:center; gap:8px; border-left:4px solid; }
        .admin-alert.success { background:#f0f7e6; color:#334937; border-color:#5a7d5a; }
        .admin-alert.error   { background:#fdf0ee; color:#9b2226; border-color:#9b2226; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/sidebar.php'; ?>

<main class="main-content">
    <div class="section-title">
        <h1>RESERVATIONS</h1>
        <hr class="header-line">

        <?php if (isset($_GET["msg"])): ?>
            <?php if ($_GET["msg"] === "verified"): ?>
                <div class="admin-alert success"> Payment verified. Reservation confirmed successfully.</div>
            <?php elseif ($_GET["msg"] === "rejected"): ?>
                <div class="admin-alert error">✗ Payment rejected. Reservation cancelled.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="toolbar">
            <button class="btn-add" onclick="openBookingModal()">
                <i class="fas fa-plus"></i> Add Booking
            </button>
            <div class="search-add">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search guest, room…" oninput="filterTable()">
                    <i class="fas fa-search"></i>
                </div>
                <div class="filter-group"><i class="fas fa-bars"></i> Filter by</div>
            </div>
        </div>

        <div class="status-filters">
            <a href="#" class="active" onclick="filterByStatus('all',this)">All</a>
            <a href="#" onclick="filterByStatus('Pending',this)">Pending</a>
            <a href="#" onclick="filterByStatus('Confirmed',this)">Confirmed</a>
            <a href="#" onclick="filterByStatus('Completed',this)">Completed</a>
            <a href="#" onclick="filterByStatus('Cancelled',this)">Cancelled</a>
        </div>
    </div>

    <div class="table-container">
        <table class="custom-table" id="resTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Guest</th>
                    <th>Contact</th>
                    <th>Room</th>
                    <th>Dates</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $reservations->fetch_assoc()):
                    $total = $row["amount_paid"] ?? $row["base_amount"];

                    $rowClass = ($row["payment_status"] === "Awaiting Verification") ? "row-awaiting" : "";

                    $pmClass = match($row["payment_method"]) {
                        "Cash"  => "pm-cash",
                        "GCash" => "pm-gcash",
                        "Card"  => "pm-card",
                        default => ""
                    };
                ?>
                <tr class="<?= $rowClass; ?>"
                    data-status="<?= htmlspecialchars($row["reservation_status"]); ?>">
                    <td class="id-column">#<?= $row["reservation_id"]; ?></td>
                    <td class="bold-text">
                        <a href="#" class="guest-link"
                           data-id="<?= $row["reservation_id"]; ?>"
                           data-name="<?= htmlspecialchars($row["first_name"] . " " . $row["last_name"]); ?>"
                           data-phone="<?= htmlspecialchars($row["phone_number"]); ?>"
                           data-room="<?= htmlspecialchars($row["room_number"] . " - " . $row["room_type"]); ?>"
                           data-checkin="<?= date("M d, Y", strtotime($row["check_in_date"])); ?>"
                           data-checkout="<?= date("M d, Y", strtotime($row["check_out_date"])); ?>"
                           data-status="<?= htmlspecialchars($row["reservation_status"]); ?>"
                           data-pay-method="<?= htmlspecialchars($row["payment_method"] ?? "-"); ?>"
                           data-pay-status="<?= htmlspecialchars($row["payment_status"] ?? "-"); ?>"
                           data-pay-ref="<?= htmlspecialchars($row["reference_number"] ?? ""); ?>"
                           data-pay-amount="<?= number_format($total, 2); ?>"
                           onclick="openReservationModal(event)">
                            <?= htmlspecialchars($row["first_name"] . " " . $row["last_name"]); ?>
                        </a>
                        <br>
                    </td>
                    <td><?= htmlspecialchars($row["phone_number"]); ?></td>
                    <td>
                        <span class="room-type-tag"><?= htmlspecialchars($row["room_type"]); ?></span>
                        <br><small>Room <?= htmlspecialchars($row["room_number"]); ?></small>
                    </td>
                    <td>
                        <small><?= date("M d", strtotime($row["check_in_date"])); ?> → <?= date("M d, Y", strtotime($row["check_out_date"])); ?></small>
                    </td>
                    <td>₱<?= number_format($total, 2); ?></td>
                    <td>
                        <?php if ($row["payment_method"]): ?>
                            <span class="pm-pill <?= $pmClass; ?>">
                                <?= htmlspecialchars($row["payment_method"]); ?>
                            </span><br>
                        <?php endif; ?>
                        <?= payBadge($row["payment_status"] ?? "Pending"); ?>
                    </td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="reservation_id" value="<?= $row["reservation_id"]; ?>">
                            <select name="reservation_status"
                                    class="status-select <?= strtolower($row["reservation_status"]); ?>"
                                    onchange="this.form.submit()">
                                <option value="Pending"   <?= $row["reservation_status"]==='Pending'   ?'selected':''; ?>>Pending</option>
                                <option value="Confirmed" <?= $row["reservation_status"]==='Confirmed' ?'selected':''; ?>>Confirmed</option>
                                <option value="Completed" <?= $row["reservation_status"]==='Completed' ?'selected':''; ?>>Completed</option>
                                <option value="Cancelled" <?= $row["reservation_status"]==='Cancelled' ?'selected':''; ?>>Cancelled</option>
                            </select>
                            <input type="hidden" name="update_status">
                        </form>
                    </td>
                    <td>
                        <div class="action-wrapper">
                            <button class="action-btn" onclick="toggleMenu(event, this)">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>

                            <div class="action-menu">
                                <div class="action-item" onclick="this.closest('tr').querySelector('.guest-link').click()"><i class="fas fa-eye"></i> View details</div>
                                <div class="action-item delete" onclick="if(confirm('Delete this reservation?')) window.location.href='reservation.php?delete=<?= $row['reservation_id']; ?>'"><i class="fas fa-trash"></i> Delete</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="reservationModal" class="modal">
    <div class="modal-content" style="max-width:520px">
        <button type="button" class="modal-close" onclick="closeReservationModal()">&times;</button>
        <h2>Reservation Details</h2>

        <div class="detail-grid">
            <div class="detail-item">
                <label>Booking ID</label>
                <p id="modalId"></p>
            </div>
            <div class="detail-item">
                <label>Guest Name</label>
                <p id="modalName"></p>
            </div>
            <div class="detail-item">
                <label>Contact</label>
                <p id="modalPhone"></p>
            </div>
            <div class="detail-item">
                <label>Room</label>
                <p id="modalRoom"></p>
            </div>
            <div class="detail-item">
                <label>Check-in</label>
                <p id="modalCheckIn"></p>
            </div>
            <div class="detail-item">
                <label>Check-out</label>
                <p id="modalCheckOut"></p>
            </div>
            <div class="detail-item">
                <label>Reservation Status</label>
                <p id="modalStatus"></p>
            </div>
        </div>

        <div class="pay-detail-box">
            <p style="margin:0 0 10px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8">Payment Info</p>
            <div class="pay-detail-row">
                <span class="pd-label">Method</span>
                <span class="pd-value" id="modalPayMethod"></span>
            </div>
            <div class="pay-detail-row">
                <span class="pd-label">Amount</span>
                <span class="pd-value" id="modalPayAmount"></span>
            </div>
            <div class="pay-detail-row">
                <span class="pd-label">Status</span>
                <span class="pd-value" id="modalPayStatus"></span>
            </div>
            <div class="pay-detail-row" id="modalRefRow" style="display:none">
                <span class="pd-label">Reference #</span>
                <span class="pd-value" id="modalPayRef"></span>
            </div>
        </div>

        <div class="verify-actions" id="verifyActions" style="display:none">
            <form method="POST">
                <input type="hidden" name="reservation_id" id="verifyResId">
                <input type="hidden" name="verify_payment" value="1">
                <button type="submit" class="btn-verify">
                    <i class="fas fa-check"></i> Verify & Confirm
                </button>
            </form>
            <form method="POST">
                <input type="hidden" name="reservation_id" id="rejectResId">
                <input type="hidden" name="reject_payment" value="1">
                <input type="hidden" name="reject_reason" value="Reference number could not be verified.">
                <button type="submit" class="btn-reject"
                        onclick="return confirm('Reject this payment and cancel the reservation?')">
                    <i class="fas fa-times"></i> Reject
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openReservationModal(event) {
    event.preventDefault();
    const link = event.currentTarget;

    document.getElementById('modalId').textContent       = '#' + link.dataset.id;
    document.getElementById('modalName').textContent     = link.dataset.name;
    document.getElementById('modalPhone').textContent    = link.dataset.phone;
    document.getElementById('modalRoom').textContent     = link.dataset.room;
    document.getElementById('modalCheckIn').textContent  = link.dataset.checkin;
    document.getElementById('modalCheckOut').textContent = link.dataset.checkout;
    document.getElementById('modalStatus').textContent   = link.dataset.status;
    document.getElementById('modalPayMethod').textContent = link.dataset.payMethod;
    document.getElementById('modalPayAmount').textContent = '₱' + link.dataset.payAmount;
    document.getElementById('modalPayStatus').textContent = link.dataset.payStatus;

    const ref = link.dataset.payRef;
    const refRow = document.getElementById('modalRefRow');
    if (ref) {
        document.getElementById('modalPayRef').textContent = ref;
        refRow.style.display = '';
    } else {
        refRow.style.display = 'none';
    }

    const verifyBlock = document.getElementById('verifyActions');
    if (link.dataset.payStatus === 'Awaiting Verification') {
        verifyBlock.style.display = 'flex';
        document.getElementById('verifyResId').value = link.dataset.id;
        document.getElementById('rejectResId').value = link.dataset.id;
    } else {
        verifyBlock.style.display = 'none';
    }

    document.getElementById('reservationModal').classList.add('show');
}

function closeReservationModal() {
    document.getElementById('reservationModal').classList.remove('show');
}
document.getElementById('reservationModal').addEventListener('click', function(e) {
    if (e.target === this) closeReservationModal();
});

function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#resTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

let currentStatus = 'all';
function filterByStatus(status, el) {
    currentStatus = status;
    document.querySelectorAll('.status-filters a').forEach(a => a.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('#resTable tbody tr').forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else {
            row.style.display = row.dataset.status === status ? '' : 'none';
        }
    });
    return false;
}

function toggleMenu(event, btn) {
    event.stopPropagation(); 

    document.querySelectorAll('.action-menu').forEach(menu => {
        menu.classList.remove('show');
    });

    const menu = btn.nextElementSibling;
    menu.classList.toggle('show');
}

document.querySelectorAll('.action-menu').forEach(menu => {
    menu.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-wrapper')) {
        document.querySelectorAll('.action-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
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
], $conn->query("SELECT * FROM rooms ORDER BY room_type")->fetch_all(MYSQLI_ASSOC))); ?>;
</script>

<script>
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

</script>
<?php include __DIR__ . '/booking_modal.php'; ?>
</body>
</html>
<?php
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";

// ── Add / Edit ─────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $room_number     = sanitize_input($_POST["room_number"]);
    $room_type       = sanitize_input($_POST["room_type"]);
    $max_capacity    = (int) $_POST["max_capacity"];
    $price_per_night = (float) $_POST["price_per_night"];
    $room_status     = sanitize_input($_POST["room_status"]);

    // Handle image upload
    $new_image = "";
    if (isset($_FILES["room_image"]) && $_FILES["room_image"]["error"] === 0) {
        $upload_dir = "../uploads/rooms/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename    = time() . "_" . basename($_FILES["room_image"]["name"]);
        $target_file = $upload_dir . $filename;
        move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file);
        $new_image = "uploads/rooms/" . $filename;
    }

    if (!empty($_POST["room_id"])) {
        // ── UPDATE ──
        $room_id = (int) $_POST["room_id"];

        if ($new_image) {
            // Update with new image
            $stmt = $conn->prepare(
                "UPDATE rooms
                 SET room_number=?, room_type=?, max_capacity=?, price_per_night=?, room_status=?, image_path=?
                 WHERE room_id=?"
            );
            $stmt->bind_param("ssidssi",
                $room_number, $room_type, $max_capacity,
                $price_per_night, $room_status, $new_image, $room_id
            );
        } else {
            // Keep existing image
            $stmt = $conn->prepare(
                "UPDATE rooms
                 SET room_number=?, room_type=?, max_capacity=?, price_per_night=?, room_status=?
                 WHERE room_id=?"
            );
            $stmt->bind_param("ssidsi",
                $room_number, $room_type, $max_capacity,
                $price_per_night, $room_status, $room_id
            );
        }
        $stmt->execute();
        $stmt->close();

    } else {
        // ── INSERT ──
        $image_path = $new_image;
        $stmt = $conn->prepare(
            "INSERT INTO rooms (room_number, room_type, max_capacity, price_per_night, room_status, image_path)
             VALUES (?,?,?,?,?,?)"
        );
        $stmt->bind_param("ssidss",
            $room_number, $room_type, $max_capacity,
            $price_per_night, $room_status, $image_path
        );
        $stmt->execute();
        $stmt->close();
    }

    header("Location: rooms.php");
    exit();
}

// ── Delete ─────────────────────────────────────────────────────
if (isset($_GET["delete"])) {
    $room_id = (int) $_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id=?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->close();
    header("Location: rooms.php");
    exit();
}

// ── Edit — fetch room data ──────────────────────────────────────
$edit_room = null;
if (isset($_GET["edit"])) {
    $room_id = (int) $_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id=?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $edit_room = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ── Counts for filter tabs ──────────────────────────────────────
$count_all         = $conn->query("SELECT COUNT(*) as c FROM rooms")->fetch_assoc()["c"];
$count_available   = $conn->query("SELECT COUNT(*) as c FROM rooms WHERE room_status='available'")->fetch_assoc()["c"];
$count_occupied    = $conn->query("SELECT COUNT(*) as c FROM rooms WHERE room_status='occupied'")->fetch_assoc()["c"];
$count_maintenance = $conn->query("SELECT COUNT(*) as c FROM rooms WHERE room_status='maintenance'")->fetch_assoc()["c"];

$rooms = $conn->query("SELECT * FROM rooms ORDER BY room_number ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- ── Add / Edit Room Modal ─────────────────────────── -->
    <div id="roomModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeRoomModal()">&times;</button>
            <h2 id="modalTitle">Add Room</h2>

            <form id="roomForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="room_id" id="formRoomId" value="">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Room Number *</label>
                        <input type="text" name="room_number" id="f_room_number" placeholder="e.g. 101" required>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Room Type *</label>
                        <select name="room_type" id="f_room_type" required>
                            <option value="">Select type…</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Standard">Standard</option>
                            <option value="Family">Family</option>
                            <option value="BeachFront">BeachFront</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Max Capacity *</label>
                        <input type="number" name="max_capacity" id="f_max_capacity" placeholder="e.g. 2" min="1" required>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Price per Night (₱) *</label>
                        <input type="number" step="0.01" name="price_per_night" id="f_price" placeholder="e.g. 2500.00" required>
                    </div>
                </div>

                <div style="margin-top:12px">
                    <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Status *</label>
                    <select name="room_status" id="f_status" required>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>

                <div style="margin-top:12px">
                    <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">
                        Room Image <span id="imgRequiredNote">*</span>
                    </label>
                    <!-- existing image preview (edit mode only) -->
                    <div id="currentImgWrap" style="display:none;margin-bottom:8px">
                        <img id="currentImg" src="" alt="Current" style="width:100%;max-height:140px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0">
                        <p style="font-size:11px;color:#94a3b8;margin:4px 0 0">Leave file empty to keep current image.</p>
                    </div>
                    <input type="file" name="room_image" id="f_image" accept="image/*">
                </div>

                <button type="submit" style="margin-top:18px;width:100%;padding:11px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer" id="formSubmitBtn">
                    Add Room
                </button>
            </form>
        </div>
    </div>

    <!-- ── Room Detail Modal ─────────────────────────────── -->
    <div id="roomDetailModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeRoomDetailModal()">&times;</button>
            <h2>Room Details</h2>

            <div id="detailImgWrap" style="margin-bottom:16px">
                <img id="detailImg" src="" alt="Room"
                     style="width:100%;height:180px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0">
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <label>Room Number</label>
                    <p id="d_number"></p>
                </div>
                <div class="detail-item">
                    <label>Room Type</label>
                    <p id="d_type"></p>
                </div>
                <div class="detail-item">
                    <label>Max Capacity</label>
                    <p id="d_capacity"></p>
                </div>
                <div class="detail-item">
                    <label>Price / Night</label>
                    <p id="d_price"></p>
                </div>
                <div class="detail-item">
                    <label>Status</label>
                    <p id="d_status"></p>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px">
                <button onclick="editFromDetail()" style="flex:1;padding:10px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">
                    <i class="fas fa-edit"></i> Edit Room
                </button>
                <button onclick="deleteFromDetail()" style="flex:1;padding:10px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="section-title">
            <h1>ROOMS</h1>
            <hr class="header-line">

            <div class="toolbar">
                <div class="filter-group">
                    <i class="fas fa-bars"></i> Filter by
                </div>
                <div class="search-add">
                    <div class="search-bar">
                        <input type="text" id="roomSearch" placeholder="Search rooms…" oninput="applySearch()">
                        <i class="fas fa-search"></i>
                    </div>
                    <button class="btn-add" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

            <div class="status-filters">
                <a href="#" class="filter-tab active" data-filter="all"
                   onclick="filterByStatus('all', this); return false;">
                    All rooms (<?= $count_all ?>)
                </a>
                <a href="#" class="filter-tab" data-filter="available"
                   onclick="filterByStatus('available', this); return false;">
                    Available (<?= $count_available ?>)
                </a>
                <a href="#" class="filter-tab" data-filter="occupied"
                   onclick="filterByStatus('occupied', this); return false;">
                    Occupied (<?= $count_occupied ?>)
                </a>
                <a href="#" class="filter-tab" data-filter="maintenance"
                   onclick="filterByStatus('maintenance', this); return false;">
                    Under Maintenance (<?= $count_maintenance ?>)
                </a>
            </div>
        </div>

        <div class="rooms-grid" id="roomsGrid">
            <?php while ($row = $rooms->fetch_assoc()): ?>
                <div class="room-card"
                     data-room-id="<?= $row["room_id"]; ?>"
                     data-room-number="<?= htmlspecialchars($row["room_number"]); ?>"
                     data-room-type="<?= htmlspecialchars($row["room_type"]); ?>"
                     data-capacity="<?= $row["max_capacity"]; ?>"
                     data-price="<?= number_format($row["price_per_night"], 2); ?>"
                     data-price-raw="<?= $row["price_per_night"]; ?>"
                     data-status="<?= htmlspecialchars($row["room_status"]); ?>"
                     data-image="../<?= htmlspecialchars($row["image_path"] ?: 'assets/images/default-room.jpg'); ?>"
                     style="cursor:pointer"
                     onclick="openRoomDetailModal(event)">

                    <div class="room-image">
                        <img src="../<?= $row["image_path"] ?: 'assets/images/default-room.jpg'; ?>" alt="Room Image">
                        <div class="room-number-badge"><?= str_pad($row["room_number"], 2, '0', STR_PAD_LEFT); ?></div>
                    </div>

                    <div class="room-details">
                        <div class="room-title-row">
                            <?php
                                $parts       = explode(' ', $row['room_type'], 2);
                                $first_word  = $parts[0];
                                $second_word = $parts[1] ?? '';
                            ?>
                            <h3 class="room-title">
                                <span class="main-name"><?= htmlspecialchars($first_word); ?></span>
                                <span class="sub-name"><?= htmlspecialchars($second_word); ?></span>
                            </h3>
                            <div class="room-price">
                                <span class="currency">PHP</span>
                                <span class="amount"><?= number_format($row["price_per_night"], 0); ?></span>
                                <span class="period">/night</span>
                            </div>
                        </div>

                        <div class="room-info">
                            <span><i class="fas fa-users"></i> <?= $row["max_capacity"]; ?> Guests</span>
                        </div>

                        <div class="room-footer">
                            <span class="status-pill <?= strtolower($row["room_status"]); ?>">
                                <?= ucfirst($row["room_status"]); ?>
                            </span>
                            <div class="card-actions">
                                <a href="#" title="View"
                                   onclick="event.stopPropagation(); openRoomDetailModal(event, document.querySelector('[data-room-id=\'<?= $row["room_id"]; ?>\']')); return false;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" title="Edit"
                                   onclick="event.stopPropagation(); openEditModal(<?= $row["room_id"]; ?>); return false;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="rooms.php?delete=<?= $row["room_id"]; ?>"
                                   onclick="event.stopPropagation(); return confirm('Delete room <?= addslashes($row["room_number"]); ?>?');"
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <p id="noRoomsMsg" style="display:none;text-align:center;padding:40px;color:#94a3b8">
            No rooms match your search.
        </p>
    </main>

    <script>
    // ── Room data from PHP ──────────────────────────────────────
    const roomData = <?= json_encode(
        array_map(fn($r) => [
            'id'       => $r['room_id'],
            'number'   => $r['room_number'],
            'type'     => $r['room_type'],
            'capacity' => $r['max_capacity'],
            'price'    => $r['price_per_night'],
            'status'   => $r['room_status'],
            'image'    => '../' . ($r['image_path'] ?: 'assets/images/default-room.jpg'),
        ],
        iterator_to_array((function() use ($conn) {
            $r = $conn->query("SELECT * FROM rooms ORDER BY room_number ASC");
            while ($row = $r->fetch_assoc()) yield $row;
        })())
    )); ?>;

    let currentDetailId = null;

    // ── Add modal ──────────────────────────────────────────────
    function openAddModal() {
        document.getElementById('modalTitle').textContent    = 'Add Room';
        document.getElementById('formSubmitBtn').textContent = 'Add Room';
        document.getElementById('formRoomId').value          = '';
        document.getElementById('roomForm').reset();
        document.getElementById('currentImgWrap').style.display = 'none';
        document.getElementById('imgRequiredNote').textContent  = '*';
        document.getElementById('f_image').required = true;
        document.getElementById('roomModal').classList.add('show');
    }

    // ── Edit modal ─────────────────────────────────────────────
    function openEditModal(roomId) {
        const room = roomData.find(r => r.id == roomId);
        if (!room) return;

        document.getElementById('modalTitle').textContent    = 'Edit Room';
        document.getElementById('formSubmitBtn').textContent = 'Update Room';
        document.getElementById('formRoomId').value          = room.id;

        document.getElementById('f_room_number').value  = room.number;
        document.getElementById('f_room_type').value    = room.type;
        document.getElementById('f_max_capacity').value = room.capacity;
        document.getElementById('f_price').value        = room.price;
        document.getElementById('f_status').value       = room.status;

        // Show existing image
        const imgWrap = document.getElementById('currentImgWrap');
        document.getElementById('currentImg').src = room.image;
        imgWrap.style.display = 'block';

        // Image not required on edit (can keep existing)
        document.getElementById('f_image').required = false;
        document.getElementById('imgRequiredNote').textContent = '(optional — leave blank to keep current)';
        document.getElementById('f_image').value = '';

        document.getElementById('roomModal').classList.add('show');
    }

    function closeRoomModal() {
        document.getElementById('roomModal').classList.remove('show');
    }

    document.getElementById('roomModal').addEventListener('click', function(e) {
        if (e.target === this) closeRoomModal();
    });

    // ── Room Detail Modal ──────────────────────────────────────
    function openRoomDetailModal(event, cardEl) {
        // Prevent triggering when clicking action links
        if (event.target.closest('a')) return;

        const card = cardEl || event.currentTarget;
        currentDetailId = card.dataset.roomId;

        document.getElementById('detailImg').src         = card.dataset.image;
        document.getElementById('d_number').textContent  = card.dataset.roomNumber;
        document.getElementById('d_type').textContent    = card.dataset.roomType;
        document.getElementById('d_capacity').textContent = card.dataset.capacity + ' guests';
        document.getElementById('d_price').textContent   = '₱' + Number(card.dataset.priceRaw).toLocaleString('en-PH', {minimumFractionDigits:2});
        document.getElementById('d_status').textContent  = card.dataset.status.charAt(0).toUpperCase() + card.dataset.status.slice(1);

        document.getElementById('roomDetailModal').classList.add('show');
    }

    function closeRoomDetailModal() {
        document.getElementById('roomDetailModal').classList.remove('show');
    }

    document.getElementById('roomDetailModal').addEventListener('click', function(e) {
        if (e.target === this) closeRoomDetailModal();
    });

    function editFromDetail() {
        closeRoomDetailModal();
        if (currentDetailId) openEditModal(currentDetailId);
    }

    function deleteFromDetail() {
        if (!currentDetailId) return;
        const room = roomData.find(r => r.id == currentDetailId);
        if (confirm('Delete room ' + (room ? room.number : '') + '?')) {
            window.location.href = 'rooms.php?delete=' + currentDetailId;
        }
    }

    // ── Status filter ──────────────────────────────────────────
    let activeFilter = 'all';

    function filterByStatus(status, el) {
        activeFilter = status;
        document.querySelectorAll('.filter-tab').forEach(a => a.classList.remove('active'));
        el.classList.add('active');
        applySearch();
    }

    // ── Search ─────────────────────────────────────────────────
    function applySearch() {
        const q    = document.getElementById('roomSearch').value.toLowerCase();
        const cards = document.querySelectorAll('#roomsGrid .room-card');
        let visible = 0;

        cards.forEach(card => {
            const matchStatus = activeFilter === 'all' || card.dataset.status === activeFilter;
            const text = (card.dataset.roomNumber + ' ' + card.dataset.roomType).toLowerCase();
            const matchSearch = !q || text.includes(q);

            if (matchStatus && matchSearch) {
                card.style.display = '';
                visible++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('noRoomsMsg').style.display = visible === 0 ? 'block' : 'none';
    }

    // ── Auto-open edit modal if ?edit= in URL ──────────────────
    <?php if ($edit_room): ?>
    document.addEventListener('DOMContentLoaded', () => openEditModal(<?= $edit_room["room_id"]; ?>));
    <?php endif; ?>
    </script>

</body>
</html>
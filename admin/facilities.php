<?php
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";

//for rooms
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["form_type"]) && $_POST["form_type"] === "room") {

    $room_number     = sanitize_input($_POST["room_number"]);
    $room_type       = sanitize_input($_POST["room_type"]);
    $max_capacity    = (int) $_POST["max_capacity"];
    $price_per_night = (float) $_POST["price_per_night"];
    $room_status     = sanitize_input($_POST["room_status"]);
    $extra_guest_fee = (float) ($_POST["extra_guest_fee"] ?? 0);
    $extra_bed_fee   = (float) ($_POST["extra_bed_fee"]   ?? 0);

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
        $room_id = (int) $_POST["room_id"];
        if ($new_image) {
            $stmt = $conn->prepare("UPDATE rooms SET room_number=?, room_type=?, max_capacity=?, price_per_night=?, room_status=?, image_path=?, extra_guest_fee=?, extra_bed_fee=? WHERE room_id=?");
            $stmt->bind_param("ssidssddI", $room_number, $room_type, $max_capacity, $price_per_night, $room_status, $new_image, $extra_guest_fee, $extra_bed_fee, $room_id);
        } else {
            $stmt = $conn->prepare("UPDATE rooms SET room_number=?, room_type=?, max_capacity=?, price_per_night=?, room_status=?, extra_guest_fee=?, extra_bed_fee=? WHERE room_id=?");
            $stmt->bind_param("ssidsddi", $room_number, $room_type, $max_capacity, $price_per_night, $room_status, $extra_guest_fee, $extra_bed_fee, $room_id);
        }
        $stmt->execute(); $stmt->close();
    } else {
        $image_path = $new_image;
        $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, max_capacity, price_per_night, room_status, image_path, extra_guest_fee, extra_bed_fee) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssidssdd", $room_number, $room_type, $max_capacity, $price_per_night, $room_status, $image_path, $extra_guest_fee, $extra_bed_fee);
        $stmt->execute(); $stmt->close();
    }
    header("Location: facilities.php?tab=rooms"); exit();
}

//for amenities
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["form_type"]) && $_POST["form_type"] === "amenity") {

    $amenity_name   = sanitize_input($_POST["amenity_name"]);
    $description    = sanitize_input($_POST["description"]);
    $price          = (float) $_POST["price"];
    $amenity_status = sanitize_input($_POST["amenity_status"]);

    $new_image = "";
    if (isset($_FILES["amenity_image"]) && $_FILES["amenity_image"]["error"] === 0) {
        $upload_dir = "../uploads/amenities/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename    = time() . "_" . basename($_FILES["amenity_image"]["name"]);
        $target_file = $upload_dir . $filename;
        move_uploaded_file($_FILES["amenity_image"]["tmp_name"], $target_file);
        $new_image = "uploads/amenities/" . $filename;
    }

    if (!empty($_POST["amenity_id"])) {
        $amenity_id = (int) $_POST["amenity_id"];
        if ($new_image) {
            $stmt = $conn->prepare("UPDATE amenities SET amenity_name=?, description=?, price=?, amenity_status=?, image_path=? WHERE amenity_id=?");
            $stmt->bind_param("ssdss i", $amenity_name, $description, $price, $amenity_status, $new_image, $amenity_id);
        } else {
            $stmt = $conn->prepare("UPDATE amenities SET amenity_name=?, description=?, price=?, amenity_status=? WHERE amenity_id=?");
            $stmt->bind_param("ssdsi", $amenity_name, $description, $price, $amenity_status, $amenity_id);
        }
        $stmt->execute(); $stmt->close();
    } else {
        $image_path = $new_image;
        $stmt = $conn->prepare("INSERT INTO amenities (amenity_name, description, price, amenity_status, image_path) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssdss", $amenity_name, $description, $price, $amenity_status, $image_path);
        $stmt->execute(); $stmt->close();
    }
    header("Location: facilities.php?tab=amenities"); exit();
}


if (isset($_GET["delete_room"])) {
    $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id=?");
    $delete_id = intval($_GET['delete_room']);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute(); $stmt->close();
    header("Location: facilities.php?tab=rooms"); exit();
}
if (isset($_GET["delete_amenity"])) {
    $stmt = $conn->prepare("DELETE FROM amenities WHERE amenity_id=?");
    $delete_id = intval($_GET['delete_amenity']);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute(); $stmt->close();
    header("Location: facilities.php?tab=amenities"); exit();
}


$active_tab = ($_GET["tab"] ?? "rooms") === "amenities" ? "amenities" : "rooms";


$count_all         = $conn->query("SELECT COUNT(*) as c FROM rooms")->fetch_assoc()["c"];
$count_available   = $conn->query("SELECT COUNT(*) as c FROM rooms WHERE room_status='available'")->fetch_assoc()["c"];
$count_occupied    = $conn->query("SELECT COUNT(*) as c FROM rooms WHERE room_status='occupied'")->fetch_assoc()["c"];
$count_maintenance = $conn->query("SELECT COUNT(*) as c FROM rooms WHERE room_status='maintenance'")->fetch_assoc()["c"];

$rooms_result   = $conn->query("SELECT * FROM rooms ORDER BY room_number ASC");
$rooms_arr      = [];
while ($r = $rooms_result->fetch_assoc()) $rooms_arr[] = $r;

$count_amenities_all         = $conn->query("SELECT COUNT(*) as c FROM amenities")->fetch_assoc()["c"];
$count_amenities_available   = $conn->query("SELECT COUNT(*) as c FROM amenities WHERE amenity_status='Available'")->fetch_assoc()["c"];
$count_amenities_unavailable = $conn->query("SELECT COUNT(*) as c FROM amenities WHERE amenity_status='Unavailable'")->fetch_assoc()["c"];

$amenities_result = $conn->query("SELECT * FROM amenities ORDER BY amenity_name ASC");
$amenities_arr    = [];
while ($a = $amenities_result->fetch_assoc()) $amenities_arr[] = $a;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        
        .facility-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
        }
        .facility-tab-btn {
            padding: 10px 28px;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: all .2s;
            margin-bottom: -2px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .facility-tab-btn.active {
            color: #1d4ed8;
            border-bottom-color: #1d4ed8;
        }
        .facility-tab-btn:hover:not(.active) {
            color: #1e40af;
            background: #f1f5f9;
            border-radius: 6px 6px 0 0;
        }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

    </style>
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    
    <div id="roomModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeRoomModal()">&times;</button>
            <h2 id="roomModalTitle">Add Room</h2>
            <form id="roomForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="room">
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
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px">
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Extra Guest Fee (₱/night)</label>
                        <input type="number" step="0.01" name="extra_guest_fee" id="f_extra_guest_fee" placeholder="0.00" min="0">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Extra Bed Fee (₱/night)</label>
                        <input type="number" step="0.01" name="extra_bed_fee" id="f_extra_bed_fee" placeholder="0.00" min="0">
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
                        Room Image <span id="roomImgNote">*</span>
                    </label>
                    <div id="currentRoomImgWrap" style="display:none;margin-bottom:8px">
                        <img id="currentRoomImg" src="" alt="Current" style="width:100%;max-height:140px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0">
                        <p style="font-size:11px;color:#94a3b8;margin:4px 0 0">Leave file empty to keep current image.</p>
                    </div>
                    <input type="file" name="room_image" id="f_room_image" accept="image/*">
                </div>
                <button type="submit" id="roomSubmitBtn" style="margin-top:18px;width:100%;padding:11px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">
                    Add Room
                </button>
            </form>
        </div>
    </div>
   
    <div id="amenityModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeAmenityModal()">&times;</button>
            <h2 id="amenityModalTitle">Add Amenity</h2>
            <form id="amenityForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="amenity">
                <input type="hidden" name="amenity_id" id="formAmenityId" value="">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div style="grid-column:1/-1">
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Amenity Name *</label>
                        <input type="text" name="amenity_name" id="f_amenity_name" placeholder="e.g. Swimming Pool" required>
                    </div>
                    <div style="grid-column:1/-1">
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Description</label>
                        <textarea name="description" id="f_amenity_desc" placeholder="Short description…" rows="3" style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid #e2e8f0;resize:vertical;font-size:13px"></textarea>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Price (₱) *</label>
                        <input type="number" step="0.01" name="price" id="f_amenity_price" placeholder="e.g. 500.00" min="0" required>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Status *</label>
                        <select name="amenity_status" id="f_amenity_status" required>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top:12px">
                    <label style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">
                        Amenity Image <span id="amenityImgNote">*</span>
                    </label>
                    <div id="currentAmenityImgWrap" style="display:none;margin-bottom:8px">
                        <img id="currentAmenityImg" src="" alt="Current" style="width:100%;max-height:140px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0">
                        <p style="font-size:11px;color:#94a3b8;margin:4px 0 0">Leave file empty to keep current image.</p>
                    </div>
                    <input type="file" name="amenity_image" id="f_amenity_image" accept="image/*">
                </div>
                <button type="submit" id="amenitySubmitBtn" style="margin-top:18px;width:100%;padding:11px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">
                    Add Amenity
                </button>
            </form>
        </div>
    </div>


    <div id="roomDetailModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeRoomDetailModal()">&times;</button>
            <h2>Room Details</h2>
            <div style="margin-bottom:16px">
                <img id="detailRoomImg" src="" alt="Room" style="width:100%;height:180px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0">
            </div>
            <div class="detail-grid">
                <div class="detail-item"><label>Room Number</label><p id="d_number"></p></div>
                <div class="detail-item"><label>Room Type</label><p id="d_type"></p></div>
                <div class="detail-item"><label>Max Capacity</label><p id="d_capacity"></p></div>
                <div class="detail-item"><label>Price / Night</label><p id="d_price"></p></div>
                <div class="detail-item"><label>Status</label><p id="d_status"></p></div>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button onclick="editRoomFromDetail()" style="flex:1;padding:10px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer"><i class="fas fa-edit"></i> Edit</button>
                <button onclick="deleteRoomFromDetail()" style="flex:1;padding:10px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer"><i class="fas fa-trash"></i> Delete</button>
            </div>
        </div>
    </div>
    
    <div id="amenityDetailModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeAmenityDetailModal()">&times;</button>
            <h2>Amenity Details</h2>
            <div style="margin-bottom:16px">
                <img id="detailAmenityImg" src="" alt="Amenity" style="width:100%;height:180px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0">
            </div>
            <div class="detail-grid">
                <div class="detail-item"><label>Name</label><p id="da_name"></p></div>
                <div class="detail-item"><label>Description</label><p id="da_desc"></p></div>
                <div class="detail-item"><label>Price</label><p id="da_price"></p></div>
                <div class="detail-item"><label>Status</label><p id="da_status"></p></div>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button onclick="editAmenityFromDetail()" style="flex:1;padding:10px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer"><i class="fas fa-edit"></i> Edit</button>
                <button onclick="deleteAmenityFromDetail()" style="flex:1;padding:10px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer"><i class="fas fa-trash"></i> Delete</button>
            </div>
        </div>
    </div>

    
    <main class="main-content">
        <div class="section-title">
            <h1>FACILITIES</h1>
            <hr class="header-line">
        </div>

        
        <div class="facility-tabs">
            <button class="facility-tab-btn <?= $active_tab === 'rooms' ? 'active' : '' ?>"
                    data-tab="rooms"
                    onclick="switchTab('rooms')">
                <i class="fas"></i> Rooms
            </button>
            <button class="facility-tab-btn <?= $active_tab === 'amenities' ? 'active' : '' ?>"
                    data-tab="amenities"
                    onclick="switchTab('amenities')">
                <i class="fas"></i> Amenities
            </button>
        </div>

        <!-- rooms tab -->
        <div id="tab-rooms" class="tab-panel <?= $active_tab === 'rooms' ? 'active' : '' ?>">
            <div class="toolbar">
                <div class="filter-group"><i class="fas fa-bars"></i> Filter by</div>
                <div class="search-add">
                    <div class="search-bar">
                        <input type="text" id="roomSearch" placeholder="Search rooms…" oninput="applyRoomSearch()">
                        <i class="fas fa-search"></i>
                    </div>
                    <button class="btn-add" onclick="openAddRoomModal()">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

            <div class="status-filters">
                <a href="#" class="filter-tab active" data-filter="all"
                   onclick="filterRoomStatus('all', this); return false;">All rooms (<?= $count_all ?>)</a>
                <a href="#" class="filter-tab" data-filter="available"
                   onclick="filterRoomStatus('available', this); return false;">Available (<?= $count_available ?>)</a>
                <a href="#" class="filter-tab" data-filter="occupied"
                   onclick="filterRoomStatus('occupied', this); return false;">Occupied (<?= $count_occupied ?>)</a>
                <a href="#" class="filter-tab" data-filter="maintenance"
                   onclick="filterRoomStatus('maintenance', this); return false;">Under Maintenance (<?= $count_maintenance ?>)</a>
            </div>

            <div class="rooms-grid" id="roomsGrid">
                <?php foreach ($rooms_arr as $row): ?>
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
                                    $parts      = explode(' ', $row['room_type'], 2);
                                    $first_word = $parts[0];
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
                                <span><?= $row["max_capacity"]; ?> Guests</span>
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
                                       onclick="event.stopPropagation(); openEditRoomModal(<?= $row["room_id"]; ?>); return false;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="facilities.php?delete_room=<?= $row["room_id"]; ?>"
                                       onclick="event.stopPropagation(); return confirm('Delete room <?= addslashes($row["room_number"]); ?>?');"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p id="noRoomsMsg" style="display:none;text-align:center;padding:40px;color:#94a3b8">No rooms match your search.</p>
        </div>

        <!-- amenities tab -->
        <div id="tab-amenities" class="tab-panel <?= $active_tab === 'amenities' ? 'active' : '' ?>">
            <div class="toolbar">
                <div class="filter-group"><i class="fas fa-bars"></i> Filter by</div>
                <div class="search-add">
                    <div class="search-bar">
                        <input type="text" id="amenitySearch" placeholder="Search amenities…" oninput="applyAmenitySearch()">
                        <i class="fas fa-search"></i>
                    </div>
                    <button class="btn-add" onclick="openAddAmenityModal()">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

            <div class="status-filters">
                <a href="#" class="filter-tab active" data-afilter="all"
                   onclick="filterAmenityStatus('all', this); return false;">All amenities (<?= $count_amenities_all ?>)</a>
                <a href="#" class="filter-tab" data-afilter="Available"
                   onclick="filterAmenityStatus('Available', this); return false;">Available (<?= $count_amenities_available ?>)</a>
                <a href="#" class="filter-tab" data-afilter="Unavailable"
                   onclick="filterAmenityStatus('Unavailable', this); return false;">Unavailable (<?= $count_amenities_unavailable ?>)</a>
            </div>
            
            <!-- recycle room style -->
            <div class="rooms-grid" id="amenitiesGrid">
                <?php foreach ($amenities_arr as $row): ?>
                    <div class="room-card"
                         data-amenity-id="<?= $row["amenity_id"]; ?>"
                         data-amenity-name="<?= htmlspecialchars($row["amenity_name"]); ?>"
                         data-amenity-desc="<?= htmlspecialchars($row["description"] ?? ''); ?>"
                         data-amenity-price="<?= $row["price"]; ?>"
                         data-amenity-status="<?= htmlspecialchars($row["amenity_status"]); ?>"
                         data-amenity-image="../<?= htmlspecialchars($row["image_path"] ?: 'assets/images/default-room.jpg'); ?>"
                         style="cursor:pointer"
                         onclick="openAmenityDetailModal(event)">

                        <div class="room-image">
                            <img src="../<?= $row["image_path"] ?: 'assets/images/default-room.jpg'; ?>" alt="<?= htmlspecialchars($row["amenity_name"]); ?>">
                        </div>
                        <div class="room-details">
                            <div class="room-title-row">
                                <h3 class="room-title">
                                    <span class="main-name"><?= htmlspecialchars($row["amenity_name"]); ?></span>
                                </h3>

                                <div class="room-price">
                                    <span class="currency">PHP</span>
                                    <span class="amount"><?= number_format($row["price"], 0); ?></span>
                                </div>
                            </div>
                            <div class="room-info">
                                <span><?= htmlspecialchars($row["description"] ?? '-'); ?></span>
                            </div>
                            <div class="room-footer">
                                <span class="status-pill <?= strtolower($row["amenity_status"]); ?>">
                                    <?= $row["amenity_status"]; ?>
                                </span>
                                <div class="card-actions">
                                    <a href="#" title="View"
                                       onclick="event.stopPropagation(); openAmenityDetailModal(event, document.querySelector('[data-amenity-id=\'<?= $row["amenity_id"]; ?>\']')); return false;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" title="Edit"
                                       onclick="event.stopPropagation(); openEditAmenityModal(<?= $row["amenity_id"]; ?>); return false;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="facilities.php?delete_amenity=<?= $row["amenity_id"]; ?>"
                                       onclick="event.stopPropagation(); return confirm('Delete amenity <?= addslashes($row["amenity_name"]); ?>?');"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p id="noAmenitiesMsg" style="display:none;text-align:center;padding:40px;color:#94a3b8">No amenities match your search.</p>
        </div>
    </main>

    <script>
    
    const roomData = <?= json_encode(array_map(fn($r) => [
        'id'            => $r['room_id'],
        'number'        => $r['room_number'],
        'type'          => $r['room_type'],
        'capacity'      => $r['max_capacity'],
        'price'         => $r['price_per_night'],
        'status'        => $r['room_status'],
        'image'         => '../' . ($r['image_path'] ?: 'assets/images/default-room.jpg'),
        'extraGuestFee' => $r['extra_guest_fee'] ?? 0,
        'extraBedFee'   => $r['extra_bed_fee']   ?? 0,
    ], $rooms_arr)); ?>;

    const amenityData = <?= json_encode(array_map(fn($a) => [
        'id'     => $a['amenity_id'],
        'name'   => $a['amenity_name'],
        'desc'   => $a['description'] ?? '',
        'price'  => $a['price'],
        'status' => $a['amenity_status'],
        'image'  => '../' . ($a['image_path'] ?: 'assets/images/default-room.jpg'),
    ], $amenities_arr)); ?>;

    
    function switchTab(tab) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.facility-tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        document.querySelector(`.facility-tab-btn[data-tab="${tab}"]`).classList.add('active');
        history.replaceState(null, '', '?tab=' + tab);
    }

    //room modal
    let currentDetailRoomId = null;

    function openAddRoomModal() {
        document.getElementById('roomModalTitle').textContent = 'Add Room';
        document.getElementById('roomSubmitBtn').textContent  = 'Add Room';
        document.getElementById('formRoomId').value = '';
        document.getElementById('roomForm').reset();
        document.getElementById('currentRoomImgWrap').style.display = 'none';
        document.getElementById('roomImgNote').textContent = '*';
        document.getElementById('f_room_image').required = true;
        document.getElementById('roomModal').classList.add('show');
    }

    function openEditRoomModal(roomId) {
        const room = roomData.find(r => r.id == roomId);
        if (!room) return;
        document.getElementById('roomModalTitle').textContent = 'Edit Room';
        document.getElementById('roomSubmitBtn').textContent  = 'Update Room';
        document.getElementById('formRoomId').value           = room.id;
        document.getElementById('f_room_number').value        = room.number;
        document.getElementById('f_room_type').value          = room.type;
        document.getElementById('f_max_capacity').value       = room.capacity;
        document.getElementById('f_price').value              = room.price;
        document.getElementById('f_status').value             = room.status;
        document.getElementById('f_extra_guest_fee').value    = room.extraGuestFee || 0;
        document.getElementById('f_extra_bed_fee').value      = room.extraBedFee   || 0;
        document.getElementById('currentRoomImg').src         = room.image;
        document.getElementById('currentRoomImgWrap').style.display = 'block';
        document.getElementById('f_room_image').required      = false;
        document.getElementById('roomImgNote').textContent    = '(optional)';
        document.getElementById('f_room_image').value         = '';
        document.getElementById('roomModal').classList.add('show');
    }

    function closeRoomModal() { document.getElementById('roomModal').classList.remove('show'); }
    document.getElementById('roomModal').addEventListener('click', e => { if (e.target === document.getElementById('roomModal')) closeRoomModal(); });

    function openRoomDetailModal(event, cardEl) {
        if (!cardEl && event.target.closest('a')) return;
        const card = cardEl || event.currentTarget;
        currentDetailRoomId = card.dataset.roomId;
        document.getElementById('detailRoomImg').src        = card.dataset.image;
        document.getElementById('d_number').textContent     = card.dataset.roomNumber;
        document.getElementById('d_type').textContent       = card.dataset.roomType;
        document.getElementById('d_capacity').textContent   = card.dataset.capacity + ' guests';
        document.getElementById('d_price').textContent      = '₱' + Number(card.dataset.priceRaw).toLocaleString('en-PH', {minimumFractionDigits:2});
        document.getElementById('d_status').textContent     = card.dataset.status.charAt(0).toUpperCase() + card.dataset.status.slice(1);
        document.getElementById('roomDetailModal').classList.add('show');
    }

    function closeRoomDetailModal() { document.getElementById('roomDetailModal').classList.remove('show'); }
    document.getElementById('roomDetailModal').addEventListener('click', e => { if (e.target === document.getElementById('roomDetailModal')) closeRoomDetailModal(); });

    function editRoomFromDetail() { closeRoomDetailModal(); if (currentDetailRoomId) openEditRoomModal(currentDetailRoomId); }
    function deleteRoomFromDetail() {
        if (!currentDetailRoomId) return;
        const room = roomData.find(r => r.id == currentDetailRoomId);
        if (confirm('Delete room ' + (room ? room.number : '') + '?'))
            window.location.href = 'facilities.php?delete_room=' + currentDetailRoomId;
    }

    /* amenity modal */
    let currentDetailAmenityId = null;

    function openAddAmenityModal() {
        document.getElementById('amenityModalTitle').textContent = 'Add Amenity';
        document.getElementById('amenitySubmitBtn').textContent  = 'Add Amenity';
        document.getElementById('formAmenityId').value = '';
        document.getElementById('amenityForm').reset();
        document.getElementById('currentAmenityImgWrap').style.display = 'none';
        document.getElementById('amenityImgNote').textContent = '*';
        document.getElementById('f_amenity_image').required = true;
        document.getElementById('amenityModal').classList.add('show');
    }

    function openEditAmenityModal(amenityId) {
        const a = amenityData.find(x => x.id == amenityId);
        if (!a) return;
        document.getElementById('amenityModalTitle').textContent = 'Edit Amenity';
        document.getElementById('amenitySubmitBtn').textContent  = 'Update Amenity';
        document.getElementById('formAmenityId').value           = a.id;
        document.getElementById('f_amenity_name').value          = a.name;
        document.getElementById('f_amenity_desc').value          = a.desc;
        document.getElementById('f_amenity_price').value         = a.price;
        document.getElementById('f_amenity_status').value        = a.status;
        document.getElementById('currentAmenityImg').src         = a.image;
        document.getElementById('currentAmenityImgWrap').style.display = 'block';
        document.getElementById('f_amenity_image').required      = false;
        document.getElementById('amenityImgNote').textContent    = '(optional)';
        document.getElementById('f_amenity_image').value         = '';
        document.getElementById('amenityModal').classList.add('show');
    }

    function closeAmenityModal() { document.getElementById('amenityModal').classList.remove('show'); }
    document.getElementById('amenityModal').addEventListener('click', e => { if (e.target === document.getElementById('amenityModal')) closeAmenityModal(); });

    function openAmenityDetailModal(event, cardEl) {
        if (!cardEl && event.target.closest('a')) return;
        const card = cardEl || event.currentTarget;
        currentDetailAmenityId = card.dataset.amenityId;
        document.getElementById('detailAmenityImg').src = card.dataset.amenityImage;
        document.getElementById('da_name').textContent  = card.dataset.amenityName;
        document.getElementById('da_desc').textContent  = card.dataset.amenityDesc || '-';
        document.getElementById('da_price').textContent = '₱' + Number(card.dataset.amenityPrice).toLocaleString('en-PH', {minimumFractionDigits:2});
        document.getElementById('da_status').textContent = card.dataset.amenityStatus;
        document.getElementById('amenityDetailModal').classList.add('show');
    }

    function closeAmenityDetailModal() { document.getElementById('amenityDetailModal').classList.remove('show'); }
    document.getElementById('amenityDetailModal').addEventListener('click', e => { if (e.target === document.getElementById('amenityDetailModal')) closeAmenityDetailModal(); });

    function editAmenityFromDetail() { closeAmenityDetailModal(); if (currentDetailAmenityId) openEditAmenityModal(currentDetailAmenityId); }
    function deleteAmenityFromDetail() {
        if (!currentDetailAmenityId) return;
        const a = amenityData.find(x => x.id == currentDetailAmenityId);
        if (confirm('Delete amenity ' + (a ? a.name : '') + '?'))
            window.location.href = 'facilities.php?delete_amenity=' + currentDetailAmenityId;
    }

    
    let activeRoomFilter = 'all';

    function filterRoomStatus(status, el) {
        activeRoomFilter = status;
        document.querySelectorAll('#tab-rooms .filter-tab').forEach(a => a.classList.remove('active'));
        el.classList.add('active');
        applyRoomSearch();
    }

    function applyRoomSearch() {
        const q = document.getElementById('roomSearch').value.toLowerCase();
        const cards = document.querySelectorAll('#roomsGrid .room-card');
        let visible = 0;
        cards.forEach(card => {
            const matchStatus = activeRoomFilter === 'all' || card.dataset.status === activeRoomFilter;
            const text = (card.dataset.roomNumber + ' ' + card.dataset.roomType).toLowerCase();
            const matchSearch = !q || text.includes(q);
            card.style.display = (matchStatus && matchSearch) ? '' : 'none';
            if (matchStatus && matchSearch) visible++;
        });
        document.getElementById('noRoomsMsg').style.display = visible === 0 ? 'block' : 'none';
    }

    let activeAmenityFilter = 'all';

    function filterAmenityStatus(status, el) {
        activeAmenityFilter = status;
        document.querySelectorAll('#tab-amenities .filter-tab').forEach(a => a.classList.remove('active'));
        el.classList.add('active');
        applyAmenitySearch();
    }

    function applyAmenitySearch() {
        const q = document.getElementById('amenitySearch').value.toLowerCase();
        const cards = document.querySelectorAll('#amenitiesGrid .room-card');
        let visible = 0;
        cards.forEach(card => {
            const matchStatus = activeAmenityFilter === 'all' || card.dataset.amenityStatus === activeAmenityFilter;
            const text = card.dataset.amenityName.toLowerCase();
            const matchSearch = !q || text.includes(q);
            card.style.display = (matchStatus && matchSearch) ? '' : 'none';
            if (matchStatus && matchSearch) visible++;
        });
        document.getElementById('noAmenitiesMsg').style.display = visible === 0 ? 'block' : 'none';
    }
    </script>
</body>
</html>
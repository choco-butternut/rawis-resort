<?php
require_once __DIR__ . '/config.php';

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $room_id       = (int) $_POST["room_id"];
    $first_name    = sanitize_input($_POST["first_name"]);
    $last_name     = sanitize_input($_POST["last_name"]);
    $email         = sanitize_input($_POST["email"]);
    $phone_number  = sanitize_input($_POST["phone_number"]);
    $address       = sanitize_input($_POST["address"]);
    $check_in      = $_POST["check_in_date"];
    $check_out     = $_POST["check_out_date"];
    $num_guests    = (int) $_POST["num_guests"];
    $extra_requests = sanitize_input($_POST["extra_requests"]);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, first_name,last_name,email,phone_number,address,role)
         VALUES (?,'',?,?,?,?,?,'guest')"
    );

    $username1 = $first_name . " ". $last_name;
    $stmt->bind_param("ssssss",
    $username1,
        $first_name,
        $last_name,
        $email,
        $phone_number,
        $address
    );
    $stmt->execute();
    $guest_id = $stmt->insert_id;
    $stmt->close();

    $stmt2 = $conn->prepare(
        "INSERT INTO reservations
         (guest_id, room_id, check_in_date, check_out_date, num_guests, created_at, reservation_status, extra_requests)
         VALUES (?,?,?,?,?,NOW(),'Pending',?)"
    );
    $stmt2->bind_param("iissis",
        $guest_id,
        $room_id,
        $check_in,
        $check_out,
        $num_guests,
        $extra_requests
    );
    $stmt2->execute();

    $reservation_id = $stmt2->insert_id;
    if (!empty($_POST["amenities"])) {
        foreach ($_POST["amenities"] as $amenity_id => $dummy) {

            $amenity_id = (int) $amenity_id;

            $result = $conn->query(
                "SELECT price FROM amenities WHERE amenity_id=$amenity_id"
            );
            $row = $result->fetch_assoc();
            $price = $row["price"];


            $amenity_id = (int) $amenity_id;
            $quantity = isset($_POST["quantity"][$amenity_id]) 
                        ? (int) $_POST["quantity"][$amenity_id] 
                        : 1;

            $stmt3 = $conn->prepare(
                "INSERT INTO reservation_amenities
                (reservation_id, amenity_id, quantity, price)
                VALUES (?,?,?,?)"
            );

            $stmt3->bind_param(
                "iiid",
                $reservation_id,
                $amenity_id,
                $quantity,
                $price
            );

            $stmt3->execute();
            $stmt3->close();
        }
    }


    $stmt2->close();

    header("Location: /payment.php?reservation_id=" . $reservation_id);
    exit();
}
?>

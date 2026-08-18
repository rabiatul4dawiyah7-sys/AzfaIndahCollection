<?php

session_start();

require_once "db_connect.php";


// ==========================================
// CHECK ADMIN LOGIN
// ==========================================

if (!isset($_SESSION["admin_id"])) {

    header("Location: admin_login.php");

    exit;
}


// ==========================================
// CHECK BOOKING ID
// ==========================================

$booking_id = $_POST["booking_id"] ?? "";

if (empty($booking_id)) {

    die("Invalid booking ID.");

}


// ==========================================
// DELETE BOOKING
// ==========================================

$sql = "DELETE FROM bookings
        WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die(
        "Delete query error: "
        . $conn->error
    );

}

$stmt->bind_param(
    "i",
    $booking_id
);


if ($stmt->execute()) {

    header(
        "Location: admin_dashboard.php"
    );

    exit;

} else {

    die(
        "Unable to delete booking: "
        . $stmt->error
    );

}


$stmt->close();

$conn->close();

?>
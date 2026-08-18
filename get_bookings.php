<?php

require_once "db_connect.php";

header("Content-Type: application/json");

$sql = "SELECT booking_date, booking_time FROM bookings";

$result = $conn->query($sql);

$bookings = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $time = date(
            "g:i A",
            strtotime($row["booking_time"])
        );

        $bookings[] = [
            "date" => $row["booking_date"],
            "time" => $time
        ];
    }
}

echo json_encode($bookings);

$conn->close();

?>
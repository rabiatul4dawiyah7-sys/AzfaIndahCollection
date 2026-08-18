<?php

require_once "db_connect.php";


// ===============================
// GET FORM DATA
// ===============================

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$service = $_POST['service'] ?? '';
$booking_date = $_POST['booking_date'] ?? '';
$booking_time = $_POST['booking_time'] ?? '';
$notes = $_POST['notes'] ?? '';


// ===============================
// CHECK REQUIRED DATA
// ===============================

if (
    empty($name) ||
    empty($phone) ||
    empty($service) ||
    empty($booking_date) ||
    empty($booking_time)
) {

    die("Please complete all required booking information.");

}


// ===============================
// CONVERT TIME
// Example: 5:00 PM → 17:00:00
// ===============================

$time_object = DateTime::createFromFormat('g:i A', $booking_time);

if ($time_object) {

    $booking_time_mysql = $time_object->format('H:i:s');

} else {

    $booking_time_mysql = $booking_time;

}


// ===============================
// CHECK EXISTING BOOKING
// ===============================

$check_sql = "SELECT id
              FROM bookings
              WHERE booking_date = ?
              AND booking_time = ?";

$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    die("Database error: " . $conn->error);
}

$check_stmt->bind_param(
    "ss",
    $booking_date,
    $booking_time_mysql
);

$check_stmt->execute();

$result = $check_stmt->get_result();


// ===============================
// SLOT ALREADY BOOKED
// ===============================

if ($result->num_rows > 0) {

    ?>

    <!DOCTYPE html>

    <html>

    <head>

        <title>Time Slot Unavailable</title>

        <style>

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;

                display: flex;
                justify-content: center;
                align-items: center;

                font-family: Arial, sans-serif;

                background: #f8f5f0;
            }

            .booking-card {

                width: 90%;
                max-width: 520px;

                background: white;

                padding: 45px 40px;

                border-radius: 20px;

                text-align: center;

                box-shadow:
                    0 15px 40px rgba(0,0,0,0.08);
            }

            .icon {

                font-size: 55px;

                margin-bottom: 15px;
            }

            h1 {

                margin-bottom: 15px;

                color: #4b3b32;

                font-size: 30px;
            }

            p {

                color: #777;

                line-height: 1.6;

                font-size: 16px;
            }

            .details {

                margin: 25px 0;

                padding: 20px;

                background: #f8f5f0;

                border-radius: 12px;

                color: #4b3b32;

                line-height: 1.8;
            }

            .back-button {

                display: inline-block;

                margin-top: 15px;

                padding: 13px 28px;

                background: #4b3b32;

                color: white;

                text-decoration: none;

                border-radius: 9px;

                font-size: 15px;
            }

            .back-button:hover {

                opacity: 0.85;
            }

        </style>

    </head>


    <body>

        <div class="booking-card">

            <div class="icon">⏰</div>

            <h1>Time Slot Unavailable</h1>

            <p>
                Sorry, this time slot has already been booked.
            </p>

            <div class="details">

                <strong>Service:</strong>
                <?= htmlspecialchars($service) ?>

                <br>

                <strong>Date:</strong>
                <?= htmlspecialchars($booking_date) ?>

                <br>

                <strong>Time:</strong>
                <?= htmlspecialchars($booking_time) ?>

            </div>

            <p>
                Please choose another available time slot.
            </p>

            <a href="booking.html" class="back-button">
                ← Choose Another Time
            </a>

        </div>

    </body>

    </html>

    <?php

    exit;
}


// ===============================
// SAVE BOOKING
// ===============================

$sql = "INSERT INTO bookings
        (name, phone, service, booking_date, booking_time, notes)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database error: " . $conn->error);

}

$stmt->bind_param(
    "ssssss",
    $name,
    $phone,
    $service,
    $booking_date,
    $booking_time_mysql,
    $notes
);


// ===============================
// SUCCESS
// ===============================

if ($stmt->execute()) {

    ?>

    <!DOCTYPE html>

    <html>

    <head>

        <title>Booking Successful</title>

        <style>

            * {
                box-sizing: border-box;
            }

            body {

                margin: 0;

                min-height: 100vh;

                display: flex;

                justify-content: center;

                align-items: center;

                font-family: Arial, sans-serif;

                background: #f8f5f0;
            }

            .booking-card {

                width: 90%;

                max-width: 520px;

                background: white;

                padding: 45px 40px;

                border-radius: 20px;

                text-align: center;

                box-shadow:
                    0 15px 40px rgba(0,0,0,0.08);
            }

            .icon {

                font-size: 55px;

                margin-bottom: 15px;
            }

            h1 {

                color: #4b3b32;

                font-size: 30px;

                margin-bottom: 15px;
            }

            p {

                color: #777;

                line-height: 1.6;

                font-size: 16px;
            }

            .details {

                margin: 25px 0;

                padding: 20px;

                background: #f8f5f0;

                border-radius: 12px;

                color: #4b3b32;

                line-height: 1.8;
            }

            .back-button {

                display: inline-block;

                margin-top: 15px;

                padding: 13px 28px;

                background: #4b3b32;

                color: white;

                text-decoration: none;

                border-radius: 9px;

                font-size: 15px;
            }

            .back-button:hover {

                opacity: 0.85;
            }

        </style>

    </head>


    <body>

        <div class="booking-card">

            <div class="icon">🎉</div>

            <h1>Booking Successful!</h1>

            <p>

                Thank you,
                <strong><?= htmlspecialchars($name) ?></strong>!

            </p>

            <p>

                Your booking has been successfully submitted
                to <strong>Azfa Indah Collection</strong>.

            </p>


            <div class="details">

    <strong>Booking ID:</strong>
    #<?= $stmt->insert_id ?>

    <br>

    <strong>Service:</strong>
    <?= htmlspecialchars($service) ?>

    <br>

    <strong>Date:</strong>
    <?= htmlspecialchars($booking_date) ?>

    <br>

    <strong>Time:</strong>
    <?= htmlspecialchars($booking_time) ?>

</div>


            <a href="index.html" class="back-button">

                 ← Back to Home

            </a>

        </div>

    </body>

    </html>

    <?php

} else {

    echo "Error saving booking: " . $stmt->error;

}


$stmt->close();
$check_stmt->close();
$conn->close();

?>
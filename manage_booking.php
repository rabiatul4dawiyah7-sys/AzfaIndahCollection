<?php

require_once "db_connect.php";

$booking = null;
$error = "";
$success = "";


// ==========================================
// FIND BOOKING
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["find_booking"])
) {

    $booking_id = $_POST["booking_id"] ?? "";
    $phone = $_POST["phone"] ?? "";

    $sql = "SELECT *
            FROM bookings
            WHERE id = ?
            AND phone = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "is",
        $booking_id,
        $phone
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $booking = $result->fetch_assoc();

    } else {

        $error =
            "Booking not found. Please check your Booking ID and phone number.";

    }

    $stmt->close();
}


// ==========================================
// UPDATE BOOKING
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_booking"])
) {

    $booking_id = $_POST["booking_id"] ?? "";
    $phone = $_POST["phone"] ?? "";

    $service = $_POST["service"] ?? "";
    $booking_date = $_POST["booking_date"] ?? "";
    $booking_time = $_POST["booking_time"] ?? "";
    $notes = $_POST["notes"] ?? "";


    // Convert AM/PM to MySQL time

    $time_object = DateTime::createFromFormat(
        "g:i A",
        $booking_time
    );

    if ($time_object) {

        $booking_time_mysql =
            $time_object->format("H:i:s");

    } else {

        $booking_time_mysql =
            $booking_time;

    }


    // Check if another booking already uses the slot

    $check_sql =
        "SELECT id
         FROM bookings
         WHERE booking_date = ?
         AND booking_time = ?
         AND id != ?";

    $check_stmt =
        $conn->prepare($check_sql);

    $check_stmt->bind_param(
        "ssi",
        $booking_date,
        $booking_time_mysql,
        $booking_id
    );

    $check_stmt->execute();

    $result =
        $check_stmt->get_result();


    if ($result->num_rows > 0) {

        $error =
            "Sorry! This time slot is already booked.";

    } else {

        $update_sql =
            "UPDATE bookings
             SET service = ?,
                 booking_date = ?,
                 booking_time = ?,
                 notes = ?
             WHERE id = ?
             AND phone = ?";

        $update_stmt =
            $conn->prepare($update_sql);

        $update_stmt->bind_param(
            "ssssis",
            $service,
            $booking_date,
            $booking_time_mysql,
            $notes,
            $booking_id,
            $phone
        );


        if ($update_stmt->execute()) {

            $success =
                "Your booking has been updated successfully.";


            // Get updated booking

            $get_sql =
                "SELECT *
                 FROM bookings
                 WHERE id = ?
                 AND phone = ?";

            $get_stmt =
                $conn->prepare($get_sql);

            $get_stmt->bind_param(
                "is",
                $booking_id,
                $phone
            );

            $get_stmt->execute();

            $updated_result =
                $get_stmt->get_result();

            if (
                $updated_result->num_rows === 1
            ) {

                $booking =
                    $updated_result->fetch_assoc();

            }

            $get_stmt->close();

        } else {

            $error =
                "Unable to update booking: "
                . $update_stmt->error;

        }

        $update_stmt->close();

    }

    $check_stmt->close();
}


// ==========================================
// CANCEL BOOKING
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["cancel_booking"])
) {

    $booking_id = $_POST["booking_id"] ?? "";
    $phone = $_POST["phone"] ?? "";


    $delete_sql =
        "DELETE FROM bookings
         WHERE id = ?
         AND phone = ?";

    $delete_stmt =
        $conn->prepare($delete_sql);

    $delete_stmt->bind_param(
        "is",
        $booking_id,
        $phone
    );


    if ($delete_stmt->execute()) {

        if ($delete_stmt->affected_rows > 0) {

            $success =
                "Your booking has been cancelled successfully.";

        } else {

            $error =
                "Booking not found.";

        }

    } else {

        $error =
            "Unable to cancel booking.";

    }

    $delete_stmt->close();

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Manage Booking | Azfa Indah Collection
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            font-family:
                Arial,
                sans-serif;

            background: #f8f5f0;

            color: #4b3b32;

            padding: 40px 20px;
        }


        .container {

            width: 100%;

            max-width: 650px;

            margin: auto;
        }


        .card {

            background: white;

            padding: 40px;

            border-radius: 20px;

            box-shadow:
                0 15px 40px
                rgba(0,0,0,0.08);
        }


        h1 {

            text-align: center;

            margin-bottom: 10px;
        }


        .subtitle {

            text-align: center;

            color: #777;

            line-height: 1.5;

            margin-bottom: 30px;
        }


        label {

            display: block;

            font-weight: bold;

            margin-bottom: 8px;
        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 13px;

            border: 1px solid #ddd;

            border-radius: 8px;

            font-size: 15px;

            margin-bottom: 20px;

            font-family: inherit;
        }


        textarea {

            resize: vertical;
        }


        button {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #4b3b32;

            color: white;

            font-size: 15px;

            cursor: pointer;
        }


        button:hover {

            opacity: 0.9;
        }


        .message {

            padding: 14px;

            border-radius: 8px;

            margin-bottom: 20px;

            text-align: center;

            line-height: 1.5;
        }


        .error {

            background: #fceaea;

            color: #a33;
        }


        .success {

            background: #edf7ed;

            color: #39733f;
        }


        .booking-info {

            background: #f8f5f0;

            padding: 20px;

            border-radius: 12px;

            margin-bottom: 25px;

            line-height: 2;
        }


        .booking-id {

            text-align: center;

            font-size: 18px;

            margin-bottom: 20px;

            font-weight: bold;
        }


        .section-title {

            margin-top: 30px;

            margin-bottom: 20px;

            border-bottom: 1px solid #eee;

            padding-bottom: 10px;
        }


        .cancel-btn {

            background: #9b4d4d;

            margin-top: 12px;
        }


        .back {

            display: block;

            text-align: center;

            margin-top: 25px;

            color: #4b3b32;

            text-decoration: none;
        }


        .divider {

            text-align: center;

            margin: 30px 0;

            color: #aaa;
        }


    </style>

</head>


<body>


<div class="container">

    <div class="card">


        <h1>
            Manage My Booking
        </h1>


        <p class="subtitle">

            View, update or cancel your appointment.

        </p>


        <?php if ($error): ?>

            <div class="message error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <?php if ($success): ?>

            <div class="message success">

                ✓ <?= htmlspecialchars($success) ?>

            </div>

        <?php endif; ?>


        <?php if (!$booking && !$success): ?>


            <!-- FIND BOOKING -->

            <form method="POST">

                <label for="booking_id">

                    Booking ID

                </label>

                <input
                    type="number"
                    id="booking_id"
                    name="booking_id"
                    placeholder="Example: 7"
                    required
                >


                <label for="phone">

                    Phone Number

                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="Enter your phone number"
                    required
                >


                <button
                    type="submit"
                    name="find_booking"
                >

                    Find My Booking

                </button>

            </form>


        <?php elseif ($booking): ?>


            <!-- BOOKING INFORMATION -->

            <div class="booking-id">

                Booking #<?= htmlspecialchars(
                    $booking["id"]
                ) ?>

            </div>


            <div class="booking-info">

                <strong>Name:</strong>

                <?= htmlspecialchars(
                    $booking["name"]
                ) ?>

                <br>


                <strong>Phone:</strong>

                <?= htmlspecialchars(
                    $booking["phone"]
                ) ?>

                <br>


                <strong>Service:</strong>

                <?= htmlspecialchars(
                    $booking["service"]
                ) ?>

                <br>


                <strong>Date:</strong>

                <?= htmlspecialchars(
                    $booking["booking_date"]
                ) ?>

                <br>


                <strong>Time:</strong>

                <?= date(
                    "g:i A",
                    strtotime(
                        $booking["booking_time"]
                    )
                ) ?>

                <br>


                <strong>Notes:</strong>

                <?= htmlspecialchars(
                    $booking["notes"] ?? "-"
                ) ?>

            </div>


            <!-- UPDATE SECTION -->

            <h3 class="section-title">

                ✏️ Update Booking

            </h3>


            <form method="POST">


                <input
                    type="hidden"
                    name="booking_id"
                    value="<?= htmlspecialchars(
                        $booking["id"]
                    ) ?>"
                >


                <input
                    type="hidden"
                    name="phone"
                    value="<?= htmlspecialchars(
                        $booking["phone"]
                    ) ?>"
                >


                <label for="service">

                    Service

                </label>

                <select
                    id="service"
                    name="service"
                    required
                >

                    <?php

                    $services = [

                        "Baju Kurung",
                        "Baju Melayu",
                        "Baju Kebaya",
                        "Jubah",
                        "Kemeja",
                        "Alteration",
                        "Tukar Zip",
                        "Baju Tunang",
                        "Langsir"

                    ];

                    foreach (
                        $services as $service
                    ):

                    ?>

                        <option
                            value="<?= $service ?>"
                            <?= $booking["service"]
                                === $service
                                ? "selected"
                                : "" ?>
                        >

                            <?= $service ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <label for="booking_date">

                    Date

                </label>

                <input
                    type="date"
                    id="booking_date"
                    name="booking_date"
                    value="<?= htmlspecialchars(
                        $booking["booking_date"]
                    ) ?>"
                    required
                >


                <label for="booking_time">

                    Time

                </label>

                <select
                    id="booking_time"
                    name="booking_time"
                    required
                >

                    <?php

                    $times = [

                        "10:00 AM",
                        "11:00 AM",
                        "12:00 PM",
                        "2:00 PM",
                        "3:00 PM",
                        "4:00 PM",
                        "5:00 PM"

                    ];

                    $current_time = date(
                        "g:i A",
                        strtotime(
                            $booking["booking_time"]
                        )
                    );

                    foreach (
                        $times as $time
                    ):

                    ?>

                        <option
                            value="<?= $time ?>"
                            <?= $current_time
                                === $time
                                ? "selected"
                                : "" ?>
                        >

                            <?= $time ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <label for="notes">

                    Additional Notes

                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                ><?= htmlspecialchars(
                    $booking["notes"] ?? ""
                ) ?></textarea>


                <button
                    type="submit"
                    name="update_booking"
                >

                    Save Changes

                </button>

            </form>


            <!-- CANCEL SECTION -->

            <h3 class="section-title">

                🗑️ Cancel Booking

            </h3>


            <form
                method="POST"
                onsubmit="return confirmCancel();"
            >

                <input
                    type="hidden"
                    name="booking_id"
                    value="<?= htmlspecialchars(
                        $booking["id"]
                    ) ?>"
                >


                <input
                    type="hidden"
                    name="phone"
                    value="<?= htmlspecialchars(
                        $booking["phone"]
                    ) ?>"
                >


                <button
                    type="submit"
                    name="cancel_booking"
                    class="cancel-btn"
                >

                    Cancel My Booking

                </button>

            </form>


        <?php endif; ?>


        <a
            href="index.html"
            class="back"
        >

            ← Back to Home

        </a>


    </div>

</div>


<script>

function confirmCancel() {

    return confirm(
        "Are you sure you want to cancel this booking?"
    );

}

</script>


</body>

</html>

<?php

$conn->close();

?>
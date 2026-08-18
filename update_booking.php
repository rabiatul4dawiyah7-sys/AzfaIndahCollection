<?php

require_once "db_connect.php";

$booking = null;
$error = "";
$success = "";


// ==========================================
// STEP 1: FIND BOOKING
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["find_booking"])) {

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
// STEP 2: UPDATE BOOKING
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_booking"])) {

    $booking_id = $_POST["booking_id"] ?? "";
    $phone = $_POST["phone"] ?? "";

    $service = $_POST["service"] ?? "";
    $booking_date = $_POST["booking_date"] ?? "";
    $booking_time = $_POST["booking_time"] ?? "";
    $notes = $_POST["notes"] ?? "";


    // Check required information

    if (
        empty($booking_id) ||
        empty($phone) ||
        empty($service) ||
        empty($booking_date) ||
        empty($booking_time)
    ) {

        $error =
            "Please complete all required information.";

    } else {


        // ==========================================
        // CONVERT TIME
        // ==========================================

        $time_object =
            DateTime::createFromFormat(
                'g:i A',
                $booking_time
            );

        if ($time_object) {

            $booking_time_mysql =
                $time_object->format('H:i:s');

        } else {

            $booking_time_mysql =
                $booking_time;

        }


        // ==========================================
        // CHECK IF NEW SLOT IS ALREADY BOOKED
        // ==========================================

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


            // ==========================================
            // UPDATE DATABASE
            // ==========================================

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

                if ($update_stmt->affected_rows >= 0) {

                    $success =
                        "Your booking has been updated successfully.";

                }

            } else {

                $error =
                    "Unable to update booking: "
                    . $update_stmt->error;

            }

            $update_stmt->close();

        }

        $check_stmt->close();

    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Update Booking | Azfa Indah Collection
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            min-height: 100vh;

            font-family: Arial, sans-serif;

            background: #f8f5f0;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 30px;
        }

        .container {

            width: 100%;

            max-width: 550px;
        }

        .card {

            background: white;

            padding: 40px;

            border-radius: 20px;

            box-shadow:
                0 15px 40px rgba(0,0,0,0.08);
        }

        h1 {

            color: #4b3b32;

            text-align: center;

            margin-bottom: 10px;
        }

        .subtitle {

            text-align: center;

            color: #777;

            margin-bottom: 30px;

            line-height: 1.5;
        }

        label {

            display: block;

            margin-bottom: 8px;

            color: #4b3b32;

            font-weight: bold;
        }

        input,
        select,
        textarea {

            width: 100%;

            padding: 13px;

            margin-bottom: 20px;

            border: 1px solid #ddd;

            border-radius: 8px;

            font-size: 15px;

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

            padding: 13px;

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

        .booking-id {

            background: #f8f5f0;

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 25px;

            color: #4b3b32;

            text-align: center;
        }

        .back {

            display: block;

            margin-top: 20px;

            text-align: center;

            color: #4b3b32;

            text-decoration: none;
        }

    </style>

</head>


<body>


<div class="container">

    <div class="card">


        <h1>
            Update Booking
        </h1>

        <p class="subtitle">

            Find your booking and update
            your appointment details.

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


            <div class="booking-id">

                Booking ID:
                <strong>
                    #<?= htmlspecialchars(
                        $booking["id"]
                    ) ?>
                </strong>

            </div>


            <!-- UPDATE FORM -->

            <form method="POST">


                <!-- Hidden information -->

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


                <!-- Service -->

                <label for="service">

                    Service

                </label>

                <select
                    id="service"
                    name="service"
                    required
                >

                    <option value="Baju Kurung"
                        <?= $booking["service"] === "Baju Kurung"
                            ? "selected" : "" ?>>
                        Baju Kurung
                    </option>

                    <option value="Baju Melayu"
                        <?= $booking["service"] === "Baju Melayu"
                            ? "selected" : "" ?>>
                        Baju Melayu
                    </option>

                    <option value="Baju Kebaya"
                        <?= $booking["service"] === "Baju Kebaya"
                            ? "selected" : "" ?>>
                        Baju Kebaya
                    </option>

                    <option value="Jubah"
                        <?= $booking["service"] === "Jubah"
                            ? "selected" : "" ?>>
                        Jubah
                    </option>

                    <option value="Kemeja"
                        <?= $booking["service"] === "Kemeja"
                            ? "selected" : "" ?>>
                        Kemeja
                    </option>

                    <option value="Alteration"
                        <?= $booking["service"] === "Alteration"
                            ? "selected" : "" ?>>
                        Alteration
                    </option>

                    <option value="Tukar Zip"
                        <?= $booking["service"] === "Tukar Zip"
                            ? "selected" : "" ?>>
                        Tukar Zip
                    </option>

                    <option value="Baju Tunang"
                        <?= $booking["service"] === "Baju Tunang"
                            ? "selected" : "" ?>>
                        Baju Tunang
                    </option>

                    <option value="Langsir"
                        <?= $booking["service"] === "Langsir"
                            ? "selected" : "" ?>>
                        Langsir
                    </option>

                </select>


                <!-- Date -->

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


                <!-- Time -->

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

                    foreach ($times as $time):

                    ?>

                        <option
                            value="<?= $time ?>"
                            <?= date(
                                "g:i A",
                                strtotime(
                                    $booking["booking_time"]
                                )
                            ) === $time
                                ? "selected"
                                : "" ?>
                        >

                            <?= $time ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <!-- Notes -->

                <label for="notes">

                    Additional Notes

                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    placeholder="Tell us anything we should know..."
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


        <?php endif; ?>


        <a
            href="index.html"
            class="back"
        >

            ← Back to Home

        </a>


    </div>

</div>


</body>

</html>

<?php

$conn->close();

?>
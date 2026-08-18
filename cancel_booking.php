<?php

require_once "db_connect.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $booking_id = $_POST["booking_id"] ?? "";
    $phone = $_POST["phone"] ?? "";

    if (empty($booking_id) || empty($phone)) {

        $error = "Please enter your Booking ID and phone number.";

    } else {

        // Check whether booking exists
        $check_sql = "SELECT id, name, booking_date, booking_time
                      FROM bookings
                      WHERE id = ?
                      AND phone = ?";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "is",
            $booking_id,
            $phone
        );

        $check_stmt->execute();

        $result = $check_stmt->get_result();

        if ($result->num_rows === 1) {

            $booking = $result->fetch_assoc();

            // Delete booking
            $delete_sql = "DELETE FROM bookings
                           WHERE id = ?
                           AND phone = ?";

            $delete_stmt = $conn->prepare($delete_sql);

            $delete_stmt->bind_param(
                "is",
                $booking_id,
                $phone
            );

            if ($delete_stmt->execute()) {

                $success =
                    "Your booking has been cancelled successfully.";

            } else {

                $error =
                    "Unable to cancel your booking.";

            }

            $delete_stmt->close();

        } else {

            $error =
                "Booking not found. Please check your Booking ID and phone number.";

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
        Cancel Booking | Azfa Indah Collection
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

            max-width: 500px;
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

        input {

            width: 100%;

            padding: 13px;

            margin-bottom: 20px;

            border: 1px solid #ddd;

            border-radius: 8px;

            font-size: 15px;
        }

        button {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #8b5e4b;

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

        .warning {

            background: #fff8e5;

            color: #856404;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 25px;

            line-height: 1.5;
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
            Cancel Booking
        </h1>

        <p class="subtitle">

            Enter your Booking ID and phone number
            to cancel your appointment.

        </p>


        <div class="warning">

            ⚠️ Once your booking is cancelled,
            the time slot will become available
            for other customers.

        </div>


        <?php if ($error): ?>

            <div class="message error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <?php if ($success): ?>

            <div class="message success">

                ✓ <?= htmlspecialchars($success) ?>

                <br><br>

                Your time slot is now available
                again.

            </div>

        <?php endif; ?>


        <?php if (!$success): ?>

            <form
                method="POST"
                onsubmit="return confirmCancel();"
            >

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


                <button type="submit">

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
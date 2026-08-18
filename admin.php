<?php

require_once "db_connect.php";

$sql = "SELECT * FROM bookings
        ORDER BY booking_date ASC, booking_time ASC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin | Azfa Indah Collection</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f8f5f0;

            color: #4b3b32;
        }

        header {

            background: white;

            padding: 22px 8%;

            box-shadow:
                0 2px 10px rgba(0,0,0,0.05);
        }

        .logo {

            font-size: 20px;

            font-weight: bold;

            letter-spacing: 1px;
        }

        .container {

            width: 90%;

            max-width: 1200px;

            margin: 50px auto;
        }

        .title {

            text-align: center;

            margin-bottom: 35px;
        }

        .title h1 {

            margin-bottom: 10px;

            font-size: 32px;
        }

        .title p {

            color: #777;
        }

        .table-container {

            background: white;

            border-radius: 15px;

            overflow-x: auto;

            box-shadow:
                0 10px 30px rgba(0,0,0,0.06);
        }

        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 800px;
        }

        th {

            background: #4b3b32;

            color: white;

            padding: 15px;

            text-align: left;
        }

        td {

            padding: 15px;

            border-bottom: 1px solid #eee;
        }

        tr:hover {

            background: #faf8f5;
        }

        .no-booking {

            text-align: center;

            padding: 40px;

            color: #777;
        }

        .back-home {

            display: inline-block;

            margin-top: 25px;

            padding: 12px 25px;

            background: #4b3b32;

            color: white;

            text-decoration: none;

            border-radius: 8px;
        }

    </style>

</head>


<body>


<header>

    <div class="logo">
        AZFA INDAH COLLECTION
    </div>

</header>


<div class="container">


    <div class="title">

        <h1>Booking Management</h1>

        <p>
            View all customer appointments
        </p>

    </div>


    <div class="table-container">

        <?php if ($result && $result->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>Name</th>

                        <th>Phone</th>

                        <th>Service</th>

                        <th>Date</th>

                        <th>Time</th>

                        <th>Notes</th>

                    </tr>

                </thead>


                <tbody>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($row['name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['phone']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['service']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['booking_date']) ?>
                            </td>

                            <td>
                                <?= date(
                                    "g:i A",
                                    strtotime($row['booking_time'])
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['notes'] ?? ''
                                ) ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="no-booking">

                <h3>No bookings yet</h3>

                <p>
                    Customer bookings will appear here.
                </p>

            </div>

        <?php endif; ?>

    </div>


    <a href="index.html" class="back-home">
        ← Back to Home
    </a>


</div>


</body>

</html>

<?php

$conn->close();

?>
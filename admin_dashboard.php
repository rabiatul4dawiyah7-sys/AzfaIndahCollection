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
// GET ALL BOOKINGS
// ==========================================

$search = $_GET["search"] ?? "";
$date = $_GET["date"] ?? "";

$sql = "SELECT *
        FROM bookings
        WHERE 1=1";

if (!empty($search)) {

    $sql .= " AND (
        name LIKE ?
        OR phone LIKE ?
        OR service LIKE ?
    )";

}

if (!empty($date)) {

    $sql .= " AND booking_date = ?";

}

$sql .= "
    ORDER BY booking_date ASC,
             booking_time ASC
";

$stmt = $conn->prepare($sql);

if (!empty($search) && !empty($date)) {

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "ssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $date
    );

} elseif (!empty($search)) {

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

} elseif (!empty($date)) {

    $stmt->bind_param(
        "s",
        $date
    );

}

$stmt->execute();

$result = $stmt->get_result();


// ==========================================
// COUNT BOOKINGS
// ==========================================

// ==========================================
// DASHBOARD STATISTICS
// ==========================================

// Total bookings
$count_sql = "SELECT COUNT(*) AS total
              FROM bookings";

$count_result = $conn->query($count_sql);

$total_bookings =
    $count_result->fetch_assoc()["total"];


// Today's bookings
$today_sql = "SELECT COUNT(*) AS total
              FROM bookings
              WHERE booking_date = CURDATE()";

$today_result = $conn->query($today_sql);

$today_bookings =
    $today_result->fetch_assoc()["total"];


// Upcoming bookings
$upcoming_sql = "SELECT COUNT(*) AS total
                 FROM bookings
                 WHERE booking_date >= CURDATE()";

$upcoming_result =
    $conn->query($upcoming_sql);

$upcoming_bookings =
    $upcoming_result->fetch_assoc()["total"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Dashboard | Azfa Indah Collection
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                sans-serif;

            background: #f8f5f0;

            color: #4b3b32;
        }


        /* =========================
           HEADER
        ========================= */

        header {

            background: white;

            padding: 20px 40px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            border-bottom: 1px solid #eee;
        }


        .logo {

            font-weight: bold;

            letter-spacing: 2px;

            font-size: 18px;
        }


        .admin-info {

            display: flex;

            align-items: center;

            gap: 20px;

            font-size: 14px;
        }


        .logout {

            text-decoration: none;

            color: white;

            background: #4b3b32;

            padding: 10px 18px;

            border-radius: 7px;
        }


        /* =========================
           MAIN
        ========================= */

        .container {

            max-width: 1200px;

            margin: auto;

            padding: 50px 25px;
        }


        .page-title {

            margin-bottom: 30px;
        }


        .page-title h1 {

            margin-bottom: 8px;
        }


        .page-title p {

            color: #777;
        }


        /* =========================
           STAT CARD
        ========================= */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(200px, 1fr)
                );

            gap: 20px;

            margin-bottom: 40px;
        }


        .stat-card {

            background: white;

            padding: 25px;

            border-radius: 15px;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,0.05);
        }


        .stat-card span {

            color: #888;

            font-size: 14px;
        }


        .stat-card h2 {

            font-size: 32px;

            margin:
                10px 0 0;
        }


        /* =========================
           BOOKING TABLE
        ========================= */

        .booking-section {

            background: white;

            padding: 30px;

            border-radius: 15px;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,0.05);
        }


        .booking-section h2 {

            margin-top: 0;

            margin-bottom: 25px;
        }


        .table-container {

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 900px;
        }


        th {

            text-align: left;

            background: #f8f5f0;

            padding: 14px;

            font-size: 13px;
        }


        td {

            padding: 14px;

            border-bottom: 1px solid #eee;

            font-size: 14px;

            vertical-align: top;
        }


        tr:hover td {

            background: #fcfaf7;
        }


        .no-booking {

            text-align: center;

            padding: 40px;

            color: #888;
        }
        .delete-btn {

    border: none;

    background: #f3dddd;

    color: #9b3d3d;

    padding: 8px 14px;

    border-radius: 6px;

    cursor: pointer;

    font-size: 13px;
}

.delete-btn:hover {

    background: #9b3d3d;

    color: white;
}


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 600px) {

            header {

                padding: 18px;

                flex-direction: column;

                gap: 15px;

                align-items: flex-start;
            }


            .container {

                padding:
                    30px 15px;
            }


            .booking-section {

                padding: 20px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<header>

    <div class="logo">

        AZFA INDAH COLLECTION

    </div>


    <div class="admin-info">

        <span>

            Welcome,
            <strong>
                <?= htmlspecialchars(
                    $_SESSION["admin_username"]
                ) ?>
            </strong>

        </span>


        <a
            href="admin_logout.php"
            class="logout"
        >

            Logout

        </a>

    </div>

</header>



<!-- =========================
     MAIN
========================= -->

<main class="container">


    <div class="page-title">

        <h1>

            Admin Dashboard

        </h1>

        <p>

            Manage customer bookings
            for Azfa Indah Collection.

        </p>

    </div>


    <!-- STAT -->

    <div class="stats">

    <!-- Total -->

    <div class="stat-card">

        <span>
            TOTAL BOOKINGS
        </span>

        <h2>
            <?= $total_bookings ?>
        </h2>

        <p>
            All bookings
        </p>

    </div>


    <!-- Today -->

    <div class="stat-card">

        <span>
            TODAY'S BOOKINGS
        </span>

        <h2>
            <?= $today_bookings ?>
        </h2>

        <p>
            Bookings for today
        </p>

    </div>


    <!-- Upcoming -->

    <div class="stat-card">

        <span>
            UPCOMING BOOKINGS
        </span>

        <h2>
            <?= $upcoming_bookings ?>
        </h2>

        <p>
            Today onwards
        </p>

    </div>

</div>


    <!-- BOOKINGS -->

    <section class="booking-section">


        <h2>

            All Bookings

        </h2>

        <form
    method="GET"
    class="search-form"
>

    <input
        type="text"
        name="search"
        placeholder="Search name, phone or service..."
        value="<?= htmlspecialchars(
            $_GET["search"] ?? ""
        ) ?>"
    >

    <input
        type="date"
        name="date"
        value="<?= htmlspecialchars(
            $_GET["date"] ?? ""
        ) ?>"
    >

    <button type="submit">
        Search
    </button>

    <a
        href="admin_dashboard.php"
        class="clear-btn"
    >
        Clear
    </a>

</form>


        <div class="table-container">


            <?php if ($result->num_rows > 0): ?>


                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Service
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Time
                            </th>

                            <th>
    Notes
</th>

<th>
    Action
</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $row =
                        $result->fetch_assoc()
                    ): ?>


                        <tr>

                            <td>

                                #
                                <?= htmlspecialchars(
                                    $row["id"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row["name"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row["phone"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row["service"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row["booking_date"]
                                ) ?>

                            </td>


                            <td>

                                <?= date(
                                    "g:i A",
                                    strtotime(
                                        $row[
                                            "booking_time"
                                        ]
                                    )
                                ) ?>

                            </td>


                            <td>

    <form
        action="admin_delete_booking.php"
        method="POST"
        onsubmit="return confirm(
            'Are you sure you want to delete this booking?'
        );"
    >

        <input
            type="hidden"
            name="booking_id"
            value="<?= $row["id"] ?>"
        >

        <button
            type="submit"
            class="delete-btn"
        >
            Delete
        </button>

    </form>

</td>

                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <div class="no-booking">

                    No bookings available yet.

                </div>


            <?php endif; ?>


        </div>

    </section>


</main>


</body>

</html>

<?php

$conn->close();

?>
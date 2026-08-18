<?php

session_start();

require_once "db_connect.php";

$error = "";


// ==========================================
// LOGIN
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";


    if (empty($username) || empty($password)) {

        $error = "Please enter username and password.";

    } else {

        $sql = "SELECT id, username, password
                FROM admin
                WHERE username = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "s",
            $username
        );

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();


            // Check password
            if ($password === $admin["password"]) {

                $_SESSION["admin_id"] =
                    $admin["id"];

                $_SESSION["admin_username"] =
                    $admin["username"];


                // Go to dashboard
                header(
                    "Location: admin_dashboard.php"
                );

                exit;

            } else {

                $error =
                    "Incorrect username or password.";

            }

        } else {

            $error =
                "Incorrect username or password.";

        }


        $stmt->close();

    }

}

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
        Admin Login | Azfa Indah Collection
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

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 20px;
        }


        .login-container {

            width: 100%;

            max-width: 420px;
        }


        .login-card {

            background: white;

            padding: 40px;

            border-radius: 20px;

            box-shadow:
                0 15px 40px
                rgba(0, 0, 0, 0.08);
        }


        .logo {

            text-align: center;

            font-size: 20px;

            font-weight: bold;

            letter-spacing: 2px;

            color: #4b3b32;

            margin-bottom: 30px;
        }


        h1 {

            text-align: center;

            color: #4b3b32;

            margin-bottom: 10px;
        }


        .subtitle {

            text-align: center;

            color: #777;

            margin-bottom: 30px;
        }


        label {

            display: block;

            color: #4b3b32;

            font-weight: bold;

            margin-bottom: 8px;
        }


        input {

            width: 100%;

            padding: 13px;

            border: 1px solid #ddd;

            border-radius: 8px;

            font-size: 15px;

            margin-bottom: 20px;

            outline: none;
        }


        input:focus {

            border-color: #8b6f5c;
        }


        .login-btn {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #4b3b32;

            color: white;

            font-size: 15px;

            cursor: pointer;
        }


        .login-btn:hover {

            opacity: 0.9;
        }


        .error {

            background: #fceaea;

            color: #a33;

            padding: 12px;

            border-radius: 8px;

            text-align: center;

            margin-bottom: 20px;
        }


        .back {

            display: block;

            text-align: center;

            margin-top: 25px;

            color: #4b3b32;

            text-decoration: none;

            font-size: 14px;
        }


    </style>

</head>


<body>


<div class="login-container">

    <div class="login-card">


        <div class="logo">

            AZFA INDAH COLLECTION

        </div>


        <h1>

            Admin Login

        </h1>


        <p class="subtitle">

            Sign in to manage your bookings.

        </p>


        <?php if ($error): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <label for="username">

                Username

            </label>

            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter username"
                required
            >


            <label for="password">

                Password

            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter password"
                required
            >


            <button
                type="submit"
                class="login-btn"
            >

                Login

            </button>


        </form>


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
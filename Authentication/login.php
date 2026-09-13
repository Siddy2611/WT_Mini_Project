<?php

session_start();

include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM Users WHERE email = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["name"] = $user["name"];

            header("Location: dashboard.php");
            exit();

        } else {

            $error = "Invalid email or password.";

        }

    } else {

        $error = "Invalid email or password.";

    }

    $stmt->close();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Personal Expense Tracker</title>

    <link rel="stylesheet" href="login.css">

</head>


<body>

    <div class="login-container">

        <h1>
            Welcome Back
        </h1>

        <p>
            Login to your Expense Tracker
        </p>



        <form
            id="loginForm"
            method="POST"
        >

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                >

                <div
                    class="error"
                    id="emailError">
                </div>

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                >

                <div
                    class="error"
                    id="passwordError">
                </div>

                        <?php

        if ($error != "") {

            echo "<div class='error'>$error</div>";

        }

        ?>
            </div>


            <button
                type="submit"
                class="login-btn"
            >
                Login
            </button>

        </form>


        <div class="register-link">

            Don't have an account?

            <a href="register.php">
                Register
            </a>

        </div>

    </div>


    <script>

        document.getElementById("loginForm")
            .addEventListener("submit", function(event) {

                let email =
                    document.getElementById("email").value.trim();

                let password =
                    document.getElementById("password").value.trim();

                let emailError =
                    document.getElementById("emailError");

                let passwordError =
                    document.getElementById("passwordError");


                let valid = true;


                // Clear previous errors

                emailError.innerHTML = "";

                passwordError.innerHTML = "";


                // Email validation

                if (email === "") {

                    emailError.innerHTML =
                        "Email is required";

                    valid = false;

                }


                // Password validation

                if (password === "") {

                    passwordError.innerHTML =
                        "Password is required";

                    valid = false;

                }


                // Stop form if validation fails

                if (!valid) {

                    event.preventDefault();

                }

            });

    </script>


</body>

</html>


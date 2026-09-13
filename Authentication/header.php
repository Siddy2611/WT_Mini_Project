<?php

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

?>

<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">
        Expense<span>Tracker</span>
    </div>


    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="expenses.php">
            Expenses
        </a>

        <a href="categories.php">
            Categories
        </a>

        <a href="budget.php">
            Budget
        </a>

        <a href="reports.php">
            Reports
        </a>


        <button
            class="logout-btn"
            onclick="logout()">

            Logout

        </button>

    </div>

</nav>


<script>

function logout() {

    let confirmLogout = confirm(
        "Are you sure you want to logout?"
    );

    if (confirmLogout) {

        window.location.href = "login.php";

    }

}

</script>

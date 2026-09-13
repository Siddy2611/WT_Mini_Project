
<?php

session_start();

include "db.php";


// Check if user is logged in

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}


// Get logged-in user's information

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"];


// =============================
// GET TOTAL INCOME
// =============================

$sql = "SELECT SUM(amount) AS total_income
        FROM Income
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$total_income = $row["total_income"];


// If no income

if ($total_income == null) {

    $total_income = 0;

}

$stmt->close();


// =============================
// GET TOTAL EXPENSES
// =============================

$sql = "SELECT SUM(amount) AS total_expenses
        FROM Expenses
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$total_expenses = $row["total_expenses"];


// If no expenses

if ($total_expenses == null) {

    $total_expenses = 0;

}

$stmt->close();


// =============================
// CALCULATE SAVINGS
// =============================

$savings = $total_income - $total_expenses;


// =============================
// GET RECENT EXPENSES
// =============================

$sql = "SELECT Expenses.expense_date,
               Expenses.description,
               Expenses.amount,
               Categories.category_name

        FROM Expenses

        LEFT JOIN Categories
        ON Expenses.category_id = Categories.category_id

        WHERE Expenses.user_id = ?

        ORDER BY Expenses.expense_date DESC

        LIMIT 5";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$recent_expenses = $stmt->get_result();

?>





<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Personal Expense Tracker</title>

    <link rel="stylesheet" href="dashboard.css">

</head>


<body>


    <!-- NAVBAR -->

    <nav class="navbar">

        <div class="logo">
            Expense<span>Tracker</span>
        </div>


        <div class="nav-links">

            <a href="expenses.php">
                Expenses
            </a>

            <a href="categories.php">
                Categories
            </a>

            <a href="budget.php">
                Budget
            </a>


            <button
                class="logout-btn"
                onclick="logout()">

                Logout

            </button>

        </div>

    </nav>



    <!-- MAIN CONTENT -->

    <main class="container">


        <!-- WELCOME -->

        <section class="welcome">

            <h1>
                Welcome, <?php echo htmlspecialchars($name); ?>!
            </h1>

            <p>
                Here's an overview of your finances for this month.
            </p>

        </section>



        <!-- SUMMARY -->

        <section class="summary">


            <!-- TOTAL INCOME -->

            <div class="card">

                <h3>
                    Total Income
                </h3>

              <h2>
    ₹<?php echo number_format($total_income, 2); ?>
</h2>

            </div>



            <!-- TOTAL EXPENSES -->

            <div class="card">

                <h3>
                    Total Expenses
                </h3>

                <h2>

                    ₹<?php
                    echo number_format($total_expenses, 2);
                    ?>

                </h2>

            </div>



            <!-- SAVINGS -->

            <div class="card">

                <h3>
                    Savings
                </h3>

                <h2>
    ₹<?php echo number_format($savings, 2); ?>
</h2>

            </div>


        </section>



        <!-- QUICK ACTIONS -->

        <section>

            <h2 class="section-title">
                Quick Actions
            </h2>


            <div class="actions">


                <button
                    class="action-btn"
                    onclick="addExpense()">

                    + Add Expense

                </button>



                <button
                    class="action-btn"
                    onclick="addIncome()">

                    + Add Income

                </button>



                <button
                    class="action-btn"
                    onclick="setBudget()">

                    + Set Budget

                </button>


            </div>

        </section>



        <!-- RECENT EXPENSES + BUDGET -->

        <section class="content-grid">


            <!-- RECENT EXPENSES -->

            <div class="table-container">

                <h2 class="section-title">
                    Recent Expenses
                </h2>


                <table>

                    <thead>

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Description
                            </th>

                            <th>
                                Amount
                            </th>

                        </tr>

                    </thead>


                    <tbody>

<?php

if ($recent_expenses->num_rows > 0) {

    while ($expense = $recent_expenses->fetch_assoc()) {

        echo "<tr>";


        // Date

        echo "<td>";

        echo $expense["expense_date"];

        echo "</td>";


        // Category

        echo "<td>";

        if ($expense["category_name"] != null) {

            echo htmlspecialchars(
                $expense["category_name"]
            );

        } else {

            echo "Not set";
        }

        echo "</td>";


        // Description

        echo "<td>";

        echo htmlspecialchars(
            $expense["description"]
        );

        echo "</td>";


        // Amount

        echo "<td>";

        echo "₹" .
             number_format(
                 $expense["amount"],
                 2
             );

        echo "</td>";


        echo "</tr>";
    }

} else {

    echo "<tr>";

    echo "<td colspan='4'
                 style='text-align: center;'>";

    echo "No expenses yet.";

    echo "</td>";

    echo "</tr>";
}

?>


                    </tbody>

                </table>

            </div>



            <!-- BUDGET -->

            <div class="budget-container">

                <h2 class="section-title">
                    Monthly Budget
                </h2>


                <p style="color: #888;">
                    No budgets set.
                </p>

            </div>


        </section>


    </main>



    <!-- FOOTER -->

    <footer>

        <p>
            Personal Expense Tracker © 2026
        </p>

    </footer>



    <!-- JAVASCRIPT -->

    <script>


        function addExpense() {

            window.location.href = "expenses.php";

        }


        function addIncome() {

            window.location.href = "income.php";

        }


        function setBudget() {

        alert("Coming Soon");
            // window.location.href = "budget.php";

        }


        function logout() {

            let confirmLogout = confirm(
                "Are you sure you want to logout?"
            );


            if (confirmLogout) {

                window.location.href = "login.php";

            }

        }


    </script>


</body>

</html>


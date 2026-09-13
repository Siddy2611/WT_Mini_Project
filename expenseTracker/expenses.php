<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* ADD EXPENSE */
if (isset($_POST["add_expense"])) {

    $amount = $_POST["amount"];
    $description = $_POST["description"];
    $expense_date = $_POST["expense_date"];
    $category_id = $_POST["category_id"];

    $sql = "INSERT INTO Expenses
            (user_id, category_id, amount, description, expense_date)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iidss",
        $user_id,
        $category_id,
        $amount,
        $description,
        $expense_date
    );

    if ($stmt->execute()) {
        echo "<script>alert('Expense added successfully!');</script>";
    } else {
        echo "<script>alert('Failed to add expense!');</script>";
    }

    $stmt->close();
}

/* DELETE EXPENSE */
if (isset($_POST["delete_expense"])) {

    $expense_id = $_POST["expense_id"];

    $sql = "DELETE FROM Expenses
            WHERE expense_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $expense_id, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Expense deleted successfully!');</script>";
    }

    $stmt->close();
}

/* UPDATE EXPENSE */
if (isset($_POST["update_expense"])) {

    $expense_id = $_POST["expense_id"];
    $amount = $_POST["amount"];
    $description = $_POST["description"];
    $expense_date = $_POST["expense_date"];
    $category_id = $_POST["category_id"];

    $sql = "UPDATE Expenses
            SET category_id = ?, amount = ?, description = ?, expense_date = ?
            WHERE expense_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "idssii",
        $category_id,
        $amount,
        $description,
        $expense_date,
        $expense_id,
        $user_id
    );

    if ($stmt->execute()) {
        echo "<script>alert('Expense updated successfully!');</script>";
    }

    $stmt->close();
}

/* GET EXPENSE TO EDIT */
$edit_expense = null;

if (isset($_POST["edit_expense"])) {

    $expense_id = $_POST["expense_id"];

    $sql = "SELECT expense_id, category_id, amount, description, expense_date
            FROM Expenses
            WHERE expense_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $expense_id, $user_id);
    $stmt->execute();

    $edit_result = $stmt->get_result();

    if ($edit_result->num_rows > 0) {
        $edit_expense = $edit_result->fetch_assoc();
    }

    $stmt->close();
}

/* GET CATEGORIES */
$sql = "SELECT category_id, category_name
        FROM Categories
        WHERE user_id = ? AND category_type = 'Expense'
        ORDER BY category_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$categories = $stmt->get_result();

/* GET EXPENSES */
$sql = "SELECT Expenses.expense_id,
               Expenses.category_id,
               Expenses.amount,
               Expenses.description,
               Expenses.expense_date,
               Categories.category_name
        FROM Expenses
        LEFT JOIN Categories
        ON Expenses.category_id = Categories.category_id
        WHERE Expenses.user_id = ?
        ORDER BY Expenses.expense_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

    <title>Expenses</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #0b0f0e;
            color: #f1f5f3;
            padding: 35px 7%;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        body > p {
            margin-bottom: 30px;
        }

        a {
            color: #8d9791;
            text-decoration: none;
        }

        a:hover {
            color: #65d69a;
        }

        /* MAIN LAYOUT */

        .content {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        /* FORM */

        .form-box {
            background: #151b18;
            border: 1px solid #26302b;
            border-radius: 10px;
            padding: 25px;
            width: 38%;
        }

        .form-box h2 {
            margin-bottom: 22px;
            font-size: 20px;
        }

        label {
            display: block;
            color: #aab3ad;
            font-size: 14px;
            margin-bottom: 7px;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            margin-bottom: 17px;
            background: #0f1412;
            color: #f1f5f3;
            border: 1px solid #303a35;
            border-radius: 6px;
            outline: none;
        }

        input:focus,
        select:focus {
            border-color: #65d69a;
        }

        button {
            padding: 9px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        button[name="add_expense"],
        button[name="update_expense"] {
            background: #65d69a;
            color: #0b0f0e;
        }

        button[name="add_expense"]:hover,
        button[name="update_expense"]:hover {
            background: #7ee2a8;
        }

        /* HISTORY */

        .history {
            width: 60%;
        }

        .history h2 {
            font-size: 21px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #151b18;
            border: 1px solid #26302b;
            border-radius: 10px;
            overflow: hidden;
        }

        th {
            background: #101513;
            color: #858e89;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        th,
        td {
            padding: 15px 17px;
            text-align: left;
            border-bottom: 1px solid #222a26;
        }

        td {
            color: #cbd2ce;
            font-size: 14px;
        }

        tr:hover {
            background: #19201d;
        }

        /* TABLE BUTTONS */

        .edit-button {
            background: #1b2921;
            color: #65d69a;
            margin-right: 5px;
        }

        .edit-button:hover {
            background: #26382f;
        }

        button[name="delete_expense"] {
            background: #2a1a1a;
            color: #ff7373;
        }

        button[name="delete_expense"]:hover {
            background: #3a2020;
        }

        /* MOBILE */

        @media (max-width: 800px) {

            body {
                padding: 25px 5%;
            }

            .content {
                flex-direction: column;
            }

            .form-box,
            .history {
                width: 100%;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

    </style>

</head>

<body>

    <h1>My Expenses</h1>

    <p>
        <a href="dashboard.php">← Back to Dashboard</a>
    </p>

    <!-- LEFT + RIGHT CONTENT -->

    <div class="content">

        <!-- ADD / EDIT FORM -->

        <div class="form-box">

            <?php if ($edit_expense == null) { ?>

                <h2>Add Expense</h2>

                <form method="POST">

                    <label>Amount</label>
                    <input type="number"
                           name="amount"
                           step="0.01"
                           required>

                    <label>Category</label>

                    <select name="category_id" required>

                        <option value="">Select Category</option>

                        <?php while ($category = $categories->fetch_assoc()) { ?>

                            <option value="<?php echo $category["category_id"]; ?>">
                                <?php echo htmlspecialchars($category["category_name"]); ?>
                            </option>

                        <?php } ?>

                    </select>

                    <label>Description</label>
                    <input type="text"
                           name="description"
                           placeholder="e.g. Lunch"
                           required>

                    <label>Date</label>
                    <input type="date"
                           name="expense_date"
                           required>

                    <button type="submit" name="add_expense">
                        Add Expense
                    </button>

                </form>

            <?php } else { ?>

                <h2>Edit Expense</h2>

                <form method="POST">

                    <input type="hidden"
                           name="expense_id"
                           value="<?php echo $edit_expense["expense_id"]; ?>">

                    <label>Amount</label>

                    <input type="number"
                           name="amount"
                           step="0.01"
                           value="<?php echo $edit_expense["amount"]; ?>"
                           required>

                    <label>Category</label>

                    <select name="category_id" required>

                        <?php

                        $category_sql = "SELECT category_id, category_name
                                         FROM Categories
                                         WHERE user_id = ?
                                         AND category_type = 'Expense'
                                         ORDER BY category_name";

                        $category_stmt = $conn->prepare($category_sql);
                        $category_stmt->bind_param("i", $user_id);
                        $category_stmt->execute();

                        $category_result = $category_stmt->get_result();

                        while ($category = $category_result->fetch_assoc()) {

                            $selected = "";

                            if ($category["category_id"] == $edit_expense["category_id"]) {
                                $selected = "selected";
                            }

                            echo "<option value='" . $category["category_id"] . "' $selected>";
                            echo htmlspecialchars($category["category_name"]);
                            echo "</option>";
                        }

                        $category_stmt->close();

                        ?>

                    </select>

                    <label>Description</label>

                    <input type="text"
                           name="description"
                           value="<?php echo htmlspecialchars($edit_expense["description"]); ?>"
                           required>

                    <label>Date</label>

                    <input type="date"
                           name="expense_date"
                           value="<?php echo $edit_expense["expense_date"]; ?>"
                           required>

                    <button type="submit" name="update_expense">
                        Update Expense
                    </button>

                </form>

            <?php } ?>

        </div>

        <!-- HISTORY -->

        <div class="history">

            <h2>History</h2>

            <table>

                <tr>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>

                <?php

                if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {

                        echo "<tr>";

                        echo "<td>₹" .
                             number_format($row["amount"], 2) .
                             "</td>";

                        echo "<td>";

                        if ($row["category_name"] != null) {
                            echo htmlspecialchars($row["category_name"]);
                        } else {
                            echo "Not set";
                        }

                        echo "</td>";

                        echo "<td>" .
                             htmlspecialchars($row["description"]) .
                             "</td>";

                        echo "<td>" .
                             $row["expense_date"] .
                             "</td>";

                        echo "<td>";

                        /* EDIT */

                        echo "<form method='POST' style='display:inline;'>";

                        echo "<input type='hidden'
                                     name='expense_id'
                                     value='" . $row["expense_id"] . "'>";

                        echo "<button type='submit'
                                      name='edit_expense'
                                      class='edit-button'>
                                      Edit
                              </button>";

                        echo "</form>";

                        /* DELETE */

                        echo "<form method='POST' style='display:inline;'>";

                        echo "<input type='hidden'
                                     name='expense_id'
                                     value='" . $row["expense_id"] . "'>";

                        echo "<button type='submit'
                                      name='delete_expense'
                                      onclick=\"return confirm('Are you sure you want to delete this expense?');\">
                                      Delete
                              </button>";

                        echo "</form>";

                        echo "</td>";

                        echo "</tr>";
                    }

                } else {

                    echo "<tr>
                            <td colspan='5' style='text-align:center;'>
                                No expenses yet.
                            </td>
                          </tr>";
                }

                ?>

            </table>

        </div>

    </div>

</body>
</html>
<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* ADD INCOME */
if (isset($_POST["add_income"])) {
    $amount = $_POST["amount"];
    $description = $_POST["description"];
    $income_date = $_POST["income_date"];
    $category_id = $_POST["category_id"];

    $sql = "INSERT INTO Income
            (user_id, category_id, amount, description, income_date)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidss", $user_id, $category_id, $amount, $description, $income_date);

    if ($stmt->execute()) {
        echo "<script>alert('Income added successfully!');</script>";
    } else {
        echo "<script>alert('Failed to add income!');</script>";
    }
    $stmt->close();
}

/* DELETE INCOME */
if (isset($_POST["delete_income"])) {
    $income_id = $_POST["income_id"];

    $sql = "DELETE FROM Income
            WHERE income_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $income_id, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Income deleted successfully!');</script>";
    }
    $stmt->close();
}

/* UPDATE INCOME */
if (isset($_POST["update_income"])) {
    $income_id = $_POST["income_id"];
    $amount = $_POST["amount"];
    $description = $_POST["description"];
    $income_date = $_POST["income_date"];
    $category_id = $_POST["category_id"];

    $sql = "UPDATE Income
            SET category_id = ?, amount = ?, description = ?, income_date = ?
            WHERE income_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idssii", $category_id, $amount, $description, $income_date, $income_id, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Income updated successfully!');</script>";
    }
    $stmt->close();
}

/* GET INCOME TO EDIT */
$edit_income = null;

if (isset($_POST["edit_income"])) {
    $income_id = $_POST["income_id"];

    $sql = "SELECT income_id, category_id, amount, description, income_date
            FROM Income
            WHERE income_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $income_id, $user_id);
    $stmt->execute();

    $edit_result = $stmt->get_result();

    if ($edit_result->num_rows > 0) {
        $edit_income = $edit_result->fetch_assoc();
    }
    $stmt->close();
}

/* GET INCOME CATEGORIES */
$sql = "SELECT category_id, category_name
        FROM Categories
        WHERE user_id = ? AND category_type = 'Income'
        ORDER BY category_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$categories = $stmt->get_result();

/* GET USER INCOME */
$sql = "SELECT Income.income_id,
               Income.category_id,
               Income.amount,
               Income.description,
               Income.income_date,
               Categories.category_name
        FROM Income
        LEFT JOIN Categories
        ON Income.category_id = Categories.category_id
        WHERE Income.user_id = ?
        ORDER BY Income.income_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Income</title>

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

button[name="add_income"],
button[name="update_income"] {
    background: #65d69a;
    color: #0b0f0e;
}

button[name="add_income"]:hover,
button[name="update_income"]:hover {
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
    letter-spacing: .5px;
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

button[name="delete_income"] {
    background: #2a1a1a;
    color: #ff7373;
}

button[name="delete_income"]:hover {
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

<h1>My Income</h1>

<p>
    <a href="dashboard.php">← Back to Dashboard</a>
</p>

<div class="content">

<!-- ADD / EDIT FORM -->
<div class="form-box">

<?php if ($edit_income == null) { ?>

<h2>Add Income</h2>

<form method="POST">

<label>Amount</label>
<input type="number" name="amount" step="0.01" required>

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
<input type="text" name="description" placeholder="e.g. Salary" required>

<label>Date</label>
<input type="date" name="income_date" required>

<button type="submit" name="add_income">Add Income</button>

</form>

<?php } else { ?>

<h2>Edit Income</h2>

<form method="POST">

<input type="hidden"
       name="income_id"
       value="<?php echo $edit_income["income_id"]; ?>">

<label>Amount</label>
<input type="number"
       name="amount"
       step="0.01"
       value="<?php echo $edit_income["amount"]; ?>"
       required>

<label>Category</label>

<select name="category_id" required>

<?php
$category_sql = "SELECT category_id, category_name
                 FROM Categories
                 WHERE user_id = ?
                 AND category_type = 'Income'
                 ORDER BY category_name";

$category_stmt = $conn->prepare($category_sql);
$category_stmt->bind_param("i", $user_id);
$category_stmt->execute();
$category_result = $category_stmt->get_result();

while ($category = $category_result->fetch_assoc()) {

    $selected = "";

    if ($category["category_id"] == $edit_income["category_id"]) {
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
       value="<?php echo htmlspecialchars($edit_income["description"]); ?>"
       required>

<label>Date</label>
<input type="date"
       name="income_date"
       value="<?php echo $edit_income["income_date"]; ?>"
       required>

<button type="submit" name="update_income">Update Income</button>

</form>

<?php } ?>

</div>

<!-- INCOME HISTORY -->
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
             $row["income_date"] .
             "</td>";

        echo "<td>";

        /* EDIT */
        echo "<form method='POST' style='display:inline;'>";

        echo "<input type='hidden'
                     name='income_id'
                     value='" . $row["income_id"] . "'>";

        echo "<button type='submit'
                      name='edit_income'
                      class='edit-button'>
                      Edit
              </button>";

        echo "</form>";

        /* DELETE */
        echo "<form method='POST' style='display:inline;'>";

        echo "<input type='hidden'
                     name='income_id'
                     value='" . $row["income_id"] . "'>";

        echo "<button type='submit'
                      name='delete_income'
                      onclick=\"return confirm('Are you sure you want to delete this income?');\">
                      Delete
              </button>";

        echo "</form>";

        echo "</td>";
        echo "</tr>";
    }

} else {

    echo "<tr>
            <td colspan='5' style='text-align:center;'>
                No income yet.
            </td>
          </tr>";
}

?>

</table>

</div>
</div>

</body>
</html>
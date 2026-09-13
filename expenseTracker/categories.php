<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* ADD CATEGORY */
if (isset($_POST["add_category"])) {
    $category_name = $_POST["category_name"];
    $category_type = $_POST["category_type"];

    $sql = "INSERT INTO Categories
            (user_id, category_name, category_type)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $category_name, $category_type);

    if ($stmt->execute()) {
        echo "<script>alert('Category added successfully!');</script>";
    } else {
        echo "<script>alert('Failed to add category!');</script>";
    }

    $stmt->close();
}

/* DELETE CATEGORY */
if (isset($_POST["delete_category"])) {
    $category_id = $_POST["category_id"];

    $sql = "DELETE FROM Categories
            WHERE category_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $category_id, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Category deleted successfully!');</script>";
    }

    $stmt->close();
}

/* GET USER CATEGORIES */
$sql = "SELECT category_id, category_name, category_type
        FROM Categories
        WHERE user_id = ?
        ORDER BY category_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Categories</title>

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

        .form-box h2,
        .history h2 {
            font-size: 21px;
            margin-bottom: 20px;
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

        button[name="add_category"] {
            background: #65d69a;
            color: #0b0f0e;
        }

        button[name="add_category"]:hover {
            background: #7ee2a8;
        }

        /* CATEGORY HISTORY */
        .history {
            width: 60%;
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

        /* DELETE BUTTON */
        button[name="delete_category"] {
            background: #2a1a1a;
            color: #ff7373;
        }

        button[name="delete_category"]:hover {
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

<h1>My Categories</h1>

<p>
    <a href="dashboard.php">← Back to Dashboard</a>
</p>

<div class="content">

    <!-- ADD CATEGORY -->
    <div class="form-box">
        <h2>Add Category</h2>

        <form method="POST">

            <label>Category Name</label>
            <input type="text"
                   name="category_name"
                   placeholder="e.g. Food"
                   required>

            <label>Category Type</label>
            <select name="category_type" required>
                <option value="">Select Type</option>
                <option value="Expense">Expense</option>
                <option value="Income">Income</option>
            </select>

            <button type="submit" name="add_category">
                Add Category
            </button>

        </form>
    </div>

    <!-- CATEGORY LIST -->
    <div class="history">

        <h2>My Categories</h2>

        <table>

            <tr>
                <th>Category</th>
                <th>Type</th>
                <th>Action</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    echo "<tr>";

                    echo "<td>" .
                         htmlspecialchars($row["category_name"]) .
                         "</td>";

                    echo "<td>" .
                         $row["category_type"] .
                         "</td>";

                    echo "<td>";

                    echo "<form method='POST' style='display:inline;'>";

                    echo "<input type='hidden'
                                 name='category_id'
                                 value='" . $row["category_id"] . "'>";

                    echo "<button type='submit'
                                  name='delete_category'
                                  onclick=\"return confirm('Are you sure you want to delete this category?');\">
                                  Delete
                          </button>";

                    echo "</form>";

                    echo "</td>";
                    echo "</tr>";
                }

            } else {

                echo "<tr>
                        <td colspan='3' style='text-align:center;'>
                            No categories yet.
                        </td>
                      </tr>";
            }
            ?>

        </table>

    </div>

</div>

</body>
</html>
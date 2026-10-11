
<?php
include "dbcon.php";

$msg = "";
$alert = "";
$edit_id = "";
$name = "";
$email = "";

// Display messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == "added") {
        $msg = "User added successfully!";
        $alert = "success";
    } elseif ($_GET['msg'] == "updated") {
        $msg = "User updated successfully!";
        $alert = "success";
    } elseif ($_GET['msg'] == "deleted") {
        $msg = "User deleted successfully!";
        $alert = "danger";
    }
}

// Load selected user into the same form
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE id = ?"
    );
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();

    $result_edit = $stmt->get_result();
    $user = $result_edit->fetch_assoc();
    $stmt->close();

    if ($user) {
        $name = $user['name'];
        $email = $user['email'];
    } else {
        $edit_id = "";
    }
}

// Add or update user using the same Submit button
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $edit_id = $_POST['edit_id'] ?? "";

    if ($name != "" && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        if ($edit_id != "" && ctype_digit($edit_id)) {
            $id = (int) $edit_id;

            $stmt = $conn->prepare(
                "UPDATE users SET name = ?, email = ? WHERE id = ?"
            );
            $stmt->bind_param("ssi", $name, $email, $id);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php?msg=updated");
                exit;
            }

            $stmt->close();
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email) VALUES (?, ?)"
            );
            $stmt->bind_param("ss", $name, $email);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php?msg=added");
                exit;
            }

            $stmt->close();
        }

        $msg = "Operation failed. Please try again.";
        $alert = "danger";
    } else {
        $msg = "Please enter a valid name and email address.";
        $alert = "warning";
    }
}

// Get registered users
$result = $conn->query(
    "SELECT * FROM users ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>User Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f4f7fb;
        }

        .main-container {
            margin-top: 35px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-control {
            margin-bottom: 15px;
        }

        .btn-submit {
            width: 100%;
        }
    </style>
</head>

<body>

<div class="container main-container">

    <h2>User Management System</h2>

    <div class="row">

        <!-- ADD AND EDIT FORM -->
        <div class="col-md-5">
            <h6>Add New User</h6>

            <?php if ($msg != ""): ?>
                <div class="alert alert-<?php echo $alert; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($msg); ?>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php">

                <input type="hidden" name="edit_id"
                       value="<?php echo htmlspecialchars((string)$edit_id); ?>">

                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control"
                       placeholder="Enter Full Name"
                       value="<?php echo htmlspecialchars($name); ?>"
                       required>

                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control"
                       placeholder="Enter Email Address"
                       value="<?php echo htmlspecialchars($email); ?>"
                       required>

                <button type="submit"
                        class="btn btn-primary btn-submit">
                    Submit
                </button>

            </form>
        </div>

        <!-- REGISTERED USERS TABLE -->
        <div class="col-md-7">
            <h6>Registered Users</h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
<?php
$display_id = 1;

if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
    <tr>
        <td><?php echo $display_id++; ?></td>

        <td><?php echo htmlspecialchars($row['name']); ?></td>

        <td><?php echo htmlspecialchars($row['email']); ?></td>

        <td>
            <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
        </td>

        <td class="text-nowrap">
            <a href="index.php?edit=<?php echo $row['id']; ?>"
               class="btn btn-success btn-sm">Edit</a>

            <a href="delete.php?id=<?php echo $row['id']; ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Are you sure you want to delete this user?')">
                Delete
            </a>
        </td>
    </tr>
<?php
    endwhile;
else:
?>
    <tr>
        <td colspan="5" class="text-center">
            No registered users found.
        </td>
    </tr>
<?php endif; ?>
</tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

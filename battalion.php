<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['Aid'])) {
    header("Location: /Final/login.php");
    exit();
}

// Database connection
function connectDB() {
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "monkey";
    $dbname = "militarydb";
    $conn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

// Get all battalions with regiment name
function getAllBattalions($conn) {
    $sql = "SELECT b.battalion_id, b.battalion_name, b.active_personnel, b.regiment_id, r.regiment_name
            FROM battalion b
            LEFT JOIN regiment r ON b.regiment_id = r.regiment_id
            ORDER BY b.battalion_id ASC";
    $result = $conn->query($sql);
    $battalions = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $battalions[] = $row;
        }
    }
    return $battalions;
}

$conn = connectDB();
$battalions = getAllBattalions($conn);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Battalion List</title>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
    <style>
        body {
            background: #329c32ff;
        }
        .container {
            margin-top: 50px;
        }
        .table th, .table td {
            vertical-align: middle !important;
        }
        .table thead th {
            background-color: #088724;
            color: #fff;
        }
        .card {
            margin-bottom: 30px;
            border: none;
        }
        .card-header {
            background-color: #088724 !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
    <header>
        <nav class="bg-dark" style="height: 10px; width: 100%;"></nav>
        <nav class="bg-dark" data-bs-theme="dark" style="height: 60px; width: 100%;">
            <ul class="nav justify-content-center mb-2 nav-tabs"><br>
                <li class="nav-item">
                    <a class="nav-link" href="adminPanel.html">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="battalion.php"><b>View Battalions</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="AdminBattInsert.php">Insert</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="AdminBattUpdate.php">Update</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="AdminBattDelete.php">Delete</a>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">All Battalion Details</h3>
                </div>
                <div class="card-body">
                    <?php if (count($battalions) > 0): ?>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Battalion Name</th>
                                    <th>Regiment Name</th>
                                    <th>Active Personnel</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($battalions as $index => $battalion): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($index + 1); ?></td>
                                        <td><?php echo htmlspecialchars($battalion['battalion_name']); ?></td>
                                        <td><?php echo htmlspecialchars($battalion['regiment_name']); ?></td>
                                        <td><?php echo htmlspecialchars($battalion['active_personnel']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning">No battalion records found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
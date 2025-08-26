<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// optional: require login
// if (!isset($_SESSION['Aid'])) { header("Location: /Final/login.php"); exit(); }

$servername = "localhost";
$dbusername = "root";
$dbpassword = "monkey";
$dbname = "militarydb";

$mother_name = $mother_nic = "";
$father_name = $father_nic = "";
$spouse_name = $spouse_nic = "";

$hasMother = $hasFather = $hasSpouse = false;
$searchAid = "";

$conn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// helper: load dependents for given Aid
function loadDependents($conn, $Aid, &$hasMother, &$mother_name, &$mother_nic, &$hasFather, &$father_name, &$father_nic, &$hasSpouse, &$spouse_name, &$spouse_nic) {
    $stmt = $conn->prepare("SELECT dependent_type, name, NIC FROM dependent WHERE Aid = ?");
    if (!$stmt) return;
    $stmt->bind_param("s", $Aid);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $type = strtolower(trim($row['dependent_type']));
        if ($type === 'mother') { $hasMother = true; $mother_name = $row['name']; $mother_nic = $row['NIC']; }
        if ($type === 'father') { $hasFather = true; $father_name = $row['name']; $father_nic = $row['NIC']; }
        if ($type === 'spouse') { $hasSpouse = true; $spouse_name = $row['name']; $spouse_nic = $row['NIC']; }
    }
    $stmt->close();
}

// POST search (button)
if (isset($_POST['btnSearch'])) {
    $searchAid = trim($_POST['txtSearch']);
    if ($searchAid !== "") {
        loadDependents($conn, $searchAid, $hasMother, $mother_name, $mother_nic, $hasFather, $father_name, $father_nic, $hasSpouse, $spouse_name, $spouse_nic);
    }
}

// auto-load when redirected back from the update process (e.g. ?aid=18)
if (isset($_GET['aid']) && trim($_GET['aid']) !== '') {
    $searchAid = trim($_GET['aid']);
    loadDependents($conn, $searchAid, $hasMother, $mother_name, $mother_nic, $hasFather, $father_name, $father_nic, $hasSpouse, $spouse_name, $spouse_nic);
}

mysqli_close($conn);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Update Dependent</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background: #30b730; }
        .regform { background: #fff; padding: 30px; border-radius: 6px; margin-top: 40px; }
        .container { max-width: 900px; }
        label { font-weight: 600; }
    </style>
</head>
<body>
<!-- added navbar -->
       <nav class="bg-dark "  style="height: 10px; width: 100%;">
        </nav>
         <nav class=" bg-dark" data-bs-theme="dark" style="height: 60px; width: 100%;">
            <ul class="nav justify-content-center mb-2 nav-tabs"><br>
                <li class="nav-item">
                    <a class="nav-link" href="adminPanel.html">Home</a>
                </li> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;    
                <li class="nav-item">
                    <a class="nav-link " aria-current="page" href="AdminDepInsert.php"><b>Insert</b></a>
                </li> &nbsp; &nbsp; &nbsp; &nbsp;
                <li class="nav-item">
                    <a class="nav-link active" href="AdminDepUpdate.php">Update</a>
                </li> &nbsp; &nbsp; &nbsp; &nbsp;
                <li class="nav-item">
                    <a class="nav-link" href="AdminDepDelete.php">Delete</a>
                </li> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                
            </ul>
        </nav>
<main>
    <div class="container">
        <div class="regform">
            <h2>Update Dependent Details</h2>

            <!-- Search form -->
            <form method="post" class="row g-2 align-items-center mb-4">
                <div class="col-auto">
                    <label class="visually-hidden" for="txtSearch">Army ID</label>
                    <input type="text" class="form-control" id="txtSearch" name="txtSearch" placeholder="Enter Army ID" value="<?php echo htmlspecialchars($searchAid); ?>" required>
                </div>
                <div class="col-auto">
                    <button type="submit" name="btnSearch" class="btn btn-success">Search</button>
                </div>
            </form>

            <!-- Dependent form (auto-filled after search) -->
            <form method="post" action="AdminDepUpdateProcess.php"> <!-- change action to your update handler -->
                <input type="hidden" name="Aid" value="<?php echo htmlspecialchars($searchAid); ?>">

                <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" id="chkMother" name="hasMother" <?php echo $hasMother ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="chkMother">Mother</label>
                </div>
                <div class="mb-3">
                    <input type="text" name="mother_name" class="form-control" placeholder="Enter Full Name" value="<?php echo htmlspecialchars($mother_name); ?>">
                    <small class="text-muted">Dependent Full Name</small>
                </div>
                <div class="mb-3">
                    <input type="text" name="mother_nic" class="form-control" placeholder="Enter NIC" value="<?php echo htmlspecialchars($mother_nic); ?>">
                    <small class="text-muted">Dependent NIC</small>
                </div>

                <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" id="chkFather" name="hasFather" <?php echo $hasFather ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="chkFather">Father</label>
                </div>
                <div class="mb-3">
                    <input type="text" name="father_name" class="form-control" placeholder="Enter Full Name" value="<?php echo htmlspecialchars($father_name); ?>">
                    <small class="text-muted">Dependent Full Name</small>
                </div>
                <div class="mb-3">
                    <input type="text" name="father_nic" class="form-control" placeholder="Enter NIC" value="<?php echo htmlspecialchars($father_nic); ?>">
                    <small class="text-muted">Dependent NIC</small>
                </div>

                <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" id="chkSpouse" name="hasSpouse" <?php echo $hasSpouse ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="chkSpouse">Spouse</label>
                </div>
                <div class="mb-3">
                    <input type="text" name="spouse_name" class="form-control" placeholder="Enter Full Name" value="<?php echo htmlspecialchars($spouse_name); ?>">
                    <small class="text-muted">Dependent Full Name</small>
                </div>
                <div class="mb-3">
                    <input type="text" name="spouse_nic" class="form-control" placeholder="Enter NIC" value="<?php echo htmlspecialchars($spouse_nic); ?>">
                    <small class="text-muted">Dependent NIC</small>
                </div>

                <div class="mt-3">
                    <button type="submit" name="btnUpdate" class="btn btn-primary">Update Dependents</button>
                </div>
            </form>

        </div>
    </div>
</main>

<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>

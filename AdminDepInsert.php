<!doctype html>
<html lang="en">
<head>
    <title>Insert Dependent</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <style>
.regform{ background: linear-gradient(#9cd769,#30b730, #088724); }
#checkdep { margin-left: 10px; margin-right: 10px; margin-bottom:5px; }
#lbldep { margin-right: 10px; margin-bottom:5px; }
.search-container { display: flex; align-items: center; margin-bottom:50px; }
.search-container input[type="text"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-left: -1px; }
.search-container button { padding: 8px 12px; background-color: #4CAF50; color: white; border: 1px solid #4CAF50; border-radius: 4px 0 0 4px; cursor: pointer; }
    </style>
</head>

<body>
<header>
    <nav class="bg-dark" style="height: 10px; width: 100%;"></nav>
    <nav class="bg-dark" data-bs-theme="dark" style="height: 60px; width: 100%;">
        <ul class="nav justify-content-center mb-2 nav-tabs"><br>
            <li class="nav-item"><a class="nav-link" href="adminPanel.html">Home</a></li>
            <li class="nav-item"><a class="nav-link active" aria-current="page" href="AdminDepInsert.php"><b>Insert</b></a></li>
            <li class="nav-item"><a class="nav-link" href="AdminDepUpdate.php">Update</a></li>
            <li class="nav-item"><a class="nav-link" href="AdminDepDelete.php">Delete</a></li>
        </ul>
    </nav>
</header>

<main>
<section class="regform">
    <div class="container py-5">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-lg-8 col-xl-6">
                <div class="card rounded-3">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="pb-md-0 mb-md-5 px-md-2">Insert Dependent Details</h3>

<?php
// DB
$con = mysqli_connect("localhost","root","monkey","militarydb");
if (!$con) { die("<div class='alert alert-danger'>Connection failed: " . mysqli_connect_error() . "</div>"); }

// init
$row = null;
$Aid = "";
$mother_name = $mother_nic = "";
$father_name = $father_nic = "";
$spouse_name = $spouse_nic = "";
$hasMother = $hasFather = $hasSpouse = false;
$msg = "";

// SEARCH - populate soldetails + existing dependents
if (isset($_POST['btnsearch'])) {
    $Aid = trim($_POST['txtAid']);
    if ($Aid === "") {
        $msg = "<div class='alert alert-warning'>Enter Army ID to search.</div>";
    } else {
        $stmt = $con->prepare("SELECT * FROM soldetails WHERE Aid = ?");
        $stmt->bind_param("s", $Aid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            // fetch dependents
            $dstmt = $con->prepare("SELECT NIC,name,dependent_type FROM dependent WHERE Aid = ?");
            $dstmt->bind_param("s", $Aid);
            $dstmt->execute();
            $dres = $dstmt->get_result();
            while ($drow = $dres->fetch_assoc()) {
                $type = strtolower(trim($drow['dependent_type']));
                if ($type === 'mother') {
                    $hasMother = true;
                    $mother_name = $drow['name'];
                    $mother_nic  = $drow['NIC'];
                } elseif ($type === 'father') {
                    $hasFather = true;
                    $father_name = $drow['name'];
                    $father_nic  = $drow['NIC'];
                } elseif ($type === 'spouse' || $type === 'wife' || $type === 'husband') {
                    $hasSpouse = true;
                    $spouse_name = $drow['name'];
                    $spouse_nic  = $drow['NIC'];
                }
            }
            $dstmt->close();
        } else {
            $msg = "<div class='alert alert-danger'>Army ID not found!!</div>";
            $row = null;
        }
        $stmt->close();
    }
}

// INSERT - handle submit of new dependents (prevents duplicate dependent_type for same Aid)
if (isset($_POST['btnSubmit'])) {
    $Aid = trim($_POST['txtAid']);
    if ($Aid === "") {
        $msg = "<div class='alert alert-warning'>Enter Army ID before submit.</div>";
    } else {
        // verify soldier exists
        $chk = $con->prepare("SELECT Aid FROM soldetails WHERE Aid = ?");
        $chk->bind_param("s", $Aid);
        $chk->execute();
        $chkres = $chk->get_result();
        if ($chkres->num_rows === 0) {
            $msg = "<div class='alert alert-danger'>Army ID not found!</div>";
        } else {
            $con->begin_transaction();
            try {
                $inserted = 0;
                // Mother
                if (isset($_POST['checkmother'])) {
                    $mname = trim($_POST['txtFnamemother']);
                    $mnic  = trim($_POST['txtnicmother']);
                    if ($mname !== "" && $mnic !== "") {
                        // insert only if not exists
                        $exists = $con->prepare("SELECT 1 FROM dependent WHERE Aid=? AND dependent_type='mother' LIMIT 1");
                        $exists->bind_param("s", $Aid); $exists->execute(); $er = $exists->get_result();
                        if ($er->num_rows === 0) {
                            $ins = $con->prepare("INSERT INTO dependent (Aid, NIC, name, dependent_type) VALUES (?, ?, ?, 'mother')");
                            $ins->bind_param("sss", $Aid, $mnic, $mname);
                            $ins->execute(); $ins->close();
                            $inserted++;
                        } else {
                            // optional: update existing
                            $up = $con->prepare("UPDATE dependent SET NIC=?, name=? WHERE Aid=? AND dependent_type='mother'");
                            $up->bind_param("sss", $mnic, $mname, $Aid); $up->execute(); $up->close();
                        }
                        $exists->close();
                    }
                }
                // Father
                if (isset($_POST['checkfather'])) {
                    $fname = trim($_POST['txtFnamefather']);
                    $fnic  = trim($_POST['txtnicfather']);
                    if ($fname !== "" && $fnic !== "") {
                        $exists = $con->prepare("SELECT 1 FROM dependent WHERE Aid=? AND dependent_type='father' LIMIT 1");
                        $exists->bind_param("s", $Aid); $exists->execute(); $er = $exists->get_result();
                        if ($er->num_rows === 0) {
                            $ins = $con->prepare("INSERT INTO dependent (Aid, NIC, name, dependent_type) VALUES (?, ?, ?, 'father')");
                            $ins->bind_param("sss", $Aid, $fnic, $fname);
                            $ins->execute(); $ins->close();
                            $inserted++;
                        } else {
                            $up = $con->prepare("UPDATE dependent SET NIC=?, name=? WHERE Aid=? AND dependent_type='father'");
                            $up->bind_param("sss", $fnic, $fname, $Aid); $up->execute(); $up->close();
                        }
                        $exists->close();
                    }
                }
                // Spouse
                if (isset($_POST['checkspouse'])) {
                    $sname = trim($_POST['txtFnamespouse']);
                    $snic  = trim($_POST['txtnicspouse']);
                    if ($sname !== "" && $snic !== "") {
                        $exists = $con->prepare("SELECT 1 FROM dependent WHERE Aid=? AND dependent_type='spouse' LIMIT 1");
                        $exists->bind_param("s", $Aid); $exists->execute(); $er = $exists->get_result();
                        if ($er->num_rows === 0) {
                            $ins = $con->prepare("INSERT INTO dependent (Aid, NIC, name, dependent_type) VALUES (?, ?, ?, 'spouse')");
                            $ins->bind_param("sss", $Aid, $snic, $sname);
                            $ins->execute(); $ins->close();
                            $inserted++;
                        } else {
                            $up = $con->prepare("UPDATE dependent SET NIC=?, name=? WHERE Aid=? AND dependent_type='spouse'");
                            $up->bind_param("sss", $snic, $sname, $Aid); $up->execute(); $up->close();
                        }
                        $exists->close();
                    }
                }

                $con->commit();
                $msg = "<div class='alert alert-success'>Saved. New inserts: $inserted (existing records updated if present).</div>";

                // reload dependent values to show updated data
                $stmt = $con->prepare("SELECT NIC,name,dependent_type FROM dependent WHERE Aid = ?");
                $stmt->bind_param("s", $Aid);
                $stmt->execute();
                $dres = $stmt->get_result();
                // reset
                $mother_name = $mother_nic = $father_name = $father_nic = $spouse_name = $spouse_nic = "";
                $hasMother = $hasFather = $hasSpouse = false;
                while ($drow = $dres->fetch_assoc()) {
                    $type = strtolower(trim($drow['dependent_type']));
                    if ($type === 'mother') { $hasMother=true; $mother_name=$drow['name']; $mother_nic=$drow['NIC']; }
                    if ($type === 'father') { $hasFather=true; $father_name=$drow['name']; $father_nic=$drow['NIC']; }
                    if ($type === 'spouse') { $hasSpouse=true; $spouse_name=$drow['name']; $spouse_nic=$drow['NIC']; }
                }
                $stmt->close();

            } catch (Exception $e) {
                $con->rollback();
                $msg = "<div class='alert alert-danger'>Transaction failed: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
        $chk->close();
    }
}
?>

<?php echo $msg; ?>

<form method="post" action="#">
    <div class="search-container">
        <button type="submit" name="btnsearch">Search</button>
        <input type="text" placeholder="Search Army ID" name="txtAid" value="<?php echo htmlspecialchars($Aid ?: ($row['Aid'] ?? '')); ?>">
    </div>

    <div class="mb-4">
        <input type="checkbox" name="checkmother" id="checkdep" <?php echo $hasMother ? 'checked' : ''; ?>><label id="lbldep">Mother</label>
        <input type="text" name="txtFnamemother" class="form-control" placeholder="Enter Full Name" value="<?php echo htmlspecialchars($mother_name); ?>" />
        <label>Dependent Full Name</label>
    </div>
    <div class="col-md-6 mb-4">
        <input type="text" name="txtnicmother" class="form-control" placeholder="Enter NIC" value="<?php echo htmlspecialchars($mother_nic); ?>" />
        <label>Dependent NIC</label>
    </div>

    <div class="mb-4">
        <input type="checkbox" name="checkfather" id="checkdep" <?php echo $hasFather ? 'checked' : ''; ?>><label id="lbldep">Father</label>
        <input type="text" name="txtFnamefather" class="form-control" placeholder="Enter Full Name" value="<?php echo htmlspecialchars($father_name); ?>" />
        <label>Dependent Full Name</label>
    </div>
    <div class="col-md-6 mb-4">
        <input type="text" name="txtnicfather" class="form-control" placeholder="Enter NIC" value="<?php echo htmlspecialchars($father_nic); ?>" />
        <label>Dependent NIC</label>
    </div>

    <div class="mb-4">
        <input type="checkbox" name="checkspouse" id="checkdep" <?php echo $hasSpouse ? 'checked' : ''; ?>><label id="lbldep">Spouse</label>
        <input type="text" name="txtFnamespouse" class="form-control" placeholder="Enter Full Name" value="<?php echo htmlspecialchars($spouse_name); ?>" />
        <label>Dependent Full Name</label>
    </div>
    <div class="col-md-6 mb-4">
        <input type="text" name="txtnicspouse" class="form-control" placeholder="Enter NIC" value="<?php echo htmlspecialchars($spouse_nic); ?>" />
        <label>Dependent NIC</label>
    </div>

    <button type="submit" class="btn btn-success" name="btnSubmit">Submit</button>
</form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>

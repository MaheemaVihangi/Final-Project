<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$dbusername = "root";
$dbpassword = "monkey";
$dbname = "militarydb";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$Aid = isset($_POST['Aid']) ? trim($_POST['Aid']) : '';
if ($Aid === '') {
    // redirect back if missing Aid
    header("Location: /Final%20(2)/Final/AdminDepUpdate.php");
    exit;
}

// helper: upsert dependent
function upsertDependent($conn, $Aid, $type, $name, $nic) {
    $check = $conn->prepare("SELECT 1 FROM dependent WHERE Aid=? AND dependent_type=? LIMIT 1");
    $check->bind_param("ss", $Aid, $type);
    $check->execute();
    $r = $check->get_result();
    if ($r && $r->num_rows > 0) {
        $up = $conn->prepare("UPDATE dependent SET name=?, NIC=? WHERE Aid=? AND dependent_type=?");
        $up->bind_param("ssss", $name, $nic, $Aid, $type);
        $up->execute();
        $up->close();
    } else {
        $ins = $conn->prepare("INSERT INTO dependent (Aid, NIC, name, dependent_type) VALUES (?, ?, ?, ?)");
        $ins->bind_param("ssss", $Aid, $nic, $name, $type);
        $ins->execute();
        $ins->close();
    }
    $check->close();
}

function deleteDependent($conn, $Aid, $type) {
    $del = $conn->prepare("DELETE FROM dependent WHERE Aid=? AND dependent_type=?");
    $del->bind_param("ss", $Aid, $type);
    $del->execute();
    $del->close();
}

$conn->begin_transaction();

try {
    // Mother
    if (isset($_POST['hasMother'])) {
        $mname = trim($_POST['mother_name'] ?? '');
        $mnic  = trim($_POST['mother_nic'] ?? '');
        if ($mname !== '' && $mnic !== '') upsertDependent($conn, $Aid, 'mother', $mname, $mnic);
    } else {
        deleteDependent($conn, $Aid, 'mother');
    }

    // Father
    if (isset($_POST['hasFather'])) {
        $fname = trim($_POST['father_name'] ?? '');
        $fnic  = trim($_POST['father_nic'] ?? '');
        if ($fname !== '' && $fnic !== '') upsertDependent($conn, $Aid, 'father', $fname, $fnic);
    } else {
        deleteDependent($conn, $Aid, 'father');
    }

    // Spouse
    if (isset($_POST['hasSpouse'])) {
        $sname = trim($_POST['spouse_name'] ?? '');
        $snic  = trim($_POST['spouse_nic'] ?? '');
        if ($sname !== '' && $snic !== '') upsertDependent($conn, $Aid, 'spouse', $sname, $snic);
    } else {
        deleteDependent($conn, $Aid, 'spouse');
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
}

$conn->close();

// redirect back and auto-load the Aid
header("Location: /Final%20(2)/Final/AdminDepUpdate.php?aid=" . urlencode($Aid));
exit;
?>
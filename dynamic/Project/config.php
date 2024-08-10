<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student3312";

$db = new mysqli($servername, $username, $password, $dbname);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

function isAdmin($db, $username) {
    $sql = "SELECT role FROM users WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    $row = mysqli_fetch_assoc($result);

    return isset($row['role']) && $row['role'] == 0;
}
?>

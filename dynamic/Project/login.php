<?php
include("config.php");
session_start();

if (isset($_SESSION['login_user'])) {
    header("location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $myusername = mysqli_real_escape_string($db, $_POST['username']);
    $mypassword = mysqli_real_escape_string($db, $_POST['password']);

    $sql = "SELECT id FROM users WHERE username = '$myusername' AND password = '$mypassword'";
    $result = mysqli_query($db, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    $count = mysqli_num_rows($result);

    if ($count == 1) {
        $_SESSION['login_user'] = $myusername;
        header("location: index.php");
    } else {
        $loginError = true;
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Login - Μαθηματική Ανάλυση</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="login-body">

<div class="main">
    <h1>Login</h1>

    <form class="l" action="login.php" method="post">
        <div class="login-container">
            <label for="username"><b><li class="fas fa-user"></li> Όνομα Χρήστη</b>
                <input type="text" placeholder="Εισάγετε το όνομα χρήστη σας" name="username" required>
            </label>

            <br/>

            <label for="password"><b><li class="fas fa-lock"></li> Κωδικός Πρόσβασης</b>
                <input type="password" placeholder="Εισάγετε τον κωδικό πρόσβασής σας" name="password" required>
            </label>

            <br/>

            <?php if (isset($loginError) && $loginError): ?>
                <div class="danger">
                    Λάθος όνομα χρήστη ή κωδικός.
                </div>
                <br/>
            <?php endif; ?>

            <button type="submit">Σύνδεση</button>
        </div>
    </form>
</div>

<script src="js/script.js"></script>
</body>
</html>

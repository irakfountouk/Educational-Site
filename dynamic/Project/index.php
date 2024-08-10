<?php
session_start();

if (!isset($_SESSION['login_user'])) {
    header("location: login.php");
    exit();
}

require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Αρχική Σελίδα - Μαθηματική Ανάλυση</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

<div class="to-top" onclick="scrollToTop()">
    <a href="#">Πήγαινε στην κορυφή</a>
</div>

<header class="header" id="top">
    <h1>Μαθηματική Ανάλυση</h1>
</header>

<main class="flex-content">
    <nav class="sidebar">
        <ul>
            <li><a class="active" href="index.php">Αρχική</a></li>
            <li><a href="announcements.php">Ανακοινώσεις</a></li>
            <li><a href="contact.php">Επικοινωνία</a></li>
            <li><a href="documents.php">Έγγραφα</a></li>
            <li><a href="homework.php">Εργασίες</a></li>
        </ul>
    </nav>
    <section class="content">
        <div class="title">
            <h2>Καλωσορίσατε στην σελίδα του μαθήματος Μαθηματικής Ανάλυσης</h2>
            <p>Αυτή η σελίδα περιέχει όλες τις ανακοινώσεις, τα έγγραφα και τις εργασίες για το μάθημα.</p>
        </div>
        <div class="card-view">
            <div class="card">
                <h2>Νέες Ανακοινώσεις</h2>
                <p>Ενημερωθείτε για τις πρόσφατες ανακοινώσεις του μαθήματος.</p>
                <p><a href="announcements.php">Μάθετε περισσότερα...</a></p>
            </div>
            <div class="card">
                <h2>Πληροφορίες Επικοινωνίας</h2>
                <p>Βρείτε τις απαραίτητες πληροφορίες για να επικοινωνήσετε με τους διδάσκοντες.</p>
                <p><a href="contact.php">Περισσότερα...</a></p>
            </div>
            <div class="card">
                <h2>Αρχεία Μαθήματος</h2>
                <p>Κατεβάστε τα διαθέσιμα αρχεία του μαθήματος.</p>
                <p><a href="documents.php">Περισσότερα...</a></p>
            </div>
            <div class="card">
                <h2>Εργασίες Μαθήματος</h2>
                <p>Δείτε τις εργασίες που πρέπει να ολοκληρώσετε για το μάθημα.</p>
                <p><a href="homework.php">Περισσότερα...</a></p>
            </div>
        </div>
        <img src="images/auth.png" alt="ΑΠΘ" class="image">
    </section>
</main>

<script src="js/script.js"></script>
</body>
</html>

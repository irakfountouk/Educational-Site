<div class="user-info">
    <div class="greet">
        <i class="fa-solid fa-circle online"></i>Ως @<?php echo $_SESSION['login_user']; ?>
    </div>
    <?php if (isAdmin($db, $_SESSION['login_user'])) { ?>
    <div class="choices isOn">
<a href="users.php"><i class="fa-solid fa-users"></i>&nbsp;&nbsp;Διαχ. Χρηστών</a>
    </div>
    <?php } ?>
    <div class="choices">
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;&nbsp;Αποσύνδεση</a>
    </div>
</div>
<div class="card-view">
    <div class="card">
        <h2>Ανακοινώσεις</h2>
        <p>Εδώ θα βρείτε όλες τις ανακοινώσεις του τμήματος</p>
        <p><a href="announcements.php">Περισσότερα...</a></p>
    </div>
    <div class="card">
        <h2>Επικοινωνία</h2>
        <p>Εδώ θα βρείτε τα στοιχεία επικοινωνίας του τμήματος</p>
        <p><a href="contact.php">Περισσότερα...</a></p>
    </div>
    <div class="card">
        <h2>Έγγραφα</h2>
        <p>Εδώ θα βρείτε όλα τα έγγραφα του τμήματος</p>
        <p><a href="documents.php">Περισσότερα...</a></p>
    </div>
    <div class="card">
        <h2>Εργασίες</h2>
        <p>Εδώ θα βρείτε όλες τις εργασίες του τμήματος</p>
        <p><a href="homework.php">Περισσότερα...</a></p>
    </div>
    <?php if (isAdmin($db, $_SESSION['login_user'])) { ?>
        <div class="card">
            <h2>Διαχείριση Χρηστών</h2>
            <p>Εδώ θα βρείτε όλους τους χρήστες του τμήματος</p>
            <p><a href="users.php">Περισσότερα...</a></p>
        </div>
    <?php } ?>
</div>
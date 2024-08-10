<?php
session_start();

if (!isset($_SESSION['login_user'])) {
    header("location: login.php");
    exit();
}

require_once("config.php");
$login_user = $_SESSION['login_user'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_submit'])) {
        $title = htmlspecialchars($_POST['title']);
        $text = htmlspecialchars($_POST['text']);

        $stmt = $db->prepare("INSERT INTO announcements (title, text, date) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $title, $text);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['submit'])) {
        $announcementId = intval($_POST['edit_id']);
        $title = htmlspecialchars($_POST['title']);
        $text = htmlspecialchars($_POST['text']);

        $stmt = $db->prepare("UPDATE announcements SET title = ?, text = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $text, $announcementId);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        $announcementId = intval($_POST['delete']);

        $stmt = $db->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $announcementId);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch announcements from the database
$result = $db->query("SELECT * FROM announcements ORDER BY date DESC");
$announcements = [];
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Ανακοινώσεις - Μαθηματική Ανάλυση</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'common/user_info.php'; ?>

<div class="to-top" onclick="scrollToTop()">
    <a href="#">Πήγαινε στην κορυφή</a>
</div>

<div class="header" id="top">
    <h1>Ανακοινώσεις</h1>
</div>

<div class="flex-content">
    <div class="sidebar">
        <ul>
            <li><a href="index.php">Αρχική</a></li>
            <li><a class="active" href="announcements.php">Ανακοινώσεις</a></li>
            <li><a href="contact.php">Επικοινωνία</a></li>
            <li><a href="documents.php">Έγγραφα</a></li>
            <li><a href="homework.php">Εργασίες</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="title">
            <h2>Ανακοινώσεις Μαθήματος</h2>
            <p>Δείτε όλες τις ανακοινώσεις για το μάθημα.</p>
        </div>

        <div class="card-view">

            <?php if (isAdmin($db, $login_user)) : ?>
                <div class="card create">
                    <h2>Κάνε μια Ανακοίνωση</h2>
                    <div class="m-t-5"></div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="text" name="title" placeholder="Τίτλος" required>
                        <textarea name="text" placeholder="Κείμενο" required></textarea>
                        <button type="submit" name="create_submit">Δημιουργία</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php foreach ($announcements as $announcement) : ?>
                <div class="card">
                    <h2 id="title_<?php echo $announcement['id']; ?>"><?php echo $announcement['title']; ?></h2>
                    <small>Αναρτήθηκε στις <?php echo date('d/m/Y', strtotime($announcement['date'])); ?><?php if ($announcement['isAutomated']) : ?> • <span class="success"> Αυτοματοποιημένο</span><?php endif; ?></small>
                    <div class="divider"></div>
                    <p id="text_<?php echo $announcement['id']; ?>"><?php echo $announcement['text']; ?></p>

                    <div class="edit-form" id="edit_<?php echo $announcement['id']; ?>" style="display:none;">
                        <form action="#" method="post">
                            <input type="hidden" name="edit_id" value="<?php echo $announcement['id']; ?>">
                            <input placeholder="Τίτλος" type="text" name="title"
                                   value="<?php echo $announcement['title']; ?>" required>
                            <textarea placeholder="Περιγραφή" name="text"
                                      required><?php echo $announcement['text']; ?></textarea>
                            <button type="submit" name="submit">Αποθήκευση</button>
                            <button type="button" onclick="cancelEdit(<?php echo $announcement['id']; ?>)">Ακύρωση
                            </button>
                        </form>
                    </div>

                    <?php if (isAdmin($db, $login_user)) : ?>

                    <div class="m-t-b-20" id="space_<?php echo $announcement['id']; ?>"></div>

                    <div class="edit-delete" id="edit_delete_<?php echo $announcement['id']; ?>">
                        <button onclick="editAnnouncement(<?php echo $announcement['id']; ?>)">Επεξεργασία</button>
                        <form action="#" method="post" style="display:inline;">
                            <input type="hidden" name="delete" value="<?php echo $announcement['id']; ?>">
                            <button type="submit">Διαγραφή</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<script>
    function editAnnouncement(id) {
        $(`#title_${id}`).hide();
        $(`#text_${id}`).hide();
        $(`#edit_${id}`).show();
        $(`#edit_delete_${id}`).hide();
        $(`#space_${id}`).hide();
    }

    function cancelEdit(id) {
        $(`#title_${id}`).show();
        $(`#text_${id}`).show();
        $(`#edit_${id}`).hide();
        $(`#edit_delete_${id}`).show();
        $(`#space_${id}`).show();
    }
</script>

<script src="js/script.js"></script>
</body>
</html>

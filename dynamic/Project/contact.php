<?php
session_start();

if (!isset($_SESSION['login_user'])) {
    header("location: login.php");
    exit();
}

require_once 'config.php';

$sql = "SELECT * FROM messages";
$result = mysqli_query($db, $sql);

if (mysqli_num_rows($result) > 0) {
    $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $messages = array_reverse($messages);
} else {
    $messages = [];
}

$login_user = $_SESSION['login_user'];
$userData = mysqli_query($db, "SELECT * FROM users WHERE username = '$login_user'");
$userData = mysqli_fetch_array($userData, MYSQLI_ASSOC);
$sender_query = mysqli_query($db, "SELECT id FROM users WHERE username = '$login_user'");
$sender_data = mysqli_fetch_array($sender_query, MYSQLI_ASSOC);
$sender_id = $sender_data['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $sender = $_POST['sender'];
    $subject = $_POST['subject'];
    $text = $_POST['text'];

    $sql = "INSERT INTO messages (sender, sender_id, subject, text) VALUES ('$sender', '$sender_id', '$subject', '$text')";
    $messageSent = mysqli_query($db, $sql);

    $tutorsQuery = mysqli_query($db, "SELECT email FROM users WHERE role = 0 AND email IS NOT NULL");
    $tutorsResult = mysqli_fetch_all($tutorsQuery, MYSQLI_ASSOC);

    if (!empty($tutorsResult)) {
        $tutorsEmails = array_column($tutorsResult, 'email');

        if (!empty($tutorsEmails)) {
            $tutors = implode(',', $tutorsEmails);
            $subject = "Ειδοποίηση από τον χρήστη $sender";
            $message = "Έχετε ένα νέο μήνυμα από τον χρήστη $sender.\n\nΘέμα: $subject\n\nΜήνυμα: $text";
            $headers = "From: $sender";

            mail($tutors, $subject, $message, $headers);
        }
    }

    header("Location: contact.php"); 
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $sql = "DELETE FROM messages WHERE id = $deleteId AND sender_id = $sender_id";
    mysqli_query($db, $sql);
    header("Location: contact.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editSubject'])) {
    $editId = $_POST['editId'];
    $editedSubject = $_POST['editedSubject'];
    $editedText = $_POST['editedText'];

    $checkOwnershipSql = "SELECT id FROM messages WHERE id = $editId AND sender_id = $sender_id";
    $ownershipCheckResult = mysqli_query($db, $checkOwnershipSql);

    if (mysqli_num_rows($ownershipCheckResult) > 0) {
        $updateSql = "UPDATE messages SET subject = '$editedSubject', text = '$editedText' WHERE id = $editId";
        mysqli_query($db, $updateSql);
    }

    header("Location: contact.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Επικοινωνία - Μαθηματική Ανάλυση</title>
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
    <h1>Επικοινωνία</h1>
</div>

<div class="flex-content">
    <div class="sidebar">
        <ul>
            <li><a href="index.php">Αρχική</a></li>
            <li><a href="announcements.php">Ανακοινώσεις</a></li>
            <li><a class="active" href="contact.php">Επικοινωνία</a></li>
            <li><a href="documents.php">Έγγραφα</a></li>
            <li><a href="homework.php">Εργασίες</a></li>
        </ul>
    </div>
    <div class="content">

        <?php if ($userData['role'] == 0): ?>

            <div class="title">
                <h2>Αποστολή email μέσω web φόρμας</h2>
            </div>

            <form class="contact-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="text" name="sender" placeholder="Αποστολέας" value="<?php echo $userData['name'] . ' ' . $userData['lastname']; ?>" required>
                <input type="text" name="subject" placeholder="Θέμα" required>
                <textarea name="text" placeholder="Κείμενο" required></textarea>
                <button type="submit" name="submit">Αποστολή</button>
            </form>

            <div class="divider m-t-b-30"></div>
            <div class="title">
                <h2>Όλα τα μηνύματα</h2>
                <p>
                    Εδώ μπορείτε να δείτε όλα τα μηνύματα που έχουν αποσταλεί στο τμήμα. Μπορείτε να επεξεργαστείτε το θέμα και το κείμενο των μηνυμάτων που έχετε αποστείλει.
                </p>
            </div>

            <div class="m-t-b-30"></div>

            <ol class="messages">
                <?php
                if (empty($messages)) {
                    echo '<p>Δεν υπάρχουν μηνύματα</p>';
                }

                foreach ($messages as $message) {
                    echo "<li id='message_{$message['id']}'>
            <div class='sender'>{$message['sender']} ";

                    if ($sender_id == $message['sender_id']) {
                        echo '<small class="identify"> • Εσείς</small>';
                    } else {
                        echo '<small class="identify"> • Άλλος</small>';
                    }

                    echo '</div>
            <div class="subject" id="subject_' . $message['id'] . '">' . $message["subject"] . '</div>
            <small class="date">' . date('Απεστάλη στις d/m/Y, H:i', strtotime($message['timestamp']));

                    if ($sender_id == $message['sender_id']) {
                        echo ' • <a href="#message_' . $message['id'] . '" onclick="editSubject(' . $message['id'] . ')">Επεξεργασία</a> •
                <a href="contact.php?delete=' . $message['id'] . '">Διαγραφή</a>';
                    }

                    echo '</small>
            <div class="divider"></div>
            <div class="text" id="text_msg_' . $message['id'] . '">' . $message["text"] . '</div>
            <div class="edit-form" id="edit_' . $message['id'] . '" style="display:none;">
                <form action="#" method="post">
                    <input type="hidden" name="editId" value="' . $message['id'] . '">
                    <input placeholder="Θέμα" type="text" name="editedSubject" value="' . $message['subject'] . '" required>
                    <textarea placeholder="Κείμενο" name="editedText" required>' . $message['text'] . '</textarea>
                    <button type="submit" name="editSubject">Αποθήκευση</button>
                    <button type="button" onclick="cancelEdit(' . $message['id'] . ')">Ακύρωση</button>
                </form>
            </div>
        </li>';
                }
                ?>
            </ol>

        <?php else: ?>
            <div class="title">
                <h2>Μη εξουσιοδοτημένη αποστολή μηνύματος</h2>
                <p>
                    <?php echo (!empty($userData['name']) || !empty($userData['lastname'])) ? 'Είστε συνδεδεμένος ως ' . $userData['name'] . ' ' . $userData['lastname'] . ' με όνομα χρήστη ' : 'Ως χρήστης με όνομα χρήστη ' ?> <?php echo $login_user; ?>
                και ρόλο φοιτητή. Δεν έχετε άδεια αποστολής μηνύματος. Συνεχίστε την περιήγησή σας επιλέγοντας μία από τις παρακάτω επιλογές.</p>
                <div class="m-t-10"></div>
                <p><li class="fas fa-info-circle"></li> Αποστείλετε email στη διεύθυνση μας <a href="mailto:tutor@csd.auth.test.gr">tutor@csd.auth.test.gr</a> από τον λογαριασμό και την εφαρμογή email σας.</p>
            </div>
            <?php include 'common/choices.php'; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function editSubject(id) {
        $(`#edit_${id}`).show();
        $(`#subject_${id}`).hide();
        $(`#text_msg_${id}`).hide();
    }

    function cancelEdit(id) {
        $(`#edit_${id}`).hide();
        $(`#subject_${id}`).show();
        $(`#text_msg_${id}`).show();
    }

    $(document).ready(function () {
        $("a").on('click', function (event) {
            if (this.hash !== "") {
                event.preventDefault();

                let hash = this.hash;

                let marginTop = 20;

                $('html, body').animate({
                    scrollTop: $(hash).offset().top - marginTop
                }, 800);
            }
        });
    });
</script>

<script src="js/script.js"></script>
</body>
</html>

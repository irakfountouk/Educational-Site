<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['login_user'])) {
    header("location: login.php");
    exit();
}

// Διαχείριση διαγραφής εργασίας
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $id = mysqli_real_escape_string($db, $_GET['id']);

    $sql_select_file = "SELECT file_path FROM homework WHERE id = $id";
    $result_select_file = mysqli_query($db, $sql_select_file);

    if ($row_select_file = mysqli_fetch_assoc($result_select_file)) {
        $file_path = $row_select_file['file_path'];

        unlink($file_path);

        $sql_delete = "DELETE FROM homework WHERE id = $id";
        if (mysqli_query($db, $sql_delete)) {
            header("location: homework.php");
            exit();
        } else {
            echo "Υπήρξε πρόβλημα κατά την διαγραφή της εργασίας: " . mysqli_error($db);
            exit();
        }
    } else {
        echo "Η εργασία που ζητήσατε δεν βρέθηκε.";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit_homework"])) {
    $edit_id = mysqli_real_escape_string($db, $_POST['edit_id']);
    $edited_title = mysqli_real_escape_string($db, $_POST['edited_title']);
    $edited_deadline = isset($_POST['edited_deadline']) ? mysqli_real_escape_string($db, $_POST['edited_deadline']) : "";
    $edited_goals = mysqli_real_escape_string($db, $_POST['edited_goals']);
    $edited_deliverables = mysqli_real_escape_string($db, $_POST['edited_deliverables']);
    $send_announcement = isset($_POST['send_announcement']);
    $errorMsg = "";

    $edited_file = $_FILES["edited_file"]["name"];
    if (!empty($edited_file)) {
        $targetDir = "uploads/";

        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));
        $uniqueFilename = $timestamp . "_" . $randomString;

        $fileType = strtolower(pathinfo($_FILES["edited_file"]["name"], PATHINFO_EXTENSION));

        $targetFileEditedHomework = $targetDir . $uniqueFilename . "." . $fileType;
        $uploadOkEditedHomework = 1;

        if ($_FILES["edited_file"]["size"] > 5000000) {
            $errorMsg = "Το αρχείο είναι πολύ μεγάλο. Μέγιστο επιτρεπόμενο μέγεθος είναι 5MB.";
            $uploadOkEditedHomework = 0;
        }

        if (!in_array($fileType, array("doc", "docx", "pdf", "txt"))) {
            $errorMsg = "Επιτρέπονται μόνο αρχεία τύπου DOC, DOCX, PDF και TXT.";
            $uploadOkEditedHomework = 0;
        }

        if ($uploadOkEditedHomework == 1) {
            if (move_uploaded_file($_FILES["edited_file"]["tmp_name"], $targetFileEditedHomework)) {
                $deadline_update = !empty($edited_deadline) ? "deadline = '$edited_deadline'," : "";
                $sql_update = "UPDATE homework SET 
                                title = '$edited_title', 
                                $deadline_update
                                goals = '$edited_goals', 
                                deliverables = '$edited_deliverables', 
                                file_name = '$uniqueFilename', 
                                file_path = '$targetFileEditedHomework' 
                                WHERE id = $edit_id";

                if (mysqli_query($db, $sql_update)) {
                    header("location: homework.php");
                } else {
                    $errorMsg = "Σφάλμα κατά την ενημέρωση της εργασίας: " . mysqli_error($db);
                }
            } else {
                $errorMsg = "Προέκυψε πρόβλημα κατά την μεταφόρτωση του αρχείου.";
            }
        }
    } else {
        $deadline_update = !empty($edited_deadline) ? "deadline = '$edited_deadline'," : "";
        $sql_update = "UPDATE homework SET 
                        title = '$edited_title', 
                        $deadline_update
                        goals = '$edited_goals', 
                        deliverables = '$edited_deliverables' 
                        WHERE id = $edit_id";

        if (mysqli_query($db, $sql_update)) {
            header("location: homework.php");
        } else {
            $errorMsg = "Σφάλμα κατά την ενημέρωση της εργασίας: " . mysqli_error($db);
        }
    }
}

function sendAnnouncement(mysqli $db, $title, $deadline, $goals, $deliverables, $uniqueFilename, $targetFileHomework)
{
    $sql = "INSERT INTO documents (title, description, file_path, isAutomated) VALUES ('$title', 'Δείτε την εκφώνηση της εργασίας στο συνημμένο αρχείο.', '$targetFileHomework', 1)";
    mysqli_query($db, $sql);

    $sql = "INSERT INTO announcements (title, text, date, isAutomated) VALUES ('$title', 'Δείτε την εκφώνηση της εργασίας στα έγγραφα.', NOW(), 1)";
    mysqli_query($db, $sql);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $title = mysqli_real_escape_string($db, $_POST['title']);
    $deadline = mysqli_real_escape_string($db, $_POST['deadline']);
    $goals = mysqli_real_escape_string($db, $_POST['goals']);
    $deliverables = mysqli_real_escape_string($db, $_POST['deliverables']);
    $errorMsg = "";

    $targetDir = "uploads/";

    $timestamp = time();
    $randomString = bin2hex(random_bytes(8));
    $uniqueFilename = $timestamp . "_" . $randomString;

    $fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));

    $targetFileHomework = $targetDir . $uniqueFilename . "." . $fileType;
    $uploadOkHomework = 1;

    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        $errorMsg = "Το αρχείο είναι πολύ μεγάλο. Μέγιστο επιτρεπόμενο μέγεθος είναι 5MB.";
        $uploadOkHomework = 0;
    }

    if (!in_array($fileType, array("doc", "docx", "pdf", "txt"))) {
        $errorMsg = "Επιτρέπονται μόνο αρχεία τύπου DOC, DOCX, PDF και TXT.";
        $uploadOkHomework = 0;
    }

    if ($uploadOkHomework == 1) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFileHomework)) {
            $sql = "INSERT INTO homework (title, deadline, goals, deliverables, file_name, file_path) 
                    VALUES ('$title', '$deadline', '$goals', '$deliverables', '$uniqueFilename', '$targetFileHomework')";

            if (mysqli_query($db, $sql)) {
                if (isset($_POST['send_announcement'])) {
                    sendAnnouncement($db, $title, $deadline, $goals, $deliverables, $uniqueFilename, $targetFileHomework);
                }
                header("location: homework.php");
            } else {
                $errorMsg = "Σφάλμα: " . $sql . "<br>" . mysqli_error($db);
            }
        } else {
            $errorMsg = "Προέκυψε πρόβλημα κατά την μεταφόρτωση του αρχείου.";
        }
    }
}

function getFileExtension($filePath)
{
    $pathInfo = pathinfo($filePath);
    return $pathInfo['extension'];
}

$sql = "SELECT * FROM homework";
$result = mysqli_query($db, $sql);
$login_user = $_SESSION['login_user'];
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Εργασίες - Μαθηματική Ανάλυση</title>
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
    <h1>Εργασίες</h1>
</div>

<div class="flex-content">
    <div class="sidebar">
        <ul>
            <li><a href="index.php">Αρχική</a></li>
            <li><a href="announcements.php">Ανακοινώσεις</a></li>
            <li><a href="contact.php">Επικοινωνία</a></li>
            <li><a href="documents.php">Έγγραφα</a></li>
            <li><a class="active" href="homework.php">Εργασίες</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="title">
            <h2>Εργασίες Μαθημάτων</h2>
            <p>Σε αυτή τη σελίδα μπορείτε να δείτε όλες τις διαθέσιμες εργασίες των μαθημάτων, να κατεβάσετε την εκφώνηση και να δείτε τους στόχους και τα παραδοτέα.</p>
        </div>

        <?php if (isAdmin($db, $login_user)) : ?>

        <div class="card-view">
            <div class="card create">
                <h2>Ανέβασμα Εργασίας</h2>
                <small>Μπορείτε να ανεβάσετε ένα αρχείο τύπου DOC, DOCX, PDF ή TXT που περιέχει την εκφώνηση της εργασίας. Μπορείτε επίσης να προωθήσετε την εργασία στις ανακοινώσεις και στα έγγραφα επιλέγοντας την αντίστοιχη επιλογή.</small>
                <div class="m-t-10"></div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <label for="title">Τίτλος:</label><br>
                    <input type="text" name="title" required>
                    <div class="m-t-5"></div>
                    <label for="deadline">Ημερομηνία παράδοσης:</label><br>
                    <input type="datetime-local" name="deadline" required>
                    <div class="m-t-5"></div>
                    <label for="goals">Στόχοι (χωρισμένοι με κόμμα):</label><br>
                    <input type="text" name="goals" required>
                    <div class="m-t-5"></div>
                    <label for="deliverables">Παραδοτέα (χωρισμένα με κόμμα):</label><br>
                    <input type="text" name="deliverables" required>
                    <div class="m-t-5"></div>
                    <label for="fileToUpload">Αρχείο Εκφώνησης:</label><br>
                    <input type="file" name="fileToUpload" required>
                    <label class="container">Να προωθήσω την εργασία στις ανακοινώσεις και στα έγγραφα;
                        <input type="checkbox" name="send_announcement">
                        <span class="checkmark"></span>
                    </label>
                    <?php
                    if (isset($errorMsg)) {
                        echo '<div class="m-t-5"></div>';
                        echo '<small class="danger">' . $errorMsg . '</small>';
                    }
                    ?>
                    <div class="m-t-5"></div>
                    <input class="button" type="submit" value="Ανέβασμα" name="submit">
                </form>
            </div>
        </div>

        <?php endif; ?>

        <div class="card-view">
            <?php
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            $rows = array_reverse($rows);
            foreach ($rows as $row) {
                echo '<div class="card" id="hw_' . $row['id'] . '">';
                echo '<div id="hw_post_' . $row['id'] . '"">';
                echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
                echo '<div class="m-t-10"></div>';
                echo '<small class="danger">Παράδοση έως ' . htmlspecialchars($row['deadline']) . ' • <a href="' . htmlspecialchars($row['file_path']) . '">Λήψη Εκφώνησης</a></small>';
                if (isAdmin($db, $login_user)) {
                    echo '<div class="divider"></div>';
                    echo '<small><a href="#hw_' . $row['id'] . '" onclick="editHomework(' . $row['id'] . ')">Επεξεργασία</a> • <a href="?action=delete&id=' . $row['id'] . '">Διαγραφή</a></small>';
                }
                echo '<div class="divider"></div>';
                echo '<div class="content">';
                echo '<h4>Στόχοι μαθήματος:</h4>';
                echo '<ol>';
                $goals = explode(',', $row['goals']);
                foreach ($goals as $goal) {
                    echo '<li>' . htmlspecialchars($goal) . '</li>';
                }
                echo '</ol>';
                echo '<h4>Παραδοτέα:</h4>';
                echo '<ol>';
                $deliverables = explode(',', $row['deliverables']);
                foreach ($deliverables as $deliverable) {
                    echo '<li>' . htmlspecialchars($deliverable) . '</li>';
                }
                echo '</ol>';
                echo '<div class="m-t-20"></div>';
                echo '<a class="button center" href="' . htmlspecialchars($row['file_path']) . '">Κατέβασε εκφώνηση (' . getFileExtension($row['file_path']) . ')</a>';
                echo '</div></div>';
                echo '<div class="edit-form" id="edit_' . $row['id'] . '" style="display:none;">';
                echo '<form action="#" method="post" enctype="multipart/form-data">';
                echo '<input type="hidden" name="edit_id" value="' . $row['id'] . '">';
                echo '<input placeholder="Τίτλος" type="text" name="edited_title" value="' . $row['title'] . '" required>';
                echo '<input placeholder="Ημερομηνία παράδοσης" type="datetime-local" name="edited_deadline" value="' . $row['deadline'] . '">';
                echo '<input placeholder="Στόχοι (χωρισμένοι με κόμμα)" type="text" name="edited_goals" value="' . $row['goals'] . '" required>';
                echo '<input placeholder="Παραδοτέα (χωρισμένα με κόμμα)" type="text" name="edited_deliverables" value="' . $row['deliverables'] . '" required>';
                echo '<input type="file" name="edited_file">';
                echo '<button type="submit" name="edit_homework">Αποθήκευση</button>';
                echo '<button type="button" onclick="cancelEdit(' . $row['id'] . ')">Ακύρωση</button>';
                echo '<div class="divider"></div>';
                echo '<small class="danger">*Αν δεν θέλετε να αλλάξετε το αρχείο εκφώνησης ή την ημερομηνία παράδοσης, αφήστε τα αντίστοιχα πεδία κενά.</small>';
                echo '</form>';
                echo '</div></div>';
            }
            ?>
        </div>
    </div>
</div>

<script>
    function cancelEdit(id) {
        $(`#edit_${id}`).hide();
        $(`#hw_post_${id}`).show();
    }

    function editHomework(id) {
        $(`#edit_${id}`).show();
        $(`#hw_post_${id}`).hide();
    }

    $(document).ready(function () {
        $("a").on('click', function (event) {
            if (this.hash !== "") {
                event.preventDefault();
                let hash = this.hash;
                let marginTop = 20;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top - marginTop
                }, {
                    duration: 800
                });
            }
        });
    });
</script>

<script src="js/script.js"></script>
</body>
</html>

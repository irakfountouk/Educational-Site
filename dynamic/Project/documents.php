<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['login_user'])) {
    header("location: login.php");
    exit();
}

// Διαχείριση διαγραφής εγγράφου
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $id = mysqli_real_escape_string($db, $_GET['id']);

    $sql_select_file = "SELECT file_path FROM documents WHERE id = $id";
    $result_select_file = mysqli_query($db, $sql_select_file);

    if ($row_select_file = mysqli_fetch_assoc($result_select_file)) {
        $file_path = $row_select_file['file_path'];

        unlink($file_path);

        $sql_delete = "DELETE FROM documents WHERE id = $id";
        if (mysqli_query($db, $sql_delete)) {
            header("location: documents.php");
            exit();
        } else {
            echo "Προέκυψε σφάλμα κατά την διαγραφή του αρχείου: " . mysqli_error($db);
            exit();
        }
    } else {
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $title = mysqli_real_escape_string($db, $_POST['title']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $errorMsg = "";

    $targetDir = "uploads/";

    if (isset($_POST["edit_id"])) {
        $edit_id = mysqli_real_escape_string($db, $_POST['edit_id']);

        if ($_FILES["fileToUpload"]["size"] > 0) {
            // Διαγραφή υπάρχοντος αρχείου πριν την ενημέρωση
            $sql_select_file = "SELECT file_path FROM documents WHERE id = $edit_id";
            $result_select_file = mysqli_query($db, $sql_select_file);

            if ($row_select_file = mysqli_fetch_assoc($result_select_file)) {
                $existing_file_path = $row_select_file['file_path'];
                unlink($existing_file_path);
            }

            $timestamp = time();
            $randomString = bin2hex(random_bytes(8));
            $uniqueFilename = $timestamp . "_" . $randomString;

            $fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));

            $targetFile = $targetDir . $uniqueFilename . "." . $fileType;

            if ($_FILES["fileToUpload"]["size"] > 5000000) {
                $errorMsg = "Το αρχείο είναι πολύ μεγάλο. Επιτρέπονται αρχεία έως 5MB.";
            }

            if (!in_array($fileType, array("doc", "docx", "pdf", "txt"))) {
                $errorMsg = "Επιτρέπονται μόνο αρχεία τύπου DOC, DOCX, PDF και TXT.";
            }

            if (empty($errorMsg) && move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
                $sql_update = "UPDATE documents SET title = '$title', description = '$description', file_path = '$targetFile' WHERE id = $edit_id";
                if (mysqli_query($db, $sql_update)) {
                    header("location: documents.php");
                    exit();
                } else {
                    echo "Προέκυψε σφάλμα κατά την ενημέρωση του αρχείου: " . mysqli_error($db);
                    exit();
                }
            } else {
                $errorMsg = "Προέκυψε σφάλμα κατά το ανέβασμα του αρχείου.";
            }
        } else {
            $sql_update = "UPDATE documents SET title = '$title', description = '$description' WHERE id = $edit_id";
            if (mysqli_query($db, $sql_update)) {
                header("location: documents.php");
                exit();
            } else {
                echo "Προέκυψε σφάλμα κατά την ενημέρωση του αρχείου: " . mysqli_error($db);
                exit();
            }
        }
    } else {
        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));
        $uniqueFilename = $timestamp . "_" . $randomString;
        $fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
        $targetFile = $targetDir . $uniqueFilename . "." . $fileType;
        $uploadOk = 1;

        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            $errorMsg = "Το αρχείο είναι πολύ μεγάλο. Επιτρέπονται αρχεία έως 5MB.";
            $uploadOk = 0;
        }

        if (!in_array($fileType, array("doc", "docx", "pdf", "txt"))) {
            $errorMsg = "Επιτρέπονται μόνο αρχεία τύπου DOC, DOCX, PDF και TXT.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1 && move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
            $sql = "INSERT INTO documents (title, description, file_path) VALUES ('$title', '$description', '$targetFile')";
            if (mysqli_query($db, $sql)) {
                header("location: documents.php");
                exit();
            } else {
                echo "Σφάλμα: " . $sql . "<br>" . mysqli_error($db);
            }
        } else {
            $errorMsg = "Προέκυψε σφάλμα κατά το ανέβασμα του αρχείου.";
        }
    }
}

function getFileExtension($filePath)
{
    $pathInfo = pathinfo($filePath);
    return $pathInfo['extension'];
}

$sql = "SELECT id, title, description, file_path, isAutomated FROM documents";
$result = mysqli_query($db, $sql);
$login_user = $_SESSION['login_user'];

?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Έγγραφα - Μαθηματική Ανάλυση</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'common/user_info.php'; ?>


<div class="header" id="top">
    <h1>Έγγραφα</h1>
</div>

<div class="flex-content">
    <div class="sidebar">
        <ul>
            <li><a href="index.php">Αρχική</a></li>
            <li><a href="announcements.php">Ανακοινώσεις</a></li>
            <li><a href="contact.php">Επικοινωνία</a></li>
            <li><a class="active" href="documents.php">Έγγραφα</a></li>
            <li><a href="homework.php">Εργασίες</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="title">
            <h2>Έγγραφα</h2>
            <p>Εδώ μπορείτε να δείτε όλα τα έγγραφα του μαθήματος.</p>
        </div>

        <?php if (isAdmin($db, $login_user)) : ?>

        <div class="card-view">
            <div class="card create">
                <h2>Προσθήκη Έγγραφου</h2>
                <small> Μπορείτε να ανεβάσετε ένα αρχείο τύπου DOC, DOCX, PDF ή TXT</small>
                <div class="m-t-20"></div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                      enctype="multipart/form-data">
                    Τίτλος:<br>
                    <input type="text" name="title" required>
                    <div class="m-t-5"></div>
                    Περιγραφή:<br>
                    <textarea name="description" rows="2" cols="40" required></textarea>
                    <div class="m-t-5"></div>
                    Αρχείο:<br>
                    <input type="file" name="fileToUpload" id="fileToUpload" required>
                    <div class="m-t-5"></div>
                    <?php if (isset($errorMsg)) { ?>
                        <p class="danger center"><?php echo $errorMsg; ?></p>
                        <div class="m-t-5"></div>
                    <?php } ?>
                    <button type="submit" name="submit">Ανέβασμα</button>
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
                echo '<div class="card" id="card_' . $row['id'] . '">';
                echo '<h2 id="title_' . $row['id'] . '">' . htmlspecialchars($row['title']) . '</h2>';
                if (isAdmin($db, $login_user)) {
                    echo '<small><a href="#card_' . $row['id'] . '" onclick="editSubject(' . $row['id'] . ')">Επεξεργασία</a> • <a href="?action=delete&id=' . $row['id'] . '">Διαγραφή</a>' . ($row['isAutomated'] ? ' • <span class="success">Αυτοματοποιημένο</span>' : '') . '</small>';
                } else {
                    echo '<small>' . ($row['isAutomated'] ? '<span class="success">Αυτοματοποιημένο</span>' : '') . '</small>';
                }
                echo '<div class="divider"></div>';
                echo '<p id="description_' . $row['id'] . '">' . htmlspecialchars($row['description']) . '</p><br/>';
                echo '<div id="dev_' . $row['id'] . '" class="m-t-10"></div>';
                echo '<a id="download_btn_' . $row['id'] . '" class="button center" href="' . htmlspecialchars($row['file_path']) . '">Κατέβασε το αρχείο (' . getFileExtension($row['file_path']) . ')</a>';
                echo '<div class="edit-form" id="edit_' . $row['id'] . '" style="display:none;">';
                echo '<form action="#" method="post" enctype="multipart/form-data">';
                echo '<input type="hidden" name="edit_id" value="' . $row['id'] . '">';
                echo '<input placeholder="Τίτλος" type="text" name="title" value="' . $row['title'] . '" required>';
                echo '<textarea placeholder="Περιγραφή" name="description" required>' . $row['description'] . '</textarea>';
                echo '<input type="file" name="fileToUpload" id="fileToUpload">';
                echo '<button type="submit" name="submit">Αποθήκευση</button>';
                echo '<button type="button" onclick="cancelEdit(' . $row['id'] . ')">Ακύρωση</button>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>

    </div>

</div>

<script>
    function editSubject(id) {
        $(`#edit_${id}`).show();
        $(`#title_${id}`).hide();
        $(`#description_${id}`).hide();
        $(`#download_btn_${id}`).hide();
        $(`#dev_${id}`).hide();
    }

    function cancelEdit(id) {
        $(`#edit_${id}`).hide();
        $(`#title_${id}`).show();
        $(`#description_${id}`).show();
        $(`#download_btn_${id}`).show();
        $(`#dev_${id}`).show();
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

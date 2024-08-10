<?php
session_start();

if (!isset($_SESSION['login_user'])) {
    header("location: login.php");
    exit();
}

require_once("config.php");
$login_user = $_SESSION['login_user'];

function getUsers($db)
{
    $stmt = $db->prepare("SELECT * FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $users;
}

function getTeachers($db)
{
    $stmt = $db->prepare("SELECT * FROM users WHERE role=0");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $users;
}

function getStudents($db)
{
    $stmt = $db->prepare("SELECT * FROM users WHERE role=1");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $users;
}

function updateUser($db, $id, $name, $lastname, $password, $role, $email)
{
    $stmt = $db->prepare("UPDATE users SET name=?, lastname=?, password=?, role=?, email=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $lastname, $password, $role, $email, $id);
    $stmt->execute();
    $stmt->close();
}

function deleteUser($db, $id)
{
    $stmt = $db->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $email = $_POST['email'];

    updateUser($db, $id, $name, $lastname, $password, $role, $email);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    deleteUser($db, $id);
    header("Location: users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && isAdmin($db, $login_user)) {
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $error = "Το όνομα χρήστη ή το email υπάρχει ήδη στο σύστημα.";
    } else {
        $stmt = $db->prepare("INSERT INTO users (name, lastname, username, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $lastname, $username, $email, $password, $role);
        $stmt->execute();
        $stmt->close();
        header("Location: users.php");
        exit();
    }
}

$allUsers = getUsers($db);
$teachers = getTeachers($db);
$students = getStudents($db);
$isAdmin = isAdmin($db, $login_user);

?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Χρηστών - Μαθηματική Ανάλυση</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .isOn {
            background-color: rgba(76, 175, 80, 0.3);
        }
    </style>
</head>
<body>

<?php include 'common/user_info.php'; ?>

<div class="to-top" onclick="scrollToTop()">
    <a href="#">Επιστροφή στην κορυφή</a>
</div>

<div class="header" id="top">
    <h1>Πίνακας Διαχείρισης Χρηστών</h1>
</div>

<div class="flex-content">
    <div class="sidebar">
        <ul>
            <li><a href="index.php">Αρχική</a></li>
            <li><a href="announcements.php">Ανακοινώσεις</a></li>
            <li><a href="contact.php">Επικοινωνία</a></li>
            <li><a href="documents.php">Έγγραφα</a></li>
            <li><a href="homework.php">Εργασίες</a></li>
        </ul>
    </div>
    <div class="content">
        <?php if ($isAdmin) { ?>
            <h2>Προσθήκη Νέου Χρήστη</h2>
            <p>Συμπληρώστε την παρακάτω φόρμα για να προσθέσετε έναν νέο χρήστη στην πλατφόρμα.</p>

            <div class="m-t-20"></div>

            <div class="card-view">
                <div class="card">
                    <h2>Φόρμα Προσθήκης Χρήστη</h2>
                    <small><li class="fas fa-info-circle"></li> Το όνομα χρήστη πρέπει να είναι μοναδικό και δεν μπορεί να αλλάξει μετά την εγγραφή.</small>
                    <div class="m-t-20"></div>
                    <form action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>' method='post'>
                        <input type="text" name="name" placeholder="Όνομα (Προαιρετικό)">
                        <input type="text" name="lastname" placeholder="Επώνυμο (Προαιρετικό)">
                        <input type="text" name="username" placeholder="Όνομα Χρήστη (username)" required>
                        <input type="email" name="email" placeholder="Email (Προαιρετικό)">
                        <input type="text" name="password" placeholder="Κωδικός Πρόσβασης" required>
                        <select name="role" required>
                            <option value="0">Εκπαιδευτικός</option>
                            <option value="1">Φοιτητής</option>
                        </select>
                        <div class="m-t-5"></div>
                        <?php if (isset($error)) { ?>
                            <p class="danger"><?= $error ?></p>
                        <?php } ?>
                        <div class="m-t-5"></div>
                        <input class="button" type="submit" name="add" value="Προσθήκη">
                    </form>
                </div>
            </div>
        <?php } ?>


        <?php if (!$isAdmin) { ?>
        <h2>Περιορισμένη Πρόσβαση</h2>
        <p>Μόνο οι διαχειριστές έχουν πρόσβαση σε αυτή τη σελίδα. Παρακαλώ επιλέξτε μία από τις παρακάτω επιλογές για να συνεχίσετε την περιήγησή σας.</p>
        <?php include 'common/choices.php'; } ?>

        <?php if (!empty($teachers) && $isAdmin) { ?>
        <div class="m-t-b-30"></div>

        <h2>Λίστα Εκπαιδευτικών</h2>
        <p>Ακολουθεί η λίστα με όλους τους εγγεγραμμένους εκπαιδευτικούς στην πλατφόρμα.</p>
        <div class="card-view">
            <?php foreach ($teachers as $user) { ?>
                <div class='card' id="user_card_<?= $user['id'] ?>">
                    <div id='user_<?= $user['id'] ?>'>
                        <h2>
                            <span id='name_<?= $user['id'] ?>'><?= $user['name'] ?></span>
                            <span id='lastname_<?= $user['id'] ?>'><?= $user['lastname'] ?></span>
                        </h2>
                        <small>
                            <span id='username_<?= $user['id'] ?>'>@<?= $user['username'] ?></span>
                            • <a href='#user_card_<?= $user['id'] ?>' onclick='editUser(<?= $user['id'] ?>)'>Επεξεργασία</a>
                            • <a href='users.php?delete=<?= $user['id'] ?>'>Διαγραφή</a>
                        </small>
                        <div class='divider'></div>
                        <p id='name_<?= $user['id'] ?>'>
                            <li class='fas fa-user'></li>
                            <b>Ονοματεπώνυμο:</b>
                            <?= (!empty($user['name']) || !empty($user['lastname'])) ? $user['name'] . ' ' . $user['lastname'] : 'Δεν έχει οριστεί' ?>
                        </p>
                        <p id='email_<?= $user['id'] ?>'>
                            <li class='fas fa-envelope'></li>
                            <b>Email:</b>
                            <?= (!empty($user['email'])) ? $user['email'] : 'Δεν έχει οριστεί' ?>
                        </p>
                        <p id='password_<?= $user['id'] ?>'>
                            <li class='fas fa-lock'></li>
                            <b>Κωδικός Πρόσβασης:</b>
                            <?= $user['password'] ?>
                        </p>
                    </div>

                    <div id="edit-user_<?= $user['id'] ?>" style="display: none;">
                        <p>
                            <li class='fas fa-user'></li>
                            Επεξεργασία Χρήστη @<?= $user['username'] ?></p>
                        <div class="m-t-5"></div>
                        <small>
                            <li class="fas fa-info-circle"></li>
                            Δεν μπορείτε να τροποποιήσετε το όνομα χρήστη.</small>
                        <div class="m-t-20"></div>
                        <form action="" method="post">
                            <input type="hidden" name="id" value="<?= $user['id']; ?>">
                            <input type="text" name="name" id="edit-name" placeholder="Όνομα"
                                   value="<?= $user['name'] ?>">
                            <input type="text" name="lastname" id="edit-lastname" placeholder="Επώνυμο"
                                   value="<?= $user['lastname'] ?>">
                            <input type="email" name="email" id="edit-email" placeholder="Email"
                                   value="<?= $user['email'] ?>">
                            <input type="text" name="password" id="edit-password" placeholder="Κωδικός"
                                   value="<?= $user['password'] ?>">
                            <select name="role" id="edit-role">
                                <option value="0" <?= ($user['role'] == 0) ? 'selected' : '' ?>>Εκπαιδευτικός</option>
                                <option value="1" <?= ($user['role'] == 1) ? 'selected' : '' ?>>Φοιτητής</option>
                            </select>
                            <input class="button" type="submit" name="edit" value="Αποθήκευση">
                            <input class="button" onclick="cancelEdit(<?= $user['id'] ?>)" type="button"
                                   value="Ακύρωση">
                        </form>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if (!empty($students) && $isAdmin) { ?>
        <div class="m-t-b-30"></div>

        <h2>Λίστα Φοιτητών</h2>
        <p>Ακολουθεί η λίστα με όλους τους εγγεγραμμένους φοιτητές στην πλατφόρμα.</p>
        <div class="card-view">
            <?php foreach ($students as $user) { ?>
                <div class='card' id="user_card_<?= $user['id'] ?>">
                    <div id='user_<?= $user['id'] ?>'>
                        <h2>
                            <span id='name_<?= $user['id'] ?>'><?= $user['name'] ?></span>
                            <span id='lastname_<?= $user['id'] ?>'><?= $user['lastname'] ?></span>
                        </h2>
                        <small>
                            <span id='username_<?= $user['id'] ?>'>@<?= $user['username'] ?></span>
                            • <a href='#user_card_<?= $user['id'] ?>' onclick='editUser(<?= $user['id'] ?>)'>Επεξεργασία</a>
                            • <a href='users.php?delete=<?= $user['id'] ?>'>Διαγραφή</a>
                        </small>
                        <div class='divider'></div>
                        <p id='name_<?= $user['id'] ?>'>
                            <li class='fas fa-user'></li>
                            <b>Ονοματεπώνυμο:</b>
                            <?= (!empty($user['name']) || !empty($user['lastname'])) ? $user['name'] . ' ' . $user['lastname'] : 'Δεν έχει οριστεί' ?>
                        </p>
                        <p id='email_<?= $user['id'] ?>'>
                            <li class='fas fa-envelope'></li>
                            <b>Email:</b>
                            <?= (!empty($user['email'])) ? $user['email'] : 'Δεν έχει οριστεί' ?>
                        </p>
                        <p id='password_<?= $user['id'] ?>'>
                            <li class='fas fa-lock'></li>
                            <b>Κωδικός Πρόσβασης:</b>
                            <?= $user['password'] ?>
                        </p>
                    </div>

                    <div id="edit-user_<?= $user['id'] ?>" style="display: none;">
                        <p>
                            <li class='fas fa-user'></li>
                            Επεξεργασία Χρήστη @<?= $user['username'] ?></p>
                        <div class="m-t-5"></div>
                        <small>
                            <li class="fas fa-info-circle"></li>
                            Δεν μπορείτε να τροποποιήσετε το όνομα χρήστη.</small>
                        <div class="m-t-20"></div>
                        <form action="" method="post">
                            <input type="hidden" name="id" value="<?= $user['id']; ?>">
                            <input type="text" name="name" id="edit-name" placeholder="Όνομα"
                                   value="<?= $user['name'] ?>">
                            <input type="text" name="lastname" id="edit-lastname" placeholder="Επώνυμο"
                                   value="<?= $user['lastname'] ?>">
                            <input type="email" name="email" id="edit-email" placeholder="Email"
                                   value="<?= $user['email'] ?>">
                            <input type="text" name="password" id="edit-password" placeholder="Κωδικός"
                                   value="<?= $user['password'] ?>">
                            <select name="role" id="edit-role">
                                <option value="0" <?= ($user['role'] == 0) ? 'selected' : '' ?>>Εκπαιδευτικός</option>
                                <option value="1" <?= ($user['role'] == 1) ? 'selected' : '' ?>>Φοιτητής</option>
                            </select>
                            <input class="button" type="submit" name="edit" value="Αποθήκευση">
                            <input class="button" onclick="cancelEdit(<?= $user['id'] ?>)" type="button"
                                   value="Ακύρωση">
                        </form>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>
</div>

<script>
    function editUser(id) {
        $(`#user_${id}`).hide();
        $(`#edit-user_${id}`).show();
    }

    function cancelEdit(id) {
        $(`#user_${id}`).show();
        $(`#edit-user_${id}`).hide();
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

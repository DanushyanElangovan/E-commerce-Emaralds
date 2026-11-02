<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';


$pageTitle = "Add New User";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);

    // Validation
    $errors = [];

    if (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords don't match";
    }

    // Check if username/email exists
    $stmt = $pdo->prepare("SELECT userID FROM user WHERE username = ? OR Email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $errors[] = "Username or email already exists";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $pdo->prepare("INSERT INTO user (username, password, Email, Firstname, Lastname, Role, accountCreationDate) 
                      VALUES (?, ?, ?, ?, ?, 'customer', NOW())")
            ->execute([$username, $hashedPassword, $email, $firstname, $lastname]);

        $userId = $pdo->lastInsertId();

        // Also create customer record
        $pdo->prepare("INSERT INTO customer (username, password, firstname, lastname, Email, accountCreatedDate) 
                      VALUES (?, ?, ?, ?, ?, NOW())")
            ->execute([$username, $hashedPassword, $firstname, $lastname, $email]);

        header("Location: ./users.php");
        exit();
    }
}
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Add new User</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-danger">Add New User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
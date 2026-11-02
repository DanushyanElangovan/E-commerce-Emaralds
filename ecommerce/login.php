<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once './includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ./");
    exit();
}

$pageTitle = "Login";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ? OR Email = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['userID'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['Role'] = $user['Role'];

        // Update last login
        $pdo->prepare("UPDATE user SET Lastlogin = NOW() WHERE userID = ?")->execute([$user['userID']]);

        if ($user['Status'] == "active"){
            if ($user['Role'] == "System_admin") {
                header("Location: ./admin/dashboard.php");
            } else {
                header("Location: ./");
            }
            exit();
        }
        else{
            $error = "You are inactive user";
        }

       
    } else {
        $error = "Invalid username or password";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Login</h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-danger">Login</button>
                    <a href="./register.php" class="btn btn-link">Register</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
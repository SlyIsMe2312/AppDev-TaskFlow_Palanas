<?php
include '../config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $password);
    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error creating account";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .signup-container { max-width: 400px; margin: auto; padding-top: 100px; }
        .card { padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>
    <div class="container signup-container">
        <div class="card">
            <h2 class="text-center">Sign Up</h2>
            <form method="POST" action="signup.php">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Sign Up</button>
                <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
                <?php if (isset($error)) echo "<p class='text-danger text-center'>$error</p>"; ?>
            </form>
        </div>
    </div>
</body>
</html>

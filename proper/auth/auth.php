<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../config.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($action == "login") {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: ../dashboard/to-do.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    } elseif ($action == "signup") {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $hashed_password);
            if ($stmt->execute()) {
                header("Location: auth.php?success=registered");
                exit();
            } else {
                $error = "Error creating account";
            }
        }
    }
} elseif (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/to-do.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Auth | TaskFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .auth-container { max-width: 400px; margin: auto; padding-top: 100px; }
        .card { padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>
    <div class="container auth-container">
        <div class="card">
            <h2 class="text-center" id="form-title">Login</h2>
            <form method="POST" action="auth.php">
                <input type="hidden" name="action" id="form-action" value="login">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <p class="mt-3 text-center">
                    Don't have an account? <a href="#" onclick="toggleForm('signup')">Sign up</a>
                </p>
                <?php if (!empty($error)) echo "<p class='text-danger text-center'>$error</p>"; ?>
            </form>
        </div>
    </div>
    <script>
        function toggleForm(type) {
            document.getElementById("form-action").value = type;
            document.getElementById("form-title").innerText = type === "login" ? "Login" : "Sign Up";
            document.querySelector("button[type='submit']").innerText = type === "login" ? "Login" : "Sign Up";
            document.querySelector("p").innerHTML = type === "login" ? 
                "Don't have an account? <a href='#' onclick='toggleForm(\"signup\")'>Sign up</a>" : 
                "Already have an account? <a href='#' onclick='toggleForm(\"login\")'>Login</a>";
        }
    </script>
</body>
</html>
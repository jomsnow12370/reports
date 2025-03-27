<?php
session_start();

// Database connection
include("conn.php");

// Login processing
$login_error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $username = mysqli_real_escape_string($c, $_POST['username']);
    $password = $_POST['password'];

    // Prepare SQL to prevent SQL injection
    $stmt = $c->prepare("SELECT user_id, username, password FROM userstbl WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Directly compare the password without password_verify()
        if ($password === $user['password']) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect to dashboard
            header("Location: index.php");
            exit();
        } else {
            // Invalid password
            $login_error = "Invalid username or password";
        }
    } else {
        // User not found
        $login_error = "Invalid username or password";
    }

    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Election Dashboard</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
    body {
        font-family: 'Montserrat', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        background-color: #212529;
    }

    .login-container {
        max-width: 400px;
        width: 100%;
        padding: 2rem;
        border-radius: 8px;
        background-color: #343a40;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }

    .login-header {
        text-align: center;
        margin-bottom: 1.5rem;
        color: #6ea8fe;
    }

    .form-control {
        background-color: #495057;
        border-color: #6c757d;
        color: #f8f9fa;
    }

    .form-control:focus {
        background-color: #495057;
        border-color: #6ea8fe;
        box-shadow: 0 0 0 0.2rem rgba(110, 168, 254, 0.25);
        color: #f8f9fa;
    }

    .btn-primary {
        background-color: #6ea8fe;
        border-color: #6ea8fe;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background-color: #4a90fe;
        border-color: #4a90fe;
        transform: translateY(-2px);
    }

    .login-footer {
        text-align: center;
        margin-top: 1rem;
        color: #6c757d;
    }

    .error-message {
        color: #ea868f;
        text-align: center;
        margin-bottom: 1rem;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h2>2025 Cat's Eye Dashboard</h2>
            <p class="text-muted">Sign in to continue</p>
        </div>

        <?php 
        if (!empty($login_error)) {
            echo "<div class='error-message'>" . htmlspecialchars($login_error) . "</div>";
        }
        ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-secondary text-light border-secondary"><i
                            class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username"
                        required>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-secondary text-light border-secondary"><i
                            class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter password" required>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Login</button>
            </div>
        </form>
        <div class="login-footer">
            <p class="small">&copy; 2025 Cat's-eye Dashboard.</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
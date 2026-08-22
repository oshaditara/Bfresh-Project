<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "bfresh_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$script = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT User_ID, Name, Password, Role FROM user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
        
        if (password_verify($pass, $user_data['Password'])) {
            $_SESSION['user_id'] = $user_data['User_ID'];
            $_SESSION['name'] = $user_data['Name'];
            $_SESSION['role'] = $user_data['Role'];

            $checkRole = strtolower(trim($user_data['Role']));
            $redirect_page = ($checkRole === 'admin') ? "admin_dashboard.php" : "customer_dashboard.php";
            
            $script = "Swal.fire({ 
                title: 'Success!', 
                text: 'Welcome back, " . $user_data['Name'] . "!', 
                icon: 'success', 
                timer: 1500, 
                showConfirmButton: false 
            }).then(() => { 
                window.location.href = '$redirect_page'; 
            });";
            
        } else {
            $script = "Swal.fire('Login Failed', 'Incorrect password!', 'error');";
        }
    } else {
        $script = "Swal.fire('Login Failed', 'No account found with this email.', 'warning');";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bfresh | Secure Access</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root { 
            --accent: #ffc107; 
            --fresh: #ff9800; 
            --dark: #191a1b; 
            --glass: rgba(0, 0, 0, 0.4); /* වීදුරු ගතිය අඩු කළා වීඩියෝව පේන්න */
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body { 
            font-family: 'Outfit', sans-serif; 
            margin: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #000;
        }

        /* --- Background Video - Max Visibility --- */
        #bg-video {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -2;
            object-fit: cover;
            filter: brightness(0.9); /* වීඩියෝව ඉතා පැහැදිලිව පෙනෙන්න */
        }

        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.2); /* අඳුරු ගතිය ගොඩක් අඩු කළා */
            z-index: -1;
        }

        .navbar { 
            backdrop-filter: blur(10px);
            background: rgba(0, 0, 0, 0.4) !important; /* Navbar එක විනිවිද පෙනෙන ලෙස */
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 10;
        }
        .navbar-brand { font-size: 1.8rem; font-weight: 800; color: var(--accent) !important; }
        .nav-link { font-weight: 600; color: white !important; }

        .login-container { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            flex-grow: 1;
            padding: 20px;
            z-index: 1;
        }

        /* Glassmorphism Auth Card - Slim & Attractive */
        .auth-card { 
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(15px);
            padding: 40px; 
            border-radius: 40px; 
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5); 
            width: 100%; 
            max-width: 400px; /* Card එකේ ප්‍රමාණය ටිකක් අඩු කළා */
            color: white;
            text-align: center;
        }

        .form-control { 
            background: rgba(255, 255, 255, 0.1); 
            border: 1px solid rgba(255, 255, 255, 0.2); 
            padding: 12px 20px; 
            border-radius: 15px; 
            margin-bottom: 20px; 
            color: white !important;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--accent);
            box-shadow: none;
            outline: none;
        }

        .btn-login { 
            background: var(--accent); 
            color: var(--dark); 
            padding: 15px; 
            border-radius: 15px; 
            font-weight: 800; 
            text-transform: uppercase;
            transition: 0.3s; 
            border: none; 
            width: 100%; 
        }
        .btn-login:hover { 
            background: white; 
            transform: scale(1.02);
        }

        .register-link { color: var(--accent); font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

    <!-- Background Video -->
    <video autoplay muted loop playsinline id="bg-video">
        <source src="uploads/slidesmp_.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">Bfresh<span>.</span></a>
            <div class="ms-auto">
                <a class="nav-link text-white text-decoration-none fw-bold" href="index.php">Home</a>
            </div>
        </div>
    </nav>

    <div class="login-container">
        <div class="auth-card">
            <div class="mb-4">
                <h2 class="fw-bold">Welcome Back</h2>
                <p class="text-white-50 small">Experience the real taste of nature.</p>
            </div>
            
            <form action="" method="POST">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <button type="submit" class="btn btn-login">Login Now</button>
            </form>
            
            <p class="mt-4 small text-white-50">New to Bfresh? <a href="register.php" class="register-link">Sign Up</a></p>
        </div>
    </div>

    <script>
        <?php if (!empty($script)) echo $script; ?>
    </script>
</body>
</html>
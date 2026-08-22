<?php

include 'functions.php'; //Linking a file with the SMS function

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
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = $_POST['address'];
    $dob = $_POST['dob'];

    // --- Validations ---
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $script = "Swal.fire({ title: 'Error', text: 'Invalid email format!', icon: 'error' });";
    } 
    else if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $script = "Swal.fire({ title: 'Error', text: 'Phone number must be 10 digits!', icon: 'error' });";
    }
    else if (strlen($pass) < 8) {
        $script = "Swal.fire({ title: 'Error', text: 'Password must be at least 8 characters!', icon: 'error' });";
    }
    else if ($pass !== $confirm_pass) {
        $script = "Swal.fire({ title: 'Error', text: 'Passwords do not match!', icon: 'error' });";
    } 
    else {
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        $role = ($email === "admin@gmail.com" && $pass === "admin1") ? 'admin' : 'customer';

        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("INSERT INTO user (Name, Email, Password, Phone_Number, Role) VALUES (?, ?, ?, ?, ?)");
            $stmt1->bind_param("sssss", $name, $email, $hashed_password, $phone, $role);
            $stmt1->execute();
            
            $user_id = $conn->insert_id;

            if ($role === 'customer') {
                $stmt2 = $conn->prepare("INSERT INTO customer (Customer_ID, Address, DOB, Loyalty_Points) VALUES (?, ?, ?, 0)");
                $stmt2->bind_param("iss", $user_id, $address, $dob);
                $stmt2->execute();
            }

            $conn->commit();

            // ---SMS Notification Sender Section (Updated) ---
            // Display Name Beautifully
            $formattedName = ucwords(strtolower($name)); 

            // Preparing the message
            $smsMessage = "Hi $formattedName, Welcome to Bfresh! Your account is now active.";

            sendLiveSMS($phone, $smsMessage); 

            $script = "Swal.fire({ title: 'Success', text: 'Account created and Welcome SMS Sent!', icon: 'success' }).then(() => { window.location.href = 'login.php'; });";

        } catch (Exception $e) {
            $conn->rollback();
            $script = "Swal.fire({ title: 'Error', text: 'Registration failed. Email exists!', icon: 'error' });";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bfresh | Join Our Community</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { 
            --accent: #ffc107; 
            --fresh: #ff9800; 
            --dark: #191a1b; 
            --glass-white: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.15);
        }

        body { 
            font-family: 'Outfit', sans-serif; 
            background: #191a1c;
            color: white;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Mesh Background */
        .bg-mesh {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 80% 20%, rgba(255, 152, 0, 0.1) 0%, transparent 40%),
                        radial-gradient(circle at 20% 80%, rgba(255, 193, 7, 0.08) 0%, transparent 40%);
        }

        /* Navbar */
        .navbar { 
            backdrop-filter: blur(15px);
            background: rgba(17, 18, 20, 0.8) !important;
            padding: 15px 0;
            border-bottom: 1px solid var(--glass-border);
        }
        .navbar-brand { font-size: 1.8rem; font-weight: 800; color: var(--accent) !important; }
        .nav-link { font-weight: 600; color: #cbd5e1 !important; transition: 0.3s; }

        .register-container { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 60px 20px;
            flex-grow: 1;
        }

        /* Glass Auth Card */
        .register-card { 
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(30px);
            padding: 45px; 
            border-radius: 40px; 
            border: 1px solid var(--glass-border);
            width: 100%; 
            max-width: 650px; 
            box-shadow: 0 40px 100px rgba(0,0,0,0.4); 
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-control { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid var(--glass-border); 
            padding: 14px 18px; 
            border-radius: 16px; 
            margin-bottom: 18px; 
            color: white !important;
            transition: all 0.3s;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.15);
            outline: none;
        }
        .form-control::placeholder { color: #94a3b8; font-size: 0.9rem; }

        .btn-register { 
            background: var(--accent); 
            color: var(--dark); 
            border-radius: 16px; 
            font-weight: 800; 
            padding: 16px; 
            transition: 0.4s; 
            border: none; 
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        .btn-register:hover { 
            background: yellow; 
            transform: translateY(-5px); 
            box-shadow: 0 15px 30px rgba(255, 193, 7, 0.2);
        }

        label { color: #94a3b8; font-weight: 600; font-size: 0.85rem; margin-left: 5px; }

        .circle-bg {
            position: absolute;
            border-radius: 50%;
            background: var(--accent);
            filter: blur(100px);
            opacity: 0.05;
            z-index: -1;
        }
    </style>
</head>
<body>

    <div class="bg-mesh"></div>
    <div class="circle-bg" style="width: 400px; height: 400px; top: 10%; right: -100px;"></div>
    <div class="circle-bg" style="width: 300px; height: 300px; bottom: 5%; left: -50px;"></div>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Bfresh<span>.</span></a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav gap-3 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-warning rounded-pill px-4 fw-bold" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="register-container">
        <div class="register-card">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Create Account</h2>
                <p class="text-secondary small">Join the community for exclusive fresh rewards.</p>
            </div>
            
            <form action="" method="POST">
    <div class="row">
        <div class="col-md-12">
            <input type="text" name="name" class="form-control" placeholder="Full Name" 
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
        </div>
        <div class="col-md-6">
            <input type="email" name="email" class="form-control" placeholder="Email Address" 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>
        <div class="col-md-6">
            <input type="tel" name="phone" class="form-control" placeholder="Phone (10 digits)" 
                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
        </div>
        <div class="col-md-12">
            <input type="text" name="address" class="form-control" placeholder="Home Address" 
                   value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
        </div>
        <div class="col-md-12">
            <label>Date of Birth</label>
            <input type="date" name="dob" class="form-control" 
                   value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" required>
        </div>
        <div class="col-md-6">
            <input type="password" name="password" class="form-control" placeholder="Password (min 8)" required>
        </div>
        <div class="col-md-6">
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
        </div>
    </div>
    <button type="submit" class="btn btn-register w-100 shadow-lg">Register Now</button>
</form>
            
            <div class="text-center mt-4">
                <p class="small text-secondary">By signing up, you agree to our <a href="#" class="text-warning text-decoration-none fw-bold">Terms of Service</a>.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (!empty($script)) echo $script; ?>
        document.querySelector('form').addEventListener('submit', function(e) {
    const phone = document.getElementsByName('phone')[0].value;
    const pass = document.getElementsByName('password')[0].value;
    const confirmPass = document.getElementsByName('confirm_password')[0].value;

    let errorMsg = "";

    if (phone.length !== 10 || isNaN(phone)) {
        errorMsg = "Phone number must be exactly 10 digits!";
    } else if (pass.length < 8) {
        errorMsg = "Password must be at least 8 characters!";
    } else if (pass !== confirmPass) {
        errorMsg = "Passwords do not match!";
    }

    if (errorMsg !== "") {
        e.preventDefault(); //The form will stop submitting.
        Swal.fire({
            title: 'Error',
            text: errorMsg,
            icon: 'error'
        });
    }
});
    </script>
</body>
</html>
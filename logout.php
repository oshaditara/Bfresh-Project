<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #010b14; }
    </style>
</head>
<body>
    <script>
        Swal.fire({
            title: 'Logged Out!',
            text: 'Success Logout',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            willClose: () => {
                window.location.href = 'login.php';
            }
        });
    </script>
</body>
</html>
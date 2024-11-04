<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background-color: #111111;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .swal2-popup {
            background-color: #1E1E1E !important;
            border-radius: 10px !important;
        }
        .swal2-title {
            color: #3D63DD !important;
        }
        .swal2-html-container {
            color: #8B8D98 !important;
        }
        .swal2-confirm {
            background-color: #3D63DD !important;
            color: #FFFFFF !important;
            border-radius: 5px !important;
            padding: 10px 24px !important;
            font-size: 16px !important;
            font-weight: bold !important;
            border: none !important;
            box-shadow: 0 2px 4px rgba(61, 99, 221, 0.3) !important;
        }
    </style>
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Logged Out',
                text: 'You have successfully logged out.',
                icon: 'success',
                confirmButtonText: 'OK',
                backdrop: `rgba(17, 17, 17, 0.8)`,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then(function() {
                window.location.href = "login.php";
            });
        });
    </script>
</body>
</html>
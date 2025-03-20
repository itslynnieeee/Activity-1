<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "registration_db";

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } 
    // Validate matching passwords
    elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } 
    else {
        // Check if email exists
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Encrypt password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert into database
            $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $hashedPassword);
            
            if ($stmt->execute()) {
                $success = "Registration successful!";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $checkEmail->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* Import Google Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        /* General Styling */
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #f9c5d1, #fbc2eb);
            margin: 0;
        }

        /* Container */
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
            width: 350px;
        }

        /* Heading */
        h2 {
            margin-bottom: 20px;
            color: #d63384;
            font-weight: 600;
        }

        /* Input Fields */
        input {
            width: 80%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #f9c5d1;
            border-radius: 8px;
            outline: none;
            font-size: 16px;
        }

        input:focus {
            border-color: #d63384;
            box-shadow: 0 0 8px rgba(214, 51, 132, 0.3);
        }

        /* Button */
        button {
            width: 90%;
            padding: 12px;
            border: none;
            background: #d63384;
            color: white;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #b62b70;
        }

        /* Error and Success Messages */
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        .success {
            color: green;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

        <form id="registerForm" method="POST" action="">
            <input type="email" name="email" id="email" placeholder="Email" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
            <p id="errorMessage" class="error"></p>
        </form>
    </div>

    <script>
        document.getElementById("registerForm").addEventListener("submit", function(event) {
            let password = document.getElementById("password").value;
            let confirmPassword = document.getElementById("confirm_password").value;
            let errorMessage = document.getElementById("errorMessage");

            if (password !== confirmPassword) {
                event.preventDefault();
                errorMessage.textContent = "Passwords do not match!";
            } else {
                errorMessage.textContent = "";
            }
        });
    </script>
</body>
</html>

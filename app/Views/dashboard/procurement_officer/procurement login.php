<?php
// ================= DATABASE CONNECTION =================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "procurement_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed");
}

$message = "";

// ================= REGISTER LOGIC =================
if (isset($_POST['register'])) {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if (empty($fullname) || empty($username) || empty($password) || empty($confirm)) {
        $message = "<span style='color:red'>All fields are required</span>";
    } elseif ($password !== $confirm) {
        $message = "<span style='color:red'>Passwords do not match</span>";
    } else {

        // check if username exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "<span style='color:red'>Username already exists</span>";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (fullname, username, password) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $fullname, $username, $hashed);

            if ($stmt->execute()) {
                $message = "<span style='color:green'>Registration successful!</span>";
            } else {
                $message = "<span style='color:red'>Registration failed</span>";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register Procurement Officer</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f2f4f7;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    background: white;
    padding: 30px;
    width: 380px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

label {
    font-weight: bold;
    margin-top: 15px;
    display: block;
}

input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

button {
    width: 100%;
    margin-top: 20px;
    padding: 12px;
    background: #27ae60;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #1e8449;
}

.msg {
    text-align: center;
    margin-top: 15px;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="container">
    <h2>Register Procurement Officer</h2>

    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="fullname">

        <label>Username</label>
        <input type="text" name="username">

        <label>Password</label>
        <input type="password" name="password">

        <label>Confirm Password</label>
        <input type="password" name="confirm">

        <button type="submit" name="register">Register</button>
    </form>

    <div class="msg"><?php echo $message; ?></div>
</div>

</body>
</html>

<?php
session_start();
include_once('../db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jde = mysqli_real_escape_string($conn, $_POST['jde']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE jde='$jde' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['jde'] = $user['jde'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_activity'] = time();
            header("Location: ../index.php");
            exit();
        }
    }
    $error = "JDE or password is incorrect.";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

body {
  background: url('../pictures/allbackground.png') no-repeat center center fixed,
  linear-gradient(to bottom, #FFE0C0, #FF6600);
  background-size: cover;
  font-family: Arial, sans-serif;
}

/* Banner kanan atas */
.top-banner {
  position: absolute;
  top: 2vh;
  right: 2vw;
  color: #FF6600;
  font-size: 2vw;
  font-weight: bold;
  line-height: 1.2;
}

/* Login container kiri tengah */
.login-container {
  position: absolute;
  top: 50%;
  left: 7vw;
  transform: translateY(-50%);
  display: flex;
  gap: 2vw;
}

/* Input group */
.input-group {
  display: flex;
  flex-direction: column;
  gap: 2vh;
}

/* Input styling */
.input-underline {
  background: transparent;
  border: none;
  border-bottom: 0.5vh solid orange;
  padding: 1vh 0.5vw;
  width: 10vw;
  font-size: 0.7vw;
  color: #fff;
  font-weight: bold;
  outline: none;
}

.input-underline::placeholder {
  color: rgba(255,255,255,0.7);
}

/* Login text */
.login-text {
  color: orange;
  font-weight: bold;
  font-size: 1vw;
}

/* Button */
.login-button {
  background: none;
  border: none;
  cursor: pointer;
}

.login-button img {
  width: 2.5vw;
  height: 2.5vw;
}

/* Error message */
.error-message {
  color: red;
  font-weight: bold;
  font-size: 1vw;
}

/* Login action */
.login-action {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2vh;
  margin-top: 2vh;
}

/* Footer bawah tengah */
.footer {
  position: absolute;
  bottom: 2vh;
  left: 50%;
  transform: translateX(-50%);
  color: #FFFFFF;
  font-size: 0.7vw;
  text-align: center;
}
</style>
</head>
<body>

<div class="top-banner">
  Rewrite<br>
  the<br>
  Future
</div>

<form method="post">
  <div class="login-container">
    <div class="input-group">
      <?php if (!empty($error)) echo "<div class='error-message'>$error</div>"; ?>
      <input type="text" name="jde" class="input-underline" placeholder="User Name" required>
      <input type="password" name="password" class="input-underline" placeholder="Password" required>
    </div>
    <div class="login-action">
      <div class="login-text">LOGIN</div>
      <button type="submit" class="login-button" title="Click to Login">
        <img src="../pictures/button_login.ico" alt="Login">
      </button>
    </div>
  </div>
</form>

<div class="footer">
  © 2025, IT Infrastructure - PTDH KCP. All Right Reserved
</div>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /dt-infra-kcp/users/user_login.php");
    exit();
}
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    header("Location: /dt-infra-kcp/users/user_login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();
?>

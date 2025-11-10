<?php
session_start();
session_unset();
session_destroy();
header("Location: /dt-infra-kcp-update/users/user_login.php");
exit();
?>
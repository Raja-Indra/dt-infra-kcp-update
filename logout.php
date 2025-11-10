<?php
session_start();
session_unset();
session_destroy();
header("Location: /dt-infra-kcp/users/user_login.php");
exit();
?>
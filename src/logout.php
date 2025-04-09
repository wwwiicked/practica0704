<?php
session_start();

unset($_SESSION['user']);

header("Location: http://localhost/pr_0704/login.html");
?>
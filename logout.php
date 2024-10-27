<?php
require_once 'functions.php';

session_destroy();
setcookie('remember_token', '', time() - 3600, '/', '', true, true);
header("Location: index.php");
exit();
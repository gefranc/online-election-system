<?php
session_start();
session_unset();
session_destroy();
header("Location: candidate_login.php");
exit();

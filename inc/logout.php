<?php

session_start();
unset($_SESSION['name']);
unset($_SESSION['pass']);
$_SESSION['out'] = 'logged out';

header('Location: ../index.php');

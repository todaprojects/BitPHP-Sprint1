<?php

session_start();

/** login username & password*/
$name = 'demo';
$pass = 'demo';

if (isset($_POST['name']) && isset($_POST['pass'])) {
    $n = htmlspecialchars($_POST['name']);
    $p = htmlspecialchars($_POST['pass']);
    if ($n == $name && $p == $pass) {
        $_SESSION['name'] = true;
        $_SESSION['pass'] = true;
    } else if(empty($n) || empty($p)) {
        $_SESSION['error'] = 'enter username and password';
    } else {
        $_SESSION['error'] = 'wrong username or password';
    }
}

header('Location: ../index.php');

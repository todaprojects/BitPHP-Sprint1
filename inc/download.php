<?php

if (isset($_GET['download'])) {
    ob_clean();
    ob_start();
    header('Content-Description: File Transfer');
    header('Content-Type: ' . filetype($_GET['fileName']));
    header('Content-Disposition: attachment; filename=' . basename($_GET['fileName']));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($_GET['fileName']));
    ob_end_flush();
    readfile($_GET['fileName']);
    exit;
} else {
    header('Location: ../index.php');
}

?>
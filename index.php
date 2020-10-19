<?php
session_start();
if (isset($_GET['logout'])) {
    header('Location: inc/logout.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Sprint 1</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php
    if (!isset($_SESSION['name']) && !isset($_SESSION['pass'])) {
    ?>
        <main>
            <?php
            if (isset($_SESSION['error'])) {
                print('<div class="wrong-name">' . $_SESSION['error'] . '</div>');
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['out'])) {
                print('<div class="log-out">' . $_SESSION['out'] . '</div>');
                unset($_SESSION['out']);
            }
            ?>
            <form action="inc/login.php" method="post">
                <label for="name">Name:</label><br>
                <input type="text" name="name" id="name" value=""><br>
                <label for="pass">Password:</label><br>
                <input type="password" name="pass" id="pass" value=""><br>
                <button type="submit">login</button>
            </form>
        </main>
    <?php
    } else {

        print('<br><header><h3><a href="./?logout">_logout_</a><h3></header>');
        define('DS', DIRECTORY_SEPARATOR);

        if (!empty($_POST)) {
            $postNameArr = array('delete', 'confirm_delete', 'upload', 'create', 'open', 'edit', 'save');
            $postIdentifierArr = array();

            foreach ($postNameArr as $postName) {
                if (array_key_exists($postName, $_POST)) {
                    $postIdentifierArr[] = $postName;
                }
            }
            if (!empty($postIdentifierArr)) {
                switch ($postIdentifierArr[0]) {
                    case 'delete':
                        $_SESSION['msg'] = deleteFile();
                        break;
                    case 'confirm_delete':
                        $_SESSION['msg'] = confirmDelete();
                        break;
                    case 'upload':
                        $_SESSION['msg'] = uploadFile();
                        break;
                    case 'create':
                        $_SESSION['msg'] = createDirectory();
                        break;
                    case 'open':
                        $_SESSION['msg'] = openFile();
                        break;
                    case 'edit':
                        $_SESSION['msg'] = editFile();
                        break;
                    case 'save':
                        $_SESSION['msg'] = saveFile();
                        break;
                }
            }
        }

        // ============ printing relative path on the top of page ============

        print('<h3>' . dirname($_SERVER['REQUEST_URI']));
        if (isset($_POST['dirName'])) {
            $openedDir = substr($_POST['dirName'], strlen(getcwd()));
            print(str_replace(DS, '/', $openedDir));
        } elseif (isset($_POST['fileName'])) {
            $openedDir = substr(dirname($_POST['fileName']), strlen(getcwd()));
            print(str_replace(DS, '/', $openedDir));
        } 
        print('</h3><hr>');

        // ============ printing files and directories ============

        listFiles();
        if (isset($_SESSION['msg'])) {
            print($_SESSION['msg']);
            unset($_SESSION['msg']);
        }

        // ============ finishing ============

        print('<div class="button">');
        print('<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" enctype="multipart/form-data">');
        print('<input type="file" name="file"><input type="hidden" name="dirName" value="' . realpath(getDir()) . '">');
        print('<button type="submit" name="upload">Upload file</button>');
        print('</form></div>');

        print('<div class="button">');
        print('<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" enctype="multipart/form-data">');
        print('<input id="dir-input" type="text" name="newDirName" placeholder="new directory" autocomplete="off">');
        print('<input type="hidden" name="dirName" value="' . realpath(getDir()) . '">');
        print('<button id="dir-btn" type="submit" name="create">Create</button>');
        print('</form></div>');

        print('<hr>');
        print('<div>' . $_SERVER['SERVER_SOFTWARE'] . '</div>');
    }

    // ============ delete file ============

    function deleteFile() {
        $msg = null;
        if (file_exists($_POST['fileName'])) {
            if (is_writable($_POST['fileName'])) {
                unlink($_POST['fileName']);
                $msg = '<div class="success">File "' . basename($_POST['fileName']) . '" has been deleted!</div>';
            } else {
                $msg = '<div class="warning">File "' . basename($_POST['fileName']) . '" permissions do not allow deletion!</div>';
            }
        } else {
            $msg = '<div class="warning">Unexpected file deletion fail</div>';
        }
        return $msg;
    }

    function confirmDelete() {
        $msg = null;
        $msg = '<form action="" method="POST"><div class="warning">Delete file "' . basename($_POST['fileName']) . '"?';
        $msg .= '<input type="hidden" name="fileName" value="' . $_POST['fileName'] . '">';
        $msg .= '<button type="submit" name="delete" class="default-button">YES</button>';
        $msg .= '<button type="submit" name="cancel" class="default-button">NO</button></form></div>';
        return $msg;
    }

    // ============ upload file ============

    function uploadFile() {
        $msg = null;
        $name = $_FILES['file']['name'];
        $target_file = getDir() . DS . basename($name);
        if ($_FILES['file']['error'] == 0) {
            if (file_exists($target_file)) {
                $msg = '<div class="info">File "' . $name  . '" already exists</div>';
            } elseif ($_FILES["file"]["size"] > 2097152) {
                $msg = '<div class="warning">"' . $name  . '" size is too big, - max 2MB are allowed.</div>';
            } else {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                    $msg = '<div class="success">File "' . $name  . '" has been uploaded</div>';
                } else {
                    $msg = '<div class="warning">There was an error uploading "' . $name  . '"</div>';
                }
            }
        } else {
            $msg = '<div class="info">No file selected</div>';
        }
        return $msg;
    }

    // ============ create directory ============

    function createDirectory() {
        $msg = null;
        if (!is_dir($_POST['dirName'] . DS . $_POST['newDirName'])) {
            mkdir($_POST['dirName'] . DS . $_POST['newDirName']);
            $msg = '<div class="success">Directory "' . $_POST['newDirName'] . '" has been created</div>';
        } elseif ($_POST['newDirName'] == '') {
            $msg = '<div class="info">Directory name should not be empty</div>';
        } else {
            $msg = '<div class="warning">Directory "' . $_POST['newDirName'] . '" already exists</div>';
        }
        return $msg;
    }

    // ============ open file ============

    function openFile() {
        $msg = null;
        if (file_exists($_POST['fileName'])) {
            $file = fopen($_POST['fileName'], 'r') or exit('<div class="warning">Unable to open file!</div>');
            $msg = '<div class="reader"><div>';
            if ($file) {
                $i = 0;
                while (($buffer = fgets($file, 4096)) !== false) {
                    $msg .= htmlspecialchars($buffer) . '<br>';
                }
                if (!feof($file)) {
                    $msg = '<div class="warning">Unexpected file read fail</div>';
                }
                fclose($file);
            }
            $msg .= '</div><form method="POST"><input type="hidden" name="fileName" value="' . $_POST['fileName'] . '">';
            if (!is_writable($_POST['fileName'])) {
                $msg .= '<div class="read-only"">file is Read-Only</div>';
                $msg .= '<div class="flex"><button type="submit" name="cancel" class="default-button">close</button></div></form></div>';
            } else {
                $msg .= '<div class="flex"><button style="background-color: lightcyan" type="submit" name="edit" class="default-button">edit</button>';
                $msg .= '<button type="submit" name="cancel" class="default-button">close</button></div></form></div>';
            }
        } else {
            $msg = '<div class="warning">Unexpected file read fail</div>';
        }
        return $msg;
    }

    // ============ edit file ============

    function editFile() {
        $msg = null;
        if (file_exists($_POST['fileName'])) {
            $file = fopen($_POST['fileName'], 'r') or exit('Unable to open file!');
            $msg = '<div class="reader"><form method="POST"><textarea name="data" rows="20">';
            if ($file) {
                $i = 0;
                while (($buffer = fgets($file, 4096)) !== false) {
                    $msg .= htmlspecialchars($buffer);
                }
                if (!feof($file)) {
                    $msg = '<div class="warning">Unexpected file read fail</div>';
                }
                fclose($file);
            }
            $msg .= '</textarea><input type="hidden" name="fileName" value="' . $_POST['fileName'] . '"><div class="flex">';
            $msg .= '<button style="background-color: lightgreen" type="submit" name="save" class="default-button">save</button>';
            $msg .= '<button style="background-color: lightpink" type="submit" name="cancel" class="default-button">cancel</button></div></form></div>';
        } else {
            $msg = '<div class="warning">Unexpected file opening fail</div>';
        }
        return $msg;
    }

    // ============ save file ============

    function saveFile() {
        $msg = null;
        if (file_exists($_POST['fileName'])) {
            $handle = fopen($_POST['fileName'], 'w');
            if (fwrite($handle, $_POST['data']) == true) {
                $msg = '<div class="success">File "' . basename($_POST['fileName']) . '" saved!</div>';
            } else {
                $msg = '<div class="warning">Unexpected file write fail</div>';
            }
            fclose($handle);
        } else {
            $msg = '<div class="warning">Unexpected file saving fail</div>';
        }
        return $msg;
    }

    // ============ printing files and directory of the opened path ============

    function listFiles() {
        $dir = getDir();
        $files = getFiles();
        foreach ($files as $file) {
            if (is_dir($dir . DS . $file)) {
                if ($file == '.') {
                    continue;
                } elseif ($file == '..') {
                    if ($dir != __DIR__) {
                        print('<div class="file" id="parent"><img src="img/back.gif" alt="Parent Directory">');
                        print('<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" class="inline">');
                        print('<input type="hidden" name="dirName" value="' . dirname($dir) . '">');
                        print('<button type="submit" class="link-button">Parent Directory</button>');
                        print('</form></div>');
                    }
                } else {
                    listDirectory($file);
                }
            } elseif (is_file($dir . DS . $file)) {
                listFile($file);
            }
        }
        if (count($files) == 2) {
            if (isset($_SESSION['msg'])) print('<p>This directory is empty<p>');
            else print('<p>This directory is empty<p>');
        }
    }

    function listDirectory($file) {
        $dir = getDir();
        print('<div class="file"><img src="img/directory.gif" alt="Directory">');
        print('<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" class="inline">');
        print('<input type="hidden" name="dirName" value="' . $dir . DS . $file . '">');
        print('<button type="submit" class="link-button">' . $file . '</button>');
        print('</form></div>');
    }

    function listFile($file) {
        $dir = getDir();
        print('<div class="file"><img src="img/text.gif" alt="File">');
        print('<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" class="inline" enctype="multipart/form-data">');
        print('<input type="hidden" name="fileName" value="' . $dir . DS . $file . '">');
        print('<button type="submit" name="open" class="link-button">' . $file . '</button>');
        print('</form><div>(size: ' . parseSize(filesize($dir . DS . $file)) . ')</div>');

        print('<div class="download"><form action="./inc/download.php" method="GET">');
        print('<input type="hidden" name="fileName" value="' . $dir . DS . $file . '">');
        print('<button type="submit" name="download" class="download-button">[DOWNLOAD]</button>');
        print('</form></div>');

        print('<div class="delete"><form action="' . $_SERVER['PHP_SELF'] . '" method="POST">');
        print('<input type="hidden" name="fileName" value="' . $dir . DS . $file . '">');
        print('<button type="submit" name="confirm_delete" class="delete-button">[DELETE]</button>');
        print('</form></div></div>');
    }

    function parseSize($bytes, $decimals = 2) {
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor > 0) {
            $letter = 'KMGT';
            return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $letter[(int)$factor - 1] . 'B';
        } else {
            return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . 'B';
        }
    }

    function getFiles() {
        $dir = getDir();
        $files = scandir($dir);
        $dirArr = array();
        $fileArr = array();
        foreach ($files as $file) {
            if (is_dir($dir . DS . $file)) {
                array_push($dirArr, $file);
            } elseif (is_file($dir . DS . $file)) {
                array_push($fileArr, $file);
            }
        }
        return array_merge($dirArr, $fileArr);
    }

    function getDir() {
        if (isset($_POST['dirName']) && basename($_POST['dirName']) != basename(dirname($_SERVER['REQUEST_URI']))) {
            return $_POST['dirName'];
        } elseif (isset($_POST['fileName'])) {
            return dirname($_POST['fileName']);
        } else {
            return __DIR__;
        }
    }
    ?>

</body>

</html>
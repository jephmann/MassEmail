<?php
    include('config/session.php');
    error_reporting(E_ALL ^ E_DEPRECATED);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>IIT ITM Mass E-Mail</title>
        <link rel="stylesheet" type="text/css" href="styles/reset.css" />
        <link rel="stylesheet" type="text/css" href="styles/common.css" />
    </head>
    <body>
        <div id="page">
            <?php include('inc/header.php'); ?>
            <ul>
                <li><a href="admin.php">Add an IIT ITM E-Mail Account</a></li>
                <li><a href="upload.php">Upload Student Mailing List</a></li>
                <li><a href="send.php">Send a Mass E-Mail to Students</a></li>
                <li><a href="index.php?logout">Logout</a></li>
            </ul>
        </div>
    </body>
</html>
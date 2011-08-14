<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title>IIT ITM Mass E-Mail</title>
        <link rel="stylesheet" type="text/css" href="styles/reset.css" />
        <link rel="stylesheet" type="text/css" href="styles/common.css" />
        <script src="js/jquery.js" type="text/javascript" language="javascript"></script>
        <script src="js/jquery.login.roshanbh.js" type="text/javascript" language="javascript"></script>
        <link rel="stylesheet" type="text/css" href="styles/login.css" />
    </head>
    <body>
        <div id="page">
            <?php include('inc/header.php'); ?>
            <div id="content">
                <form method="post" action="" id="login_form">
                    <div align="center">
                        <div class="row">
                            <label class="lbl300" id="lblusername" for="username">User Name : </label>
                            <input name="username" type="text" id="username" value="" maxlength="20" />
                        </div>
                        <div class="row" style="margin-top:5px">
                            <label class="lbl300" id="lblpassword" for="password">Password : </label>
                            <input name="password" type="password" id="password" value="" maxlength="20" />
                        </div>
                        <div class="buttondiv">
                            <input name="Submit" type="submit" id="submit" class="btnsubmit" value="Engage" style="margin-left:-10px; height:23px"  />
                            <span id="msgbox" style="display:none"></span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
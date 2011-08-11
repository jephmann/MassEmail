<?php
    include('config/session.php');
    include('config/db.php');
    
    // RETRIEVE LAST UPDATED TIMESTAMP FROM TABLES
    /*
    function lastupdated($host,$un,$pw,$db,$table){
        $connection=mysql_connect($host,$un,$pw) or die ('Unable to connect!');
        mysql_select_db($db,$connection);
        // $dataArray = mysql_fetch_array(mysql_query("show table status from ".$db." like ".$table));
        // $lastupdated = $dataArray['Update_time']; 
        mysql_close($connection);
        return $db,$table;
        
        }
    */
    
    if (isset($_POST['uploadStudents']) && $_FILES['csvstudents']['size'] > 0) {
        
        $fileName = $_FILES['csvstudents']['name'];
        $tmpName  = $_FILES['csvstudents']['tmp_name'];        $
        $fileSize = $_FILES['csvstudents']['size'];
        $fileType = $_FILES['csvstudents']['type'];
        $ext = strtolower(end(explode('.', $_FILES['csvstudents']['name'])));
        // test stuff        
        //echo '<br/>Filename: '.$fileName;        
        //echo '<br/>Tempname: '.$tmpName;        
        //echo '<br/>Filesize: '.$fileSize;        
        //echo '<br/>Filetype: '.$fileType;
        //echo '<br/>Extension '.$ext;
        
        // truncate students table
        $connection=mysql_connect($host,$un,$pw) or die ('Unable to connect!');
        mysql_select_db($db,$connection);
        $query=("TRUNCATE TABLE `students`");
        mysql_query($query) or die('<p>Error, TRUNCATE query failed</p>');
        mysql_close($connection);
        // upload to MySQL
        $openstudents = fopen($tmpName, 'r');
        while (!feof($openstudents)) {
            $studentrecord = fgetcsv($openstudents, 1024);
            $studentemail=$studentrecord[0];
            $studentfullname=$studentrecord[1];
            $studentstatus=$studentrecord[2];
            $studentfullname=addslashes($studentfullname);
            if ($studentemail != null || $studentemail != '' || strlen(trim($studentemail)) != 0){
                $connection=mysql_connect($host,$un,$pw) or die ('Unable to connect!');
                mysql_select_db($db,$connection);
                $query=("INSERT INTO students (email, name, status) VALUES ('$studentemail','$studentfullname','$studentstatus')");
                // echo '<p>'.$studentfullname.'</p>';
                mysql_query($query) or die('<p>Error, INSERT query failed</p>');
                mysql_close($connection);
            }
        }
        echo ('<script>alert("Student Mailing List UPDATED!")</script>');
    }
    
    //if (isset($_POST['uploadStudents'])) {
        
    //}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>IIT ITM Mass E-Mail</title>
        <link rel="stylesheet" type="text/css" href="styles/reset.css" />
        <link rel="stylesheet" type="text/css" href="styles/common.css" />
        <link rel="stylesheet" type="text/css" href="styles/validationEngine.jquery.css" />
        <link rel="stylesheet" type="text/css" href="styles/template.jquery.css" />
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
        <script type="text/javascript" charset="utf-8" src="js/languages/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" charset="utf-8" src="js/jquery.validationEngine.js"></script>
        <script>
            $(document).ready(function(){
                $("#frmUpload").validationEngine();
            });
        </script>
    </head>
    <body>
        <div id="page">
    <?php include('inc/header.php'); ?>
    <?php include('inc/nav.php'); ?>
            <h2>Upload Student Mailing List</h2>
            <p>Doublecheck the following before uploading:</p>
            <ul>
                <li>Are you uploading a csv file?</li>
                <li>Make sure that the csv file has no column headers or field names.</li>
                <li>Make sure that the csv file presents the following data in the following order:<br />
                    Student email address, Student name, Student status</li>
            </ul>
            <form id="frmUpload" name="frmUpload" method="post" enctype="multipart/form-data" action="">
                <input type="file" name="csvstudents" id="csvstudents"
                           class="validate[required] text-input"/>
                <input type="submit" class="btnsubmit" value="Upload Student File" name="uploadStudents" id="uploadStudents"/>
            </form>
        </div>
    </body>
</html>
<?php
    include('config/session.php');
    include('config/db.php');
    
    if (isset($_POST['uploadCSV']) && $_FILES['csv']['size'] > 0) {        
        if($_FILES['csv']['error'] == 0){        
            $fileName = $_FILES['csv']['name'];
            $ext = strtolower(end(explode('.', $fileName)));        
            if($ext==='csv'){
                $tmpName  = $_FILES['csv']['tmp_name'];
                $fileSize = $_FILES['csv']['size'];
                $fileType = $_FILES['csv']['type'];
                // test stuff        
                //echo '<br/>Filename: '.$fileName;        
                //echo '<br/>Tempname: '.$tmpName;        
                //echo '<br/>Filesize: '.$fileSize;        
                //echo '<br/>Filetype: '.$fileType;
                //echo '<br/>Extension '.$ext; 
                if(($openCSV=fopen($tmpName, 'r')) !== false);{
                    set_time_limit(0);
                    // truncate (delete) old table data
                    $connection=mysql_connect($host,$un,$pw) or die ('Unable to connect!');
                    mysql_select_db($db,$connection);
                    $query=("TRUNCATE TABLE `students`");
                    mysql_query($query) or die('<p>Error, TRUNCATE query failed</p>');
                    mysql_close($connection);
                    // upload (insert) new data
                    while (!feof($openCSV)) {
                        $csvdata = fgetcsv($openCSV, 1024);
                        // "sanitize"
                        $studentemail=trim($csvdata[0]);
                        $studentfullname=trim($csvdata[1]);
                        $studentstatus=trim($csvdata[2]);
                        $studentfullname=addslashes($studentfullname);
                        // if csv record includes an email address, upload to database
                        // (might be more compact to check against regular expression)
                        if ($studentemail != null || $studentemail != '' || strlen(trim($studentemail)) != 0){
                            $connection=mysql_connect($host,$un,$pw) or die ('Unable to connect!');
                            mysql_select_db($db,$connection);
                            $query=("INSERT INTO students (email, name, status) VALUES ('$studentemail','$studentfullname','$studentstatus')");
                            // echo '<p>'.$studentfullname.'</p>';
                            mysql_query($query) or die('<p>Error, INSERT query failed</p>');
                            mysql_close($connection);
                        }
                    }
                    fclose($openCSV);
                    echo ('<script>alert("Student Mailing List UPDATED!")</script>');
                }

            }else{
                echo ('<script>alert("File must be CSV.")</script>');
            }
        }else{
            echo ('File Error ('.$_FILES['csv']['error'].')');
        }        
    }
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
                $("#frmUploadCSV").validationEngine();
            });
        </script>
    </head>
    <body>
        <div id="page">
            <?php include('inc/header.php'); ?>
            <?php include('inc/nav.php'); ?>
            <div id="content">
                <h2>Upload Student Mailing List</h2>
                <p>Doublecheck the following before uploading:</p>
                <ul class="ulistdisc">
                    <li>Are you uploading a CSV file?</li>
                    <li>The CSV file must have no column headers or field names.</li>
                    <li>The CSV file must present the following data in the following sequence:<br />
                        <span class="code">Student email address, Student name, Student status</span></li>
                </ul>
                <div class="form">
                    <br/>
                    <form id="frmUploadCSV" name="frmUploadCSV" method="post" enctype="multipart/form-data" action="">
                        <div class="row">
                            <input type="file" name="csv" id="csv" size="100" class="validate[required] text-input"/>
                        </div>
                        <div class="rowbtn">
                            <input type="submit" class="btnsubmit" value="Upload Mailing List" name="uploadCSV" id="uploadCSV"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
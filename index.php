<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
    include_once('scripts/class.phpmailer.php');
    include('scripts/class.smtp.php'); // optional, gets called from within class.phpmailer.php if not already loaded
    
    $feedback=('');
    $optionstatus=('');
    $csv=('data/mydata.csv');
    $csv=fopen($csv, 'r');
    while(!feof($csv)){
        $csvrecord=fgetcsv($csv,1024);
        $userstatus=$csvrecord[2];
           if ($userstatus != null || $userstatus != '' || strlen(trim($userstatus)) != 0){
        $optionstatus.='<option value="'.$userstatus.'">'.$userstatus.'</option>';
           }
    }
    fclose($csv);    
    
    if (isset($_POST['submittedForm'])) {
       $csv=('data/mydata.csv');
       // check if file exists, then keep going (yet to write this part)
       $studentstatus=$_POST['studentstatus'];
       $accountname=$_POST['accountname'];
       $accountemail=$_POST['accountemail'];       
       $esubject=$_POST['emailsubject'];
       $ebody=$_POST['emailbody'];
       // open csv and loop through it
       $csv = fopen($csv, 'r');
       while (!feof($csv)) {
           $csvrecord = fgetcsv($csv, 1024);
           $useremail=$csvrecord[0];     // REQUIRED; if blank, skip to next record
           if ($useremail != null || $useremail != '' || strlen(trim($useremail)) != 0){
               $userfullname=$csvrecord[1];  // might not use at all if lastname,firstname -- unless...?
               $userstatus=$csvrecord[2];    // keyed to a dropdown selection on the form
               if ($studentstatus==$userstatus || $studentstatus == ''){  
                    $mail = new PHPMailer();
                    $mail->IsSMTP();               // set mailer to use SMTP
                    $mail->Host = ('mail.iit.edu');  // specify main and backup server
                    $mail->From = $accountemail;
                    $mail->FromName = $accountname;
                    $mail->AddAddress($useremail, $userfullname);
                    $mail->AddReplyTo($accountemail, $accountname);
                    $mail->WordWrap = 60;
                    $mail->IsHTML(true);
                    $mail->Subject = $esubject;
                    $mail->Body = $ebody;
                    if(!$mail->Send())  {
                        echo ('Message could not be sent.');
                        echo ('Mailer Error: '.$mail->ErrorInfo);
                        exit;
                    } else {
                        echo ('MAIL SENT!');
                    }
               }
               
            }
        }
        fclose($csv);
    }
    else
    {
        $feedback = '<p>All fields are required.</p>';
    }

           
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Mass Email</title>
        <link type="text/css" href="styles/tinymce.css" rel="stylesheet" />
        <script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js" ></script>
        <script type="text/javascript">
            tinyMCE.init({
        mode : "textareas",
        theme : "advanced",
        plugins : "emotions,spellchecker,advhr,insertdatetime,preview", 
                
        // Theme options - button# indicated the row# only
        theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,fontselect,fontsizeselect,formatselect",
        theme_advanced_buttons2 : "cut,copy,paste,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,anchor,image,|,code,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "insertdate,inserttime,|,spellchecker,advhr,,removeformat,|,sub,sup,|,charmap,emotions",      
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true

                });
    </script>
    </head>
    <body>
        <div id="page">
        <h1>Welcome to ITM Mass Email</h1>
        <?php echo "$feedback"; ?>
        <form method="post" action="" id="frmEmailCSV">
            <div class="row">
                <label for="accountname">Your Name</label>
                <input name="accountname" type="text" title="Account Name is a required field" value="" />
            </div>
            <div class="row">
                <label for="accountemail">Your Email Address</label>
                <input name="accountemail" type="text" title="Account Email is a required field" value="" />
            </div>
            <div class="row">
                <label for="studentstatus">Student Status</label>
                <select name="studentstatus" title="Student Status is a required field">
                    <option value="">ALL</opiton>
                    <?=$optionstatus ?>
                </select>
            </div>
            <div class="row">
                <label for="emailsubject">Email Subject</label>
                <input name="emailsubject" type="text" title="Email Subject is a required field" value="" />
            </div>
            <div class="row">
                <label for="emailbody">Email Body</label>
                <textarea name="emailbody" rows="5" cols="51" title="Email Body is a required field"></textarea>
            </div>
            <div class="row">
                <input type="submit" name="submittedForm" value="SEND EMAILS" />
            </div>
        </form>
        </div>
    </body>
</html>
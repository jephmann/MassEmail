<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
    include_once('scripts/class.phpmailer.php');
    include('scripts/class.smtp.php'); // optional, gets called from within class.phpmailer.php if not already loaded
    
    $feedback=('');
    $optionstatus=('');
    $submit=('');
    $rawstatus=array();
    $nosubmit=('<p>This form cannot submit without mailing list data.</p>');
    $csv=('data/students.csv');    
    if(file_exists($csv)){    
        if(filesize($csv) != 0){
            $submit=('<input type="submit" name="submittedForm" value="SEND EMAILS" />');
            // set up status dropdown
            $optionstatus='<option value="">Please SELECT</option>';
            $csv=fopen($csv, 'r');
            while(!feof($csv)){
                $csvrecord=fgetcsv($csv,1024);
                $userstatus=$csvrecord[2];
                if ($userstatus != null || $userstatus != '' || strlen(trim($userstatus)) != 0){
                    $rawstatus[]=$userstatus;
                }
            }
            fclose($csv);
            $uniquestatus=array_unique($rawstatus);
            foreach ($uniquestatus as $status){
                $optionstatus.='<option value="'.$status.'">'.$status.'</option>';
            }
            $optionstatus.='<option value="*">ALL</option>';
            // on submit, send emails            
            if (isset($_POST['submittedForm'])) {
               $csv=('data/students.csv');
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
                       if ($studentstatus==$userstatus || $studentstatus == '*'){  
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
                                $feedback = ('Message could not be sent. Mailer Error: '.$mail->ErrorInfo);
                                exit;
                            }else{
                                $submit = $nosubmit;
                                $feedback = ('MAIL SENT!');
                            }
                       }
                    }
                }
                fclose($csv);
                if (unlink('data/students.csv')){
                    $feedback.=' Data removed.';
                }else{
                    $feedback.=' Data still there?!?';
                }
            }else{
                $feedback = ('Welcome! All fields are required.');
            }
        }
    }else{
        $feedback = ('Awaiting mailing list data. Once data arrives, refresh this page.');
        $optionstatus='<option value="">* AWAITING DATA *</option>';
        $submit = $nosubmit;
    }
    
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ITM Mass Email</title>
        <link rel="stylesheet" type="text/css" href="styles/tinymce.css" />
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
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
            <h2><?=$feedback; ?></h2>
            <form method="post" action="" id="frmEmailCSV">
                <div class="row">
                    <label id="lblaccountname" for="accountname">Your Name</label>
                    <input name="accountname" type="text" size="40" title="Account Name is a required field" value="" />
                </div>
                <div class="row">
                    <label id="lblaccountemail" for="accountemail">Your Email Address</label>
                    <input name="accountemail" type="text" size="40" title="Account Email is a required field" value="" />
                </div>
                <div class="row">
                    <label id="lblstudentstatus" for="studentstatus">Student Status</label>
                    <select name="studentstatus" title="Student Status is a required field">
                        <?=$optionstatus ?>
                    </select>
                </div>
                <div class="row">
                    <label id="lblemailsubject" for="emailsubject">Email Subject</label>
                    <input name="emailsubject" type="text" size="40" title="Email Subject is a required field" value="" />
                </div>
                <div class="row">
                    <label id="lblemailbody" for="emailbody">Email Body:</label>
                </div>
                <div class="row">
                    <textarea name="emailbody" title="Email Body is a required field"></textarea>
                </div>
                <div class="rowbtn">
                    <?=$submit; ?>
                </div>
            </form>
        </div>
    </body>
</html>
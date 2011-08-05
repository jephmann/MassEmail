<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
    include_once('scripts/class.phpmailer.php');
    include('scripts/class.smtp.php'); // optional, gets called from within class.phpmailer.php if not already loaded
    
    $optionSELECT = '<option value="">Please SELECT</option>';
    $optionALL = '<option value="*">ALL</option>';
    $optionNONE = '<option value="">* AWAITING DATA *</option>';
    $dosubmit=('<input type="submit" name="submittedForm" value="SEND EMAILS" />');
    $nosubmit=('<p>This form cannot submit without mailing list data.</p>');
    
    $dataitm=('data/itm.csv');
    $datastudents=('data/students.csv');
    
    $optionfrom=$optionSELECT;
    $openitm=fopen($dataitm,'r');
    while(!feof($openitm)){
        $itmrecord=fgetcsv($openitm,1024);
        $itmaccount=$itmrecord[0];
        $optionfrom.=('<option value="'.$itmaccount.'">'.$itmaccount.'</option>');
    }
    fclose($openitm);    
    
    $feedback=('');
    $optionto=('');
    $submit=('');
    $rawstatus=array();
    
    if(file_exists($datastudents)){    
        if(filesize($datastudents) != 0){
            $submit=$dosubmit;
            
            // set up status dropdown
            $optionto=$optionSELECT;
            $openstudents=fopen($datastudents, 'r');
            while(!feof($openstudents)){
                $studentrecord=fgetcsv($openstudents,1024);
                $studentstatus=$studentrecord[2];
                if ($studentstatus != null || $studentstatus != '' || strlen(trim($studentstatus)) != 0){
                    $rawstatus[]=$studentstatus;
                }
            }
            fclose($openstudents);
            $uniquestatus=array_unique($rawstatus);
            foreach ($uniquestatus as $status){
                $optionto.='<option value="'.$status.'">'.$status.'</option>';
            }
            $optionto.=$optionALL;
            
            // on submit, send emails            
            if (isset($_POST['submittedForm'])) {
               // $csv=('data/students.csv');
               $post_from=$_POST['optionfrom'];
               $post_to=$_POST['optionto'];
               $post_subject=$_POST['emailsubject'];
               $post_body=$_POST['emailbody'];
               // open csv and loop through it
               $openstudents = fopen($datastudents, 'r');
               while (!feof($openstudents)) {
                   $studentrecord = fgetcsv($openstudents, 1024);
                   $studentemail=$studentrecord[0];     // REQUIRED; if blank, skip to next record
                   if ($studentemail != null || $studentemail != '' || strlen(trim($studentemail)) != 0){
                       $studentfullname=$studentrecord[1];  // might not use at all if lastname,firstname -- unless...?
                       $studentstatus=$studentrecord[2];    // keyed to a dropdown selection on the form
                       if ($post_to == $studentstatus || $post_to == '*'){
                           
                            $openitm=fopen($dataitm,'r');
                            while(!feof($openitm)){
                                $itmrecord=fgetcsv($openitm,1024);
                                if($post_from == $itmrecord[0]){
                                    $itmaccount=$itmrecord[0];
                                    $itmusernm=$itmrecord[1];
                                    $itmdomain=$itmrecord[2];
                                    $itmpsswrd=$itmrecord[3];
                                }
                            }
                            fclose($openitm);
                            $itmemail=($itmusernm.'@'.$itmdomain);
                            $mail = new PHPMailer();
                            $mail->IsSMTP();
                            switch($itmdomain){
                                case 'gmail.com':
                                    $mail->Host = ('smtp.'.$itmdomain);
                                    $mail->SMTPAuth = true;
                                    $mail->SMTPSecure = "ssl";
                                    $mail->Port = 465;
                                    $mail->Username = $itmemail; // SMTP username
                                    $mail->Password = $itmpsswrd; // SMTP password
                                    break;
                                default:
                                    $mail->Host = ('mail.'.$itmdomain);                                    
                                    }
                            $mail->From = $itmemail;
                            $mail->FromName = $itmaccount;
                            $mail->AddAddress($studentemail, $studentfullname);
                            $mail->AddReplyTo($itmemail, $itmaccount);
                            $mail->WordWrap = 60;
                            $mail->IsHTML(true);
                            $mail->Subject = $post_subject;
                            $mail->Body = $post_body;
                            if(!$mail->Send()) {
                                $feedback = ('Message could not be sent. Mailer Error: '.$mail->ErrorInfo);
                                exit;
                            }else{
                                $submit = $nosubmit;
                                $feedback = ('MAIL SENT!');
                            }
                       }
                    }
                }
                fclose($openstudents);
                if (unlink($datastudents)){
                    $feedback.=' Data removed.';
                    $optionto = $optionNONE;
                }else{
                    $feedback.=' Data still there?!?';
                }
            }else{
                $feedback = ('Welcome! All fields are required.');
            }
        }
        
    }else{
        $feedback = ('Awaiting mailing list data. Once data arrives, refresh this page.');
        $optionto = $optionNONE;
        $submit = $nosubmit;
    }
    
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ITM Mass Email</title>
        <link rel="stylesheet" type="text/css" href="styles/tinymce.css" />
        <link rel="stylesheet" type="text/css" href="styles/validationEngine.jquery.css" />
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript" charset="utf-8" src="js/languages/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" charset="utf-8" src="js/jquery.validationEngine.js"></script>
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
        <script>
            $(document).ready(function(){
                $("#form.id").validationEngine();
            });
        </script>
    </head>
    <body>
        <div id="page">
            <h1>Welcome to ITM Mass Email</h1>
            <h2><?=$feedback; ?></h2>
            <form method="post" action="" id="frmMassEmail">
                <div class="row">
                    <label id="lbloptionfrom" for="optionfrom">FROM (Select One):</label>
                    <select id="optionfrom" name="optionfrom" title="FROM is a required field"
                            class="validate[required]">
                        <?=$optionfrom ?>
                    </select>
                </div>
                <div class="row">
                    <label id="lbloptionto" for="optionto">TO (Select One):</label>
                    <select id="optionto" name="optionto" title="TO is a required field"
                            class="validate[required]">
                        <?=$optionto ?>
                    </select>
                </div>
                <div class="row">
                    <label id="lblemailsubject" for="emailsubject">SUBJECT:</label>
                    <input id="emailsubject" name="emailsubject" type="text" size="40" title="SUBJECT is a required field" value=""
                            class="validate[required] text-input"/>
                </div>
                <div class="row">
                    <label id="lblemailbody" for="emailbody">MESSAGE:</label>
                </div>
                <div class="row">
                    <textarea id="emailbody" name="emailbody" title="MESSAGE is a required field"
                            class="validate[required] text-input"/></textarea>
                </div>
                <div class="rowbtn">
                    <?=$submit; ?>
                </div>
            </form>
        </div>
    </body>
</html>
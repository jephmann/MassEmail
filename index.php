<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
    
    session_start();
    //  Developed by Roshan Bhattarai
    //  Visit http://roshanbh.com.np for this script and more.
    //  This notice MUST stay intact for legal use
    
    //  if session is not set redirect the user
    if(empty($_SESSION['u_name']))
        header("Location:login.php");
    
    //if logout then destroy the session and redirect the user
    if(isset($_GET['logout']))
        {
        session_destroy();
        header("Location:login.php");        
        }
     //   echo "<a href='index.php?logout'><b>Logout<b></a>";
     //   echo "<div align='center'>You Are inside secured Page</a>";
        
    include_once('scripts/class.phpmailer.php');
    include('scripts/class.smtp.php');
    
    $dataitm=('data/itm.csv');
    $datastudents=('data/students.csv');
    
    function selectoptions($csv, $field, $selectedoption){
        $selectoptions = ('');
        $opencsv = fopen($csv,  'r');
        while(!feof($opencsv)){
            $csvrecord = fgetcsv($opencsv,1024);
            $csvdata = $csvrecord[$field];
            if($csvdata != null || $csvdata != '' || strlen(trim($csvdata)) != 0){
                $arraydata[] = $csvdata;
            }
        }
        fclose($opencsv);
        $uniquedata = array_unique($arraydata);
        foreach($uniquedata as $datum){
            if($selectedoption == $datum){
                $selected = (' selected');
            }else{
                $selected = ('');
            }
            $selectoptions .= (chr(10).'<option'.$selected.' value="'.$datum.'">'.$datum.'</option>');
        }
        return $selectoptions;
    }
    
    $optionSELECT = chr(10).'<option value="">Please SELECT</option>';
    $optionALL = chr(10).'<option value="*">ALL</option>';
    $optionNONE = chr(10).'<option value="" class="phperror">* AWAITING DATA *</option>';
    $dosubmit=('<input taborder="50" type="submit" id="submittedForm" name="submittedForm" value="SEND EMAILS" />');
    $nosubmit=('<p class="phperror">This form cannot submit without mailing list data.</p>');
    $phpgenericerror=('<span class="phperror">required!</span>');
    
    $nofrom=('');
    $noto=('');
    $nosubject=('');
    $nomessage=('');
    $default_from=('');
    $default_to=('');
    $default_subject=('');
    $default_body=('');
    
    // set up FROM dropdown
    $optionfrom=$optionSELECT.selectoptions($dataitm,0,'');
    
    if(file_exists($datastudents)){    
        if(filesize($datastudents) != 0){
            $submit=$dosubmit;
            
            // set up TO dropdown            
            $optionto=$optionSELECT.selectoptions($datastudents,2,'').$optionALL;
            
            // on submit, send emails            
            if (isset($_POST['submittedForm'])) {
               $e = 0;
               $post_from=trim($_POST['optionfrom']);
               if (strlen($post_from) == 0){
                   $e=$e+1;
                   $nofrom=$phpgenericerror;
                   }
               $post_to=trim($_POST['optionto']);
               if (strlen($post_to) == 0){
                   $e=$e+1;
                   $noto=$phpgenericerror;
                   }
               $post_subject=trim($_POST['emailsubject']);
               if (strlen($post_subject) == 0){
                   $e=$e+1;
                   $nosubject = $phpgenericerror;
                   }
               $post_body=trim($_POST['emailbody']);
               if (strlen($post_body) == 0){
                   $e=$e+1;
                   $nomessage = $phpgenericerror;
                   }
               if ($e!=0){
                   $feedback = ('<span class="phperror">All fields are required.</span>');
                   $optionfrom=$optionSELECT.selectoptions($dataitm,0,$post_from);
                   $optionto=$optionSELECT.selectoptions($datastudents,2,$post_to).$optionALL;
                   $default_from=$post_from;
                   $default_to=$post_to;
                   $default_subject=$post_subject;
                   $default_body=$post_body;
               }else{
               // open csv and loop through it
               $openstudents = fopen($datastudents, 'r');
               while (!feof($openstudents)) {
                   $studentrecord = fgetcsv($openstudents, 1024);
                   $studentemail=$studentrecord[0];
                   if ($studentemail != null || $studentemail != '' || strlen(trim($studentemail)) != 0){
                       $studentfullname=$studentrecord[1];
                       $studentstatus=$studentrecord[2];
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
                    $feedback.=(' Data removed.');
                    echo ('<script>alert("'.$feedback.='")</script>');
                    header("location: " . $_SERVER['REQUEST_URI']);
                    $optionto = $optionNONE;
                    $submit = $nosubmit;
                }else{
                    $feedback.=(' Data still there?!? INCONCEIVABLE!!!');
                }
               }
            }else{
                $feedback = ('All fields are required, including MESSAGES.');
                $submit = $dosubmit;
                $e=0;
            }
        }
        
    }else{
        $feedback = ('Awaiting mailing list data. Once data arrives, refresh this page.');
        $optionto = $optionNONE;
        $submit = $nosubmit;
        $e=0;
        $default_from=('');
        $default_to=('');
        $default_subject=('');
        $default_body=('');
    }
    
    
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>IIT ITM Mass Email</title>
        <link rel="stylesheet" type="text/css" href="styles/common.css" />
        <link rel="stylesheet" type="text/css" href="styles/validationEngine.jquery.css" />
        <link rel="stylesheet" type="text/css" href="styles/template.jquery.css" />
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
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
                $("#frmMassEmail").validationEngine();
            });
        </script>
    </head>
    <body>
        <div id="page">
            <h1>Welcome to ITM Mass Email</h1>
            <h2><a href="index.php?logout">Logout</a></h2>
            <h3><?=$feedback; ?></h3>
            <form name="frmMassEmail" id="frmMassEmail" method="post" action="" id="frmMassEmail">
                <div class="row">
                    <label id="lbloptionfrom" for="optionfrom">FROM (Select One):<?=$nofrom ?></label>
                    <select taborder="10" id="optionfrom" name="optionfrom"
                            title="FROM is a required field"
                            class="validate[required]">
                            <?=$optionfrom ?>
                    </select>
                </div>
                <div class="row">
                    <label id="lbloptionto" for="optionto">TO (Select One):<?=$noto ?></label>
                    <select taborder="20" id="optionto" name="optionto"
                            title="TO is a required field"
                            class="validate[required]">
                            <?=$optionto ?>
                    </select>
                </div>
                <div class="row">
                    <label id="lblemailsubject" for="emailsubject">SUBJECT:<?=$nosubject ?></label>
                    <input taborder="30" id="emailsubject" name="emailsubject" type="text" size="40"
                           title="SUBJECT is a required field"
                           value="<?=$default_subject ?>"
                           class="validate[required] text-input"/>
                </div>
                <div class="rowmessage">
                    <label id="lblemailbody" for="emailbody">MESSAGE:<?=$nomessage ?></label>
                </div>
                <div class="rowmessage">
                    <textarea taborder="40" id="emailbody" name="emailbody"
                              title="MESSAGE is a required field"
                              class="validate[required] text-input"/><?=$default_body ?></textarea>
                </div>
                <div class="rowbtn">
                    <?=$submit; ?>
                </div>
            </form>
        </div>
    </body>
</html>
<?php
    include('config/session.php');
    error_reporting(E_ALL ^ E_DEPRECATED);
    include_once('scripts/class.phpmailer.php');
    include('scripts/class.smtp.php');
    include('config/db.php');
    
    function selectoptions($selectedoption,$host,$un,$pw,$db,$table,$field){
        $selectoptions = ('');
        $connection=mysqli_connect($host,$un,$pw,$db) or die ('Unable to connect!');
        $query=('SELECT '.$field.' FROM '.$table.' GROUP BY '.$field.' ORDER BY '.$field);
        $result=mysqli_query($connection,$query) or die ("Error in query: $query. ".mysqli_error());
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $fielddata=$row[$field];
                if($selectedoption == $fielddata){
                    $selected = (' selected');                    
                }else{
                    $selected = ('');                    
                }
                $selectoptions .= (chr(10).'<option'.$selected.' value="'.$fielddata.'">'.$fielddata.'</option>');
            }
            mysqli_close($connection);
        }
        return $selectoptions;
    }
    
    $optionSELECT = chr(10).'<option value="">Please SELECT</option>';
    $phpgenericerror=('<span class="phperror">required!</span>');
    
    $nofrom=('');
    $noto=('');
    $nosubject=('');
    $nomessage=('');
    $default_from=('');
    $default_to=('');
    $default_subject=('');
    $default_body=('');
    
    $feedback = 'All fields are required, including Message.';
    
    $optionfrom=$optionSELECT.selectoptions('',$host,$un,$pw,$db,'itm','name');
    $optionto=$optionSELECT.selectoptions('',$host,$un,$pw,$db,'students','status');
    
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
            $feedback = '<span class="phperror">All fields are required.</span>';
            $optionfrom=$optionSELECT.selectoptions($post_from,$host,$un,$pw,$db,'itm','name');
            $optionto=$optionSELECT.selectoptions($post_to,$host,$un,$pw,$db,'students','status');
            $default_from=$post_from;
            $default_to=$post_to;
            $default_subject=$post_subject;
            $default_body=$post_body;
        }else{
            // open ITM data ONCE, get itm info per dropdown
            $connection=mysqli_connect($host,$un,$pw,$db) or die ('Unable to connect!');
            $query=("SELECT * FROM itm WHERE itm.name = '".$post_from."'");
            $result=mysqli_query($connection,$query) or die ("Error in query: $query. ".mysqli_error());
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_array($result)) {
                    $itmaccount=$row['name'];
                    $itmusernm=$row['username'];
                    $itmdomain=$row['domain'];
                    $itmpsswrd=$row['password'];
                }
                mysqli_close($connection);
            }
            $itmemail=($itmusernm.'@'.$itmdomain);
            echo '<br />'.$itmaccount;
            echo '<br />'.$itmusernm;
            echo '<br />'.$itmdomain;
            echo '<br />'.$itmpsswrd;
            echo '<br />'.$itmemail;
            // open student data and loop through it
            $connection=mysqli_connect($host,$un,$pw,$db) or die ('Unable to connect!');
            $query=("SELECT * FROM students WHERE students.status = '".$post_to."'");
            $result=mysqli_query($connection,$query) or die ("Error in query: $query. ".mysqli_error());
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_array($result)) {                    
                    $studentemail=$row['email'];
                    if ($studentemail != null || $studentemail != '' || strlen(trim($studentemail)) != 0){
                        $studentfullname=$row['name'];
                        $studentstatus=$row['status'];
                        if ($post_to == $studentstatus || $post_to == '*'){
                            $mail = new PHPMailer();
                            $mail->IsSMTP();
                            switch($itmdomain){
                                case ('gmail.com'):
                                    $mail->Host = ('smtp.'.$itmdomain);
                                    $mail->SMTPAuth = (true);
                                    $mail->SMTPSecure = ('ssl');
                                    $mail->Port = (465);
                                    $mail->Username = ($itmemail);
                                    $mail->Password = ($itmpsswrd);
                                    break;
                                default:
                                    $mail->Host = ('mail.'.$itmdomain);                                    
                            }
                            $mail->From = ($itmemail);
                            $mail->FromName = ($itmaccount);
                            $mail->AddAddress($studentemail, $studentfullname);
                            $mail->AddReplyTo($itmemail, $itmaccount);
                            $mail->WordWrap = (60);
                            $mail->IsHTML(true);
                            $mail->Subject = ($post_subject);
                            $mail->Body = ($post_body);
                            if(!$mail->Send()){
                                $feedback = 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo;
                                echo '<p>'.$studentfullname.' | '.$studentemail.' | '.$feedback.'</p>';
                                exit;
                            }else{
                                $feedback = 'Mail Sent!';
                                echo '<p>'.$studentfullname.' | '.$studentemail.' | '.$feedback.'</p>';
                            }
                        }
                    }
                }                
                mysqli_close($connection);
                echo ('<script>alert("'.$feedback.'")</script>');
            }
        }
    }else{
        $e=0;
    }
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>IIT ITM Mass Email</title>
        <link rel="stylesheet" type="text/css" href="styles/reset.css" />
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
            <?php include('inc/header.php'); ?>
            <?php include('inc/nav.php'); ?>
            <div id="content">
                <h2>Send a Mass E-Mail to Students</h2>
                <p><?=$feedback; ?></p>
                <form name="frmMassEmail" id="frmMassEmail" method="post" action="" id="frmMassEmail">
                    <div class="row">
                        <label class="lbl300" id="lbloptionfrom" for="optionfrom">FROM (Select One):<?=$nofrom ?></label>
                        <select taborder="10" id="optionfrom" name="optionfrom"
                                title="FROM is a required field"
                                class="validate[required]">
                                <?=$optionfrom ?>
                        </select>
                    </div>
                    <div class="row">
                        <label class="lbl300" id="lbloptionto" for="optionto">TO (Select One):<?=$noto ?></label>
                        <select taborder="20" id="optionto" name="optionto"
                                title="TO is a required field"
                                class="validate[required]">
                                <?=$optionto ?>
                        </select>
                    </div>
                    <div class="row">
                        <label class="lbl300" id="lblemailsubject" for="emailsubject">SUBJECT:<?=$nosubject ?></label>
                        <input taborder="30" id="emailsubject" name="emailsubject" type="text" size="40"
                               title="SUBJECT is a required field"
                               value="<?=$default_subject ?>"
                               class="validate[required] text-input"/>
                    </div>
                    <div class="rowmessage">
                        <label class="lbl650" id="lblemailbody" for="emailbody">MESSAGE:<?=$nomessage ?></label>
                    </div>
                    <div class="rowmessage">
                        <textarea taborder="40" id="emailbody" name="emailbody"
                                  title="MESSAGE is a required field"
                                  class="validate[required] text-input"/><?=$default_body ?></textarea>
                    </div>
                    <div class="rowbtn">
                        <input taborder="50" class="btnsubmit" type="submit" id="submittedForm" name="submittedForm" value="SEND EMAILS" />
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
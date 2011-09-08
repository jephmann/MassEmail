<?php
    include('config/session.php');
    error_reporting(E_ALL ^ E_DEPRECATED);
    include_once('scripts/class.phpmailer.php');
    include('scripts/class.smtp.php');
    include('config/db.php');
    include('scripts/selectoptions.php');
    
    $optionSELECT = (chr(10).'<option value="">Please SELECT</option>');
    $phpgenericerror=('<span class="phperror">required!</span>');
    
    $nofrom=('');
    $noto=('');
    $nosubject=('');
    $nomessage=('');
    $default_from=('');
    $default_to=('');
    $default_copies=('');
    $default_subject=('');
    $default_body=('');
    
    $feedback = 'Apart from Copies, all fields are required, including Message.';
    
    $optionfrom=$optionSELECT.selectoptions('',$host,$un,$pw,$db,'itm','name');
    $optionto=$optionSELECT.selectoptions('',$host,$un,$pw,$db,'students','status');
    
    if (isset($_POST['submittedForm'])) {
        $e = 0; // begin error count
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
            
        $post_copies=str_replace('\s', '', trim($_POST['copies']));
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
            $default_copies=$post_copies;
            $default_subject=$post_subject;
            $default_body=$post_body;
        }else{
            // open ITM data ONCE, get itm info per dropdown
            $cnITM=mysqli_connect($host,$un,$pw,$db) or die ('Unable to connect!');
            $qITM=("SELECT * FROM itm WHERE itm.name = '".$post_from."'");
            $resultITM=mysqli_query($cnITM,$qITM) or die ("Error in query: $qITM. ".mysqli_error());
            if (mysqli_num_rows($resultITM) > 0) {
                while($row = mysqli_fetch_array($resultITM)) {
                    $itmaccount=$row['name'];
                    $itmusernm=$row['username'];
                    $itmdomain=$row['domain'];
                    $itmpsswrd=$row['password'];
                }
                mysqli_close($cnITM);
            }
            
            $itmemail=($itmusernm.'@'.$itmdomain);
            // email variables, per domain
            switch($itmdomain){
                case 'gmail.com':
                    $mailhost=('smtp.'.$itmdomain);
                    $mailSMTPAuth=(true);
                    $mailSMTPSecure=('ssl');
                    $mailUsername=($itmemail);
                    $mailPassword=($itmpsswrd);
                    $mailPort=465;                    
                    break;
                default:
                    /*
                       host formerly 'mail'.$itmdoman
                       but 'hawk.iit.edu' does not work here
                    */    
                    $mailhost=('mail.iit.edu');
                    $mailSMTPAuth=(false);
                    $mailSMTPSecure=('');
                    $mailPort=null; // what if no port number?
                    $mailUsername=('');
                    $mailPassword=('');
            }
            // open student data and loop through it
            $cnStudents=mysqli_connect($host,$un,$pw,$db) or die ('Unable to connect!');
            $quStudents=("SELECT * FROM students WHERE students.status = '".$post_to."'");
            $resultStudents=mysqli_query($cnStudents,$quStudents) or die ("Error in query: $quStudents. ".mysqli_error());
            if (mysqli_num_rows($resultStudents) > 0) {
                while($row = mysqli_fetch_array($resultStudents)) {                    
                    $studentemail=$row['email'];
                    $studentfullname=$row['name'];
                    //if ($post_to == $studentstatus || $post_to == '*'){
                    $mail = new PHPMailer();
                    $mail->IsSMTP();
                    $mail->Host = $mailhost;
                    $mail->SMTPAuth = $mailSMTPAuth;
                    $mail->SMTPSecure = $mailSMTPSecure;
                    $mail->Port = $mailPort;
                    $mail->Username = $mailUsername;
                    $mail->Password = $mailPassword;   
                    $mail->From = $itmemail;
                    $mail->FromName = $itmaccount;
                    $mail->AddAddress($studentemail, $studentfullname);
                    $mail->AddReplyTo($itmemail, $itmaccount);
                    $mail->WordWrap = 60;
                    $mail->IsHTML(true);
                    $mail->Subject = $post_subject;
                    $mail->Body = $post_body;
                    if(!$mail->Send()){
                        $feedback = 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo;
                        echo '<p>'.$studentfullname.' | '.$studentemail.' | '.$feedback.'</p>';
                        exit;
                    }else{
                        $feedback = 'Mail Sent!';
                        echo '<p>'.$studentfullname.' | '.$studentemail.' | '.$feedback.'</p>';
                    }
                    //}                    
                }                
                mysqli_close($cnStudents);
                // if copies, create and loop through copies array
                if(strlen($post_copies)!=0){
                    $array_copies=explode(',',$post_copies);
                    for ($i=0;$i<count($array_copies);$i++) {
                        $copymail = $array_copies[$i];
                        $moremail = new PHPMailer();
                        $moremail->IsSMTP();
                        $moremail->Host = $mailhost;
                        $moremail->SMTPAuth = $mailSMTPAuth;
                        $moremail->SMTPSecure = $mailSMTPSecure;
                        $moremail->Port = $mailPort;
                        $moremail->Username = $mailUsername;
                        $moremail->Password = $mailPassword;   
                        $moremail->From = ($itmemail);
                        $moremail->FromName = ($itmaccount);
                        $moremail->AddAddress($copymail, $copymail);
                        $moremail->AddReplyTo($itmemail, $itmaccount);
                        $moremail->WordWrap = (60);
                        $moremail->IsHTML(true);
                        $moremail->Subject = ($post_subject);
                        $moremail->Body = ($post_body);
                        if(!$moremail->Send()){
                            echo '<p>'.$copymail.' | '.$feedback.'</p>';
                            $feedback = 'Message could not be sent. Mailer Error: '.$moremail->ErrorInfo;
                            exit;
                        }else{
                            echo '<p>'.$copymail.' | '.$feedback.'</p>';
                            $feedback = 'Mail Sent!';
                        }
                    }
                }
                else
                {
                    echo 'Copies?!? '.$post_copies;
                }
                
                echo ('<script>alert("'.$feedback.'")</script>');
                // header("location:send.php");
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
        <script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
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
                theme_advanced_resizing : true,
                // Classes for textarea: mceEditor gets the Editor; mceNoEditor remains plain
                editor_selector : "mceEditor",
                editor_deselector : "mceNoEditor"
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
                <p><?php echo $feedback; ?></p>
                <form name="frmMassEmail" id="frmMassEmail" method="post" action="" id="frmMassEmail">
                    <div class="row">
                        <label class="lbl300" id="lbloptionfrom" for="optionfrom">FROM (Select One):<?php echo $nofrom ?></label>
                        <select taborder="10" id="optionfrom" name="optionfrom"
                                title="FROM is a required field"
                                class="validate[required]">
                                <?php echo $optionfrom ?>
                        </select>
                    </div>
                    <div class="row">
                        <label class="lbl300" id="lbloptionto" for="optionto">TO (Select One):<?php echo $noto ?></label>
                        <select taborder="20" id="optionto" name="optionto"
                                title="TO is a required field"
                                class="validate[required]">
                                <?php echo $optionto ?>
                        </select>
                    </div>
                    <div class="row">
                        <label class="lbl300" id="lblcopies" for="copies">COPIES (Optional):</label>
                        <textarea taborder="30" id="copies" name="copies" cols="35" rows="7"
                                title="Optional (please separate with commas)"
                                class="mceNoEditor"><?php echo $default_copies ?></textarea>
                    </div>
                    <div class="row">
                        <label class="lbl300" id="lblemailsubject" for="emailsubject">SUBJECT:<?php echo $nosubject ?></label>
                        <input taborder="40" id="emailsubject" name="emailsubject" type="text" size="40"
                               title="SUBJECT is a required field"
                               value="<?php echo $default_subject ?>"
                               class="validate[required] text-input"/>
                    </div>
                    <div class="rowmessage">
                        <label class="lbl650" id="lblemailbody" for="emailbody">MESSAGE:<?php echo $nomessage ?></label>
                    </div>
                    <div class="rowmessage">
                        <textarea taborder="50" id="emailbody" name="emailbody"
                                  title="MESSAGE is a required field"
                                  class="mceEditor validate[required] text-input"/><?php echo $default_body ?></textarea>
                    </div>
                    <div class="rowbtn">
                        <input taborder="60" class="btnsubmit" type="submit" id="submittedForm" name="submittedForm" value="SEND EMAILS" />
                    </div>
                </form>
            </div>
            <?php include('inc/footer.php'); ?>
        </div>
    </body>
</html>
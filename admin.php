<?php
    include('config/session.php');
    error_reporting(E_ALL ^ E_DEPRECATED);
    include('config/db.php');
    include('scripts/selectoptions.php');
    
    $optionSELECT = (chr(10).'<option value="">Please SELECT</option>');   
    $phpgenericerror=('<span class="phperror">required!</span>');
    
    $noaccountname=('');
    $noaccountusername=('');
    $noaccountpassword=('');
    $noaccountdomain=('');
    $default_accountname=('');
    $default_accountusername=('');
    $default_accountpassword=('');
    $default_accountdomain=('');
     
    $feedback = ('All fields are required.');
    
    $optiondomains=$optionSELECT.selectoptions('',$host,$un,$pw,$db,'domains','domain');
    
    if (isset($_POST['submittedForm'])) {
        $e = 0;
        $post_name=trim($_POST['accountname']);
        if (strlen($post_name) == 0){
            $e=$e+1;
            $noaccountname=$phpgenericerror;            
            }
        $post_username=trim($_POST['accountusername']);
        if (strlen($post_username) == 0){
            $e=$e+1;
            $noaccountusername=$phpgenericerror;            
            }
        $post_password=trim($_POST['accountpassword']);
        if (strlen($post_password) == 0){
            $e=$e+1;
            $noaccountpassword=$phpgenericerror;
            }
        $post_domain=trim($_POST['accountdomain']);
        if (strlen($post_domain) == 0){
            $e=$e+1;
            $noaccountdomain = $phpgenericerror;            
            }
        if ($e!=0){
            $feedback = '<span class="phperror">All fields are required.</span>';    
            $optiondomains=$optionSELECT.selectoptions($post_domain,$host,$un,$pw,$db,'domains','domain');
            $default_accountname=$post_name;
            $default_accountusername=$post_username;
            $default_accountpassword=$post_password;
            $default_accountdomain=$post_domain;
        }else{
            $connection=mysql_connect($host,$un,$pw) or die ('Unable to connect!');
            mysql_select_db($db,$connection);
            $query=("INSERT INTO itm (name, username, domain, password) VALUES ('$post_name','$post_username','$post_domain','$post_password')");
            mysql_query($query) or die('<p>Error, INSERT query failed</p>');
            mysql_close($connection);
            echo ('<script>alert("New ITM Account ADDED!\rCheck the FROM dropdown in the Mass E-Mail form.")</script>');
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
                $("#frmAddITM").validationEngine();
            });
        </script>
    </head>
    <body>
        <div id="page">
            <?php include('inc/header.php'); ?>
            <?php include('inc/nav.php'); ?>
            <div id="content">
            <h2>Add an IIT ITM E-Mail Account</h2>
            <p><?=$feedback; ?></p>
            <form name="frmAddITM" id="frmAddITM" method="post" action="">
                <div class="row">
                    <label class="lbl300" id="lblaccountname" for="accountname">Account Name:<?=$noaccountname ?></label>
                    <input taborder="10" id="accountname" name="accountname" type="text" size="40"
                           title="Account Name is a required field"
                           value="<?=$default_accountname ?>"
                           class="validate[required] text-input"/>
                </div>
                <div class="row">
                    <label class="lbl300" id="lblaccountusername" for="accountusername">Account Username:<?=$noaccountusername ?></label>
                    <input taborder="20" id="accountusername" name="accountusername" type="text" size="40"
                           title="Account Username is a required field"
                           value="<?=$default_accountusername ?>"
                           class="validate[required] text-input"/>
                </div>
                <div class="row">
                    <label class="lbl300" id="lblaccountpassword" for="accountpassword">Account Password:<?=$noaccountpassword ?></label>
                    <input taborder="30" id="accountpassword" name="accountpassword" type="password" size="40"
                           title="Account Password is a required field"
                           value="<?=$default_accountpassword ?>"
                           class="validate[required] text-input"/>
                </div>
                <div class="row">
                    <label class="lbl300" id="lblaccountdomain" for="accountdomain">Account Domain:<?=$noaccountdomain ?></label>
                    <select taborder="40" id="accountdomain" name="accountdomain"
                            title="Account Domain is a required field"
                            class="validate[required]">
                                <?=$optiondomains ?>
                    </select>
                </div>                
                <div class="rowbtn">
                    <input taborder="50" type="submit" class="btnsubmit"
                           id="submittedForm"
                           name="submittedForm" value="ADD ACCOUNT" />
                </div>
            </form>
            </div>
        </div>
    </body>
</html>
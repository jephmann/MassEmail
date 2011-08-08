<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>IIT ITM</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <script src="js/jquery.js" type="text/javascript" language="javascript"></script>
        <script language="javascript">
          //  Developed by Roshan Bhattarai
          //  Visit http://roshanbh.com.np for this script and more.
          //  This notice MUST stay intact for legal use

        $(document).ready(function()
        {
                $("#login_form").submit(function()
                {
                        //remove all the class add the messagebox classes and start fading
                        $("#msgbox").removeClass().addClass('messagebox').text('Validating....').fadeIn(1000);
                        //check the username exists or not from ajax
                        $.post("ajax_login.php",{ user_name:$('#username').val(),password:$('#password').val(),rand:Math.random() } ,function(data)
                {
                          if(data=='yes') //if correct login detail
                          {
                                $("#msgbox").fadeTo(200,0.1,function()  //start fading the messagebox
                                {
                                  //add message and change the class of the box and start fading
                                  $(this).html('Logging in.....').addClass('messageboxok').fadeTo(900,1,
                      function()
                                  {
                                         //redirect to secure page
                                         document.location='index.php';
                                  });

                                });
                          }
                          else
                          {
                                $("#msgbox").fadeTo(200,0.1,function() //start fading the messagebox
                                {
                                  //add message and change the class of the box and start fading
                                  $(this).html('Your login detail sucks...').addClass('messageboxerror').fadeTo(900,1);
                                });
                  }

                });
                        return false; //not to post the  form physically
                });
                //now call the ajax also focus move from
                $("#password").blur(function()
                {
                        $("#login_form").trigger('submit');
                });
        });
        </script>
        <link rel="stylesheet" type="text/css" href="styles/login.css" />
    </head>
    <body>
        <form method="post" action="" id="login_form">
            <div align="center">
                <div class="top">
                    <h1>IIT ITM</h1>
                </div>
                <div>
                    User Name : <input name="username" type="text" id="username" value="" maxlength="20" />
                </div>
                <div style="margin-top:5px">
                    Password :
                    &nbsp;&nbsp;
                    <input name="password" type="password" id="password" value="" maxlength="20" />
                </div>
                <div class="buttondiv">
                    <input name="Submit" type="submit" id="submit" value="Login" style="margin-left:-10px; height:23px"  />
                    <span id="msgbox" style="display:none"></span>
                </div>
            </div>
        </form>
    </body>
</html>
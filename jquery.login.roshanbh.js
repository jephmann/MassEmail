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
                            $(this).html('Engaging.....').addClass('messageboxok').fadeTo(900,1,
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
                            $(this).html('Norman Swine, Saxon Dog...').addClass('messageboxerror').fadeTo(900,1);
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
<?php
    session_start();
    include('config/db.php');
    /*
     * Developed by Roshan Bhattarai
     * Visit http://roshanbh.com.np for this script and more.
     * This notice MUST stay intact for legal use
     */
    
    //Connect to database from here
    $link = mysql_connect($host, $un, $pw);
    if (!$link) {
        die('Could not connect: ' . mysql_error());
        }
        //select the database | Change the name of database from here
        mysql_select_db($dblogin);
        
        //get the posted values
        $user_name=htmlspecialchars($_POST['user_name'],ENT_QUOTES);
        $pass=md5($_POST['password']);

        //now validating the username and password
        $sql="SELECT user_name, password FROM tbl_user WHERE user_name='".$user_name."'";
        $result=mysql_query($sql);
        $row=mysql_fetch_array($result);

        //if username exists
        if(mysql_num_rows($result)>0)
        {
            //compare the password
            if(strcmp($row['password'],$pass)==0)
            {
		echo "yes";
		//now set the session from here if needed
		$_SESSION['u_name']=$user_name;
            }
            else
                echo "no";
            }
            else
                echo "no"; //Invalid Login            
?>
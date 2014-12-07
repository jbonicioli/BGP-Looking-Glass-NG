<?php
/*-----------------------------------------------------------------------------
* BGP Looking Glass NG                                                        *
*                                                                             *
* Main Author: Vaggelis Koutroumpas vaggelis@koutroumpas.gr                   *
* (c)2008-2014 for AWMN                                                       *
* Credits: see CREDITS file                                                   *
*                                                                             *
* This program is free software: you can redistribute it and/or modify        *
* it under the terms of the GNU General Public License as published by        * 
* the Free Software Foundation, either version 3 of the License, or           *
* (at your option) any later version.                                         *
*                                                                             *
* This program is distributed in the hope that it will be useful,             *
* but WITHOUT ANY WARRANTY; without even the implied warranty of              *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the                *
* GNU General Public License for more details.                                *
*                                                                             *
* You should have received a copy of the GNU General Public License           *
* along with this program. If not, see <http://www.gnu.org/licenses/>.        *
*                                                                             *
*-----------------------------------------------------------------------------*/

require("includes/config.php");
require("includes/functions.php");


if (admin_logged()) {
    header("Location: index.php");
    exit;
} 

if (isset ($_GET['action']) && $_GET['action'] == "register") {
	
    $errors = array();
    
    $_POST['Username'] = trim($_POST['Username']);
    if (!preg_match("/^[a-zA-Z0-9][a-zA-Z0-9_-]{2,29}$/", $_POST['Username'])) {
        $errors['username'] = "Please choose a username with 3 to 30 latin characters &amp; numbers without spaces and symbols. Hyphens '-' and underscores '_' are allowed but the username cannot begin with either of those 2 characters.";
    } else {
        
        if (mysql_num_rows(mysql_query("SELECT id FROM `staff` WHERE `Username` = '".addslashes($_POST['Username'])."' ",$db))){
            $errors['username'] = "Username is already in use." ;
        } 
    }
    
    if (!$_POST['Password']) {
        $errors['password'] = "Please enter the Password" ;
    } else {
        if ($_POST['Password'] != $_POST['Password2']) {
            $errors['password'] = "Passwords do not match";
        }    
    }
    
    $_POST['Email'] = trim($_POST['Email']);
    if ($_POST['Email']){
        if (!preg_match("/^([a-zA-Z0-9]+([\.+_-][a-zA-Z0-9]+)*)@(([a-zA-Z0-9]+((\.|[-]{1,2})[a-zA-Z0-9]+)*)\.[a-zA-Z]{2,7})$/", $_POST['Email'])) {
            $errors['email'] = "The Email address you gave is not valid" ;
        }
    }elseif(!$_POST['Email']){
        $errors['email'] = "Please enter the Email address";
    } else {
        
        if (mysql_num_rows(mysql_query("SELECT id FROM `staff` WHERE `Email` = '".addslashes($_POST['Email'])."' ",$db))){
            $errors['email'] = "Email is already in use." ;
        } 
    }
    
    if (count($errors) == 0) {
        
        $INSERT = mysql_query("INSERT INTO `staff` (Username, Password, Email, Admin_level, Help, Active) VALUES (      
            '" . addslashes($_POST['Username']) . "',
            '" . sha1($_POST['Password']) . "',
            '" . addslashes($_POST['Email']) . "',
            'user',
            '1',
            '1'
        )", $db);

        if ($INSERT){
        	if (admin_login($_POST['Username'], $_POST['Password'], '0')){
                //header ("Location: index.php");
                $loggedin_ok = true;    
            }else{
                $error_occured = TRUE;
            }
        	
        }else{
            $error_occured = TRUE;
        }
        
    }	
    
}

$maintitle_title = "Register";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$CONF['APP_NAME'];?> | <?=$maintitle_title;?> | <?=$_SERVER['HTTP_HOST'];?></title>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />

<!-- INCLUDE STYLES & JAVASCRIPTS -->
<link href="./includes/css.php" rel="stylesheet" type="text/css"  media="screen" />
<script type="text/javascript" src="./includes/js.php?login=1"></script> 
<!-- INCLUDE STYLES & JAVASCRIPTS END -->

<script type="text/javascript">
$(function() {
    $('#username').focus();

    <?if ($loggedin_ok == true){?>
    parent.jQuery.colorbox.close();
    window.parent.location.reload();
    <?}?>

});
</script>

<? if (!1) { ?>
<link href="includes/style.css" rel="stylesheet" type="text/css" />
<? } ?>

</head>

<body id="login" style="height: auto;">

    <!-- NO JAVASCRIPT NOTIFICATION START -->
    <noscript>
        <div class="maintitle_nojs">This site needs Javascript enabled to function properly!</div>
    </noscript>
    <!-- NO JAVASCRIPT NOTIFICATION END -->
	
    <form id="login_form" name="login_form" method="POST" action="register.php?action=register">
        <h1>Register new account</h1>
		
		<? if (isset ($error_occured)) { ?><p id="login_message">An error occured!</p><? } ?>

        <? if (!empty($errors)) { ?>
            <div id="errors">
                <p>Please check:</p>
                <ul>
                    <? foreach ($errors as $key => $value) { echo "<li>" . $value . "</li>"; }?> 
                </ul>
            </div>
        <? } ?> 		
		
        <label for="Username">Username:</label>
        <input name="Username" id="Username" type="text" size="20" maxlength="20" class="input_field" value="<?=$_POST['Username']?>" />
        <label for="Password">Password: </label>
        <input name="Password" id="Password" type="password" size="20" maxlength="20" class="input_field" />
        <label for="Password2">Password (repeat): </label>
        <input name="Password2" id="Password2" type="password" size="20" maxlength="20" class="input_field" />
        <label for="Email">Email:</label>
        <input name="Email" id="Email" type="text" size="20" maxlength="255" class="input_field" value="<?=$_POST['Email']?>" />
        
        <div class="clr">&nbsp;</div>

        <input type="submit" name="go" id="go" value="Register" class="button_primary" />
        
    </form>
    
    <div id="forgot_dialog" title="Did you forget your password?" style="display:none">
        <p>If you lost your password contact the Administrator on this email: <br /><br /><a href="mailto:<?=$CONF['MAIL_SUPPORT']?>"><?=$CONF['MAIL_SUPPORT']?></a>.</p>
    </div>
    
</body>
</html>
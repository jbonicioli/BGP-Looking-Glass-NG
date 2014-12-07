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
    if ($_GET[action] == "logout") {
        admin_logout();
        if ($return = $_GET['return']){
            header("Location: ".urldecode($return));
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }    
} 

if (isset ($_GET['action']) && $_GET['action'] == "login") {

    if ($_POST['username'] && $_POST['password']) {
    
        if (admin_login($_POST['username'], $_POST['password'], $_POST['remember'])){
                    
            if ($return = $_GET['return']){
                //header("Location: ".urldecode($return));
                $loggedin_ok = true;
                //exit;
            } else {
                //header("Location: index.php");
                $loggedin_ok = true;
                //exit;
            }    
            
        } else {
            
            $msg = "Please check your username/password.";
        }
        
    } else {
        $msg = "Please enter your username/password.";
    }
    
// logout
} elseif (isset ($_GET['action']) && $_GET['action'] == "logout") {
    admin_logout();
    //$loggedin_ok = true;
    //$msg = "You have been logged out successfully.";    
    header ("Location: index.php?loggedout=ok");
}

$maintitle_title = "Login";

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
    
    $("#forgot_dialog").dialog({
        resizable:false, autoOpen: false, modal:true, 'open': function(event, ui){ 
            $('body').css('overflow-x','hidden'); 
        } 
    });
    $(".forgot_trigger").click(function(){
        $("#forgot_dialog").dialog('open');
        return false;
    });

    <?if ($loggedin_ok == true){?>
       if (window.location != window.parent.location){
            window.parent.location.reload();
            parent.jQuery.colorbox.close();
        }else{
            window.location.replace("index.php");
        }
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

<?/*
    <h1 id="login_title"><img src="images/logo.png" alt="<?=$CONF['APP_NAME'];?>" /></h1>
*/?>      

    <form id="login_form" name="login_form" method="POST" action="login.php?action=login&return=<?=$return?>">
        <h1>Login</h1>
        
        <? if (isset ($msg)) { ?><p id="login_message"><?=$msg?></p><? } ?>

        <label for="username">Username:</label>
        <input name="username" id="username" type="text" size="20" maxlength="20" class="input_field" value="<?=$_POST['username']?>" />
        <label for="password">Password: </label>
        <input name="password" id="password" type="password" size="20" maxlength="20" class="input_field" />

        <input type="checkbox" name="remember" id="remember" value="1"<? if ($_POST['remember']) echo " checked=\"checked\"";?> /><label for="remember" style="display:inline"> Remember</label>
        <a href="#" class="forgot_trigger" style="padding-left:10px">Did you forget your password?</a>
        <div class="clr">&nbsp;</div>

        <input type="submit" name="go" id="go" value="Login" class="button_primary" />
    
        <a href="register.php" id="login_message" style="padding-left:10px">Click here to register a new account</a>
        <div class="clr">&nbsp;</div>

    </form>
    
    <div id="forgot_dialog" title="Did you forget your password?" style="display:none">
        <p>If you lost your password contact the Administrator on this email: <br /><br /><a href="mailto:<?=$CONF['MAIL_SUPPORT']?>"><?=$CONF['MAIL_SUPPORT']?></a>.</p>
    </div>
    
</body>
</html>
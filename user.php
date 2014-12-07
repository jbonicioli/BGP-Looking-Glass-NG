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

admin_auth();


//Define current page data
$mysql_table = 'staff';

// ----------------------------------------------------------------------


//SELECT RECORD FOR EDIT
$SELECT = mysql_query("SELECT * FROM `".$mysql_table."` WHERE `id`='".addslashes($_SESSION['admin_id'])."'",$db);
if (!mysql_num_rows($SELECT)){Header ("Location: index.php"); exit(); }
$RESULT = mysql_fetch_array($SELECT);


//EDIT RECORD
if ($_POST['action'] == "edit" && $_SESSION['admin_id']) {
    
    $id = $_SESSION['admin_id'] = (int)$_SESSION['admin_id'];
    $change_pass = 0;    
    
    $errors = array();
    
    $_POST['Username'] = trim($_POST['Username']);
    if (!preg_match("/^[a-zA-Z0-9][a-zA-Z0-9_-]{2,29}$/", $_POST['Username'])) {
        $errors['username'] = "Please choose a username with 3 to 30 latin characters &amp; numbers without spaces and symbols. Hyphens '-' and underscores '_' are allowed but the username cannot begin with either of those 2 characters.";
    } else {
        
        if (mysql_num_rows(mysql_query("SELECT id FROM `".$mysql_table."` WHERE `Username` = '".addslashes($_POST['Username'])."'  AND id != '".addslashes($id)."' ",$db))){
            $errors['username'] = "Username is already in use." ;
        } 
    }
    
    if ($_POST['Password'] && $_POST['Password2']) {
        if ($_POST['Password'] != $_POST['Password2']) {
            $errors['password'] = "The passwords do not match. Leave blank if you do not wish to change the password";        
        } else {
            $change_pass = 1;
        }
    }
    
    
    $_POST['Email'] = trim($_POST['Email']);
    if ($_POST['Email']){
        if (!preg_match("/^([a-zA-Z0-9]+([\.+_-][a-zA-Z0-9]+)*)@(([a-zA-Z0-9]+((\.|[-]{1,2})[a-zA-Z0-9]+)*)\.[a-zA-Z]{2,7})$/", $_POST['Email'])) {
            $errors['email'] = "The Email address you gave is not valid" ;
        }
    }elseif(!$_POST['Email']){
        $errors['email'] = "Please enter the Email address";
    }
        
    if (count($errors) == 0) {
        
        $UPDATE = mysql_query("UPDATE `".$mysql_table."` SET
            Username  = '" . mysql_escape_string($_POST['Username'])  . "',
            Email     = '" . mysql_escape_string($_POST['Email'])     . "',
            Firstname = '" . mysql_escape_string($_POST['Firstname']) . "',
            Lastname  = '" . mysql_escape_string($_POST['Lastname'])  . "'
            
            WHERE id= '" . addslashes($id) . "'",$db);
        
        if ($change_pass) {
            $UPDATE_PASS = mysql_query("UPDATE `".$mysql_table."` SET Password = '" . sha1($_POST['Password']) . "' WHERE id= '" . addslashes($id) . "'",$db);
        }
        
        if ($UPDATE){

                session_unset();
                setcookie ($CONF['COOKIE_NAME'], "",time()-60*60*24*15, "/");
            
            header("Location: index.php?section=".$SECTION."&saved_success=1&change_pass=".$change_pass);
            exit();
        }else{
            $error_occured = TRUE;
        }
        
    }
    
}


?>
<? if (!1) { ?>
<link href="includes/style.css" rel="stylesheet" type="text/css" />
<? } ?>

                <script>
                $(function() {

                    
                    <?if (staff_help()){?>
                    //TIPSY for the ADD Form
                    $('#Username').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Firstname').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Lastname').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Email').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Password').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Password2').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    <?}?>
                    
    
    
                //CLOSE THE NOTIFICATION BAR
                $("a.close_notification").click(function() {
                    var bar_class = $(this).attr('rel');
                    //alert(bar_class);
                    $('.'+bar_class).hide();
                    return false;
                });

                
                });
                

                </script>
                
                <!-- STAFF SECTION START -->

                <div id="main_content">
            
                    <? if ($_GET['saved_success']) { ?>
                        <p class="success"><span style="float: right;"><a href="javascript:void(0)" style="margin:0 auto" class="<?if (staff_help()){?>tip_east<?}?> close_notification" rel="success" title="Close notification bar"><span>Close Notification Bar</span></a></span>
                        Record saved successfully. <? if ($_GET['change_pass']) echo " Password changed."; ?></p>
                    <? } ?>
                    <? if ($error_occured) { ?>
                        <p class="error"><span style="float: right;"><a href="javascript:void(0)" style="margin:0 auto" class="<?if (staff_help()){?>tip_east<?}?> close_notification" rel="error" title="Close notification bar"><span>Close Notification Bar</span></a></span>An error occured.</p>
                    <? } ?>
                    
                    <p class="notification_success"><span style="float: right;"><a href="javascript:void(0)" style="margin:0 auto" class="<?if (staff_help()){?>tip_east<?}?> close_notification" rel="notification_success" title="Close notification bar"><span>Close Notification Bar</span></a></span><span id="notification_success_response"></span></p>
                    <p class="notification_fail"><span style="float: right;"><a href="javascript:void(0)" style="margin:0 auto" class="<?if (staff_help()){?>tip_east<?}?> close_notification" rel="notification_fail" title="Close notification bar"><span>Close Notification Bar</span></a></span><span id="notification_fail_response"></span></p>
                        
                    <div id="toggler">
                    
                        <!-- ADD/EDIT ACCOUNTS START -->
                        <? if (!empty($errors)) { ?>
                            <div id="errors">
                                <p>Please check:</p>
                                <ul>
                                    <? foreach ($errors as $key => $value) { echo "<li>" . $value . "</li>"; }?> 
                                </ul>
                            </div>
                        <? } ?>                        
                        
                        <form id="form" method="post" action="index.php?section=<?=$SECTION;?>&action=edit">
                        
                            
                            <fieldset>
                                
                                <legend>&raquo; Edit account</legend>
                        
                                     <div class="columns">
                                        <div class="colx2-left">
                                        
                                            <p>
                                                <label for="Username" class="required">Username</label>
                                                <input type="text" name="Username" id="Username" title="Enter the Username" value="<? if($_POST['Username']){ echo $_POST['Username']; }else{ echo stripslashes($RESULT['Username']);} ?>">
                                            </p>
                                            
                                            <p>
                                                <label for="username">Firstname</label>
                                                <input type="text" name="Firstname" id="Firstname" title="Enter the Firstname" value="<? if($_POST['Firstname']){ echo $_POST['Firstname']; }else{ echo stripslashes($RESULT['Firstname']);} ?>">
                                            </p>
                                            
                                            <p>
                                                <label for="Lastname">Lastname</label>
                                                <input type="text" name="Lastname" id="Lastname" title="Enter the Lastname" value="<? if($_POST['Lastname']){ echo $_POST['Lastname']; }else{ echo stripslashes($RESULT['Lastname']);} ?>">
                                            </p>
                                            
											                                           
                                        </div>
                                        <div class="colx2-right">
                                            
                                            <p>
                                                <label for="Password" class="required">Password</label>
                                                <input type="text" name="Password" id="Password" title="Enter the Password" >
                                                <br />Enter password twice if you wish to change it.<br />Leave empty to keep the current password.</strong>
                                            </p>
                                            
                                            <p>
                                                <label for="Password2">Password (repeat)</label>
                                                <input type="text" name="Password2" id="Password2" title="Re-enter the Password for validation">
                                            </p>
                                            
                                            <p>
                                                <label for="Email" class="required">E-Mail</label>
                                                <input type="text" name="Email" id="Email" title="Enter the Email" value="<? if($_POST['Email']){ echo $_POST['Email']; }else{ echo stripslashes($RESULT['Email']);} ?>">
                                            </p>
                                            

                                            
                                        </div>
                                        
                                     </div>
                        
                           </fieldset>

                           <fieldset>
                                <legend>&raquo; Action</legend>
                                <button type="submit"  >Save</button>&nbsp; &nbsp;
                                <button type="reset"  id="button">Cancel</button>
                                <input  type="hidden" name="action" id="action" value="edit" />
                                &nbsp;&nbsp;&nbsp;After changing your account details you will be logged out from the system automatically for the changes to take effect.
                           </fieldset>
                        </form>                    
                        
                        <!-- ADD/EDIT ACCOUNTS END -->
                        
                        <br />
                        
                    </div>
                    
                        
                </div>    
                
                <!-- STAFF SECTION END --> 
                
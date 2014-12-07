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

if ($_SESSION['admin_level'] != 'admin'){
	header ("Location: index.php");
}

//Define current page data
$mysql_table = 'settings';

// ----------------------------------------------------------------------

$action_title = "All System Settings"; 

$url_vars = "action=".$_GET['action'];
    

//SELECT RECORD FOR EDIT
if ( $_GET['action'] == "edit" && $_GET['id'] ) {
    $SELECT = mysql_query("SELECT * FROM `".$mysql_table."` WHERE `id`='".addslashes($_GET['id'])."'",$db);
    $RESULT = mysql_fetch_array($SELECT);
}


if ($_POST['action'] == "edit" && $_POST['id']) {
    
    $id = $_POST['id'] = (int)$_POST['id'];
    $change_pass = 0;    
    
    $errors = array();
    
    
    $_POST['Value'] = trim($_POST['Value']);
    if (!$_POST['Value']) {
        $errors['value'] = "Please fill in the setting value.";
    }
        
        
    if (count($errors) == 0) {
        
        $UPDATE = mysql_query("UPDATE `".$mysql_table."` SET
            Value = '" . addslashes($_POST['Value']) . "'
            
            WHERE id= '" . $_POST['id'] . "'",$db);
        
        if ($UPDATE){
            $_SESSION['admin_help'] = $_POST['Help'];
            header("Location: index.php?section=".$SECTION."&saved_success=1");
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
                	
                	// most effect types need no options passed by default
                    var options = {};    
                    
                    // Hide/Show the ADD Form
                    $( "#button" ).click(function() {
                        $( "#toggler" ).toggle( "blind", options, 500, function (){
                            
                        } );
                        return false;
                    });

                    // Hide/Show the RESULTS Table
                    $( "#button2" ).click(function() {
                        $( "#toggler2" ).toggle( "blind", options, 500, function (){
                            $('#toggle_state').val('1');
                        } );
                        return false;
                    });
                    
                    //Init
                    <?if ($_POST['action'] || $_GET['action'] == 'edit'){?>
                        $( "#toggler" ).show();
                    <?}else{?>
                        $( "#toggler" ).hide();
                    <?}?>
                    $( "#toggler2" ).show();
                    
                    
                    <?if (staff_help()){?>
                    //TIPSY for the ADD Form
                    $('#Name').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Value').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Description').tipsy({trigger: 'focus', gravity: 's', fade: true});
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
                
                <!-- SETTINGS SECTION START -->
                
                
                <div id="main_content">
                
                <div class="mainsubtitle_bg">
                    <div class="mainsubtitle"><a href="javascript: void(0)" id="button2">List System Settings</a></div>
                </div> 
                            
                <br />
                
                	<!-- SHOW WARNING -->
                	<p class="alert"><span style="float: right;"><a href="javascript:void(0)" style="margin:0 auto" class="<?if (staff_help()){?>tip_east<?}?> close_notification" rel="alert" title="Close notification bar"><span>Close Notification Bar</span></a></span><strong>Please change these settings only if you are absolutely sure. These can break the system if not configured properly!</strong></p>
                	
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
                    
                        <!-- EDIT SETTINGS START -->
                        <? if (!empty($errors)) { ?>
                            <div id="errors">
                                <p>Please check:</p>
                                <ul>
                                    <? foreach ($errors as $key => $value) { echo "<li>" . $value . "</li>"; }?> 
                                </ul>
                            </div>
                        <? } ?>                        
                        
                        <form id="form" method="post" action="index.php?section=<?=$SECTION;?>&action=<?if ($_GET['action'] == 'edit'){ echo 'edit&id='.$_GET['id'];}else{ echo 'add'; } ?>">
                        
                            
                            <fieldset>
                                
                                <legend>&raquo; <?if ($_GET['action'] == 'edit'){?>Edit Setting<?}else{?>New Setting<?}?></legend>
                        
                                     <div class="columns">
                                        <div class="colx2-left">
                                        
                                            <p>
                                                <label for="Name" >Setting Name</label>
                                                <input type="text" name="Name" id="Name" title="Setting Name" size="80" value="<?=stripslashes($RESULT['Name']);?>" readonly>
                                            </p>

											<p>
                                                <label for="Value"  class="required">Setting Value</label>
                                                <input type="text" name="Value" id="Value" title="Enter the setting value here" value="<? if($_POST['Value']){ echo $_POST['Value']; }elseif ($_GET['action'] == "edit"){ echo stripslashes($RESULT['Value']);} ?>">
                                            </p>

											
                                        </div>
                                        <div class="colx2-right">
                                            
                                            
                                            <p>
                                                <label for="Description">Setting Description</label>
                                                <textarea name="Description" id="Description" rows="4" cols="80"  readonly title="Setting Description"><?=stripslashes($RESULT['Description']);?></textarea>
                                            </p>
                                                                                        
                                               
                                        </div>
                                        
                                     </div>
                        
                           </fieldset>

                           <fieldset>
                                <legend>&raquo; Action</legend>
                                <button type="submit"  >Save</button>&nbsp; &nbsp;
                                <button type="reset"  id="button">Cancel</button>
                                <input  type="hidden" name="action" id="action" value="<?if ($_GET['action'] == 'edit'){ echo 'edit';}else{ echo 'add'; } ?>" />
                                <?if ($_GET['action'] == 'edit'){?><input  type="hidden" name="id" id="id" value="<?=$RESULT['id'];?>" /><?}?>
                           </fieldset>
                        </form>                    
                        
                        <!-- EDIT SETTINGS END -->
                        
                        <br />
                        
                    </div>
                        
                    <div id="toggler2">
                      
                    <!-- LIST SETTINGS START -->
                      
                      <fieldset>
                                
                      <legend>&raquo; Settings List</legend>
                        
                      
                      <!-- SHOW GENERAL SETTINGS -->
                      <h3>General Settings</h3>
                      <table width="100%" border="0" cellspacing="2" cellpadding="5">
                      <tr>
                        <th width="220">Setting Name</th>
                        <th width="300">Setting Value</th>
                        <th>Description</th>
                        <th width="50">Actions</th>
                      </tr>
                      <?
                      $i=-1;
                      $SELECT_RESULTS  = mysql_query("SELECT `".$mysql_table."`.* FROM `".$mysql_table."` WHERE Type = 'general' ORDER BY Name" ,$db);
                      while($LISTING = mysql_fetch_array($SELECT_RESULTS)){
                      $i++;
                      ?>      
                      <tr onmouseover="this.className='on' " onmouseout="this.className='off' " id="tr-<?=$LISTING['id'];?>">
                        <td nowrap><a href="index.php?section=<?=$SECTION;?>&action=edit&id=<?=$LISTING['id'];?>" title="Edit setting" class="<?if (staff_help()){?>tip_south<?}?>"><?=$LISTING['Name'];?></a></td>
                        <td nowrap ><?=$LISTING['Value'];?></td>
                        <td><?=$LISTING['Description'];?></td>
                        <td align="center" nowrap="nowrap"><a href="index.php?section=<?=$SECTION;?>&amp;action=edit&amp;id=<?=$LISTING['id'];?>" title="Edit" class="<?if (staff_help()){?>tip_south<?}?> edit"><span>Edit</span></a></td>
					  </tr>
                      <?}?>
                    </table>
                      
                      
                      <!-- SHOW PANEL SETTINGS -->
                      <br />
                      <h3>Control Panel Settings</h3>
                      <table width="100%" border="0" cellspacing="2" cellpadding="5">
                      <tr>
                        <th width="220">Setting Name</th>
                        <th width="300">Setting Value</th>
                        <th>Description</th>
                        <th width="50">Actions</th>
                      </tr>
                      <?
                      $i=-1;
                      $SELECT_RESULTS  = mysql_query("SELECT `".$mysql_table."`.* FROM `".$mysql_table."` WHERE Type = 'panel' ORDER BY Name" ,$db);
                      while($LISTING = mysql_fetch_array($SELECT_RESULTS)){
                      $i++;
                      ?>      
                      <tr onmouseover="this.className='on' " onmouseout="this.className='off' " id="tr-<?=$LISTING['id'];?>">
                        <td nowrap><a href="index.php?section=<?=$SECTION;?>&action=edit&id=<?=$LISTING['id'];?>" title="Edit setting" class="<?if (staff_help()){?>tip_south<?}?>"><?=$LISTING['Name'];?></a></td>
                        <td nowrap ><?=$LISTING['Value'];?></td>
                        <td><?=$LISTING['Description'];?></td>
                        <td align="center" nowrap="nowrap"><a href="index.php?section=<?=$SECTION;?>&amp;action=edit&amp;id=<?=$LISTING['id'];?>" title="Edit" class="<?if (staff_help()){?>tip_south<?}?> edit"><span>Edit</span></a></td>
					  </tr>
                      <?}?>
                    </table>
                    
                    </fieldset>
                    
                    <!-- LIST SETTINGS END -->
                    
                    </div>
                        
                </div>    
                
                <!-- SETTINGS  SECTION END --> 
                
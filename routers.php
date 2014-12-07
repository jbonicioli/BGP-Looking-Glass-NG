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
$mysql_table = 'routers';
$sorting_array = array("id", "RouterName", "NodeID", "NodeName", "Type", "Ip", "Port", "User", "Pass", "Active", "User_id", "Status", "Trace", "Stats");

// ----------------------------------------------------------------------

$action_title = "All BGP Routers"; 

//if ($action == "list") {
    
    $search_vars = "";
        
    $q = mysql_real_escape_string($_GET['q'], $db);
    if ($q) { 
        $search_vars .= "&q=$q"; 
        $action_title = "Search: " . $q;
    }
    
    $qu = mysql_real_escape_string($_GET['search_user_id'], $db);
    if ($qu) { 
        $search_vars .= "&search_user_id=$qu";
        
        $CHAN_OWNER = " AND User_id = '".$qu."' ";
         
    }
    
    
    $qt = mysql_real_escape_string($_GET['search_type'], $db);
    if ($qt) { 
        $search_vars .= "&search_type=$qt";
        
        $ROUTER_TYPE = " AND Type = '".$qt."' ";
         
    }
    
    if ($_SESSION['admin_level'] == 'user'){
		$level = " AND User_id = '".$_SESSION['admin_id']."' ";
    }else{
		$level = '';
    }
    
    $search_query = "WHERE ($mysql_table.id LIKE '%$q%' OR $mysql_table.RouterName LIKE '%$q%' OR $mysql_table.NodeID LIKE '%$q%' OR $mysql_table.NodeName LIKE '%$q%'  OR $mysql_table.Type LIKE '%$q%' OR $mysql_table.Ip LIKE '%$q%'  OR $mysql_table.Port LIKE '%$q%' OR $mysql_table.User LIKE '%$q%' OR $mysql_table.Pass LIKE '%$q%' OR $mysql_table.Status LIKE '%$q%') $CHAN_OWNER $ROUTER_TYPE $level";
    
      
    // Sorting
    if (isset($_GET['sort'])){
        if (in_array($_GET['sort'], $sorting_array)) {
            if ($_GET['by'] !== "desc" && $_GET['by'] !== "asc") {
                $_GET['by'] = "desc";
            }
            $order = "ORDER BY `". mysql_escape_string($_GET['sort']) ."` ". mysql_escape_string($_GET['by']) . " ";
        }
    } else {
        $order = "ORDER BY `id` DESC ";
        $_GET['sort'] = "id";
        $_GET['by'] = "desc";
    }
    $sort_vars = "&sort=".$_GET['sort']."&by=".$_GET['by'];


    // Paging
    $count = mysql_query("SELECT id FROM $mysql_table $search_query",$db);
    $items_number  = mysql_num_rows($count);
    if ($_GET['items_per_page'] && is_numeric($_GET['items_per_page'])){
        $_SESSION['items_per_page'] = $_GET['items_per_page'];
    }
    if ($_POST['items_per_page'] && is_numeric($_POST['items_per_page'])){
        $_SESSION['items_per_page'] = $_POST['items_per_page'];
    }
    if (isset($_SESSION['items_per_page']) && is_numeric($_SESSION['items_per_page'])){
        $num = $_SESSION['items_per_page'];
    } else { 
        $_SESSION['items_per_page'] = $CONF['ADMIN_ITEMS_PER_PAGE'];
        $num = $CONF['ADMIN_ITEMS_PER_PAGE'];     
    }
    $e = $num;
    $pages = $items_number/$num;
    if (!$_GET['pageno']){
        $pageno = 0; 
    }else{
        $pageno = $_GET['pageno'];
    }
    if (isset($_POST['goto'])) {
        if ($_POST['goto'] <= $pages + 1) {
            $pageno = $num * ($_POST['goto'] - 1);
        } else {
            $pageno = 0;
        }
    }
    $current_page = 0;
    for($i=0;$i<$pages;$i++){
        $y=$i+1;
        $page=$i*$num;
        if ($page == $pageno){
            $current_page = $y;
        }
    } 
    $total_pages=$i; // sinolo selidon
    
    //Final Query for records listing
    $SELECT_RESULTS  = mysql_query("SELECT `".$mysql_table."`.* FROM `".$mysql_table."` ".$search_query." ".$order." LIMIT ".$pageno.", ".$e ,$db);
    $url_vars = "action=".$_GET['action'] . $sort_vars . $search_vars;
    
    $q = htmlspecialchars($q);
    $search_vars = htmlspecialchars($search_vars);
    $url_vars = htmlspecialchars($url_vars);




//SELECT RECORD FOR EDIT
if ( $_GET['action'] == "edit" && $_GET['id'] ) {
	
	if ($_SESSION['admin_level'] == 'user'){
		$user_id = " AND User_id = '".$_SESSION['admin_id'] . "' ";  	
	}else{
		$user_id = '';
	}
	
    $SELECT = mysql_query("SELECT * FROM `".$mysql_table."` WHERE `id`='".mysql_escape_string($_GET['id'])."' $user_id",$db);
    $RESULT = mysql_fetch_array($SELECT);
}

//ADD NEW RECORD
if ($_POST['action'] == "add" ) {
    
    $errors = array();
    
    $_POST['RouterName'] = trim($_POST['RouterName']);
    if (!preg_match('/^[a-z0-9 .\-]+$/i', $_POST['RouterName'])) {
    //if (!preg_match("/^[a-zA-Z0-9][a-zA-Z0-9]{2,19}$/", $_POST['RouterName'])) {
        $errors['routername'] = "Please enter a valid router name (Only alphanumeric characters allowed).";
    } else {
        //if (mysql_num_rows(mysql_query("SELECT id FROM `".$mysql_table."` WHERE `RouterName` = '".mysql_escape_string($_POST['RouterName'])."' AND User_id = '".$_POST['User_id']."' ",$db))){
        //    $errors['router'] = "This Router Name is already registered." ;
        //} 
    }

    if (!$_POST['NodeID'] || !is_numeric($_POST['NodeID'])){
        $errors['nodeid'] = "You must type the NodeID that this router is running on. Only NodeID Number. No Node Name, no # symbols.";
    }
    
    $_POST['NodeName'] = trim($_POST['NodeName']);
    if (!preg_match('/^[a-z0-9 .\-]+$/i', $_POST['NodeName'])) {
    //if (!preg_match("/^[a-zA-Z0-9][a-zA-Z0-9]{2,19}$/", $_POST['NodeName'])) {
        $errors['nodename'] = "Please enter a valid node name (Only alphanumeric characters allowed).";
    }

    if (!$_POST['User_id']) {
        $errors['userid'] = "Please select the Router Owner" ;
    }
    
    if ( !$_POST['Type']){
        $errors['type'] = "Please select the Router BGP Implementation (MikroTik or Quagga)";
    }
    
    $_POST['Ip'] = trim($_POST['Ip']);
    if (!filter_var( $_POST['Ip'], FILTER_VALIDATE_IP)) {
        $errors['ip'] = "Please fill in the router's IP Address. No hostnames allowed.";
    } else {
        
        if (mysql_num_rows(mysql_query("SELECT id FROM `".$mysql_table."` WHERE `Ip` = '".mysql_escape_string($_POST['Ip'])."' ",$db))){
            $errors['ip'] = "This IP Address is already registered on the system." ;
        } 
    }
    
    if (!$_POST['Port'] || !is_numeric($_POST['Port'])){
        $errors['port'] = "Please fill in the port for the Router. SSH port for MikroTik, Quagga BGPd Port for Linux.";
    }

    if ( $_POST['Type'] == 'mikrotik' && !$_POST['User']){
        $errors['user'] = "Please fill in the SSH read only username for MikroTik.";
    }
    
    if (!$_POST['Pass']){
		$errors['pass'] = "Please fill in the Router Read Only Password";
    }
    
    
    if (count($errors) == 0) {
        
        $INSERT = mysql_query("INSERT INTO `".$mysql_table."` (RouterName, NodeID, NodeName, User_id, Type, Ip, Port, User, Pass, Active
        	) VALUES (      
            '" . mysql_escape_string($_POST['RouterName']) . "',
            '" . mysql_escape_string($_POST['NodeID'])."',
            '" . mysql_escape_string($_POST['NodeName']) . "',
            '" . mysql_escape_string($_POST['User_id']) . "',
            '" . mysql_escape_string($_POST['Type']) . "',
            '" . mysql_escape_string($_POST['Ip']) . "',
            '" . mysql_escape_string($_POST['Port']) . "',
            '" . mysql_escape_string($_POST['User']) . "',
            '" . mysql_escape_string($_POST['Pass']) . "',
            '1'
        )", $db);

        if ($INSERT){
            header("Location: index.php?section=".$SECTION."&saved_success=1");
            exit();
        }else{
            $error_occured = TRUE;
        }
        
    }
        
}


if ($_POST['action'] == "edit" && $_POST['id']) {
    
    $id = $_POST['id'] = (int)$_POST['id'];
    $change_pass = 0;    
    
    $errors = array();

    $_POST['RouterName'] = trim($_POST['RouterName']);
    if (!preg_match('/^[a-z0-9 .\-]+$/i', $_POST['RouterName'])) {
    //if (!preg_match("/^[a-zA-Z0-9][a-zA-Z0-9]{2,19}$/", $_POST['RouterName'])) {
        $errors['routername'] = "Please enter a valid router name (Only alphanumeric characters allowed).";
    } else {
        //if (mysql_num_rows(mysql_query("SELECT id FROM `".$mysql_table."` WHERE `RouterName` = '".mysql_escape_string($_POST['RouterName'])."' AND User_id = '".$_POST['User_id']."' AND id != '".$_POST['id']."'  ",$db))){
        //    $errors['router'] = "This Router Name is already registered." ;
        //} 
    }

    if (!$_POST['NodeID'] || !is_numeric($_POST['NodeID'])){
        $errors['nodeid'] = "You must type the NodeID that this router is running on. Only NodeID Number. No Node Name, no # symbols.";
    }
    
    $_POST['NodeName'] = trim($_POST['NodeName']);
    if (!preg_match('/^[a-z0-9 .\-]+$/i', $_POST['NodeName'])) {
    //if (!preg_match("/^[a-zA-Z0-9][a-zA-Z0-9]{2,19}$/", $_POST['NodeName'])) {
        $errors['nodename'] = "Please enter a valid node name (Only alphanumeric characters allowed).";
    }
    
    if (!$_POST['User_id']) {
        $errors['userid'] = "Please select the Router Owner" ;
    }
    
    if ( !$_POST['Type']){
        $errors['type'] = "Please select the Router BGP Implementation (MikroTik or Quagga)";
    }
    
    $_POST['Ip'] = trim($_POST['Ip']);
    if (!filter_var( $_POST['Ip'], FILTER_VALIDATE_IP)) {
        $errors['ip'] = "Please fill in the router's IP Address. No hostnames allowed.";
    } else {
        
        if (mysql_num_rows(mysql_query("SELECT id FROM `".$mysql_table."` WHERE `Ip` = '".mysql_escape_string($_POST['Ip'])."'  AND id != '".$_POST['id']."' ",$db))){
            $errors['ip'] = "This IP Address is already registered on the system." ;
        } 
    }
    
    if (!$_POST['Port'] || !is_numeric($_POST['Port'])){
        $errors['port'] = "Please fill in the port for the Router. SSH port for MikroTik, Quagga BGPd Port for Linux.";
    }

    if ( $_POST['Type'] == 'mikrotik' && !$_POST['User']){
        $errors['user'] = "Please fill in the SSH read only username for MikroTik.";
    }
    
    if (!$_POST['Pass']){
        $errors['pass'] = "Please fill in the Router Read Only Password";
    }


    if (count($errors) == 0) {
        
        $UPDATE = mysql_query("UPDATE `".$mysql_table."` SET
            RouterName = '" . mysql_escape_string($_POST['RouterName']) . "',
            NodeID = '" . mysql_escape_string($_POST['NodeID']) . "',
            NodeName = '" . mysql_escape_string($_POST['NodeName']) . "',
            User_id = '" . mysql_escape_string($_POST['User_id']) . "',
            Type = '" . mysql_escape_string($_POST['Type']) . "',
            Ip = '" . mysql_escape_string($_POST['Ip']) . "',
            Port = '" . mysql_escape_string($_POST['Port']) . "',
            User = '" . mysql_escape_string($_POST['User']) . "',
            Pass = '" . mysql_escape_string($_POST['Pass']) . "'
            
            WHERE id= '" . $_POST['id'] . "'",$db);
        
        if ($UPDATE){
            $_SESSION['admin_help'] = $_POST['Help'];
            header("Location: index.php?section=".$SECTION."&saved_success=1");
            exit();
        }else{
            $error_occured = TRUE;
        }
        
    }
    
}elseif ($_POST['action'] == 'edit'){
	$error_occured = TRUE;
}




// DELETE RECORD
if ($_GET['action'] == "delete" && $_POST['id']){
    $id = addslashes(str_replace ("tr-", "", $_POST['id']));

	if ($_SESSION['admin_level'] == 'user'){
		$user_id = " AND User_id = '".$_SESSION['admin_id'] . "' ";  	
	}else{
		$user_id = '';
	}
    
    $SELECT_CHANNEL = mysql_query("SELECT RouterName FROM routers WHERE id = '".$id."' $user_id", $db);
    $CHANNEL = mysql_fetch_array($SELECT_CHANNEL);

    if (mysql_num_rows($SELECT_CHANNEL)){
		$DELETE = mysql_query("DELETE FROM `".$mysql_table."` WHERE `id`= '".$id."' " ,$db);
	    
	    if ($DELETE){
	        ob_end_clean();
	        echo "ok";
	    } else {
	        ob_end_clean();
	        echo "An error has occured.";
	    }
	}
	
    exit();
} 

// ENABLE/DISABLE RECORD
if ($_GET['action'] == "toggle_active" && $_POST['id'] && isset($_POST['option'])){
    $id = addslashes($_POST['id']);
    $option = addslashes($_POST['option']);
    
    if ($_SESSION['admin_level'] == 'user'){
		$user_id = " AND User_id = '".$_SESSION['admin_id'] . "' ";  	
	}else{
		$user_id = '';
	}
    
    $UPDATE = mysql_query("UPDATE `".$mysql_table."` SET `Active` = '".$option."' WHERE `id`= '".$id."' $user_id",$db);
	
	if ($UPDATE) {
        //print_r($_GET);
        ob_clean();
        echo "ok";
    } else {
        ob_clean();
        echo "An error has occured.";
    }
    exit();
}

// ENABLE/DISABLE BGP STATS
if ($_GET['action'] == "toggle_stats" && $_POST['id'] && isset($_POST['option'])){
    $id = addslashes($_POST['id']);
    $option = addslashes($_POST['option']);
    
    if ($_SESSION['admin_level'] == 'user'){
        $user_id = " AND User_id = '".$_SESSION['admin_id'] . "' ";     
    }else{
        $user_id = '';
    }
    
    $UPDATE = mysql_query("UPDATE `".$mysql_table."` SET `Stats` = '".$option."' WHERE `id`= '".$id."' $user_id",$db);
    
    if ($UPDATE) {
        //print_r($_GET);
        ob_clean();
        echo "ok";
    } else {
        ob_clean();
        echo "An error has occured.";
    }
    exit();
}


// ENABLE/DISABLE TRACEROUTE
if ($_GET['action'] == "toggle_trace" && $_POST['id'] && isset($_POST['option'])){
    $id = addslashes($_POST['id']);
    $option = addslashes($_POST['option']);
    
    if ($_SESSION['admin_level'] == 'user'){
        $user_id = " AND User_id = '".$_SESSION['admin_id'] . "' ";     
    }else{
        $user_id = '';
    }
    
    $UPDATE = mysql_query("UPDATE `".$mysql_table."` SET `Trace` = '".$option."' WHERE `id`= '".$id."' $user_id",$db);
    
    if ($UPDATE) {
        //print_r($_GET);
        ob_clean();
        echo "ok";
    } else {
        ob_clean();
        echo "An error has occured.";
    }
    exit();
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
                            
                            //if ( $('#toggle_state').val('1') )
                            $('#toggle_state').val('1');

                            
                            
                        } );
                        return false;
                    });
                    
                    //Init
                    <?if ($_POST['action'] || $_GET['action'] == 'edit' || $_GET['action'] == 'add'){?>
                        $( "#toggler" ).show();
                    <?}else{?>
                        $( "#toggler" ).hide();
                    <?}?>
                    $( "#toggler2" ).show();
                    
                    
                    <?if (staff_help()){?>
                    //TIPSY for the ADD Form
                    $('#RouterName').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#NodeID').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#User_id').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Type').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Ip').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Port').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#User').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Pass').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    $('#Active').tipsy({trigger: 'focus', gravity: 'w', fade: true});
                    <?}?>
                    

                    //DELETE RECORD
                    $('a.delete').click(function () {
                        var record_id = $(this).attr('rel');
                        if(confirm('Are you sure you want to delete this record?\n\rThis action cannot be undone!')){
                            $.post("index.php?section=<?=$SECTION;?>&action=delete", {
                                    id: record_id
                                }, function(response){
                                    if (response == "ok"){
                                        $('#'+record_id).hide();
                                        $("#notification_success_response").html('Record deleted successfully.');
                                        $('.notification_success').show();
                                        var total_records = $('span#total_records').html();
                                         total_records--;
                                         $('span#total_records').html(total_records);
                                    } else {
                                        $("#notification_fail_response").html('An error occured.' );
                                        $('.notification_fail').show();
                                        //alert(response);
                                    }
                                });
                            return false;
                        }
                    });

                    
                    
                    //SET ACTIVE FLAG
                    $('a.toggle_active').click(function () {
                    	var dochange = '0';
	                    if ($(this).hasClass('activated')){
	                       // if(confirm('Are you sure you want disable this router?')){    
	                            var option = '0';
	                            var dochange = '1';
							//}
	                    } else if ($(this).hasClass('deactivated')){
	                        var option = '1';
	                        var dochange = '1';
	                    }
	                    if (dochange == '1'){
		                    var myItem = $(this);
		                    var record_id = $(this).attr('rel');
		                    $.post("index.php?section=<?=$SECTION;?>&action=toggle_active", {
                          		id: record_id,
		                        option: option
		                    }, function(response){
		                        if (response == "ok"){
		                            $(myItem).toggleClass('activated');
		                            $(myItem).toggleClass('deactivated');
		                        } else {
		                            $("#notification_fail_response").html('An error occured.' );
		                            $('.notification_fail').show();
		                            //alert(response);
		                        }
		                    });
		                    
						}
	                    return false;
	            	});
    
    
                    
                    //SET BGP STATS  FLAG
                    $('a.toggle_stats').click(function () {
                        var dochange = '0';
                        if ($(this).hasClass('yes')){
                           // if(confirm('Are you sure you want disable this router?')){    
                                var option = '0';
                                var dochange = '1';
                            //}
                        } else if ($(this).hasClass('no')){
                            var option = '1';
                            var dochange = '1';
                        }
                        if (dochange == '1'){
                            var myItem = $(this);
                            var record_id = $(this).attr('rel');
                            $.post("index.php?section=<?=$SECTION;?>&action=toggle_stats", {
                                id: record_id,
                                option: option
                            }, function(response){
                                if (response == "ok"){
                                    $(myItem).toggleClass('yes');
                                    $(myItem).toggleClass('no');
                                } else {
                                    $("#notification_fail_response").html('An error occured.' );
                                    $('.notification_fail').show();
                                    //alert(response);
                                }
                            });
                            
                        }
                        return false;
                    });


                    //SET TRACE FLAG
                    $('a.toggle_trace').click(function () {
                        var dochange = '0';
                        if ($(this).hasClass('yes')){
                           // if(confirm('Are you sure you want disable this router?')){    
                                var option = '0';
                                var dochange = '1';
                            //}
                        } else if ($(this).hasClass('no')){
                            var option = '1';
                            var dochange = '1';
                        }
                        if (dochange == '1'){
                            var myItem = $(this);
                            var record_id = $(this).attr('rel');
                            $.post("index.php?section=<?=$SECTION;?>&action=toggle_trace", {
                                id: record_id,
                                option: option
                            }, function(response){
                                if (response == "ok"){
                                    $(myItem).toggleClass('yes');
                                    $(myItem).toggleClass('no');
                                } else {
                                    $("#notification_fail_response").html('An error occured.' );
                                    $('.notification_fail').show();
                                    //alert(response);
                                }
                            });
                            
                        }
                        return false;
                    });
    
                //CLOSE THE NOTIFICATION BAR
                $("a.close_notification").click(function() {
                    var bar_class = $(this).attr('rel');
                    //alert(bar_class);
                    $('.'+bar_class).hide();
                    return false;
                });


                //SHOW/HIDE INPUT FIELDS BASED ON DROPDOWN MENU SELECTION
                $('#Type').live('change', function(){
                    var myval = $('option:selected',this).val();
                    if (myval == 'mikrotik') { 
                        $('#User').show();
                    }else if(myval == 'quagga') {
                        $('#User').hide();
                    }
                });


                
                });
                

                </script>
                
                <!-- ROUTERS SECTION START -->
                
                <div id="main_content">
                
                <div class="mainsubtitle_bg">
                    <div class="mainsubtitle"><a href="javascript: void(0)" id="button2">List all BGP Routers</a> | <?if ($_GET['action'] == 'edit'){?><a href="index.php?section=<?=$SECTION;?>&action=add">Add New BGP Router</a> | <a href="index.php?section=<?=$SECTION;?>">Back to Routers List</a><?}else{?><a href="javascript: void(0)" id="button">Add New BGP Router</a><?}?></div>
                </div> 
                            
                <br />
                    
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
                    
                        <!-- ADD/EDIT ROUTERS START -->
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
                                
                                <legend>&raquo; <?if ($_GET['action'] == 'edit'){?>Edit BGP Router<?}else{?>New BGP Router<?}?></legend>
                        
                                     <div class="columns">
                                        <div class="colx2-left">
                                        
                                            <p>
                                                <label for="RouterName" class="required">BGP Router Name</label>
                                                <input type="text" name="RouterName" id="RouterName" title="Enter a Router Name (only alphanumeric values allowed)" value="<? if ($_GET['action'] == "edit"){ echo stripslashes($RESULT['RouterName']);}elseif($_POST['RouterName']){ echo $_POST['RouterName']; } ?>">
                                            </p>
                                            <p>
                                                <label for="NodeID" class="required">Node ID</label>
                                                <input type="text" name="NodeID" id="NodeID" title="Enter the Node ID this router is running on" value="<? if ($_GET['action'] == "edit"){ echo stripslashes($RESULT['NodeID']);}elseif($_POST['NodeID']){ echo $_POST['NodeID']; } ?>">
                                            </p>

                                            <p>
                                                <label for="NodeName" class="required">Node Name</label>
                                                <input type="text" name="NodeName" id="NodeName" title="Enter the Node Name this router is running on" value="<? if ($_GET['action'] == "edit"){ echo stripslashes($RESULT['NodeName']);}elseif($_POST['NodeName']){ echo $_POST['NodeName']; } ?>">
                                            </p>

                                            <? if ($_SESSION['admin_level'] == 'admin'){?>
                                            <p>
                                                <label for="User_id" class="required">Router Owner</label>
                                                <select name="User_id" id="User_id" title="Select a channel owner" >
                                                    <option value="" selected="selected">--Select--</option>
                                                    <? 
                                                    $SELECT_USERS = mysql_query("SELECT id, Username, Firstname, Lastname FROM staff WHERE Active ='1' ORDER BY id ASC", $db);
                                                    while ($USERS = mysql_fetch_array($SELECT_USERS)){
                                                    ?>                                                    
                                                    <option value="<?=$USERS['id'];?>"   <? if ($_POST['User_id'] == $USERS['id']){ echo "selected=\"selected\""; }elseif ($_GET['action'] == "edit" && $RESULT['User_id'] == $USERS['id'])   { echo "selected=\"selected\""; }?> ><?=$USERS['Username'];?> <?if ($USERS['Firstname'] || $USERS['Lastname']){?>(<?=$USERS['Firstname'];?> <?=$USERS['Lastname'];?> )<?}?></option>
                                                    <?}?>                                                    
                                                    
                                                </select>
                                            </p>
                                            <?} else {?>
                                            <input  type="hidden" name="User_id" id="User_id" value="<?=$_SESSION['admin_id']?>" /> 
                                            <?}?>

                                            
                                            <p>
                                                <label for="Type" class="required">Router Type</label>
                                                <select name="Type" id="Type" title="Select the type of this router" >
                                                    <option value="" selected="selected">--Select--</option>
                                                    <option value="mikrotik"   <? if ($_POST['Type'] == 'mikrotik'){ echo "selected=\"selected\""; $show_mikrotik=1; }elseif ($_GET['action'] == "edit" && $RESULT['Type'] == 'mikrotik')   { echo "selected=\"selected\""; $show_mikrotik=1;}?> >MikroTik Router</option>
                                                    <option value="quagga" <? if ($_POST['Type'] == 'quagga'){ echo "selected=\"selected\""; $show_quagga = 1; }elseif ($_GET['action'] == "edit" && $RESULT['Type'] == 'quagga') { echo "selected=\"selected\""; $show_quagga = 1;  }?> >Quagga Router</option>
                                                </select>
                                            </p>
                                            
                                            
                                            
                                        </div>
                                        <div class="colx2-right">
                                            
                                            <p id="User" <?if ($show_mikrotik != '1'){?>style="display: none;"<?}?>>
                                                <label for="User" class="required">MikroTik SSH User</label>
                                                <input type="text" name="User" id="User" title="Enter the SSH Read Only username of the router" value="<? if ($_GET['action'] == "edit"){ echo stripslashes($RESULT['User']);}elseif($_POST['User']){ echo $_POST['User']; } ?>">
                                            </p>

                                            <p>
                                                <label for="Pass" class="required">Read Only Password</label>
                                                <input type="text" name="Pass" id="Pass" title="Enter the Read Only Password for the router" value="<? if ($_GET['action'] == "edit"){ echo stripslashes($RESULT['Pass']);}elseif($_POST['Pass']){ echo $_POST['Pass']; } ?>">
                                            </p>

                                            
                                            
                                            <p>
                                                <label for="Ip" class="required">Router IP Address</label>
                                                <input type="text" name="Ip" id="Ip"  title="Enter the IP Address of the Router" value="<? if ($_GET['action'] == "edit"){ echo stripslashes($RESULT['Ip']);}elseif($_POST['Ip']){ echo $_POST['Ip']; } ?>">
                                            </p>
                                            <p>
                                                <label for="Port" class="required">Router Port</label>
                                                <input type="text" name="Port" id="Port"  title="Enter the Router's Port. If it is a MikroTik Router you enter the SSH Port (22 by default) if it is a Quagga Router you enter the BGPd Port (2605 by default)" value="<? if ($_GET['action'] == "edit"){ echo stripslashes($RESULT['Port']);}elseif($_POST['Port']){ echo $_POST['Port']; } ?>">
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
                        
                        <!-- ADD/EDIT ROUTERS END -->
                        
                        <br />
                        
                    </div>
                        
                    <div id="toggler2">
                      
                    <!-- LIST ROUTERS START -->
                      
                      <fieldset>
                                
                          <legend>&raquo; BGP Routers List</legend>
                        
                      <form name="search_form" action="index.php?section=<?=$SECTION;?>" method="get" class="search_form">
                        <input type="hidden" name="section" value="<?=$SECTION;?>" />
                        <table border="0" cellspacing="0" cellpadding="4">
                            <tr>
                                <td>Keywords:</td>
                                <td><input type="text" name="q" id="search_field_q" class="input_field" value="<?=$q?>" /></td>
                                
								<td>Router Type:</td>
                                <td>
                                    <select name="search_type" class="select_box">
                                        <option value="">Any type</option> 
                                        <option value="mikrotik" <? if ($_GET['search_type'] == 'mikrotik'){ echo "selected=\"selected\""; }?> >MikroTik Routers</option>
                                        <option value="quagga"   <? if ($_GET['search_type'] == 'quagga'){ echo "selected=\"selected\""; }?> >Quagga (Linux) Routers</option>
                                    </select>
                                </td>
                                <?if ($_SESSION['admin_level'] == 'admin'){?>                                
                    			<td>Owner:</td>
                                <td>
                                    <select name="search_user_id" class="select_box">
                                        <option value="">All Owners</option> 
                                        											<? 
										$SELECT_USERS = mysql_query("SELECT id, Username FROM staff WHERE Active ='1' ORDER BY Username ASC", $db);
										while ($USERS = mysql_fetch_array($SELECT_USERS)){
                                            $USERROUTERS = mysql_num_rows(mysql_query("SELECT 1 FROM routers WHERE User_id = '".$USERS['id']."' ", $db));
										?>                                                    
                                        <option value="<?=$USERS['id'];?>"   <? if ($_GET['search_user_id'] == $USERS['id']){ echo "selected=\"selected\""; }?> ><?=$USERS['Username'];?> (<?=$USERROUTERS;?> )</option>
										<?}?>  
                                        
                                    </select>
                                </td>
                                <?}?>                                
                                
                                <td><button type="submit"  >Search</button></td>
                            </tr>
                        </table> 
                      </form>

                      <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:15px; margin-top: 15px;">
                        <tr>
                            <td width="36%" height="30">
                                <h3 style="margin:0"><?=$action_title;?> <? if ($q || $qu || $qt) { ?><span style="font-size:12px"> (<a href="index.php?section=<?=$SECTION;?>" class="tip_south" title="Clear search">x</a>)</span><? } ?></h3> 
                            </td>
                            <td width="28%" align="center">
                                <? if ($items_number) { ?>
                                    Total Records: <span id="total_records"><?=$items_number?></span>
                                <? } ?>
                            </td>
                            <td width="36%"><? if ($items_number) { include "includes/paging.php"; } ?></td>
                        </tr>
                      </table>                            
                        
                        
                  
                      <table width="100%" border="0" cellspacing="2" cellpadding="5">
                      <tr>
                        <th><?=create_sort_link("id","id");?></th>
                        <th><?=create_sort_link("RouterName","Router Name");?></th>
                        <th><?=create_sort_link("NodeID","Node ID");?></th>
                        <th><?=create_sort_link("NodeName","Node Name");?></th>
                        <?if ($_SESSION['admin_level'] == 'admin'){?>
                        <?/*<th><a href="javascript:void(0)" <?if (staff_help()){?>class="tip_south"<?}?> title="Router Owner">Owner</a></th>*/?>
                        <th><?=create_sort_link("User_id", "Owner");?></th>
                        <?}?>
                        <th><?=create_sort_link("Type", "Router Type");?></th>
                        <th><?=create_sort_link("Ip", "Router IP Address");?></th>
                        <?if ($_SESSION['admin_level'] == 'admin'){?>
                        <th><?=create_sort_link("Port", "Router Port");?></th>
                        <th><?=create_sort_link("User", "Read Only Username");?></th>
                        <th><?=create_sort_link("Pass", "Read Only Password");?></th>
                        <th><?=create_sort_link("Stats", "Allow BGP Stats");?></th>
                        <?}?>
                        <th><?=create_sort_link("Trace", "Allow Traceroute");?></th>
                        <th><?=create_sort_link("Status", "Router Alive");?></th>
                        <th><?=create_sort_link("Active", "Active");?></th>
                        <th><a href="javascript:void(0)" <?if (staff_help()){?>class="tip_south"<?}?> title="Use the icons bellow to do basic actions on the routers.">Actions</a></th>
                      </tr>
                      <!-- RESULTS START -->
                      <?
                      $i=-1;
                      while($LISTING = mysql_fetch_array($SELECT_RESULTS)){
                      $i++;
                      
                      if ($_SESSION['admin_level'] == 'admin'){
					  	$SELECT_CHAN_USER = mysql_query("SELECT Username, id FROM staff WHERE id = '".$LISTING['User_id']."' ", $db);
					  	$CHAN_USER = mysql_fetch_array($SELECT_CHAN_USER);					  	  
					  }
                      
                      ?>      
                      <tr onmouseover="this.className='on' " onmouseout="this.className='off' " id="tr-<?=$LISTING['id'];?>">
                        <td align="center" nowrap ><?=$LISTING['id'];?></td>
                        <td nowrap><a href="index.php?section=<?=$SECTION;?>&action=edit&id=<?=$LISTING['id'];?>" title="Edit router" class="<?if (staff_help()){?>tip_south<?}?>"><?=$LISTING['RouterName'];?></a></td>
                        <td align="left" nowrap >#<?=$LISTING['NodeID'];?></td>
                        <td align="left" nowrap > &nbsp; &nbsp; <?=$LISTING['NodeName'];?></td>
                        <?if ($_SESSION['admin_level'] == 'admin'){?>
                        <td align="center" nowrap><a href="index.php?section=routers&search_user_id=<?=$CHAN_USER['id'];?>" title="Show user's routers" class="<?if (staff_help()){?>tip_south<?}?>"><?=$CHAN_USER['Username'];?></a></td>
                        <?}?>
                        <td align="center" nowrap><?if ($LISTING['Type'] == 'mikrotik') {?>
                            <a href="index.php?section=routers&search_type=mikrotik" title="Show MikroTik Routers" class="<?if (staff_help()){?>tip_south<?}?> mikrotik"><span>MikroTik Router</span></a>
                        <? }elseif ($LISTING['Type'] == 'quagga') {?>
                            <a href="index.php?section=routers&search_type=quagga" title="Show Quagga (Linux) Routers" class="<?if (staff_help()){?>tip_south<?}?> quagga"><span>Quagga (Linux) Router</span></a>
                        <?} ?></td>
                        <td align="center" nowrap ><?=$LISTING['Ip'];?></td>
                        <?if ($_SESSION['admin_level'] == 'admin'){?>
                        <td align="center" nowrap ><?=$LISTING['Port'];?></td>
                        <td align="center" nowrap ><?if ($LISTING['Type'] == 'mikrotik') { echo $LISTING['User']; }else {echo "---"; }?></td>
                        <td align="center" nowrap ><?=$LISTING['Pass'];?></td>
                        <td align="center" nowrap ><a href="javascript:void(0)"  rel="<?=$LISTING['id']?>" class="toggle_stats <?if (staff_help()){?>tip_south<?}?> <? if ($LISTING['Stats'] == '1') { ?>yes<? } else { ?>no<? } ?>" title="BGP Stats collection is: <? if ($LISTING['Stats'] == '1') { ?>Enabled<? } else { ?>Disabled<? } ?>"><span>BGP Stats is: <? if ($LISTING['Stats'] == '1') { ?>Enabled<? } else { ?>Disabled<? } ?></span></a></td>
                        <?}?>
                        <td align="center" nowrap ><?if ($LISTING['Type'] == 'mikrotik') {?><a href="javascript:void(0)"  rel="<?=$LISTING['id']?>" class="toggle_trace <?if (staff_help()){?>tip_south<?}?> <? if ($LISTING['Trace'] == '1') { ?>yes<? } else { ?>no<? } ?>" title="Traceroute is: <? if ($LISTING['Trace'] == '1') { ?>Enabled<? } else { ?>Disabled<? } ?>"><span>Traceroute is: <? if ($LISTING['Trace'] == '1') { ?>Enabled<? } else { ?>Disabled<? } ?></span></a><?}else{?>---<?}?></td>
                        <td align="center" nowrap ><a href="javascript:void(0)" class="<?if (staff_help()){?>tip_south<?}?> <? if ($LISTING['Status'] == 'up') { ?>enabled<? } else { ?>disabled<? } ?>" title="Router is: <?=strtoupper($LISTING['Status']);?>"><span>Router is: <?=strtoupper($LISTING['Status']);?></span></a></td>
                        <td align="center" nowrap ><a href="javascript:void(0)" class="<?if (staff_help()){?>tip_south<?}?> toggle_active <? if ($LISTING['Active'] == '1') { ?>activated<? } else { ?>deactivated<? } ?>" rel="<?=$LISTING['id']?>" title="Enable/Disable"><span>Enable/Disable</span></a></td>
                        <td align="center" nowrap="nowrap">
                            <a href="index.php?section=<?=$SECTION;?>&amp;action=edit&amp;id=<?=$LISTING['id'];?><?=$sort_vars;?><?=$search_vars;?>" title="Edit" class="<?if (staff_help()){?>tip_south<?}?> edit"><span>Edit</span></a>
                            <a href="javascript:void(0)" rel="tr-<?=$LISTING['id']?>" title="Delete" class="<?if (staff_help()){?>tip_south<?}?> delete"><span>Delete</span></a>
                        </td>
                      </tr>
                      <?}?>

                      <!-- RESULTS END -->
                    </table>
                    
                    <? if (!$items_number) { ?>
                        <div class="no_records">No records found</div>
                    <? } ?>
            

                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:10px 0">
                        <tr>
                            <td width="36%" height="30">
                            <? include "includes/items_per_page.php"; ?>
                            </td>
                            <td width="28%">&nbsp;</td>
                            <td width="36%"> 
                                <? if ($items_number) { include "includes/paging.php"; } ?>
                            </td>
                        </tr>
                    </table>
                    
                    </fieldset>
                    
                    <!-- LIST ROUTERS END -->
                    
                    </div>
                        
                </div>    
                
                <!-- ROUTERS SECTION END --> 
                
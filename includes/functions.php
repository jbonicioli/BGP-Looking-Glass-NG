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

//Start gzip compression & session
if(php_sapi_name() != "cli" && stristr($_SERVER['PHP_SELF'], "js.php") == FALSE) {
	ob_start();
	session_start();
}
//Set some global parameters
error_reporting(E_ALL ^ E_NOTICE);


//MySQL Connection script
$db = @mysql_connect( $CONF['db_host'], $CONF['db_user'], $CONF['db_pass'] );
@mysql_query('set names utf8'); 
@mysql_select_db($CONF['db'],$db);

//In case of mysql error, exit with message
if (mysql_error()){
    exit("<html>\n<head>\n<title>Error!</title>\n</head>\n<body>\nAn error occured while connecting to database.</body>\n</html>");
}

//GET SETTINGS FROM DB
$SELECT_SETTINGS = mysql_query("SELECT Name, Value FROM settings", $db);
while ($SETTINGS = mysql_fetch_array($SELECT_SETTINGS)){
	$CONF[$SETTINGS['Name']] = $SETTINGS['Value'];
}

#########################################
#        Admin Login Functions          #
#########################################

// create sessions - cookie
function admin_create_sessions($id,$username,$password,$remember, $help, $level, $impersonate=false)
{
    global $CONF, $_SESSION;
    
    if ($impersonate == true){
		if ($id != $_SESSION['admin_orig']){
			$_SESSION['admin_orig'] = $_SESSION['admin_id'];
		}else{
			unset($_SESSION['admin_orig']);			
		}
    }else{
    	if ($id == $_SESSION['admin_orig']){
			unset($_SESSION['admin_orig']);
		}
	}    
    
    
    //session_register('awmn_routers');
    $_SESSION['admin_id'] = $id;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_md5part'] = substr(sha1($password),0,10);
    $_SESSION['admin_help'] = $help;
    $_SESSION['admin_level'] = $level;
    
    if(isset($remember))
    {
        setcookie($CONF['COOKIE_NAME'], $_SESSION['admin_id'] . "||" . $_SESSION['admin_username']  ."||" . $_SESSION['admin_md5part']. "||" . $_SESSION['admin_help'], time()+60*60*24*15, "/");
        return;
    }
}

// do admin login
function admin_login($username,$password,$remember)
{
    global $db;
        
    $md5pass = sha1($password);
    $username = mysql_real_escape_string($username);
    $USER_SELECT = @mysql_query("SELECT * FROM `staff` WHERE Username='".addslashes($username)."' AND Password='".addslashes($md5pass)."' AND Active='1' LIMIT 1",$db);
    $user_check = @mysql_num_rows($USER_SELECT);     

    if ($user_check) { 
        $USER = @mysql_fetch_array($USER_SELECT);
        admin_create_sessions($USER['id'], $USER['Username'], $USER['Password'], $remember, $USER['Help'], $USER['Admin_level']);
        return true;
    } else {         
        return false;
    }
}

// do admin logout
function admin_logout(){
	global $CONF;
		
    session_unset();
    //session_destroy();
    setcookie ($CONF['COOKIE_NAME'], "",time()-60*60*24*15, "/");
}

// check if admin is logged
function admin_logged()
{
    global $db, $CONF;

    if(isset($_SESSION['admin_username']) && isset($_SESSION['admin_md5part'])) {
        return true;
    } elseif(isset($_COOKIE[$CONF['COOKIE_NAME']])) {
        $cookie = explode("||", $_COOKIE[$CONF['COOKIE_NAME']]);
        $USER_SELECT = @mysql_query("SELECT * FROM `staff` WHERE id='".addslashes($cookie[0])."' AND Username='".addslashes($cookie[1])."' AND Active='1' LIMIT 1",$db);
        $USER_CHECK = @mysql_num_rows($USER_SELECT);
        if ($USER_CHECK) {
            $USER = @mysql_fetch_array($USER_SELECT);
            if (substr(sha1($USER['Password']),0,10) == $cookie[2]) {
                admin_create_sessions($USER['id'], $USER['Username'], $USER['Password'], 1, $USER['Help'], $USER['Admin_level']);
                return true;
            }
        } else {
            admin_logout();
            return false;
        }
    } else {
        return false;
    }
}

// quick protect a page. examples :
//
// default parameters:
// admin_auth();
//
// custom parameters:
// admin_auth("admin|user", "login.php");
                                                                         
function admin_auth($level = 'admin', $login_page = "login.php"){
    global $db, $CONF;
    
    if (!admin_logged()) {
        header("Location: " . $login_page);
        exit;
    } else {

        $USERS_SELECT = @mysql_query("SELECT Admin_level, Active FROM `staff` WHERE id='".addslashes($_SESSION['admin_id'])."' LIMIT 1",$db);
        $USERS = @mysql_fetch_array($USERS_SELECT);
        if (!$USERS['Active']){ 
            admin_logout();
            header("Location:".  $login_page);
            exit;
        }
        
        $_SESSION['admin_access'] = $USERS['Admin_level'];
        
        
    }

}


//IMPERSONATE USER
if ($_GET['action'] == 'switch_user' && $_POST['user_id'] && ($_SESSION['admin_level'] == 'admin' || $_SESSION['admin_orig']) ){
	
	$SELECT_SWITCH_USER= mysql_query("SELECT * FROM staff WHERE id = '".(int)$_POST['user_id']."' ", $db);
	$SWITCH_USER = mysql_fetch_array($SELECT_SWITCH_USER);

	if(isset($_COOKIE[$CONF['COOKIE_NAME']])) {
		$remember = '1'; 	
	}else{
		$remember = '0';
	}
	
	if ($_SESSION['admin_orig']){
		$impersonate = false;
	}else{
		$impersonate = true;
	}
	
	if ($SWITCH_USER['Admin_level'] == 'admin' && $_SESSION['admin_orig'] == $SWITCH_USER['id']){
		$user_level = 'admin';
		
	}elseif ($SWITCH_USER['Admin_level'] == 'admin' && $_SESSION['id'] != $SWITCH_USER['id'] ){
		$user_level = 'user';
	}else{
		$user_level = 'user';
	}	
	
	admin_create_sessions($SWITCH_USER['id'],$SWITCH_USER['Username'],$SWITCH_USER['Password'],$remember, 1, $user_level, $impersonate);
	
	exit('ok');
	
}

//Check if user is logged
$USERLOGGED = admin_logged();


//Set global vars $SECTION, $TYPE, $MODE
if (isset($_GET['section'])){
    $SECTION = $_GET['section'];
}


//Set global vars for TITLE, Section heading & CSS class for section heading
if (isset($SECTION) && $SECTION == 'staff'){
    $maintitle_class = 'maintitle_properties_staff';
    $maintitle_title = 'Users Management';
}elseif (isset($SECTION) && $SECTION == 'user'){
    $maintitle_class = 'maintitle_properties_user';
    $maintitle_title = 'Account Management';
}elseif (isset($SECTION) && $SECTION == 'settings'){
    $maintitle_class = 'maintitle_settings';
    $maintitle_title = 'System Settings';
}elseif (isset($SECTION) && $SECTION == 'lg'){
    $maintitle_class = 'maintitle_lg';
    $maintitle_title = 'AWMN BGP Looking Glass';
}elseif (isset($SECTION) && $SECTION == 'whois'){
    $maintitle_class = 'maintitle_whois';
    $maintitle_title = 'AWMN Web Whois Lookup';
}elseif (isset($SECTION) && $SECTION == 'traceroute'){
    $maintitle_class = 'maintitle_traceroute';
    $maintitle_title = 'AWMN Web Traceroute';
}elseif (isset($SECTION) && $SECTION == 'routers'){
    $maintitle_class = 'maintitle_routers';
    $maintitle_title = 'BGP Routers';
}else{
    $maintitle_class = 'maintitle_home';
    $maintitle_title = 'Dashboard';
}



// create sort link for table listings
function create_sort_link($attr, $title) {
    global $_SERVER, $_GET, $search_vars, $SECTION;

    if ($_GET['sort'] == $attr) {
        if ($_GET['by'] == "desc") {
            $by_value = "asc";
            $image = " <img src=\"images/sort_down.gif\" align=\"absmiddle\" />";
        } else {
            $by_value = "desc";
            $image = " <img src=\"images/sort_up.gif\" align=\"absmiddle\" />";
        }
    }

    return "<a href=\"index.php?section=$SECTION&sort=".$attr."&by=".$by_value."&pageno=".$_GET['pageno']. $search_vars ."\">".$title."</a> ". $image;
}

function staff_help(){
    global $_SESSION;
    
    if ($_SESSION['admin_help']){
        return TRUE;
    }else{
//        return FALSE;
        return TRUE;
    }
    
}   

function date_conv($date, $showtime=false){
    $date = explode(" ", $date);
    $time = $date[1];
    $date = explode("-", $date[0]);
          //          print_r($date);
    if ($showtime == TRUE){
        $time = explode(":", $time);
        return $date[2] . "-" . $date[1] . "-" . $date[0] . " " . $time[0] . ":" . $time[1] . ":" . $time[2];
        //print_r($date2);
    }
    return $date[2] . "-" . $date[1] . "-" . $date[0];
}


//Helper function. Get tag from XML string.
function getTagContents($tagName,$dom) {
    $node=$dom->getElementsByTagName($tagName)->item(0);
    return $node->nodeValue;
}


function sec2hms ($oldTime, $newTime, $padHours = false)  
  {  

  $sec = strtotime($newTime) - strtotime($oldTime);
  
    // start with a blank string  
    $hms = "";  
     
    // do the hours first: there are 3600 seconds in an hour, so if we divide  
    // the total number of seconds by 3600 and throw away the remainder, we’re  
    // left with the number of hours in those seconds  
    $hours = intval(intval($sec) / 3600);  
  
    // add hours to $hms (with a leading 0 if asked for)  
    $hms .= ($padHours)  
          ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"  
          : $hours. ":";  
     
    // dividing the total seconds by 60 will give us the number of minutes  
    // in total, but we’re interested in *minutes past the hour* and to get  
    // this, we have to divide by 60 again and then use the remainder  
    $minutes = intval(($sec / 60) % 60);  
  
    // add minutes to $hms (with a leading 0 if needed)  
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";  
  
    // seconds past the minute are found by dividing the total number of seconds  
    // by 60 and using the remainder  
    $seconds = intval($sec % 60);  
  
    // add seconds to $hms (with a leading 0 if needed)  
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);  
  
    // done!  
    return $hms;  
     
  }  

?>
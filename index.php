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
<script type="text/javascript" src="./includes/js.php"></script> 
<!-- INCLUDE STYLES & JAVASCRIPTS END -->

</head>

<body>

    <!-- NO JAVASCRIPT NOTIFICATION START -->
    <noscript>
        <div class="maintitle_nojs">This site needs Javascript enabled to function properly!</div>
    </noscript>
    <!-- NO JAVASCRIPT NOTIFICATION END -->
    
    <div id="wrapper">

		<!-- HEADER START -->
		<div id="header">
			<div id="logo<?if (is_file("./images/logo.custom.png")){?>_custom<?}?>">
				<a href="index.php"><span><?=$CONF['APP_NAME'];?></span></a>
			</div>
		</div>

        <!-- MENU START -->
        <div id="navigation">
            
            <!-- MAIN MENU START -->
            <ul>
                <li class="menu_home"      ><a href="index.php" <? if ($SECTION=='' || !$SECTION) echo " class=\"selected\""; ?>><span>Dashboard</span></a></li>
                <li class="menu_lg"        ><a href="index.php?section=lg" title="AWMN Wind WHOIS Lookup" <? if ($SECTION=='lg' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='lg' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='lg' && staff_help()){?>class="tip_south"<?}?> ><span>BGP Looking Glass</span></a></li>
                <li class="menu_traceroute"><a href="index.php?section=traceroute" title="AWMN Web Traceroute" <? if ($SECTION=='traceroute' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='traceroute' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='traceroute' && staff_help()){?>class="tip_south"<?}?> ><span>Web Traceroute</span></a></li>
                <li class="menu_whois"     ><a href="index.php?section=whois" title="AWMN Wind WHOIS Lookup" <? if ($SECTION=='whois' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='whois' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='whois' && staff_help()){?>class="tip_south"<?}?> ><span>Web Whois</span></a></li>
                <? if ($USERLOGGED && $_SESSION['admin_level'] == 'user'){?>
                <li class="menu_routers"  ><a href="index.php?section=routers" title="Manage your Routers" <? if ($SECTION=='routers' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='routers' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='routers' && staff_help()){?>class="tip_south"<?}?> ><span>BGP Routers</span></a></li>
                <?}?>
                <? if ($USERLOGGED && $_SESSION['admin_level'] == 'admin'){?>
                <li class="menu_routers"  ><a href="index.php?section=routers" title="Manage registered Routers" <? if ($SECTION=='routers' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='routers' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='routers' && staff_help()){?>class="tip_south"<?}?> ><span>BGP Routers</span></a></li>
                <li class="menu_staff"    ><a href="index.php?section=staff" title="Manage Staff" <? if ($SECTION=='staff' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='staff' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='staff' && staff_help()){?>class="tip_south"<?}?> ><span>Staff</span></a></li>
                <li class="menu_settings" ><a href="index.php?section=settings" title="Manage system settings" <? if ($SECTION=='settings' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='settings' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='settings' && staff_help()){?>class="tip_south"<?}?> ><span>System Settings</span></a></li>
                <?}?>
                <?if ($CONF['BGP_LIVE_STATS_DOMAIN']){?>
				<li class="menu_stats"     ><a href="http://<?=$CONF['BGP_LIVE_STATS_DOMAIN'];?>" title="Live BGP Statistics" target="_blank" <? if ($SECTION=='stats' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='stats' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='stats' && staff_help()){?>class="tip_south"<?}?> ><span>Live BGP Statistics</span></a></li>
				<?}?>
				<?if ($CONF['WIND_DOMAIN']){?>
				<li class="menu_wind"      ><a href="http://<?=$CONF['WIND_DOMAIN'];?>" title="WiND Database for <?=$CONF['WIRELESS_COMMUNITY_NAME'];?>" target="_blank" class="tip_south"><span>WiND</span></a></li>
				<?}?>
            </ul>
            <!-- MAIN MENU END -->

            <!-- USER MENU START -->
			<div id="user_panel">
			<? if ($USERLOGGED){?>
	            <?if ($_SESSION['admin_level'] == 'admin' || $_SESSION['admin_orig']){?>
				<select name="switch_user" id="switch_user" title="Switch to user" class="tip_south" >
	                <option value="" selected="selected">--Select--</option>
					<? 
					$SELECT_USERS = mysql_query("SELECT Username, id FROM staff WHERE Active ='1' ORDER BY Username ASC", $db);
					while ($USERS = mysql_fetch_array($SELECT_USERS)){
					?>                                                    
	                <option value="<?=$USERS['id'];?>"   <? if ($_SESSION['admin_id'] == $USERS['id']){ echo "selected=\"selected\""; }?> ><?=$USERS['Username'];?></option>
					<?}?>                                                    
	            </select>
	            <?}else{?>
				User: <a href="index.php?section=user&action=edit&id=<?=$_SESSION['admin_id'];?>" <?if (staff_help()){?>class="tip_south"<?}?> title="Edit account"><strong><?=$_SESSION['admin_username'];?></strong></a>
				<?}?>
				<a href="login.php?action=logout" class="logout <?if (staff_help()){?>tip_east<?}?>" title="Logout of the system">Logout</a>
			<?}else{?>
                <a href="register.php" class="login_popup signup <?if (staff_help()){?>tip_south<?}?>" title="Create an account to register your Routers"><strong>Register</strong></a>  
                <a href="login.php" class="login_popup logout <?if (staff_help()){?>tip_east<?}?>" title="Login to the system">Login</a>
            <?}?>
            </div>
			<!-- USER MENU END --> 
		</div>
        <!-- MENU END -->


        <div class="clr">&nbsp;</div>
        <!-- HEADER END -->


        <!-- MAIN START --><br />
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <!-- SIDEBAR START -->
            <td valign="top" id="sidebar">
    	        <h2 class="sidebar_title">System Statistics</h2>
                
                <table width="80%" border="0" cellspacing="2" cellpadding="2" align="right">
                  <tr>
                    <td align="right" nowrap="nowrap" height="25" class="smalltahoma">Registered Routers <img src="images/nav_routers.png" align="top"></td>
                    <td class="smalltahoma"><strong><?=mysql_num_rows(mysql_query("SELECT 1 FROM `routers` WHERE 1"));?></strong></td>
                  </tr>
                  <tr>
                    <td align="right" nowrap="nowrap" height="25" class="smalltahoma">Active Routers  <img src="images/ico_enabled.png" align="top"></td>
                    <td class="smalltahoma"><strong><?=mysql_num_rows(mysql_query("SELECT 1 FROM `routers` WHERE Active= '1' "));?></strong></td>
                  </tr>
                  <tr>
                    <td align="right" nowrap="nowrap" height="25" class="smalltahoma">Healthy/Live Routers  <img src="images/ico_active.png" align="top"></td>
                    <td class="smalltahoma"><strong><?=mysql_num_rows(mysql_query("SELECT 1 FROM `routers` WHERE Status = 'up' "));?></strong></td>
                  </tr>
                  <tr>
                    <td align="right" nowrap="nowrap" height="25" class="smalltahoma">MikroTik Routers <img src="images/ico_mikrotik.png" align="top"></td>
                    <td class="smalltahoma"><strong><?=mysql_num_rows(mysql_query("SELECT 1 FROM `routers` WHERE Type= 'mikrotik' "));?></strong></td>
                  </tr>
                  <tr>
                    <td align="right" nowrap="nowrap" height="25" class="smalltahoma">Quagga Routers <img src="images/ico_quagga.png" align="top"></td>
                    <td class="smalltahoma"><strong><?=mysql_num_rows(mysql_query("SELECT 1 FROM `routers` WHERE Type= 'quagga' "));?></strong></td>
                  </tr>
                  <tr>
                    <td align="right" nowrap="nowrap" height="25" class="smalltahoma">BGP Enabled Routers <img src="images/nav_bgp.png" align="top"></td>
                    <td class="smalltahoma"><strong><?=mysql_num_rows(mysql_query("SELECT 1 FROM `routers` WHERE Status = 'up' AND Active = '1' AND Stats = '1' "));?></strong></td>
                  </tr>
                  <tr>
                    <td align="right" nowrap="nowrap" height="25" class="smalltahoma">Trace Enabled Routers <img src="images/nav_traceroute.png" align="top"></td>
                    <td class="smalltahoma"><strong><?=mysql_num_rows(mysql_query("SELECT 1 FROM `routers` WHERE Status = 'up' AND Active = '1' AND Trace = '1' "));?></strong></td>
                  </tr>
                  <tr>
                    <td align="right" nowrap="nowrap" height="25" class="smalltahoma">Registered Users  <img src="images/nav_staff.png" align="top"></td>
                    <td class="smalltahoma" nowrap="nowrap" ><strong><?=mysql_num_rows(mysql_query("SELECT 1 FROM `staff` WHERE 1"));?></strong></td>
                  </tr>                  
                  <tr>
                    <td align="right" nowrap="nowrap" height="25" class="smalltahoma">Most Registered Routers</td>
                    <td class="smalltahoma" nowrap="nowrap" ><strong><?
                        $SELECT_TOP_USER = mysql_query("SELECT User_id, count(User_id) as Total FROM routers WHERE User_id != '33' GROUP BY User_id ORDER BY Total DESC LIMIT 0,1;", $db);
                        $TOP_USER = mysql_fetch_array($SELECT_TOP_USER);
                        $SELECT_USER = mysql_query("SELECT Username FROM staff WHERE id = '".$TOP_USER['User_id']."' ", $db);
                        $TOP_USERNAME = mysql_fetch_array($SELECT_USER);
                        echo $TOP_USERNAME['Username'] . " (" . $TOP_USER['Total'] . ")";
                    ?></strong></td>
                  </tr>                  
          
		  		  <?if ($CONF['SIDEBAR_LIVE_STATS_URL']){?>
					<tr>
					<td align="center" nowrap="nowrap" height="25" colspan="2" class="smalltahoma"><?=$CONF['SIDEBAR_LIVE_STATS_URL'];?></td>
				  </tr>
				  <?}?>                  
				  <?if ($CONF['SIDEBAR_SUPPORT_URL']){?>
				  <tr>
					<td align="center" nowrap="nowrap" height="25" colspan="2" class="smalltahoma"><?=$CONF['SIDEBAR_SUPPORT_URL'];?></td>
				  </tr>
				  <?}?>                  
                                    
                </table>

            </td>
            <!-- SIDEBAR END -->
            <td class="main_content_spacer"></td>
            <td valign="top" id="main">
            
                <div class="maintitle_bg">
                    <div class="<?=$maintitle_class;?>"><a href="index.php?section=<?=$SECTION;?>"><?=$maintitle_title;?></a></div>
                </div>    
                
					<? 
					if (!$SECTION){
						if (file_exists('dashboard.php')) {
							include "dashboard.php";
						}else{
							include "dashboard.php.dist";
						}
					}

					if ($SECTION && preg_match('!^[\w @.-]*$!', $SECTION)) {
						if (file_exists($SECTION.'.php')) {
							include $SECTION.'.php';    
						}else{ 
							header("Location: index.php"); 
							exit; 
						}
					}
					?>        
            </td>
         </tr>
        </table>
        <!-- MAIN END -->

    </div>
    
    <!-- FOOTER START -->
    <div id="footer">
        <span style="float:right"><?=$CONF['FOOTER_TEXT'];?></span> <a href="https://github.com/Cha0sgr/BGP-Looking-Glass-NG" target="_blank">BGP Looking Glass NG</a>
    </div>
    <!-- FOOTER END -->
    
</body>
</html>
<? 
$buffer = ob_get_clean(); 
ob_start("ob_gzhandler"); 
echo $buffer;
?>
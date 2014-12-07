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

require("config.php");
include('Net/SSH2.php');

//MySQL Connection script
$db = @mysql_connect( $CONF['db_host'], $CONF['db_user'], $CONF['db_pass'] );
@mysql_query('set names utf8'); 
@mysql_select_db($CONF['db'],$db);

$SELECT_ROUTERS = mysql_query("SELECT * FROM routers WHERE Active = '1' ORDER BY id ASC", $db);

while ($ROUTERS = mysql_fetch_array($SELECT_ROUTERS)){

	if ($ROUTERS['Type'] == 'quagga'){

	    $link = @fsockopen ($ROUTERS['Ip'], $ROUTERS['Port'], $errno, $errstr, 2);

		if (!$link){
	        //Router down - disabling
	        mysql_query("UPDATE `routers` SET `Status` = 'down' WHERE `id` = '".$ROUTERS['id']."' ", $db);
	        //mysql_query("UPDATE `routers` SET `Active` = '0' WHERE `id` = '".$ROUTERS['id']."' ", $db);
	        echo "Router " . $ROUTERS['RouterName'] . " DOWN! DISABLING\n";
	            
		}else{
			//Router up - enabling
	        mysql_query("UPDATE `routers` SET `Status` = 'up' WHERE `id` = '".$ROUTERS['id']."' ", $db);
	        echo "Router " . $ROUTERS['RouterName'] . " Connected! Enabling\n";
	            
		}

		if ($link){

			socket_set_timeout ($link, 5);

			$readbuf = '';

			while (!feof($link)) {
				$readbuf = fread ($link, 8192);

				if (strstr($readbuf, 'User Access Verification')){
					$password = $ROUTERS['Pass'];
					fputs ($link, "{$password}\n");
				}elseif (strstr($readbuf, 'Password:')){
					fputs ($link, "\n");
					fputs ($link, "\n");
		            
		            //WRONG PASS! Disabling router!
		            mysql_query("UPDATE `routers` SET `Status` = 'down' WHERE `id` = '".$ROUTERS['id']."' ", $db);
		            //mysql_query("UPDATE `routers` SET `Active` = '0' WHERE `id` = '".$ROUTERS['id']."' ", $db);
					echo "Router " . $ROUTERS['RouterName'] . " Pass BAD! DISABLING\n";
					fclose ($link);
					break;
					
				}else{
					//GOOD PASS! Enabling router!
		            mysql_query("UPDATE `routers` SET `Status` = 'up' WHERE `id` = '".$ROUTERS['id']."' ", $db);
		            echo "Router " . $ROUTERS['RouterName'] . " Pass Good! Enabling\n";
		            fclose ($link);
		            break;
		            
				}

			}

		}
	

	}elseif ( $ROUTERS['Type'] == 'mikrotik'){

		if (!$ssh = new Net_SSH2($ROUTERS['Ip'], $ROUTERS['Port'])){
			//Router down - disabling
	        mysql_query("UPDATE `routers` SET `Status` = 'down' WHERE `id` = '".$ROUTERS['id']."' ", $db);
	        //mysql_query("UPDATE `routers` SET `Active` = '0' WHERE `id` = '".$ROUTERS['id']."' ", $db);
	        echo "Router " . $ROUTERS['RouterName'] . " DOWN! DISABLING\n";	        
		}else{
			//Router up - enabling
	        mysql_query("UPDATE `routers` SET `Status` = 'up' WHERE `id` = '".$ROUTERS['id']."' ", $db);
	        echo "Router " . $ROUTERS['RouterName'] . " Connected! Enabling\n";
	    }
        
        $ssh->setTimeout(20);
		
		if (!$ssh->login($ROUTERS['User'], $ROUTERS['Pass'])) {
	        //WRONG PASS! Disabling router!
	        mysql_query("UPDATE `routers` SET `Status` = 'down' WHERE `id` = '".$ROUTERS['id']."' ", $db);
	        //mysql_query("UPDATE `routers` SET `Stats` = '0' WHERE `id` = '".$ROUTERS['id']."' ", $db);
	        //mysql_query("UPDATE `routers` SET `Active` = '0' WHERE `id` = '".$ROUTERS['id']."' ", $db);
			echo "Router " . $ROUTERS['RouterName'] . " Pass BAD! DISABLING\n";
		}else{
			//GOOD PASS! Enabling router!
            mysql_query("UPDATE `routers` SET `Status` = 'up' WHERE `id` = '".$ROUTERS['id']."' ", $db);
            //mysql_query("UPDATE `routers` SET `Stats` = '1' WHERE `id` = '".$ROUTERS['id']."' ", $db);
	        echo "Router " . $ROUTERS['RouterName'] . " Pass Good! Enabling\n";
    	}

	}

}	




?>
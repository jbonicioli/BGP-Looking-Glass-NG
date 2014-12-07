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

include('Net/SSH2.php');

function mikrotik_traceroute ($IP, $USER, $PASS, $PORT, $TRACE_IP, $TRACE_DNS, $TRACE_SIZE, $TRACE_TIMEOUT, $TRACE_COUNT, $SSH_TIMEOUT=10){


		if (!$ssh = new Net_SSH2($IP, $PORT)){
			return "<font color=red><code><strong>SSH Error: Cannot Connect.</strong></code></font><br>\n";
		}
		if (!$ssh->login($USER, $PASS)) {
	    	return "<font color=red><code><strong>SSH Wrong User/Pass.</strong></code></font><br>\n";
		}
		
		if (!$TRACE_IP){
			return "<font color=red><code><strong>No IP was given to trace!</strong></code></font><br>\n";
		}
		if ($TRACE_DNS == '1'){
			$DNS = 'yes';
		}else{
			$DNS = 'no';
		}
		if (!$TRACE_TIMEOUT){
			$TRACE_TIMEOUT = '1000ms';
		}
		if (!$TRACE_SIZE){
			$TRACE_SIZE = '1500';
		}
		if (is_numeric($TRACE_COUNT)){
			$TRACE_COUNT = $TRACE_COUNT;
		}else{
			$TRACE_COUNT = '10';
		}
						

		//GET MIKROTIK VERSION
		$SSH = $ssh->exec("/system resource print");

		$SSH = str_replace ("  ", "", $SSH);
		$SSH = str_replace ("\n", " ", $SSH);
		$SSH = str_replace (": ", "=", $SSH);
		$RESOURCES = explode(" ", $SSH);
		//print_r($RESOURCES);

		$VERSION = FALSE;
		foreach($RESOURCES as $options){

			if ($VERSION  == FALSE && strstr($options, "version=") ){
				$VERSION  = str_replace("version=", "", $options) ;		
			}

		}

		//echo $VERSION;

		$VERSION_parts = explode (".", $VERSION);
		$VERSION_MAJ = $VERSION_parts['0'];
		$VERSION_MIN = $VERSION_parts['1'];
		//echo "Major Version: ".$VERSION_MAJ . " \n";
		//echo "Minor Version: ".$VERSION_MIN . " \n";
		$OLD_TRACE = FALSE;

		if (intval($VERSION_MAJ) == '6' && intval($VERSION_MIN) >= '3'){
			$COUNT = "count=" . $TRACE_COUNT;
		}elseif (intval($VERSION_MAJ) >= '6'){
			$COUNT= '';
		}elseif (intval($VERSION_MAJ) < '5'){
			$OLD_TRACE = TRUE;
		}else{
			$COUNT = '';
		}

		//echo "/tool traceroute size=".$TRACE_SIZE." timeout=".$TRACE_TIMEOUT." use-dns=".$DNS." " .$TRACE_IP . " " . $COUNT;
		$ssh->setTimeout($SSH_TIMEOUT);
		if ($OLD_TRACE){
			$SSH2  = $ssh->exec("/tool traceroute " .$TRACE_IP);
		}else{
			$SSH2  = $ssh->exec("/tool traceroute size=".$TRACE_SIZE." timeout=".$TRACE_TIMEOUT." use-dns=".$DNS." " .$TRACE_IP . " " . $COUNT);
		}
		
        //echo $ssh->read();
		
        //return var_dump($SSH2);

		//CLEAN UP RESULTS BEFORE RETURNING
		$SSH2 = explode("\n", $SSH2);

		//$NEWLINE[] = array();
		$traceheader = $SSH2[0];

		$SSH2 = array_reverse($SSH2);
		foreach($SSH2 as $lines){

			$LINE_PARTS = explode(" ", $lines);

			//print_r($LINE_PARTS);

			$NEWLINE[] = $lines;

			if ($LINE_PARTS[1] == '1'){
				break;
			}


		}	
		$NEWLINE[] = $traceheader;
		$SSH2 = array_reverse($NEWLINE);
		$SSH2 = implode("\n", $SSH2);
		//var_dump($SSH2);

		if (stristr($SSH2,"invalid value for argument address") || stristr($SSH2,"while resolving ip-address") ){
			$SSH2 = "<font color=red><code><strong>DNS resolving is not working on this router. Please try with IP address instead.</strong></code></font><br>\n";
		}
		if (stristr($SSH2,"not enough permissions (9)")){
			$SSH2 = "<font color=red><code><strong>This router is not configured properly to allow Traceroutes.<br />Choose another router or contact the owner to allow Traceroutes on this router.</strong></code></font><br>\n";
		}

		return $SSH2;

}


function mikrotik_get_peers ($IP, $USER, $PASS, $PORT){


		if (!$ssh = new Net_SSH2($IP, $PORT)){
			return "<font color=red><code><strong>SSH Error: Cannot Connect.</strong></code></font><br>\n";
		}
		if (!$ssh->login($USER, $PASS)) {
	    	return "<font color=red><code><strong>SSH Wrong User/Pass.</strong></code></font><br>\n";
		}
		$SSH  = $ssh->exec("/routing bgp peer print status");
		$SSH2 = $ssh->exec("/routing bgp instance print where name=default");
		
		$BGP_INSTANCE = explode(" ", $SSH2);

			$RID = FALSE;
			$AS = FALSE;

			foreach($BGP_INSTANCE as $options){

				if ($RID  == FALSE && strstr($options, "router-id=") ){
					$RID  = str_replace("router-id=", "", $options) ;		
				}
				if ($AS == FALSE && strstr($options, "as=") ){
					$AS = str_replace("as=", "", $options );		
				}
        	}

        //print_r($SSH);
        if ($SSH && $RID){

        	$SSH = explode("\n     ", $SSH);
        	$SSH = implode(" ", $SSH);
        	
        	//$SSH = explode("\n     ", $SSH);
        	//$SSH = implode(" ", $SSH);
        	//$SSH = str_replace ("\n\n", "\n", $SSH);

			$SSH = explode ("\n", $SSH);
			//print_r($SSH);

			//MAKE RESULTS QUAGGA STYLE FORMATTED
			$bgpcount = 0;

			for ($i = 1; $i <= count($SSH); $i++) {
				
				if ($SSH[$i] != ''){
					
					$ARRAY = explode(" ", str_replace ("  ", " ", str_replace ("\r", "", $SSH[$i])));
					//echo "<pre>";
					//print_r($ARRAY);
					//echo "</pre>";
					

					//echo key (preg_grep("/^dst-address=.*/", $ARRAY) );
					
					if ($ARRAY[0] != ''){
						$PEERID 	= $ARRAY[0];
						$STATUS 	= $ARRAY[1];
					}else{
						$PEERID 	= $ARRAY[1];
						$STATUS 	= $ARRAY[2];
					}
					
					$NEIGHBOR1 	= str_replace("remote-address=", "", $ARRAY[ key (preg_grep("/^remote-address=.*/", $ARRAY) ) ] );
					$REMOTEAS 	= str_replace("remote-as=", "", $ARRAY[ key (preg_grep("/^remote-as=.*/", $ARRAY) ) ] );
					$MSG_RCV 	= str_replace("updates-received=", "", $ARRAY[ key (preg_grep("/^updates-received=.*/", $ARRAY) ) ] );
					$MSG_SND 	= str_replace("updates-sent=", "", $ARRAY[ key (preg_grep("/^updates-sent=.*/", $ARRAY) ) ] );
					$HOLD_TMR 	= str_replace("used-hold-time=", "", $ARRAY[ key (preg_grep("/^used-hold-time=.*/", $ARRAY) ) ] );
					$KA_TMR 	= str_replace("used-keepalive-time=", "", $ARRAY[ key (preg_grep("/^used-keepalive-time=.*/", $ARRAY) ) ] );
					$STATE 	    = str_replace("state=", "", $ARRAY[ key (preg_grep("/^state=.*/", $ARRAY) ) ] );
					$PREFIXES 	= str_replace("prefix-count=", "", $ARRAY[ key (preg_grep("/^prefix-count=.*/", $ARRAY) ) ] );
					$UPTIME 	= str_replace("uptime=", "", $ARRAY[ key (preg_grep("/^uptime=.*/", $ARRAY) ) ] );
					
					$PEERID   = sprintf("%-3s",  $PEERID);
					$STATUS   = sprintf("%-6s",  $STATUS);
					$NEIGHBOR = sprintf("%-16s", $NEIGHBOR1);
					$REMOTEAS = sprintf("%-7s",  $REMOTEAS);
					$MSG_RCV  = sprintf("%-8s",  $MSG_RCV);
					$MSG_SND  = sprintf("%-9s",  $MSG_SND);
					$HOLD_TMR = sprintf("%-8s",  $HOLD_TMR);
					$KA_TMR   = sprintf("%-9s",  $KA_TMR);
					$STATE    = sprintf("%-13s", $STATE);
					$PREFIXES = sprintf("%-11s", $PREFIXES);
					$UPTIME   = sprintf("%-15s", $UPTIME);

					if (strstr ($NEIGHBOR, ':')){
						$NEIGHBOR = $NEIGHBOR . " ";
					}
					
					if ($NEIGHBOR1){ 
						$BGPLINES2[] = $PEERID . $STATUS . " " . str_replace("%all","",$NEIGHBOR) . $REMOTEAS . $MSG_RCV . $MSG_SND . $HOLD_TMR . $KA_TMR . $STATE . $PREFIXES . $UPTIME;
						$bgpcount++;
					}
				
				}
			}


			$BGPLINES[] = "show ip bgp summary";
			$BGPLINES[] = "BGP router identifier  $RID, local AS number $AS";
			$BGPLINES[] = "Status: E=Enabled X=Disabled";
			$BGPLINES[] = "Peers $bgpcount";
			$BGPLINES[] = "";
			$BGPLINES[] = "ID Status Neighbor        AS     MsgRcvd MsgSent  HldTmr  KpAlTmr  State        Prefixes   Uptime";

			$BGPLINES[] = implode("\n", $BGPLINES2);

			$BGPLINES[] = " ";
			$BGPLINES[] = "Total number of neighbors " . $bgpcount; 

			//print_r($BGPLINES);

			return "<pre>\n".implode("\n",$BGPLINES) ."</pre>\n";



		}else{
			return "SSH ERROR";
		}

}

	
function mikrotik_get_bgp_table ($IP, $USER, $PASS, $PORT){
        
		if (!$ssh = new Net_SSH2($IP, $PORT)){
			return "<font color=red><code><strong>SSH Error: Cannot Connect.</strong></code></font><br>\n";
		}
		if (!$ssh->login($USER, $PASS)) {
	    	return "<font color=red><code><strong>SSH Wrong User/Pass.</strong></code></font><br>\n";
		}
		$SSH  = $ssh->exec("/ip route print terse");
		$SSH2 = $ssh->exec("/routing bgp instance print where name=default");
		
		$BGP_INSTANCE = explode(" ", $SSH2);

			$RID = FALSE;
			$AS = FALSE;

			foreach($BGP_INSTANCE as $options){

				if ($RID  == FALSE && strstr($options, "router-id=") ){
					$RID  = str_replace("router-id=", "", $options) ;		
				}
				if ($AS == FALSE && strstr($options, "as=") ){
					$AS = str_replace("as=", "", $options );		
				}

			}


        //print_r($SSH);
        if ($SSH && $RID){

			$SSH = explode ("\n", $SSH);

	   
			//MAKE RESULTS QUAGGA STYLE FORMATTED

			$BGPLINES[] = "show ip bgp";
			$BGPLINES[] = "BGP table version is 0, local router ID is $RID";
			$BGPLINES[] = "Status codes: s suppressed, d damped, h history, * valid, > best, i - internal";
			$BGPLINES[] = "Origin codes: i - IGP, e - EGP, ? - incomplete";
			$BGPLINES[] = "";
			$BGPLINES[] = "   Network           Next Hop              Metric LocPrf Weight Path";

			$bgpcount = 0;

			for ($i = 0; $i <= count($SSH); $i++) {
				
				if ($SSH != ''){
					
					$ARRAY = explode(" ", str_replace ("  ", " ", $SSH[$i]));
					//print_r($ARRAY);
					
					//echo key (preg_grep("/^dst-address=.*/", $ARRAY) );
					if ($ARRAY[0] == ''){ 
						$STATUS 	= $ARRAY[2];
					}else{
						$STATUS 	= $ARRAY[1];
					}
					
					$NETWORK 	= str_replace("dst-address=", "", $ARRAY[ key (preg_grep("/^dst-address=.*/", $ARRAY) ) ] );
					$NEXTHOP 	= str_replace("gateway=", "", $ARRAY[key (preg_grep("/^gateway=.*/", $ARRAY) ) ] );
					$AS_PATH 	= str_replace("bgp-as-path=", "", str_replace (",", " ", $ARRAY[key (preg_grep("/^bgp-as-path=.*/", $ARRAY) ) ] ));
					$ORIGIN 	= str_replace("bgp-origin=", "", $ARRAY[key (preg_grep("/^bgp-origin=.*/", $ARRAY) ) ] );
					
					if ($ORIGIN == 'igp'){
						$ORIGIN = 'i';
					}
					
					if ($ORIGIN == 'egp'){
						$ORIGIN = 'e';
					}

					if ($ORIGIN == 'incomplete'){
						$ORIGIN = '?';
					}
					
					
					$BGP_STATUS = FALSE;
					$IS_BGP = FALSE;
					if ( $STATUS == "ADb" ){
						$BGP_STATUS = "*>";
						$IS_BGP = TRUE;		
					}
					if ( $STATUS == "Db" ){
						$BGP_STATUS = "*";
						$IS_BGP = TRUE;		
					}
										

					if ($AS_PATH == ''){
						$AS_PATH_SEP = '';
					}else{
						$AS_PATH_SEP = ' ';						
					}
					
					
					$BGP_STATUS = sprintf("%-2s", $BGP_STATUS);
					$NETWORK    = sprintf("%-18s", $NETWORK);
					$NEXTHOP    = sprintf("%-45s", $NEXTHOP);
					
					if ($IS_BGP == TRUE){ 
						$BGPLINES[] = $BGP_STATUS ." " . $NETWORK . $NEXTHOP . " 0 " . $AS_PATH . $AS_PATH_SEP . $ORIGIN;
						$bgpcount++;
					}
				
				}
			}

			$BGPLINES[] = " ";
			$BGPLINES[] = "Total number of prefixes " . $bgpcount; 

			//print_r($BGPLINES);

			return "<pre>\n".implode("\n",$BGPLINES) ."</pre>\n";



		}else{
			return "SSH ERROR";
		}

		
}


?>
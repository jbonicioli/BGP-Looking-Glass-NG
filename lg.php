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

//LOOKING GLASS FUNCTIONS
function printError ($message){
	return "<font color=red><code><strong>" . $message . "</strong></code></font><br>\n";
}

function safeOutput ($string){
	return htmlentities (substr ($string, 0, 50));
}

function execQuagga ($address, $port, $password, $request, $argument){
	
	$command = $request . (!empty ($argument) ? (" " . safeOutput ($argument)) : "");
	$link = fsockopen ($address, $port, $errno, $errstr, 5);
	if (!$link){
		printError ("Error connecting to router");
		return;
	}
	
	socket_set_timeout ($link, 5);
	
	//$username = $router[$routerid]["username"];
	//if (!empty ($username)) fputs ($link, "{$username}\n");
		
	fputs ($link, "{$password}\nterminal length 0\n{$command}\n");

	//send many 'enters' to telnet in case the shell is interactive and waits for --more--
	//pretty LAME implementation. I need to fix this...
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	fputs ($link, "\n");
	usleep ('20000');
	
	// let daemon print bulk of records uninterrupted
	if (empty ($argument) && $request > 0) sleep (3);
	fputs ($link, "quit\n");
	
	while (!feof ($link)) $readbuf = $readbuf . fgets ($link, 256);
	
	fclose ($link);

	$start = strpos ($readbuf, $command);
	$len = strpos ($readbuf, "quit") - $start;
	while ($readbuf[$start + $len] != "\n") $len--;
	
	$DATA = substr($readbuf, $start, $len);


	$DATAparts = explode ("\n", $DATA);
	$NEWDATA = "";
	$setbreak = false;
	for ($r = 1; $r <= count($DATAparts); $r++) {
    	//Check if output is compelte and end loop.
		if ( stristr($DATAparts[$r],"Total number of") ) {
			$setbreak = true;
		}
		if ( stristr($DATAparts[$r],"Read thread") && stristr($DATAparts[$r+2],">") ) {
			$setbreak = true;
		}
		
		$NEWDATA .= $DATAparts[$r];
		if ($setbreak == true){
			break;
		}
	}

	//Format and return data
	$results = "<pre>\n";
	$results .= $NEWDATA;
	$results .= "</pre>\n";
	
    return $results;

}

function execMikrotik ($address, $username, $password, $port, $request){
	require ("includes/mikrotik_bgp.php");
	if ($request == 'show ip bgp summary'){
		return mikrotik_get_peers($address, $username, $password, $port);
	}
	if ($request == 'show ip bgp'){
		return mikrotik_get_bgp_table($address, $username, $password, $port);
	}
	if ($request == 'show ip bgp neighbor'){
		//return mikrotik_get_bgp_table($address, $username, $password, $port);
		return "<font color=red><code><strong>Command not implemented for MikroTik Routers.</strong></code></font><br>\n";
	}

}


?>

<style>
.custom-combobox {
	position: relative;
	display: inline-block;
}
.custom-combobox-toggle {
	position: absolute;
	top: 0;
	bottom: 0;
	margin-left: -1px;
	padding: 0;
	/* support: IE7 */
	*height: 1.7em;
	*top: 0.1em;
}
.custom-combobox-input {
	margin: 0;
	padding: 0.3em;
}
pre {
	font-weight: bold;
	color:#333;
	font-size: 13px;
	line-height: 18px;
	font-family: monospace;
}

.ui-autocomplete {
    max-height: 300px;
    overflow-y: auto;   /* prevent horizontal scrollbar */
    overflow-x: hidden; /* add padding to account for vertical scrollbar */
    z-index:1000 !important;
}

</style>

<script>
	(function( $ ) {
		$.widget( "custom.combobox", {
			_create: function() {
				this.wrapper = $( "<span>" )
				.addClass( "custom-combobox" )
				.insertAfter( this.element );
				this.element.hide();
				this._createAutocomplete();
				this._createShowAllButton();
			},
			_createAutocomplete: function() {
				var selected = this.element.children( ":selected" ),
				value = selected.val() ? selected.text() : "";
				this.input = $( "<input>" )
				.appendTo( this.wrapper )
				.val( value )
				.attr( "title", "" )
				.addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
				.autocomplete({
					delay: 0,
					minLength: 0,
					source: $.proxy( this, "_source" )
				})
				.tooltip({
					tooltipClass: "ui-state-highlight"
				});
				this._on( this.input, {
					autocompleteselect: function( event, ui ) {
						ui.item.option.selected = true;
						this._trigger( "select", event, {
							item: ui.item.option
						});
					},
					autocompletechange: "_removeIfInvalid"
				});
			},
			_createShowAllButton: function() {
				var input = this.input,
				wasOpen = false;
				$( "<a>" )
				.attr( "tabIndex", -1 )
				.attr( "title", "Show All Items" )
				.tooltip()
				.appendTo( this.wrapper )
				.button({
					icons: {
						primary: "ui-icon-triangle-1-s"
					},
					text: false
				})
				.removeClass( "ui-corner-all" )
				.addClass( "custom-combobox-toggle ui-corner-right" )
				.mousedown(function() {
					wasOpen = input.autocomplete( "widget" ).is( ":visible" );
				})
				.click(function() {
					input.focus();
					// Close if already visible
					if ( wasOpen ) {
						return;
					}
					// Pass empty string as value to search for, displaying all results
					input.autocomplete( "search", "" );
				});
			},
			_source: function( request, response ) {
				var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
				response( this.element.children( "option" ).map(function() {
					var text = $( this ).text();
					if ( this.value && ( !request.term || matcher.test(text) ) )
						return {
							label: text,
							value: text,
							option: this
						};
				}) );
			},
			_removeIfInvalid: function( event, ui ) {
				// Selected an item, nothing to do
				if ( ui.item ) {
					return;
				}
				// Search for a match (case-insensitive)
				var value = this.input.val(),
				valueLowerCase = value.toLowerCase(),
				valid = false;
				this.element.children( "option" ).each(function() {
					if ( $( this ).text().toLowerCase() === valueLowerCase ) {
						this.selected = valid = true;
						return false;
					}
				});
				// Found a match, nothing to do
				if ( valid ) {
					return;
				}
				// Remove invalid value
				this.input
				.val( "" )
				.attr( "title", value + " didn't match any item" )
				.tooltip( "open" );
				this.element.val( "" );
				this._delay(function() {
					this.input.tooltip( "close" ).attr( "title", "" );
				}, 2500 );
				this.input.data( "ui-autocomplete" ).term = "";
			},
			_destroy: function() {
				this.wrapper.remove();
				this.element.show();
			}
		});
	})( jQuery );


	$(function() {
		$( "#combobox" ).combobox();
		$( "#toggle" ).click(function() {
			$( "#combobox" ).toggle();
		});
		$('.ui-autocomplete-input').css('width','500px')
	});

</script>

<div id="main_content">

					<!-- BGP LOOKING GLASS START -->
                      
					<fieldset>
					        
					<legend>&raquo; BGP Looking Glass - Select Router</legend>

					<form name="search_form" action="index.php?section=<?=$SECTION;?>" method="get" class="search_form">
						<input type="hidden" name="section" value="<?=$SECTION;?>" />
						
						<table border="0" cellspacing="0" cellpadding="4">
						    <tr>
						        <td>BGP Router:</td>
						        <td colspan="4">
						            <div class="ui-widget">
						                <select name="bgp_router" id="combobox" class="select_box">
						                    <option value="">--Select--</option> 
											<? 
											$SELECT_USERS = mysql_query("SELECT id, RouterName, Ip, NodeID, NodeName, Type FROM routers WHERE Active ='1' AND Status = 'up' ORDER BY NodeID ASC, RouterName ASC", $db);
											while ($USERS = mysql_fetch_array($SELECT_USERS)){
												if ($USERS['Type'] == 'mikrotik'){
													$rtype = 'MikroTik';
												}elseif ($USERS['Type'] == 'quagga'){
													$rtype = 'Quagga';
												}
											?>                                                    
											<option value="<?=$USERS['id'];?>"   <? if ($_GET['bgp_router'] == $USERS['id']){ echo "selected=\"selected\""; }?> >#<?=$USERS['NodeID'];?> - <?=$USERS['NodeName'];?> (<?=$USERS['RouterName'];?> - <?=$USERS['Ip'];?> - <?=$rtype;?>)</option>
											<?}?> 
						                </select>
						            </div>
						        </td>
						        
							</tr> 
						    <tr>
						        <td>BGP Command:</td>
						        <td>
						            <select name="bgp_command" id="bgp_command"  class="select_box">
						                <option value="">--Select--</option> 
						                <option value="1" <? if ($_GET['bgp_command'] == "1"){ echo "selected=\"selected\""; }?> >show ip bgp summary</option>
						                <option value="2" <? if ($_GET['bgp_command'] == "2"){ echo "selected=\"selected\""; }?> >show ip bgp</option>
						                <option value="3" <? if ($_GET['bgp_command'] == "3"){ echo "selected=\"selected\""; }?> >show ip bgp neighbor</option>
						            </select>
						        </td>
						        
						        <td>Arguements (optional):</td>
						        <td><input type="text" name="arguements" id="arguements" class="input_field" value="<?=htmlentities($_GET['arguements']);?>" /></td>

						        <td><button type="submit">Execute</button></td>
						    </tr>
						</table> 

					</form>                           

                    <?
                    if ($_GET['bgp_command'] && $_GET['bgp_router']){

                      	if ($_GET['bgp_command'] == '1'){
                      		$request = 'show ip bgp summary';
                      	}

                      	if ($_GET['bgp_command'] == '2'){
                      		$request = 'show ip bgp';
                      	}

                      	if ($_GET['bgp_command'] == '3'){
                      		$request = 'show ip bgp neighbor';
                      	}

                      	if ($request){

	                      	$SELECT_ROUTER = mysql_query("SELECT Ip, Port, User, Pass, Type FROM routers WHERE id = '".mysql_escape_string($_GET['bgp_router'])."' AND Active ='1' AND Status = 'up' ", $db);
	                      	
	                      	if (mysql_num_rows($SELECT_ROUTER)){
	                      		$ROUTER = mysql_fetch_array($SELECT_ROUTER);

	                      		if ($ROUTER['Type'] == 'quagga'){
	                      			$RESULTS = execQuagga ($ROUTER['Ip'], $ROUTER['Port'], $ROUTER['Pass'], $request, $_GET['arguements']);
	                      		}elseif ($ROUTER['Type'] == 'mikrotik'){
	                      			$RESULTS = execMikrotik ($ROUTER['Ip'], $ROUTER['User'], $ROUTER['Pass'], $ROUTER['Port'], $request);
	                      		}
	                      		
							}
                      	}
                    ?>
                    <div id="main_content">
						<table border="0" cellspacing="0" cellpadding="4">
	                        <tr>
	                        	<td>
	                      			<h2>Results:</h2>
	                      			<?=$RESULTS;?>
								</td>
	                      	</tr>
	                    </table>
                	</div>
                    <?}?>
                      
                    
                    </fieldset>
                    
                    <!-- BGP LOOKING GLASS END -->
</div>

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

require ("includes/mikrotik_bgp.php");

function printError ($message){
	return "<font color=red><code><strong>" . $message . "</strong></code></font><br>\n";
}


// DO TRACE
if ( $_GET['do_trace'] == '1' ) {

	$error = false;
	if (!is_numeric($_GET['trace_router'])){
  		$error = printError("Please select a router to trace from.");
  	}
	$_GET['trace_router'] = (int)$_GET['trace_router'];
  	
  	if (!is_numeric($_GET['trace_size'])){
  		$error = printError("Please enter a valid Packet Size.");
  	}else{
  		if ($_GET['trace_size'] >= '28' && $_GET['trace_size'] <= '1500'){

  		}else{
  			$error = printError("Please enter a valid Packet Size. Allowed values are 28 to 1500 bytes.");
  		}
  	}
  	$_GET['trace_size'] = (int)$_GET['trace_size'];
  	
  	if (!is_numeric($_GET['trace_count'])){
  		$error = printError("Please enter a valid Trace Count.");
  	}else{
  		if ($_GET['trace_count'] >= '1' && $_GET['trace_count'] <= '1000'){
  			$SSH_TIMEOUT = ($_GET['trace_count'] / 3) * 90;
  		}else{
  			$error = printError("Please enter a valid Trace Count. Allowed values are 1 to 1000.");
  		}
  	}
  	$_GET['trace_count'] = (int)$_GET['trace_count'];
  	
  	if (!is_numeric($_GET['trace_timeout'])){
  		$error = printError("Please enter a valid Timeout.");
  	}else{
  		if ($_GET['trace_timeout'] >= '10' && $_GET['trace_timeout'] <= '2000'){

  		}else{
  			$error = printError("Please enter a valid Timeout. Allowed values are 10 to 2000 ms.");
  		}
  	}
  	$_GET['trace_timeout'] = (int)$_GET['trace_timeout'];
  	
  	if (!$_GET['trace_ip']){
  		$error = printError("Please enter an IP Address or Hostname you want to trace to.");
  	}else{

  		if ( preg_match("/(?=^.{1,254}$)(^(?:(?!\d+\.|-)[a-zA-Z0-9_\-]{1,63}(?<!-)\.?)+(?:[a-zA-Z]{2,})$)/", $_GET['trace_ip']) ){

  			$trace_ip_rev = strrev($_GET['trace_ip']);
  			$trace_ip_parts = explode(".", $trace_ip_rev);
  			$tld = strrev($trace_ip_parts[0]);
  			if ($tld != 'awmn' && $tld != 'wn' ){
				$error = printError("Invalid TLD. You can trace only to .awmn domains");
			}

			if (stristr($_GET['trace_ip'], "leechers") || stristr($_GET['trace_ip'], "piranka")  || stristr($_GET['trace_ip'], "mini") ){
				$error = 'An error occured...';
			}

  		}elseif(filter_var( $_GET['trace_ip'], FILTER_VALIDATE_IP)){

  			$awmnips_start = ip2long("10.0.0.0");
  			$awmnips_end   = ip2long("10.255.255.255");
  			
  			$trace_ip_long = ip2long($_GET['trace_ip']);

  			if ($trace_ip_long > $awmnips_start && $trace_ip_long < $awmnips_end){

  				if (stristr($_GET['trace_ip'], "10.3.41.") || stristr($_GET['trace_ip'], "10.87.176.10") || stristr($_GET['trace_ip'], "10.49.226.211") ){
  					$error = 'An error occured...';
  				}

  			}else{
  				$error = printError("Invalid IP. You can trace only to AWMN IPs (10.0.0.0 - 10.255.255.255)");
  			}

  		}else{
  			$error = printError("Invalid IP or Hostname.");
  		}

  	}

  	if ($error == false){
        $SELECT_ROUTER = mysql_query("SELECT Ip, Port, User, Pass FROM routers WHERE id = '".mysql_escape_string($_GET['trace_router'])."' AND Active ='1' AND Status = 'up' AND Trace = '1' AND Type = 'mikrotik' ", $db);
      	if (mysql_num_rows($SELECT_ROUTER)){
      		$ROUTER = mysql_fetch_array($SELECT_ROUTER);
        	$RESULTS = mikrotik_traceroute($ROUTER['Ip'], $ROUTER['User'], $ROUTER['Pass'], $ROUTER['Port'], $_GET['trace_ip'], $_GET['trace_dns'], $_GET['trace_size'], $_GET['trace_timeout']."ms", $_GET['trace_count'], $SSH_TIMEOUT);
        }
  	}else{
  		$RESULTS = $error;
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

					<!-- TRACEROUTE START -->
                      
					<fieldset>
					        
					<legend>&raquo; Web Traceroute - Select trace options</legend>

					<form name="search_form" action="index.php?section=<?=$SECTION;?>" method="get" class="search_form">
						<input type="hidden" name="section" value="<?=$SECTION;?>" />
						
						<table border="0" cellspacing="0" cellpadding="4">
						    <tr>
						        <td>
						            <div class="ui-widget">Router: &nbsp; 
						                <select name="trace_router" id="combobox" class="select_box">
						                    <option value="">--Select--</option> 
											<? 
											$SELECT_USERS = mysql_query("SELECT id, RouterName, Ip, NodeID, NodeName, Type FROM routers WHERE Active ='1' AND Status = 'up' AND Trace = '1' ORDER BY NodeID ASC, RouterName ASC", $db);
											while ($USERS = mysql_fetch_array($SELECT_USERS)){
											?>                                                    
											<option value="<?=$USERS['id'];?>"   <? if ($_GET['trace_router'] == $USERS['id']){ echo "selected=\"selected\""; }?> >#<?=$USERS['NodeID'];?> - <?=$USERS['NodeName'];?> (<?=$USERS['RouterName'];?> - <?=$USERS['Ip'];?>)</option>
											<?}?> 
						                </select>
						            </div>
						        </td>
						        
							</tr> 
						    <tr>
						        <td>
						        	Resolve DNS Names:
									<select name="trace_dns" id="trace_dns"  class="select_box">
						                <option value="0" <? if ($_GET['trace_dns'] == "0" || !$_GET['trace_dns']){ echo "selected=\"selected\""; }?> >No</option>
						                <option value="1" <? if ($_GET['trace_dns'] == "1"){ echo "selected=\"selected\""; }?> >Yes</option>
						            </select>
						        	&nbsp; &nbsp; 
						        	Packet Size (28-1500): <input type="text" name="trace_size" id="trace_size" class="input_field" size="4" value="<?if ($_GET['trace_size']){ echo htmlentities($_GET['trace_size']); }else{ echo "50"; } ?>" />
									&nbsp; &nbsp; 
						        	Trace Count (>=v6.3): <input type="text" name="trace_count" id="trace_count" class="input_field" size="2" value="<?if ($_GET['trace_count']){ echo htmlentities($_GET['trace_count']); }else{ echo "1"; } ?>" />
							    </td>
						    </tr>
						    <tr>
						        <td>
						        	Trace to (IP or Hostname):
						        	<input type="text" name="trace_ip" id="trace_ip" class="input_field" size="29" value="<?if ($_GET['trace_ip']){ echo htmlentities($_GET['trace_ip']); }else{ echo "10.19.143.12"; } ?>" />
									&nbsp; &nbsp; 
						        	Timeout (ms):
						        	<input type="text" name="trace_timeout" id="trace_timeout" class="input_field" size="4" value="<?if ($_GET['trace_timeout']){ echo htmlentities($_GET['trace_timeout']); }else{ echo "250"; } ?>" />
									&nbsp; &nbsp; 
									<input type="hidden" name="do_trace" value="1" />
						        	<button type="submit" style="margin-bottom:0px; margin-top:0px;">Execute</button>
						        </td>
						    </tr>
						
						</table> 

					</form>                           

                    <?if ($RESULTS){?>
                    <div id="main_content">
						<table border="0" cellspacing="0" cellpadding="4">
	                        <tr>
	                        	<td>
	                      			<h2>Results:</h2>
	                      			<pre><?=$RESULTS;?></pre>
								</td>
	                      	</tr>
	                    </table>
                	</div>
                    <?}?>  
                    
                    </fieldset>
                    
                    <!-- TRACEROUTE END -->




</div>

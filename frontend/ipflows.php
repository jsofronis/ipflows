<?php
# version 1.0.0.1 (build date 2019-02-01 00:45:00)

#
## ipflows.php - IP flows correlation and visualization plugin
## Copyright (c) 2011 Masaryk University
## Author: Michal Potfaj <140462@mail.muni.cz>
##
##  Redistribution and use in source and binary forms, with or without
##  modification, are permitted provided that the following conditions are met:
##
##   * Redistributions of source code must retain the above copyright notice,
##     this list of conditions and the following disclaimer.
##   * Redistributions in binary form must reproduce the above copyright notice,
##     this list of conditions and the following disclaimer in the documentation
##     and/or other materials provided with the distribution.
##   * Neither the name of Masaryk University nor the names of its contributors may be
##     used to endorse or promote products derived from this software without
##     specific prior written permission.
##
##  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
##  AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
##  IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
##  ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
##  LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
##  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
##  SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
##  INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
##  CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
##  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
##  POSSIBILITY OF SUCH DAMAGE.
##
#

include "protocols.php";
include "functions.php";
include "help.php";

function ipflows_ParseInput($plugin_id) {
	$_SESSION['refresh'] = 0;
} 

function ipflows_Run($plugin_id) {
	global $protocols;
	$_SESSION['refresh'] = 0;
	$timefrom = date('Y/m/d H:i:s', time()-600);
	$timeto = date('Y/m/d H:i:s', time()-599);
	$maxflows = 30;
	$srcip = "";
	$srcmask = "";
	$srcport = "";
	$dstip = "";
	$dstmask = "";
	$dstport = "";
	$proto = "";
	$ipproto = "both";
	$ipprotoM[0] = "both";
	$ipprotoM[1] = "IPv4";
	$ipprotoM[2] = "IPv6";
	$processing = "fast";
	$processingM[0] = "fast";
	$processingM[1] = "strict";
	$showflows = "all";
	$showflowsM[0] = "all";
	$showflowsM[1] = "multi";
	$showflowsM[2] = "single";
	$filtername = "Filter";
	$channels = $_SESSION['profileinfo']['channel'];

	if (!empty($_POST['timefrom'])) 
		{$timefrom = $_POST['timefrom'];}
	if (!empty($_POST['timeto']))
		{$timeto = $_POST['timeto'];}
        if (!empty($_POST['maxflows']))
                {$maxflows = $_POST['maxflows'];}
	if (!empty($_POST['srcip']))
		{$srcip = $_POST['srcip'];}
	if (!empty($_POST['srcmask']))
		{$srcmask = $_POST['srcmask'];}
	if (!empty($_POST['srcport']))
		{$srcport = $_POST['srcport'];}
	if (!empty($_POST['dstip']))
		{$dstip = $_POST['dstip'];}
	if (!empty($_POST['dstmask']))
		{$dstmask = $_POST['dstmask'];}
	if (!empty($_POST['dstport']))
		{$dstport = $_POST['dstport'];}
        if (!empty($_POST["proto"]))
                {$proto = $_POST["proto"];}
        if (!empty($_POST["ipproto"]))
                {$ipproto = $_POST["ipproto"];}
        if (!empty($_POST["processing"]))
                {$processing = $_POST["processing"];}
        if (!empty($_POST["showflows"]))
                {$showflows = $_POST["showflows"];}
        if (!empty($_POST["filter_name"]))
                {$filtername = $_POST["filter_name"];}
print "
<script type=\"text/javascript\" language=\"javascript\" src=\"plugins/ipflows/prototype.js\"></script>
<script type=\"text/javascript\" language=\"javascript\" src=\"plugins/ipflows/prototype-base-extensions.js\"></script>
<script type=\"text/javascript\" language=\"javascript\" src=\"plugins/ipflows/prototype-date-extensions.js\"></script>
<script type=\"text/javascript\" language=\"javascript\" src=\"plugins/ipflows/datepicker_my.js\"></script>";
print "<link rel=\"stylesheet\" href=\"plugins/ipflows/datepicker.css\" />";
print "<link rel=\"stylesheet\" href=\"plugins/ipflows/ipflows.css\" />";
print "<link rel=\"stylesheet\" href=\"plugins/ipflows/tooltip.css\" />";
print "<script language=\"javascript\" type=\"text/javascript\">
function createPickers() {
         $(document.body).select('input.datepicker').each( function(e) {
                 new Control.DatePicker(e, { 'icon': 'plugins/ipflows/calendar.png', 'timePicker': 'true', 'use24hrs': 'true', 'firstWeekDay': '1', 'locale': 'en_my' }); 
        } );
 }
Event.observe(window, 'load', createPickers); </script>

<script type=\"text/javascript\" language=\"javascript\" src=\"plugins/ipflows/my.js\"></script>
";
	print "<div class=\"shadetabs\" style=\"background-color: #CFDFDE\">\n";
	print "<form name=\"post_form\" action=\"nfsen.php\" style=\"display:inline;\" method=\"POST\" onSubmit=\"return form_check();\">\n";
        print "<table>";
	print "<tr><td class=\"navigator\">Start time:</td><td class=\"navigator\">"; show_help("timefrom","?");
        print "<input type=\"text\" size=\"17\" maxlength=\"19\" name=\"timefrom\" value=\"$timefrom\" align=\"right\" class=\"datepicker\">";
	print "</td><td class=\"navigator\">End time:</td><td class=\"navigator\">"; show_help("timeto","?");
        print "<input type=\"text\" size=\"17\" maxlength=\"19\" name=\"timeto\" value=\"$timeto\" align=\"right\" class=\"datepicker\"></td>\n";
	print "<td class=\"navigator\">Show flows:</td><td class=\"navigator\">"; show_help("maxflows","?");
	print "<input type=\"text\" size=\"5\" maxlength=\"4\" name=\"maxflows\" value=\"$maxflows\" align=\"right\"></td><td width=\"10\"></td><td width=\"2\" bgcolor=\"gray\"></td>\n";
	print "<td class=\"navigator\">";
	show_help("filter_name","?");
	print "<input type=\"text\" size=\"8\" maxlength=\"15\" name=\"filter_name\" value=\"$filtername\" align=\"right\">&nbsp;<b><a href=\"?about=true\" class=\"lred\">About</a></b></td></tr>\n";
	print "<tr><td class=\"navigator\">Source IP:</td><td class=\"navigator\">"; show_help("srcip","?");
        print "<input type=\"text\" size=\"17\" maxlength=\"39\" name=\"srcip\" value=\"$srcip\" align=\"right\"></td>\n";
	print "<td class=\"navigator\">Source mask:</td><td class=\"navigator\">"; show_help("srcmask","?");
	print "<input type=\"text\" size=\"4\" maxlength=\"3\" name=\"srcmask\" value=\"$srcmask\" align=\"right\"></td>\n";
	print "<td class=\"navigator\">Source port:</td><td class=\"navigator\">"; show_help("srcport","?");
	print "<input type=\"text\" size=\"5\" maxlength=\"5\" name=\"srcport\" value=\"$srcport\" align=\"right\"></td><td width=\"10\" rowspan=\"2\"></td><td width=\"2\" rowspan=\"2\" bgcolor=\"gray\"></td>\n";
	print "<td rowspan=\"2\" class=\"navigator\">"; show_help("filter_list","?");print "<select size=\"2\" name=\"filter_list\" onClick=\"filter_change();\">";
	$cookie = $_COOKIE["ipflows_abc"];
	$arr =   preg_split ("/\\|/", $cookie); // split("\\|", $cookie);
	for ($i = 0; $i < count($arr); $i++) {
		if ($arr[$i] == "") continue;
		$tmp = preg_split("/\\,/", $arr[$i]);
		print "<option";
		if ($tmp[0] == $filtername) print " selected=\"selected\"";
		print " value=\"" . $tmp[0] . "\">" . ($i+1) . ". " . $tmp[0] . "</option>";
	}
	print "</select></td></tr>\n";
	print "<tr><td class=\"navigator\">Dest. IP:</td><td class=\"navigator\">"; show_help("dstip","?");
        print "<input type=\"text\" size=\"17\" maxlength=\"39\" name=\"dstip\" value=\"$dstip\" align=\"right\"></td>\n";
	print "<td class=\"navigator\">Dest. mask:</td><td class=\"navigator\">"; show_help("dstmask","?");
	print "<input type=\"text\" size=\"4\" maxlength=\"3\" name=\"dstmask\" value=\"$dstmask\" align=\"right\"></td>\n";
	print "<td class=\"navigator\">Dest. port:</td><td class=\"navigator\">"; show_help("dstport","?");
	print "<input type=\"text\" size=\"5\" maxlength=\"5\" name=\"dstport\" value=\"$dstport\" align=\"right\"></td></tr>\n";
        print "<tr><td class=\"navigator\">IP Protocol:</td><td class=\"navigator\">"; show_help("proto","?");
	print "<select name=\"proto\">\n";
	foreach ($protocols as $num => $value) {
		print "<option value=\"" . $value . "\"";
		if ($value == $proto) print " selected=\"selected\"";
		print ">" . $value . "</option>\n";
	}
	print "</select>&nbsp;\n"; show_help("ipproto","?");print "<select name=\"ipproto\">\n";
	foreach ($ipprotoM as $num => $value) {
		print "<option value=\"" . $value . "\"";
		if ($value == $ipproto) print " selected=\"selected\"";
		print ">" . $value . "</option>\n";
	}
	print "</select></td>";
	print "<td class=\"navigator\">Processing:</td><td class=\"navigator\">"; show_help("processing","?");
	print "<select name=\"processing\">\n";
	foreach ($processingM as $num => $value) {
		print "<option value=\"" . $value . "\"";
		if ($value == $processing) print " selected=\"selected\"";
		print ">" . $value . "</option>\n";
	}
	print "</select></td>";
	print "<td class=\"navigator\">"; show_help("showflows","?");print "<select name=\"showflows\">\n";
	foreach ($showflowsM as $num => $value) {
		print "<option value=\"" . $value . "\"";
		if ($value == $showflows) print " selected=\"selected\"";
		print ">" . $value . "</option>\n";
	}
	print "</select> flows </td><td class=\"navigator\">\n";
	show_help("submit_button","?");
	print "<input type=\"submit\" value=\"Show\"></td><td width=\"10\"></td><td width=\"2\" bgcolor=\"gray\"></td>\n";
        print "<td class=\"navigator\">";
	show_help("filter_save","?");
	print "<input type=\"button\" name=\"filter_save\" onClick=\"filter_insert();\" value=\"Save\">";
	show_help("filter_delete","?");
        print "<input type=\"button\" name=\"filter_del\" onClick=\"filter_delete();\" value=\"Del\">";
	print "</td></tr>\n";
	print "</table></form>";

	print "</div>\n";

	$command = 'ipflows::try';

	if ((!empty($_POST["timefrom"])) && (!empty($_POST["timeto"]))) {

		$opts['timefrom'] = insertDot($_POST['timefrom']);
		$opts['timeto'] = insertDot($_POST['timeto']);
		$opts['maxflows'] = $_POST['maxflows'];
		$opts['srcip'] = $_POST['srcip'];
		$opts['srcmask'] = $_POST['srcmask'];
		$opts['srcport'] = $_POST['srcport'];
		$opts['dstip'] = $_POST['dstip'];
		$opts['dstmask'] = $_POST['dstmask'];
		$opts['dstport'] = $_POST['dstport'];
        	$opts['proto'] = $_POST['proto'];
		$opts['ipproto'] = $_POST['ipproto'];
		$opts['processing'] = $_POST['processing'];
		$opts['showflows'] = $_POST['showflows'];

		$opts['profile'] = $_SESSION['profile'];
		$opts['profilegroup'] = $_SESSION['profilegroup'];
#		$opts['profileinfo'] = $_SESSION['profileinfo'];
	
		$out_list = nfsend_query($command, $opts);
	
		if ( !is_array($out_list) ) {
		        SetMessage('error', "Error calling backend plugin");
		        print "Error calling backend plugin";
		        return FALSE;
		}
		
		$records;	
		if (!empty($out_list['records'])) $records = $out_list['records'];

		print "<br>\n";
		if (!empty($_SESSION['records'])) unset($_SESSION['records']);
		if (!empty($records)) $_SESSION['records'] = $records;
		$_SESSION['maxflows'] = $maxflows;
		$timewindow = (humanToTime($timeto) - humanToTime($timefrom));
		if ($timewindow < 0) $timewindow = 0;
		$_SESSION['timewindow'] = $timewindow;
		unset($timewindow);	
		unset($records);
		unset($maxflows);
                if (!empty($_SESSION['filter'])) unset($_SESSION['filter']);
		if (!empty($_SESSION['filter_records'])) unset($_SESSION['filter_records']);
		if (!empty($_SESSION['filter_records_num'])) unset($_SESSION['filter_records_num']);
		if (!empty($_SESSION['table-sflow'])) unset($_SESSION['table-sflow']);
                if (!empty($_SESSION['agr0'])) unset($_SESSION['agr0']);
                if (!empty($_SESSION['maxnodes0'])) unset($_SESSION['maxnodes0']);
                if (!empty($_SESSION['filter_node0'])) unset($_SESSION['filter_node0']);
                if (!empty($_SESSION['sflow0'])) unset($_SESSION['sflow0']);
                if (!empty($_SESSION['zoom0'])) unset($_SESSION['zoom0']);
		if (!empty($_SESSION['filter_node1'])) unset($_SESSION['filter_node1']);
		if (!empty($_SESSION['agr1'])) unset($_SESSION['agr1']);
		if (!empty($_SESSION['maxnodes1'])) unset($_SESSION['maxnodes1']);
		if (!empty($_SESSION['sflow1'])) unset($_SESSION['sflow1']);
		if (!empty($_SESSION['zoom1'])) unset($_SESSION['zoom1']);

		$command2 = 'ipflows::getvars';
		$out_list = nfsend_query($command2, $opts);
		$www_dir = $out_list['www_dir'];
		$frontend_tmp_dir = $out_list['frontend_tmp_dir'];
		$_SESSION['www_dir'] = $www_dir;
		$_SESSION['frontend_tmp_dir'] = $frontend_tmp_dir;
		print "
<iframe src=\"" . $www_dir . "/plugins/ipflows/ipflows-table.php\"
    width=\"95%\" height=\"72%\" name=\"ipflowsframe\"
    frameborder=\"1\" marginwidth=\"1\" marginheight=\"1\">
</iframe>";
	}
	if (!empty($_GET['about'])) {
        print "<div class=\"shadetabs\" style=\"background-color: #CFDFDE\">\n";
print "<table><tr><td><font face=\"Courier\">
<br>
<b>ipflows</b> - IP flows correlation and visualization plugin<br>
<i>Copyright (c) 2010 Masaryk University</i><br>
<b>Author:</b> Michal Potfaj <140462@mail.muni.cz><br>
<br>
<b>Description:</b><br>
This NfSen plugin is used for correlation and visualization of IP flows in a computer<br>
network. The plugin was created as a part of a master thesis <b>IP flows correlation and<br>
visualization in computer network</b>. Plugin needs <i>nfdump</i>, <i>Perl</i>, <i>PHP</i> and <i>Graphviz</i><br>
for its work.<br>
<br>
<b>Licence:</b><br>
Redistribution and use in source and binary forms, with or without<br>
modification, are permitted provided that the following conditions are met:<br>
<br>
&nbsp; &nbsp;* Redistributions of source code must retain the above copyright notice,<br>
&nbsp; &nbsp; &nbsp;this list of conditions and the following disclaimer.<br>
&nbsp; &nbsp;* Redistributions in binary form must reproduce the above copyright notice,<br>
&nbsp; &nbsp; &nbsp;this list of conditions and the following disclaimer in the documentation<br>
&nbsp; &nbsp; &nbsp;and/or other materials provided with the distribution.<br>
&nbsp; &nbsp;* Neither the name of Masaryk University nor the names of its contributors may be<br>
&nbsp; &nbsp; &nbsp;used to endorse or promote products derived from this software without<br>
&nbsp; &nbsp; &nbsp;specific prior written permission.<br>
<br>
&nbsp; THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS \"AS IS\"<br>
&nbsp; AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE<br>
&nbsp; IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE<br>
&nbsp; ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE<br>
&nbsp; LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR<br>
&nbsp; CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF<br>
&nbsp; SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS<br>
&nbsp; INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN<br>
&nbsp; CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)<br>
&nbsp; ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE<br>
&nbsp; POSSIBILITY OF SUCH DAMAGE.<br>
</font></td></tr></table></div>";
	}
}
?>

<?php
# version 1.0.0.1 (build date 2019-02-01 00:45:00)

#
## ipflows-table.php - IP flows correlation and visualization plugin
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

session_start();

include "functions.php";
include "help.php";

# a file with the list of protocols
include "protocols.php";
$views = declare_views();
$views2 = declare_views2();
# read session input data for the table
$records;
if (!empty($_SESSION['records'])) $records = $_SESSION['records'];
$www_dir = "";
if (!empty($_SESSION['www_dir'])) $www_dir = $_SESSION['www_dir'];
$maxflows = $_SESSION['maxflows'];
$recordsnr = 0;
if (!empty($records)) $recordsnr = count($records);
$filter = 0;
$filter_records_num;
if (!empty($_SESSION['filter'])) $filter = $_SESSION['filter'];
if (!empty($_GET['filter'])) $filter = $_GET['filter'];
if ($filter == -1) $filter = 0;
if (!empty($_SESSION['filter_records_num'])) $filter_records_num = $_SESSION['filter_records_num'];
$sflow = 1;
$redirect = "";
if (!empty($_GET['redirect'])) $redirect = $_GET['redirect'];
if ($redirect == "") {
	if (!empty($_SESSION['table-sflow'])) $sflow = $_SESSION['table-sflow'];
	if (!empty($_GET['sflow'])) $sflow = $_GET['sflow'];
	$_SESSION['table-sflow'] = $sflow;
}

# read a list of available nfsen channels
$channels = $_SESSION['profileinfo']['channel'];

# display document header
print "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
        <meta HTTP-EQUIV=\"Cache-Control\" content=\"no-cache\">
        <meta HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"ipflows.css\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"tooltip.css\">
	<title>NFSEN - Ipflows Graph</title>
	<script type=\"text/javascript\" language=\"javascript\" src=\"my.js\"></script>
</head>
<body>
";

print "<div class=\"shadetabs\" style=\"width: 100%; background-color: #CFDFDE\">\n";

# display flows menu
if ($filter > 0) $recordsnr = $filter_records_num[('filter' . $filter)];
print "
<table><tr><td class=\"navigator\">
Showing ";show_help("cor_table","?");
print"<b>Table</b> &nbsp; show ";
show_help("cor_graph","?");
print "<b><a href=\"javascript:openFramePage('" . $www_dir . "/plugins/ipflows/ipflows-graph.php')\" class=\"lred\">Graph</a></b> &nbsp; &nbsp;total flows: ";
show_help("tot_flows","?");
print "<b>" . $recordsnr ."</b>";
print " &nbsp; &nbsp;showing";
$pages = 0;
for ($i=1; $i <= $recordsnr; $i) {
	if ((($i > 1) && ($i < ($sflow - $maxflows))) || (($i > ($sflow + $maxflows)) && ($i < ($recordsnr - $maxflows)))) {
		$i += $maxflows;
		$pages++;
		continue;
	}
	if ($pages > 0) {
		print " (" . $pages . " more page";
		if ($pages > 1) print "s";
		print ") ";
		$pages = 0;
	}
	print "&nbsp;";
	$to = ($i + $maxflows - 1);
	if ($to > $recordsnr) $to = $recordsnr;
	if ($i == $sflow) {
		print "<b>" . $i . "-" . $to . "</b>";
	} else {
		print "<a class=\"lred\" href=\"?sflow=" . $i . "&amp;filter=";
		if ($filter == 0) print "-1"; else print $filter;
		print "\">" . $i . "-" . $to . "</a>";
	}
	print "&nbsp;";
	$i += $maxflows;
}

# show a filter menu
$filter_node;
if (!empty($_SESSION[('filter_node' . $filter)])) $filter_node = $_SESSION[('filter_node' . $filter)];
$filter_records;
if (!empty($_SESSION['filter_records'])) $filter_records = $_SESSION['filter_records'];
if (($redirect != "") || ($filter >= 0)) {
	print "&nbsp; &nbsp;";
	if (!empty($_SESSION['agr1'])) show_help("cor_filter","?");
	show_filter_menu();
}
print "</td></tr></table></div><font face=\"Arial\" size=\"1\"><br></font>";

# display the table header (also with available channels already)
print "<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\" >
	<tr>
		<td class=\"navigator\" bgcolor=\"#CFDFDE\">"; show_help("tb_nr","?"); print "<b>Nr.</b></td>
		<td class=\"navigator\" bgcolor=\"#CFDFDE\">"; show_help("tb_time","?"); print "<b>Time</b></td>
		<td class=\"navigator\" bgcolor=\"#CFDFDE\">"; show_help("tb_durat","?"); print "<b>Durat.</b></td>
		<td class=\"navigator\" bgcolor=\"#CFDFDE\">"; show_help("tb_src","?"); print "<b>Source</b></td>
		<td class=\"navigator\" bgcolor=\"#CFDFDE\">"; show_help("tb_dst","?"); print "<b>Destination</b></td>
		<td class=\"navigator\" bgcolor=\"#CFDFDE\">"; show_help("tb_prot","?"); print "<b>Prot.</b></td>
		<td class=\"navigator\" bgcolor=\"#CFDFDE\">"; show_help("tb_pkts","?"); print "<b>Pkts</b></td>
		<td class=\"navigator\" bgcolor=\"#CFDFDE\">"; show_help("tb_bytes","?"); print "<b>Bytes</b></td>";
foreach ($channels as $channel => $channel_description) {
	print "<td class=\"navigator\" bgcolor=\"#CFDFDE\">"; show_help("tb_channel","?"); print"<b>" . $channel . "</b></td>\n";
}
print "</tr>\n";

# display records
if(!empty($records)) {
	$index = 0;
        foreach ($records as $record) {
                $tmpdata = preg_split ("/\\|/", $record, 11);
                $r['number'] = $tmpdata[0];
                if (($filter > 0) && ((empty($filter_records[$r['number']][('filter' . $filter)])) || ($filter_records[$r['number']][('filter' . $filter)] != $filter_node))) continue;
		$index++;
		# skip the record outside the shown interval
		if ($index < $sflow) continue;
		if ($index >= ($sflow + $maxflows)) break;
		# parse input data
		$r['time'] = removeDot($tmpdata[1]);
		$r['durat'] = $tmpdata[2];
		$r['srcip'] = $tmpdata[3];
		$r['srcport'] = $tmpdata[4];
		$r['dstip'] = $tmpdata[5];
		$r['dstport'] = $tmpdata[6];
		$r['proto'] = $tmpdata[7];
		$r['pkts'] = $tmpdata[8];
		$r['bytes'] = $tmpdata[9];
		$r['all_channels'] = $tmpdata[10];
		$r['channels'] = preg_split("/\\|/", $r['all_channels']);
		# write data to a table
                print "<tr><td class=\"navigator\" bgcolor=\"gray\">" . $r['number'] . "</td>\n";
		print "<td class=\"navigator\">" . $r['time'] . "</td>\n";
		print "<td class=\"navigator\">" . $r['durat'] . "ms</td>\n";
		print "<td class=\"navigator\">" . $r['srcip'] . ":" . $r['srcport'] . "</td>\n";
		print "<td class=\"navigator\">" . $r['dstip'] . ":" . $r['dstport'] . "</td>\n<td class=\"navigator\">";
                if ($r['proto'] == 0) { print "masked"; } else {
         	       if (isset($protocols[$r['proto']])) {
                	       print $protocols[$r['proto']];
                       } else { print "unknown (" . $r['proto'] . ")"; }
                }
                print "</td>\n<td class=\"navigator\">" . $r['pkts'] . "</td>\n";
		print "<td class=\"navigator\">" . $r['bytes'] . "</td>\n";
                $channels2 = $channels;
                foreach ($channels2 as $channel => $channel_description) {
	                $channels2[$channel] = "";
                }
                for ($i = 0; $i < count($r['channels']); $i) {
        	        $channels2[$r['channels'][$i+2]] = $channels2[$r['channels'][$i+2]] . "&nbsp;" . "<a class=\"lred\" href=\"#\" title=\"" . removeDot($r['channels'][$i]) . " (" . $r['channels'][$i+1] . "ms)\">" . (($i / 3) + 1) . ".</a>";
//                      $channels2[$r['channels'][$i+2]] = $channels2[$r['channels'][$i+2]] . "&nbsp;" . "<a class=\"tt2\" href=\"#\">" . (($i / 3) + 1) . ".<span class=\"tooltip\"><span class=\"top\"></span><span class=\"middle\">" . removeDot($r['channels'][$i]) . " (" . $r['channels'][$i+1] . "ms)</span><span class=\"bottom\"></span></a>";
                         $i += 3;
                }
                foreach ($channels2 as $channel2 => $channel2_description) {
                	if (strpos("x".$r['all_channels']."x", $channel2) == false) {
                       		print "<td class=\"navigator\" bgcolor=\"#FF0000\">&nbsp;</td>\n";
                        } else {
                                print "<td class=\"navigator\" bgcolor=\"#00FF00\" align=\"center\"> " . $channel2_description . "</td>\n";
                        }
                }
                print "</tr>\n";
         }
}
print "</table>
</body>
</html>";

if (!empty($filter)) $_SESSION['filter'] = $filter;
unset($records);
unset($filter_records);
unset($filter_records_num);
unset($filter);
unset($protocols);
?>

<?php
# version 1.0.0.1 (build date 2019-02-01 00:45:00)

#
## ipflows-graph.php - IP flows correlation and visualization plugin
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
# a file with the list of colors
include "colors.php";
# a file with the list of protocols
include "protocols.php";
# a file with additional functions
include "functions.php";
# a file with help texts inserted
include "help.php";

$views = declare_views();
$views2 = declare_views2();
# temporary files for generating a graph
$myfile = $_SESSION['frontend_tmp_dir'] . "/graph" . session_id();
$www_dir = "";
if (!empty($_SESSION['www_dir'])) $www_dir = $_SESSION['www_dir'];
$mypage = $www_dir . "/plugins/ipflows/tmp/graph" . session_id() . ".svg";
$mygraph_width = 200;
$mygraph_height = 200;

# values for writing a graph file
$channels;
$nodes;
$edges;
$filter_records;
$filter_records_num;

function set_page_wh() {
	global $mygraph_width, $mygraph_height, $myfile;
	$values = `cat $myfile.svg | grep "<svg width="`;
	$tmp = str_replace("<svg width=\"", "", $values);
	$tmp = str_replace("pt\" height=\"", "|", $tmp);
	$tmp = str_replace("pt\"\n", "", $tmp);
	$tmp = preg_split ("/\\|/", $tmp);
	if ($tmp[0] != "") $mygraph_width = $tmp[0];
	if ($tmp[1] != "") $mygraph_height = $tmp[1];
}

# Displays the main graph page
function show_page() {
	global $views, $views2, $nodes, $sflow, $maxnodes, $agr, $filter, $mypage, $mygraph_width, $mygraph_height, $zoom, $filter_records_num, $www_dir;
	set_page_wh();
	print "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
        <META HTTP-EQUIV=\"Cache-Control\" content=\"no-cache\">
        <META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
	<link rel=\"stylesheet\" type=\"text/css\" href=\"ipflows.css\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"tooltip.css\">
<title>NFSEN - Ipflows Graph</title>
<script type=\"text/javascript\" language=\"javascript\" src=\"my.js\"></script>
</head>
<body>\n";

# show a view menu:
$topwidth = round($mygraph_width * ($zoom / 100));
if ($topwidth < 900) $topwidth = 900;
print "<div class=\"shadetabs\" style=\"width: " . $topwidth . "px; background-color: #CFDFDE\">\n";
print "<form name=\"post_form2\" action=\"ipflows-graph.php?thispost=1&amp;delete=1\" style=\"display:inline;\" method=\"POST\" onSubmit=\"return form_check2(";
print $views2['time']['maxval'];
print ");\">\n";
print "<table>
<tr><td class=\"navigator\">Showing ";
show_help("cor_graph","?");
print "<b>Graph</b> &nbsp; &nbsp;show ";
show_help("cor_table","?");
print "<b><a class=\"lred\" href=\"javascript:this.openFramePage('" . $www_dir . "/plugins/ipflows/ipflows-table.php?redirect=1')\">Table</a></b>&nbsp; &nbsp;
view up to&nbsp;";
show_help("gr_nodes","?");
print "<input type=\"text\" size=\"4\" maxlength=\"4\" name=\"maxnodes\" value=\"" . $maxnodes . "\">&nbsp;nodes&nbsp; &nbsp; image zoom&nbsp;";
show_help("gr_zoom","?");
print "<input type=\"text\" size=\"3\" maxlength=\"3\" name=\"zoom\" value=\"" . $zoom . "\">%&nbsp; &nbsp;";
show_help("gr_agg","?");
print "aggregate within:</td></tr>
<tr><td class=\"navigator\">";
foreach ($views as $key => $value) {
	show_help("gr_" . $key, "?");
	print "<input type=checkbox name=\"" . $key . "\" value=\"1\"";
	if ($agr[$key] == 1) {
		print " checked=\"yes\"><b>" . $value . "</b>";
	} else {
		print ">" . $value;
	}
	print "&nbsp;&nbsp;";
}
print "</td></tr><tr><td class=\"navigator\">";
foreach ($views2 as $key => $value) {
	$len = strlen(($views2[$key]['maxval'] . ""));
	if ($agr[$key] > 0) print "<b>";
	print $views2[$key]['before'] . " ";
	show_help("gr_" . $key, "?");
	print "<input type=\"text\" size=\"" . $len . "\" maxlength=\"" . $len . "\" name=\"" . $key . "\" value=\"" . $agr[$key] . "\">" . $views2[$key]['after'] . "&nbsp; &nbsp; &nbsp;\n";
	if ($agr[$key] > 0) print "</b>";
}

$i = 0;
$j = "";
foreach ($views as $key => $value) $agrX[$key] = 0;
foreach ($views2 as $key => $value) $agrX[$key] = 0;
if (!empty($_SESSION[('agr' . $i)])) $j = $_SESSION[('agr' . $i)];
while ($j != "") {
	if ($i > $filter) break;
	foreach ($j as $key => $value) {
		if ($j[$key] > 0) {
			if ($agrX[$key] == 0) {
				$agrX[$key] = $j[$key];
			} else {
				if ($agrX[$key] > $j[$key]) $agrX[$key] = $j[$key];
			}
		}
	}
	$i++;
	if (!empty($_SESSION[('agr' . $i)])) { $j = $_SESSION[('agr' . $i)]; } else { $j = ""; }
}
show_help("gr_allp","?");
print "<a class=\"lred\" href=\"javascript:this.mark_filter(";
$i = 0;
foreach ($agrX as $key => $value) {
	if ($i > 0) print ",";
	if ($i < count($views)) {
		if ($agrX[$key] == 0) {print "false"; } else { print "true";}
	} else {
		print $agrX[$key];
	}
	$i++;
}
print ")\">All previous</a>&nbsp;&nbsp;";
show_help("gr_def","?");
print "<a class=\"lred\" href=\"javascript:this.mark_filter(";
$i = 0;
foreach ($agr as $key => $value) {
        if ($i >= (count($views) + count($views2))) break;
        if ($i > 0) print ",";
        if ($i < count($views)) {
                if ($agr[$key] == 0) {print "false"; } else { print "true";}
        } else {
                print $agr[$key];
        }
        $i++;
}
print ")\">Default</a>&nbsp;&nbsp;";
show_help("gr_none","?");
print "<a class=\"lred\" href=\"javascript:this.clear_filter()\">Clear</a>&nbsp; &nbsp; &nbsp;";
show_help("gr_rewrite","?");
print "<input type=submit value=\"Rewrite graph\"></td></tr></table></form>\n";
print "</div>";

# show a node menu
$nodesnr = count($nodes);
print "<div class=\"shadetabs\" style=\"width: " . $topwidth . "px; background-color: #CFDFDE\">\n";
print "<table><tr><td class=\"navigator\">";
show_help("gr_flows","?");
print "Flows: <b>" . $filter_records_num[('filter' . $filter)] . "</b> &nbsp; ";
show_help("gr_nodes","?");
print "Nodes: <b>" . $nodesnr ."</b> &nbsp; showing: ";
$pages = 0;
for ($i=1; $i < $nodesnr; $i) {
        if ((($i > 1) && ($i < ($sflow - $maxnodes))) || (($i > ($sflow + $maxnodes)) && ($i < ($nodesnr - $maxnodes)))) {
                $i += $maxnodes;
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
        $to = ($i + $maxnodes - 1);
        if ($to > $nodesnr) $to = $nodesnr;
        if ($i == $sflow) {
                print "<b>" . $i . "-" . $to . "</b>";
        } else {
                print "<a class=\"lred\" href=\"?sflow=" . $i . "&amp;filter=";
		if ($filter == 0) print "-1"; else print $filter;
		print "\">" . $i . "-" . $to . "</a>";
        }
        print "&nbsp;";
        $i += $maxnodes;
}
print "\n";
print "&nbsp;&nbsp;<a href=\"ipflows-download.php?type=svg\" title=\"Save SVG image\"><img src=\"edit.png\" border=\"0\" alt=\"Save SVG image\"></a>&nbsp;";
print "<a href=\"ipflows-download.php?type=png\" title=\"Save PNG image\"><img src=\"save.png\" border=\"0\" alt=\"Save PNG image\"></a>&nbsp;";

# show a filter menu
print "&nbsp; &nbsp;";
if (!empty($_SESSION['agr1'])) show_help("cor_filter","?");
show_filter_menu();
print "</td></tr></table></div>\n";

# show the graph
//set_page_wh();
print "
<object data=\"" . $mypage . "\" type=\"image/svg+xml\" width=\"" . round($mygraph_width * $zoom / 100) . "\" height=\"" . round($mygraph_height * $zoom / 100) . "\">
</object>
</body>
</html>
";
}

# Generates the list of all aviable channels from nfsen profile
function generate_channels() {
	global $channels;
	$channels_num = 0;
	$all_channels = $_SESSION['profileinfo']['channel'];
	foreach ($all_channels as $key => $value) {
		$channels_num++;
		# channel name
		$channels[$key]['label'] = $key;
		# channel color (black)
		$channels[$key]['color'] = 22;
		# flows, bytes and packets information
		$channels[$key]['flows'] = 0;
		$channels[$key]['bytes'] = 0;
		$channels[$key]['pkts'] = 0;
		$channels[$key]['time'] = 0;
		$channels[$key]['durat'] = 0;
	}
}

function generate_nodes() {
	global $records, $channels, $nodes, $edges, $protocols, $agr, $filter, $filter_node, $filter_records, $filter_records_num, $views2;
	# count how many aggregation arguments is set (the all aggreration is different from party aggregation)
	$agr['count'] = $agr['srcip'] + $agr['srcport'] + $agr['dstip'] + $agr['dstport'] + $agr['proto'] + $agr['ipproto'] + $agr['srcclass'] + $agr['dstclass'] + $agr['channels'];
	$agr['count2'] = $agr['time'] + $agr['durat'] + $agr['pkts'] + $agr['bytes'];
	$filter_records_num['filter' . $filter] = 0;
	# number of edges and nodes for properly aggregation
	$edge_num = 0;
	$nodes_shown = 0;
	# aggreration values (1 - src, 2 - dst, 3 - edges)
	$agr_data1;
	$agr_data2;
	$agr_data3;
	$agr_color;
	if (!empty($records)) foreach ($records as $record) {
		$agr_label1 = "";
		$agr_label2 = "";
		$agr_data1 = "";
		$agr_data2 = "";
		# parse input data - hope it is clearly - see backend plugin code
                $tmpdata = preg_split("/\\|/", $record, 11);
		$r['number'] = $tmpdata[0];
                if (($filter > 0) && ((empty($filter_records[$r['number']][('filter' . $filter)])) || ($filter_records[$r['number']][('filter' . $filter)] != $filter_node))) continue;
		$r['time'] = $tmpdata[1];
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
		$agr_color = $r['number'];

		# aggregates the data
		if (($agr['count'] < 9) || ($agr['count2'] > 0)) {
			if ($agr['srcip'] == 1) {
				$agr_data1 .= "|" . $r['srcip'];
			        $agr_label1 .= $r['srcip'];
				if ($agr['dstip'] == 1) $agr_data1 .= "|" . $r['dstip'];
				if ($agr['dstport'] == 1) $agr_data1 .= "|" . $r['dstport'];
				if ($agr['dstclass'] == 1) $agr_data1 .= "|" . return_class($r['dstip']);
			}
			if ($agr['srcport'] == 1) {
				$agr_data1 .= "|" . $r['srcport'];
				$agr_label1 .= ":" . $r['srcport'];
				if ($agr['dstport'] == 1) $agr_data1 .= "|" . $r['dstport'];
				if ($agr['dstip'] == 1) $agr_data1 .= "|" . $r['dstip'];
				if ($agr['dstclass'] == 1) $agr_data1 .= "|" . return_class($r['dstip']);
			}
			if ($agr['dstip'] == 1) {
			        $agr_data2 .= "|" . $r['dstip'];
			        $agr_label2 .= $r['dstip'];
				if ($agr['srcip'] == 1) $agr_data2 .= "|" . $r['srcip'];
				if ($agr['srcport'] == 1) $agr_data2 .= "|" . $r['srcport'];
				if ($agr['srcclass'] == 1) $agr_data2 .= "|" . return_class($r['srcip']);
			}       
			if ($agr['dstport'] == 1) {
			        $agr_data2 .= "|" . $r['dstport'];
			        $agr_label2 .= ":" . $r['dstport'];
				if ($agr['srcport'] == 1) $agr_data2 .= "|" . $r['srcport'];
				if ($agr['srcip'] == 1) $agr_data2 .= "|" . $r['srcip'];
				if ($agr['srcclass'] == 1) $agr_data2 .= "|" . return_class($r['srcip']);
			}
			if ($agr['proto'] == 1) {
				$agr_data1 .= "|" . $r['proto'];
				$agr_data2 .= "|" . $r['proto'];
				$agr_label1 .= "-" . $protocols[$r['proto']];
				$agr_label2 .= "-" . $protocols[$r['proto']];
			}
			if ($agr['ipproto'] == 1) {
				if ((strpos($r['srcip'], ":") != "") || (strpos($r['dstip'], ":") != "")) {
					$agr_data1 .= "|" . "v6";
					$agr_data2 .= "|" . "v6";
        	                        $agr_label1 .= " (IPv6)";
	                                $agr_label2 .= " (IPv6)";
				} else {
					$agr_data1 .= "|" . "v4";
					$agr_data2 .= "|" . "v4";
                                        $agr_label1 .= " (IPv4)";
                                        $agr_label2 .= " (IPv4)";
				}
			}
			if ($agr['srcclass'] == 1) {
				$agr_data1 .= "|" . return_class($r['srcip']);
				if ($agr['dstclass'] == 1) $agr_data1 .= "|" . return_class($r['dstip']);
				$agr_label1 .= " (class " . return_class($r['srcip']) . ")";
			}
                        if ($agr['dstclass'] == 1) {
				$agr_data2 .= "|" . return_class($r['dstip']);
				if ($agr['srcclass'] == 1) $agr_data2 .= "|" . return_class($r['srcip']);
				$agr_label2 .= " (class " . return_class($r['dstip']) . ")";

                        }
			if ($agr['channels'] == 1) {
				$agr_label1 .= "(ALL)";
				$agr_label2 .= "(ALL)";
				$agr_data1 .= "|1";
				$agr_data2 .= "|2";
			}
			foreach ($views2 as $key => $value) {
				if ($agr[$key] > 0) {
					$dc = 0;
					$tt = 0;
					if ($key != "time") {
						$dc = floor($r[$key] / $agr[$key]);
					} else {
						$tt = humanToTime($r['time']);
						$dc = floor($tt / $agr['time']);
					}
					$agr_data1 .= "|" . $key . $dc;
					$agr_data2 .= "|" . $key . $dc;
					$mess = "";
					if ($key != "time") {
						$mess = ($dc * $agr[$key]) . "-" . ((($dc + 1) * $agr[$key]) - 1);
					 } else {
						$mess = date('Y/m/d.H:i:s', ($tt - ($tt % $agr['time']))) . "+" . $agr['time'];
					}
					$agr_label1 .= "(" . $views2[$key]['before'] . " " . $mess . $views2[$key]['after'] . ")";
                                        $agr_label2 .= "(" . $views2[$key]['before'] . " " . $mess . $views2[$key]['after'] . ")";
				}
			}
			if ($agr_data1 == "") $agr_data1 = $r['number'];
			if ($agr_data2 == "") $agr_data2 = $r['number'];
			if ($agr_label1 == "") $agr_label1 = $r['srcip'] . ":" . $r['srcport'] . "-" . $protocols[$r['proto']];
			if ($agr_label2 == "") $agr_label2 = $r['dstip'] . ":" . $r['dstport'] . "-" . $protocols[$r['proto']];
			$agr_data1 .= "|1";
			$agr_data2 .= "|2";
			for ($i = 3; $i <= count($r['channels']); $i) {
				$agr_data1 .= "|" . $r['channels'][$i-1];
				$agr_data2 .= "|" . $r['channels'][$i-1];
				$i += 3;
			}
                        if (!empty($nodes[$agr_data1])) $agr_color = $nodes[$agr_data1]['color'];
                        if (!empty($nodes[$agr_data2])) $agr_color = $nodes[$agr_data2]['color'];
                } else {
                        $agr_label1 = "ALL";
                        $agr_label2 = "ALL";
                        $agr_color = 22;
                        $agr_data1 .= "|1|" . $r['channels'][2];
                        $agr_data2 .= "|2|" . $r['channels'][(count($r['channels'])-1)];
                }

		# generates all (aggregated) nodes
		if (empty($nodes[$agr_data1])) {
			$nodes_shown++;
			$nodes[$agr_data1]['label'] = $agr_label1;
			$nodes[$agr_data1]['color'] = $agr_color;
			$nodes[$agr_data1]['edgeto'] = $r['channels'][2];
			$nodes[$agr_data1]['edgetocolor'] = $agr_color;
			$nodes[$agr_data1][('number' . $filter)] = $nodes_shown;
			$nodes[$agr_data1]['tooltip'] = "";
			$nodes[$agr_data1]['flows'] = 0;
			$nodes[$agr_data1]['pkts'] = 0;
			$nodes[$agr_data1]['bytes'] = 0;
			$nodes[$agr_data1]['time'] = 0;
			$nodes[$agr_data1]['durat'] = 0;
		}
                if (strlen($nodes[$agr_data1]['tooltip'])<200) {
			$nodes[$agr_data1]['tooltip'] .= $r['number'] . ". ";
		} elseif (substr($nodes[$agr_data1]['tooltip'], (strlen($nodes[$agr_data1]['tooltip'])-1)) == " ") {
			$nodes[$agr_data1]['tooltip'] .= "...";
		}
		$nodes[$agr_data1]['flows']++;
		$nodes[$agr_data1]['pkts'] += $r['pkts'];
		$nodes[$agr_data1]['bytes'] += $r['bytes'];
                $tmptime = humanToTime2($r['time']);
                if (($nodes[$agr_data1]['time'] == 0) || ($tmptime < $nodes[$agr_data1]['time'])) $nodes[$agr_data1]['time'] = $tmptime;
                if (($tmptime + $r['durat']) > $nodes[$agr_data1]['durat']) $nodes[$agr_data1]['durat'] = ($tmptime + $r['durat']);
		if (empty($nodes[$agr_data2])) {
			$nodes_shown++;
	                $nodes[$agr_data2]['label'] = $agr_label2;
                        $nodes[$agr_data2]['color'] = $agr_color;
	                $nodes[$agr_data2]['edgefrom'] = $r['channels'][(count($r['channels'])-1)];
                        $nodes[$agr_data2]['edgefromcolor'] = $agr_color;
			$nodes[$agr_data2][('number' . $filter)] = $nodes_shown;
			$nodes[$agr_data2]['tooltip'] = "";
                        $nodes[$agr_data2]['flows'] = 0;
                        $nodes[$agr_data2]['pkts'] = 0;
                        $nodes[$agr_data2]['bytes'] = 0;
			$nodes[$agr_data2]['time'] = 0;
			$nodes[$agr_data2]['durat'] = 0;
		}
                if (strlen($nodes[$agr_data2]['tooltip'])<200) {
			$nodes[$agr_data2]['tooltip'] .= $r['number'] . ". ";
                } elseif (substr($nodes[$agr_data2]['tooltip'], (strlen($nodes[$agr_data2]['tooltip'])-1)) == " ") {
                        $nodes[$agr_data2]['tooltip'] .= "...";
                }
		if (($nodes[$agr_data2]['time'] == 0) || ($tmptime < $nodes[$agr_data2]['time'])) $nodes[$agr_data2]['time'] = $tmptime;
		if (($tmptime + $r['durat']) > $nodes[$agr_data2]['durat']) $nodes[$agr_data2]['durat'] = ($tmptime + $r['durat']);
		$nodes[$agr_data2]['flows']++;
                $nodes[$agr_data2]['pkts'] += $r['pkts'];
                $nodes[$agr_data2]['bytes'] += $r['bytes'];
                $channels[$r['channels'][2]]['flows']++;
                $channels[$r['channels'][2]]['pkts'] += $r['pkts'];
                $channels[$r['channels'][2]]['bytes'] += $r['bytes'];
                if (($channels[$r['channels'][2]]['time'] == 0) || ($tmptime < $channels[$r['channels'][2]]['time']))
	                $channels[$r['channels'][2]]['time'] = $tmptime;
        	if (($tmptime + $r['durat']) > $channels[$r['channels'][2]]['durat'])
                        $channels[$r['channels'][2]]['durat'] = ($tmptime + $r['durat']);

		# generate all (aggregated) edges
                for ($i = 3; $i < count($r['channels']); $i) {
			if (($agr['count']  == 0) && ($agr['count2'] == 0)) {
				$agr_data3=$edge_num;
			} elseif ($agr['count'] == 9) {
                                $agr_data3="3".$r['channels'][$i-1].$r['channels'][$i+2];
			} else {
                                $agr_data3=$r['channels'][$i-1] . $r['channels'][$i+2] . "color" . $agr_color . "color";
			}
			if (empty($edges[$agr_data3])) {
				$edges[$agr_data3]['from']=$r['channels'][$i-1];
				$edges[$agr_data3]['to']=$r['channels'][$i+2];
				$edges[$agr_data3]['color']=$agr_color;
				$edges[$agr_data3][('number' . $filter)]=$nodes_shown;
				$edges[$agr_data3]['tooltip'] = "";
				$edges[$agr_data3]['flows'] = 0;
				$edges[$agr_data3]['pkts'] = 0;
				$edges[$agr_data3]['bytes'] = 0;
				$edges[$agr_data3]['time'] = 0;
				$edges[$agr_data3]['durat'] = 0;
                                $edge_num++;
			}
                        if (strlen($edges[$agr_data3]['tooltip'])<200) {
				$edges[$agr_data3]['tooltip'] .= $r['number'] . ". ";
	                } elseif (substr($edges[$agr_data3]['tooltip'], (strlen($edges[$agr_data3]['tooltip'])-1)) == " ") {
        	                $edges[$agr_data3]['tooltip'] .= "...";
	                }
			$edges[$agr_data3]['flows']++;
	                $edges[$agr_data3]['pkts'] += $r['pkts'];
        	        $edges[$agr_data3]['bytes'] += $r['bytes'];
                	if (($edges[$agr_data3]['time'] == 0) || ($tmptime < $edges[$agr_data3]['time'])) $edges[$agr_data3]['time'] = $tmptime;
                	if (($tmptime + $r['durat']) > $edges[$agr_data3]['durat']) $edges[$agr_data3]['durat'] = ($tmptime + $r['durat']);
			$channels[$r['channels'][$i+2]]['flows']++;
			$channels[$r['channels'][$i+2]]['pkts'] += $r['pkts'];
			$channels[$r['channels'][$i+2]]['bytes'] += $r['bytes'];
                        if (($channels[$r['channels'][$i+2]]['time'] == 0) || ($tmptime < $channels[$r['channels'][$i+2]]['time']))
				$channels[$r['channels'][$i+2]]['time'] = $tmptime;
                        if (($tmptime + $r['durat']) > $channels[$r['channels'][$i+2]]['durat'])
				$channels[$r['channels'][$i+2]]['durat'] = ($tmptime + $r['durat']);
			$i += 3;
		}
		$filter_records[$r['number']][('filter' . ($filter + 1))] = $agr_color;
		$filter_records_num[('filter' . $filter)]++;
	}
	unset($_SESSION['filter_records']);
	$_SESSION['filter_records'] = $filter_records;
	$_SESSION['filter_records_num'] = $filter_records_num;
}

# Generates a graph file from graph data
function generate_graph() {
	global $channels, $nodes, $edges, $sflow, $maxnodes, $filter, $www_dir;
	$filter_records_num = $_SESSION['filter_records_num'];
	# header
        $graph = "digraph IpFlow {";
	# generate channels first (actualy, they are nodes)
	$graph .= "\nnode [shape=box,fontname=Verdana,fontsize=10];";
	if (!empty($channels)) foreach ($channels as $value) {
		$graph .= "\n\"" . $value['label'] . "\" [label=\"" . $value['label'] . "\",color=" . get_color($value['color']) . ",tooltip=\"Duration: " . removeDot(timeToHuman2($value['time'])) . "-" . cutHumanTime(timeToHuman2($value['time']), timeToHuman2($value['durat'])) . ", flows: " . c_value($value['flows']) . ", bytes: " . c_value($value['bytes']) . "B (" . c_value($value['pkts']) . "pkts.)\",style=\"filled\",fillcolor=\"white\"];";
	}
	# generate nodes (with edges to channels)
        $graph .= "\nnode [shape=ellipse,fontname=Verdana,fontsize=7,height=0.1,width=0.5];";
	if (!empty($nodes)) foreach ($nodes as $key => $value) {
		if ($value[('number' . $filter)] < $sflow) continue;
		if ($value[('number' . $filter)] >= ($sflow + $maxnodes)) continue;
		$graph .= "\n\"" . $key . "\" [label=\"" . $value['label'] . "\",color=" . get_color($value['color']) . ",tooltip=\"Duration: " . removeDot(timeToHuman2($value['time'])) . "-" . cutHumanTime(timeToHuman2($value['time']), timeToHuman2($value['durat'])) . ", flows: " . c_value($value['flows']) . ", " . c_value($value['bytes']) . "B (" . c_value($value['pkts']) . "pkts.): " . $value['tooltip'] . "\",URL=\"" . $www_dir . "/plugins/ipflows/ipflows-graph.php?filter=" . ($filter + 1) . "&amp;filter_node=" . $value['color'] . "&amp;delete=1\",target=\"ipflowsframe\",style=\"filled\",fillcolor=\"white\"];"; 
		if (!empty($value['edgefrom'])) {
			$graph .= "\n\"" . $value['edgefrom'] . "\"->\"" . $key . "\" [color=" . get_color($value['edgefromcolor']) . ",penwidth=\"" . c_penwidth($value['flows'], $filter_records_num[('filter' . $filter)]) . "\",tooltip=\"Duration: " . removeDot(timeToHuman2($value['time'])) . "-" . cutHumanTime(timeToHuman2($value['time']), timeToHuman2($value['durat'])) . ", flows: " . c_value($value['flows']) . ", " . c_value($value['bytes']) . "B (" . c_value($value['pkts']) . "pkts.): " . $value['tooltip'] . "\",edgeURL=\"" . $www_dir . "/plugins/ipflows/ipflows-graph.php?filter=" . ($filter + 1) . "&amp;filter_node=" . $value['color'] . "&amp;delete=1\",target=\"ipflowsframe\"];";
		}
		if (!empty($value['edgeto'])) {
			$graph .= "\n\"" . $key . "\"->\"" . $value['edgeto'] . "\" [color=" . get_color($value['edgetocolor']) . ",penwidth=\"" . c_penwidth($value['flows'], $filter_records_num[('filter' . $filter)]) . "\",tooltip=\"Duration: " . removeDot(timeToHuman2($value['time'])) . "-" . cutHumanTime(timeToHuman2($value['time']), timeToHuman2($value['durat'])) . ", flows: " . c_value($value['flows']) . ", " . c_value($value['bytes']) . "B (" . c_value($value['pkts']) . "pkts.): " . $value['tooltip'] . "\",edgeURL=\"" . $www_dir . "/plugins/ipflows/ipflows-graph.php?filter=" . ($filter + 1) . "&amp;filter_node=" . $value['color'] . "&amp;delete=1\",target=\"ipflowsframe\"];";
		}
	}
	# generate edges between each two channels
	if (!empty($edges)) foreach ($edges as $value) {
                if ($value[('number' . $filter)] < $sflow) continue;
                if ($value[('number' . $filter)] >= ($sflow + $maxnodes)) continue;
		$graph .= "\n\"" . $value['from'] . "\"->\"" . $value['to'] . "\" [color=" . get_color($value['color']) . ",penwidth=\"" . c_penwidth($value['flows'], $filter_records_num[('filter' . $filter)]) . "\",tooltip=\"Duration: " . removeDot(timeToHuman2($value['time'])) . "-" . cutHumanTime(timeToHuman2($value['time']), timeToHuman2($value['durat'])) . ", flows: " . c_value($value['flows']) . ", " . c_value($value['bytes']) . "B (" . c_value($value['pkts']) . "pkts.): " . $value['tooltip'] . "\",edgeURL=\"" . $www_dir . "/plugins/ipflows/ipflows-graph.php?filter=" . ($filter + 1) . "&amp;filter_node=" . $value['color'] . "&amp;delete=1\",target=\"ipflowsframe\"];";
	}
	$graph .= "\n}\n";
	return $graph;
}

# get and set all session data
$filter = 0;
$filter_node = 0;
$records;
if (!empty($_SESSION['records'])) $records = $_SESSION['records'];

if (!empty($_SESSION['filter'])) $filter = $_SESSION['filter'];
if (!empty($_GET['filter'])) $filter = $_GET['filter'];
if ($filter == -1) $filter = 0;
$_SESSION['filter'] = $filter;
if (!empty($_GET['delete'])) {
	unset($_SESSION[('filter_node' . ($filter + 1))]);
	unset($_SESSION[('maxnodes' . ($filter + 1))]);
	unset($_SESSION[('sflow' . ($filter + 1))]);
	unset($_SESSION[('agr' . ($filter + 1))]);
	unset($_SESSION[('zoom' . ($filter + 1))]);
	unset($_SESSION[('agr' . $filter)]);
}

if (!empty($_SESSION[('filter_node' . $filter)])) $filter_node = $_SESSION[('filter_node' . $filter)];
if (!empty($_GET['filter_node'])) $filter_node = $_GET['filter_node'];
$_SESSION[('filter_node' . $filter)] = $filter_node;

$maxflows = $_SESSION['maxflows'];
if (empty($_SESSION[('maxnodes' . $filter)])) {
	$maxnodes = (2 * $maxflows);
	if ($maxnodes > 9999) $maxnodes = 9999;
} else {
	$maxnodes = $_SESSION[('maxnodes' . $filter)];
}
if (!empty($_POST['maxnodes'])) $maxnodes = $_POST['maxnodes'];
if ($maxnodes < 10) $maxnodes = 10;
$_SESSION[('maxnodes' . $filter)] = $maxnodes;

$zoom = 150;
if (!empty($_SESSION[('zoom' . $filter)])) $zoom = $_SESSION[('zoom' . $filter)];
if (!empty($_POST['zoom'])) $zoom = $_POST['zoom'];
if ($zoom < 10) $zoom = 10;
if ($zoom > 999) $zoom = 999;
$_SESSION[('zoom' . $filter)] = $zoom;

$sflow = 1;
if (!empty($_SESSION[('sflow' . $filter)])) $sflow = $_SESSION[('sflow' . $filter)];
if (!empty($_GET['sflow'])) $sflow = $_GET['sflow'];

if (!empty($_SESSION[('agr' . $filter)])) {
	$agr = $_SESSION[('agr' . $filter)];
} else {
	if ($filter == 0) {
		foreach ($views as $key => $value) $agr[$key] = 0;
		foreach ($views2 as $key => $value) $agr[$key] = 0;
		$agr['channels'] = 1;
	} else {
		$agr = $_SESSION[('agr' . ($filter - 1))];
	}
}

if (!empty($_GET['thispost'])) {
	$sflow = 1;
	foreach ($views as $key => $value) {
		$agr[$key] = 0;
		if (!empty($_POST[$key])) $agr[$key] = $_POST[$key];
	}
	foreach ($views2 as $key => $value) {
		$agr[$key] = 0;
		if (!empty($_POST[$key])) $agr[$key] = $_POST[$key];
	}
}

$_SESSION[('sflow' . $filter)] = $sflow;
$_SESSION[('agr' . $filter)] = $agr;
if (!empty($_SESSION['filter_records'])) $filter_records = $_SESSION['filter_records'];
if (!empty($_SESSION['filter_records_num'])) $filter_records_num = $_SESSION['filter_records_num'];

generate_channels();
generate_nodes();
$graph_text = generate_graph();
$_SESSION['graph_text'] = $graph_text;
write_graph($graph_text, $myfile, "svg");
show_page();

unset($colors);
unset($protocols);
unset($channels);
unset($edges);
unset($nodes);
unset($records);
unset($filter_records);
unset($filter_records_num);
?>

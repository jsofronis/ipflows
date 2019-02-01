<?php
# version 1.0.0.1 (build date 2019-02-01 00:45:00)

#
## functions.php - IP flows correlation and visualization plugin
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

date_default_timezone_set('UTC');

# a list of views for aggregating the graph data
function declare_views() {
	$views['srcip']="Src. IP";
	$views['dstip']="Dst. IP";
	$views['srcport']="Src. Port";
	$views['dstport']="Dst. Port";
	$views['proto']="Prot.";
	$views['srcclass']="IPv4 Src. Class";
	$views['dstclass']="IPv4 Dst. Class";
	$views['ipproto']="IP Prot.";
	$views['channels']="Channels";
	return $views;
}

# a list of views for granulation of graph data
function declare_views2() {
	$views2['time']['before']="Time";
	$views2['time']['after']="s";
	$views2['time']['maxval']="0";
	if (!empty($_SESSION['timewindow'])) $views2['time']['maxval']=$_SESSION['timewindow'];
	$views2['durat']['before']="Duration";
	$views2['durat']['after']="ms";
	$views2['durat']['maxval']="60000";
	$views2['pkts']['before']="Packets";
	$views2['pkts']['after']="";
	$views2['pkts']['maxval']="1000";
	$views2['bytes']['before']="Bytes";
	$views2['bytes']['after']="";
	$views2['bytes']['maxval']="10000";
	return $views2;
}

# Shows filter menu in correlation table and graph.
function show_filter_menu() {
	global $views, $views2, $filter, $filter_records_num;
        $fexists = "";
        if (!empty($_SESSION['filter_node1'])) $fexists = $_SESSION['filter_node1'];
        $i = 0;
        while ($fexists != "") {
                $finfo = "";
                $fn;
                if (!empty($_SESSION[('filter_node' . ($i+1))])) $fn = $_SESSION[('filter_node' . ($i+1))];
                        $finfo = $filter_records_num[('filter' . $i)] . " flow";
			if ($filter_records_num[('filter' . $i)] > 1) $finfo .= "s";
			$finfo .= ", Aggregated data: ";
                        foreach ($views as $key => $value) {
                                if ($_SESSION[('agr' . $i)][$key]==1) {
                                        if (!empty($_SESSION[('filter_node' . ($i+1))])) {
                                                $finfo .= $value .  " (" . return_flow_info($fn, $key, $i) . ") &nbsp;";
                                        } else {
                                                $finfo .= $value . " &nbsp;";
                                        }
                                }
                        }
                        foreach ($views2 as $key => $value) {
                                if ($_SESSION[('agr' . $i)][$key]>0) {
                                        if (!empty($_SESSION[('filter_node' . ($i+1))])) {
                                                $finfo .= $value['before'] .  " (" . return_flow_info($fn, $key, $i) . $value['after'] . ") &nbsp;";
                                        } else {
                                                $finfo .= $value['before'] . " (" . $_SESSION[('agr' . $i)][$key] . $value['after'] . ") &nbsp;";
                                        }
                                }
                        }
                if ($i == 0) print "&nbsp;";
                if ($filter == $i) print "<b>";
                print "<a class=\"lred\" href=\"?filter=";
                if ($i == 0) print "-1"; else print $i;
                print "&amp;redirect=1\" title=\"" . $finfo . "\">";
                if ($i==0) { print "No filter"; } else { print "Filter nr. " . $i; }
                if ($filter == $i) print "</b>";
                print "</a>";
                $i++;
                if (!empty($_SESSION[('filter_node' . $i)])) {
                        $fexists = $_SESSION[('filter_node' . $i)];
                } else {
                        $fexists = "";
                }
                if ($fexists != "") print " > ";
        }
}

# Set an edge width
function c_penwidth($flows, $allflows) {
        $maxpen = 20.0;
        $minpen = 0.5;
        $pen = round(($maxpen / ($allflows / $flows)), 1);
        if ($pen < $minpen) $pen = $minpen;
        return $pen;
}

# Returns a color from a number from colors.php file
function get_color($color_num) {
        global $colors;
        $colors_num = count($colors);
        if ($color_num >= $colors_num) $color_num %= $colors_num;
        return $colors[$color_num];
}

# Comprimes a simple number to the number + binary prefix
function c_value($int) {
        $prefix['0']="";
        $prefix['1']="K";
        $prefix['2']="M";
        $prefix['3']="G";
        $prefix['4']="T";
        $prefix['5']="P";
        $prefix['6']="E";
        $prefix['7']="Z";
        $prefix['8']="Y";
        $index=0;
        $int2 = $int;
        while (($int2 / 1000) > 1) {
                $index++;
                $int2 = round($int2/1000);
        }
        return $int2 . $prefix[$index];
}

# Transfers human date and time format to seconds.
function humanToTime($date) {
        $tmp = preg_split("/\\./", $date); // preg_split("\\.", $date);
	if (count($tmp) == 1) $tmp = preg_split("/\\ /", $date);
        $tmp1 = preg_split("/\\//", $tmp[0]);
        $tmp2 = preg_split("/\\:/", $tmp[1]);
        return mktime($tmp2[0], $tmp2[1], $tmp2[2], $tmp1[1], $tmp1[2], $tmp1[0]);
}

# Transfers human date and time format to miliseconds.
function humanToTime2($date) {
        $tmp = preg_split("/\\./", $date);
        $tmp1 = preg_split("/\\//", $tmp[0]);
        $tmp2 = preg_split("/\\:/", $tmp[1]);
        return ((mktime($tmp2[0], $tmp2[1], $tmp2[2], $tmp1[1], $tmp1[2], $tmp1[0]) * 1000) + $tmp[2]);
}

# Removes a dot from human date and time format.
function removeDot($date) {
	$tmp = preg_split("/\\./", $date);
	if ((count($tmp)) < 3) return $date;
	return $tmp[0] . " " . $tmp[1] . "." . $tmp[2];
}

# Inserts a dot to human date and time format.
function insertDot($date) {
	$tmp = preg_split( "/\\ /", $date); //preg_split("\\ ", $date);
	return $tmp[0] . "." . $tmp[1];
}

# Transfers a time in miliseconds to human readable format.
function timeToHuman2($time) {
	if ($time == 0) return "no time";
	$ret = date('Y/m/d.H:i:s' , floor($time / 1000)) . ".";
	$tmp = substr(($time . ""), strlen($time) - 3);
	return $ret . $tmp;	
}

# Cut the same values which are present in both times from the 2nd time so it shorts the output of the whole interval time1-time2.
function cutHumanTime($time1, $time2) {
	if ($time1 == "no time") $time1 = "";
	if ($time2 == "no time") $time2 = "";
	if (($time1 == "") || ($time2 == "")) return "no time";
	$tmpdata1 = preg_split("/\\./", $time1);
	$tmpdata2 = preg_split("/\\./", $time2);
	$tmpdata11 = preg_split("/\\//", $tmpdata1[0]);
	$tmpdata21 = preg_split("/\\//", $tmpdata2[0]);
	if ($tmpdata11[0] != $tmpdata21[0]) return removeDot($time2);
	if ($tmpdata11[1] != $tmpdata21[1]) return $tmpdata21[1] . "/" . $tmpdata21[2] . " " . $tmpdata2[1] . "." . $tmpdata2[2];
	if ($tmpdata11[2] != $tmpdata21[2]) return $tmpdata21[2] . " " . $tmpdata2[1] . "." . $tmpdata2[2];
	$tmpdata12 = preg_split("/\\:/", $tmpdata1[1]);
	$tmpdata22 = preg_split("/\\:/", $tmpdata2[1]);
	if ($tmpdata12[0] != $tmpdata22[0]) return $tmpdata2[1] . " " . $tmpdata2[2];
	if ($tmpdata12[1] != $tmpdata22[1]) return $tmpdata22[1] . ":" . $tmpdata22[2] . "." . $tmpdata2[2];
	if ($tmpdata12[2] != $tmpdata22[2]) return $tmpdata22[2] . "." . $tmpdata2[2];
	return $tmpdata2[2];
}

# Retuns an IPv4 address class.
function return_class($ip) {
        if (strpos($ip, ":") != "") return "IPv6";
        $tmpdata = preg_split("/\\./", $ip);
        if ($tmpdata[0] < 128) return "A";
        if ($tmpdata[0] < 192) return "B";
        if ($tmpdata[0] < 224) return "C";
        if ($tmpdata[0] < 240) return "D";
        return "E";
}

# Returns information about each flow.
function return_flow_info($flownr, $type, $depth) {
        global $records, $protocols;
        $ret = "unknown";
        $curflow = preg_split("/\\|/", $records[($flownr - 1)], 11);
        if ($type == "channels") {
                $ret = "ALL->";
                $tmp = preg_split("/\\|/", $curflow[10]);
                for ($i=3; $i <= count($tmp); $i) {
                        $ret .= $tmp[$i-1] . "->";
                        $i += 3;
                }
                $ret .= "ALL";
        }
        if ($type == "srcip") $ret = $curflow[3];
        if ($type == "srcport") $ret = $curflow[4];
        if ($type == "dstip") $ret = $curflow[5];
        if ($type == "dstport") $ret = $curflow[6];
        if ($type == "proto") $ret = $protocols[$curflow[7]];
        if ($type == "ipproto") {
                if (strpos($curflow[3], ":") != "") $ret = "IPv6"; else $ret = "IPv4";
        }
        if ($type == "srcclass") $ret = return_class($curflow[3]);
        if ($type == "dstclass") $ret = return_class($curflow[5]);
        if ($type == "durat") $ret = $curflow[2];
        if ($type == "pkts") $ret = $curflow[8];
        if ($type == "bytes") $ret = $curflow[9];
        if ($type == "time") {
                $ret = $curflow[1];
                $tmp = $_SESSION[('agr' . $depth)];
                $ret = date('Y/m/d.H:i:s', (humanToTime($ret) - (humanToTime($ret) % $tmp['time']))) . "+" . $tmp['time'];
        }
        if (($type == "durat") || ($type == "pkts") || ($type == "bytes")) {
                $tmp = $_SESSION[('agr' . $depth)];
                $dc = floor($ret / $tmp[$type]);
                $ret = ($dc * $tmp[$type]) . "-" . ((($dc + 1) * $tmp[$type]) - 1);
        }
        return $ret;
}

# Write a definiton text to a file and generates an appropriate graph for the definiton file.
function write_graph($text, $myfile, $type) {
        # delete old files
        if (file_exists($myfile . "." . $type)) { unlink($myfile . "." . $type); }
        $fh = fopen($myfile . ".dot", 'w');
        fwrite($fh, $text);
        fclose($fh);
        # use dot rendering engine (neato is possible too)
        exec("dot -T" . $type . " " . $myfile . ".dot > " . $myfile . "." . $type);
	unlink($myfile . ".dot");
}

?>

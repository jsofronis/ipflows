<?php
# version 1.0.0.1 (build date 2019-02-01 00:45:00)

#
## help.php - IP flows correlation and visualization plugin
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

# Help texts.

# Top - header texts.
$help1['timefrom'] =	"Start time";
$help1['timeto'] = 	"End time";
$help1['maxflows'] =	"Maximum flows";
$help1['filter_name'] =	"Filter name";
$help1['srcip'] =	"Source IP";
$help1['srcmask'] =	"Source mask";
$help1['srcport'] =	"Source port";
$help1['filter_list'] =	"The filters list";
$help1['dstip'] =	"Destination IP";
$help1['dstmask'] = 	"Destination mask";
$help1['dstport'] =	"Destination port";
$help1['proto'] =	"Protocol type";
$help1['ipproto'] =	"IP protocol version";
$help1['processing'] =	"Type of processing";
$help1['showflows'] =	"Show flows";
$help1['filter_save'] =	"Save the filter";
$help1['filter_delete']="Delete the filter";
$help1['submit_button']="Submit the input values";
$help1['cor_table'] =	"Correlation table";
$help1['cor_graph'] =	"Correlation graph";
$help1['tot_flows'] =	"Total flows";
$help1['tb_nr'] =	"Flow number";
$help1['tb_time'] =	"Flow time";
$help1['tb_durat'] =	"Flow duration";
$help1['tb_src'] =	"Source";
$help1['tb_dst'] =	"Destination";
$help1['tb_prot'] =	"Protocol";
$help1['tb_pkts'] =	"Packets";
$help1['tb_bytes'] =	"Bytes";
$help1['tb_channel'] =	"Channel name";
$help1['gr_nodes'] =	"Maximum nodes";
$help1['gr_zoom'] =	"Image zoom";
$help1['gr_agg'] =	"Aggregation and granularity";
$help1['gr_srcip'] =	"Source IP aggregation";
$help1['gr_dstip'] =	"Destination IP aggregation";
$help1['gr_srcport'] =	"Source port aggregation";
$help1['gr_dstport'] =	"Destination port aggregation";
$help1['gr_proto'] =	"Protocol aggregation";
$help1['gr_srcclass'] =	"Source IP class aggregation";
$help1['gr_dstclass'] =	"Destination IP class aggregation";
$help1['gr_ipproto'] =	"IP protocol version aggregation";
$help1['gr_channels'] =	"Channel aggregation";
$help1['gr_time'] =	"Time granularity";
$help1['gr_durat'] =	"Duration granularity";
$help1['gr_pkts'] =	"Packets granularity";
$help1['gr_bytes'] =	"Bytes granularity";
$help1['gr_allp'] =	"Check all previous aggregations";
$help1['gr_def'] =	"Check default aggregations";
$help1['gr_none'] =	"Clear aggregations";
$help1['gr_flows'] =	"Total flows";
$help1['gr_nodes'] =	"Total nodes";
$help1['gr_rewrite'] =	"Rewrite graph";
$help1['cor_filter'] =	"Used filter(s)";

# Center texts.
$help2['timefrom'] =	"Insert a start time value of an interval used for the correlation.";
$help2['timeto'] =	"Insert an end time value of an interval used for the correlation.";
$help2['maxflows'] =	"The number of maximum flows which can be shown in a table.";
$help2['filter_name'] =	"Save the inserted filter under this name.";
$help2['srcip'] =	"Source IP or network for filtered flows in the result.";
$help2['srcmask'] =	"Mask of source network for filtered flows. Use 32 or 128 or let it empty for a single IP.";
$help2['srcport'] =	"Source port for filtered flows in the result.";
$help2['filter_list'] =	"The list of saved filters. Click on the filter name to load the filter.";
$help2['dstip'] =	"Destination IP or network for filtered flows in the result.";
$help2['dstmask'] =	"Mask of source network for filtered flows. Use 32 or 128 or let it empty for a single IP.";
$help2['dstport'] =	"Destination port for filtered flows in the result.";
$help2['proto'] =	"The type of IP protocol for filtered flows. ";
$help2['ipproto'] =	"The version of IP protocol for filtered flows.";
$help2['processing'] =	"Use <b>fast</b> processing in ordinary NfSen configurations. The <b>strict</b> processing forces <i>nfdump</i> to go through all saved files on the disc - it could take too long!";
$help2['showflows'] =	"This option chooses which flows to show. <b>single</b> - show only flows present in a single channel, <b>multi</b> - show only flows present in more than one channel, <b>all</b> - show all flows";
$help2['filter_save'] =	"Save the current filter with the inserted filter name.";
$help2['filter_delete']="Delete the chosen filter.";
$help2['submit_button']="The filter with inserted values will be sent to a backend and <i>nfdump</i> will find appropriate flows in the NfSen database and the output table will be shown.";
$help2['cor_table']	= "Correlation table consists of a list of all flows which fit the inserted filter(s). The flows are sorted by time from the first to the last.";
$help2['cor_graph']	= "Correlation graph consists of a graphic representation of all flows which fit the inserted filter(s).";
$help2['tot_flows']	= "The number of all flows which fit the inserted filter(s) so it is a number of all flows in the table. If the number is higher than the maximum flows number the flows are divided into pages.";
$help2['tb_nr']		= "The number of every caught flow.";
$help2['tb_time']	= "The time of very first moment when every flow has been seen.";
$help2['tb_durat']	= "The total time when was every flow present on all channels. (the time of the last occurence minus the time of the first occurence)";
$help2['tb_src']	= "The source IP and port of every flow.";
$help2['tb_dst']	= "The destination IP and port of every flow.";
$help2['tb_prot']	= "The type of an IP protocol of every flow.";
$help2['tb_pkts']	= "The number of packets present in every flow.";
$help2['tb_bytes']	= "The number of bytes present in every flow.";
$help2['tb_channel']	= "If the flow is not present on the current channel the field is red, the field is green otherwise with the number of time occurence of the flow on the channel. You can hold on mouse on the number to see the time and durration of every flow on the channel.";
$help2['gr_nodes'] =    "The number how many nodes could be present in the correlation graph. It does not count channel nodes. If there is more nodes present the correlation graph is divided into more pages.";
$help2['gr_zoom'] =     "The size of the correlation graph in percentages.";
$help2['gr_agg'] =	"<b>Aggregation</b> means that flows are joined into sets when they have some parameters same (depends on the type of the parameter, e.g. the type of the aggregation).<br><b>Granularity</b> means that flows are joined into sets when they have some parameters inside the same granulation interval (depends on the type of the parameter and its value).<br>The aggregations and granularities can be joined together which means there will be usually more sets available (i.e. all flows inside one set have to have all its parameters same).";
$help2['gr_srcip'] =    "This option will aggregate all flows with the same source IP address into a single set. (i.e. there will be as many sets available as many flows with different source IPs are present in the main set)";
$help2['gr_dstip'] =    "This option will aggregate all flows with the same destination IP address into a single set. (i.e. there will be as many sets available as many flows with different destination IPs are present in the main set)";
$help2['gr_srcport'] =  "This option will aggregate all flows with the same source port into a single set. (i.e. there will be as many sets available as many flows with different source IPs are present in the main set)";
$help2['gr_dstport'] =  "This option will aggregate all flows with the same destination port into a single set. (i.e. there will be as many sets available as many flows with different destination IPs are present in the main set)";
$help2['gr_proto'] =    "This option will aggregate all flows with the same IP protocol into a single set. (i.e. there will be as many sets available as many flows with different IP protocol are present in the main set)";
$help2['gr_srcclass'] = "This option will aggregate all flows with the same source IPv4 class into a single set. (i.e. there will be as many sets available as many flows with different source IPv4 classes are present in the main set)";
$help2['gr_dstclass'] = "This option will aggregate all flows with the same destination IPv4 class into a single set. (i.e. there will be as many sets available as many flows with different destination IPv4 classes are present in the main set)";
$help2['gr_ipproto'] =  "This option will aggregate all flows with the same IP protocol version into a single set. (i.e. there will be as many sets available as many flows with different IP protocol versions are present in the main set)";
$help2['gr_channels'] = "This option will aggregate all flows which go through same channels on the network in the same order. (i.e. there will be as many sets available as many different possible options for all IP flows paths in the whole monitored network)";
$help2['gr_time'] =     "This option granulates the main set into time subsets for every <interval> seconds. (i.e. time the interval from the main filter will be divided into smaller time intervals based on the <interval> time and all ip flows will be sorted into these subsets depends on the flows first catch time)";
$help2['gr_durat'] =    "This option granulates the main set of IP flows into subsets depends on the duration of every flow. (i.e. the <interval> value creates subsets with these interval durations and all flows will be sorted to these subsets based on each flow duration - actually, interval value 1 represents an aggragation)";
$help2['gr_pkts'] =     "This option granulates the main set of IP flows into subsets depends on the number of packets in every flow. (i.e. the <interval> value creates subsets with these interval packet numbers and all flows will be sorted to these subsets based on every flow packets - actually, interval value 1 represents an aggragation)";
$help2['gr_bytes'] =    "This option granulates the main set of IP flows into subsets depends on the number of bytes in every flow. (i.e. the <interval> value creates subsets with these interval byte numbers and all flows will be sorted to these subsets based on every flow bytes - actually, interval value 1 represents an aggragation)";
$help2['gr_allp'] =     "This link will check and fill up all aggregation and granularity parameters which have been checked/filled before in all previous correlation graphs before this last graph.";
$help2['gr_def'] =      "This link will check the default aggregation and granularity options which used to be set in the current graph render.";
$help2['gr_none'] =     "This link will erase all inserted aggregation and granularity options.";
$help2['gr_flows'] =    "How many flows are in the whole correlation graph.";
$help2['gr_nodes'] =    "How many nodes are shown in the correlation graph. If there is more nodes for correlation graph available than the max_nodes value is, there will be more pages (graphs) available.";
$help2['gr_rewrite'] =  "This button will rewrite the correlation graph with the new correlation and granularity parameters.";
$help2['cor_filter'] =	"This is the list of all used filter(s). The <b>bold</b> value means the active one. You can hold the mouse on the filter name and it shows you the current filter preferences and aggregation/granularity values.";

# Bottom texts
$help3['timefrom'] =	"Expected value: YYYY/MM/DD HH:MM:SS";
$help3['timeto'] =	"Expected value: YYYY/MM/DD HH:MM:SS";
$help3['maxflows'] =	"Expected value: 10-9999";
$help3['filter_name'] =	"Expected value: filter name";
$help3['srcip'] =	"Expected value: an IP in IPv4 or IPv6 format";
$help3['srcmask'] =	"Expected value: 0-32 for IPv4 or 0-128 for IPv6";
$help3['srcport'] =	"Expected value: 0-65535";
$help3['filter_list'] =	"&nbsp;";
$help3['dstip'] =	"Expected value: an IP in IPv4 or IPv6 format";
$help3['dstmask'] =	"Expected value: 0-32 for IPv4 or 0-128 for IPv6";
$help3['dstport'] =	"Expected value: 0-65535";
$help3['proto'] =	"&nbsp;";
$help3['ipproto'] =	"&nbsp;";
$help3['processing'] =	"Expected value: fast";
$help3['showflows'] =	"Expected value: all";
$help3['filter_save'] =	"&nbsp;";
$help3['filter_delete']="&nbsp;";
$help3['submit_button']="&nbsp;";
$help3['cor_table']	= "&nbsp;";
$help3['cor_graph']	= "&nbsp;";
$help3['tot_flows']	= "&nbsp;";
$help3['tb_nr']		= "&nbsp;";
$help3['tb_time']	= "&nbsp;";
$help3['tb_durat']	= "&nbsp;";
$help3['tb_src']	= "&nbsp;";
$help3['tb_dst']	= "&nbsp;";
$help3['tb_prot']	= "&nbsp;";
$help3['tb_pkts']	= "&nbsp;";
$help3['tb_bytes']	= "&nbsp;";
$help3['tb_channel']	= "&nbsp;";
$help3['gr_nodes'] =    "Expected value: 10-9999 (default = 2*max_flows)";
$help3['gr_zoom'] =     "Expected value: 10-999% (default 150%)";
$help3['gr_agg'] =	"&nbsp;";
$help3['gr_srcip'] =    "&nbsp;";
$help3['gr_dstip'] =    "&nbsp;";
$help3['gr_srcport'] =  "&nbsp;";
$help3['gr_dstport'] =  "&nbsp;";
$help3['gr_proto'] =    "&nbsp;";
$help3['gr_srcclass'] = "&nbsp;";
$help3['gr_dstclass'] = "&nbsp;";
$help3['gr_ipproto'] =  "&nbsp;";
$help3['gr_channels'] = "Default aggregation";
$help3['gr_time'] =     "Expected value: 0-(input interval size)s";
$help3['gr_durat'] =    "Expected value: 0-60000ms";
$help3['gr_pkts'] =     "Expected value: 0-1000";
$help3['gr_bytes'] =    "Expected value: 0-10000";
$help3['gr_allp'] =     "&nbsp;";
$help3['gr_def'] =      "&nbsp;";
$help3['gr_none'] =     "&nbsp;";
$help3['gr_flows'] =    "&nbsp;";
$help3['gr_nodes'] =    "&nbsp;";
$help3['gr_rewrite'] =  "&nbsp;";
$help3['cor_filter'] =	"&nbsp;";

# Returns a help tooltip.
function show_help($content, $mark) {
	global $help1, $help2, $help3;
	if (empty($mark)) $mark = "?";
	print	"<a href=\"#\" class=\"tt\">" . $mark . "<span class=\"tooltip\">";
	print "<span class=\"top\">" . $help1[$content] . "</span>";
	print "<span class=\"middle\">" . $help2[$content] . "</span>";
	print "<span class=\"bottom\">" . $help3[$content] . "</span>";
	print "</span></a>";

}

?>


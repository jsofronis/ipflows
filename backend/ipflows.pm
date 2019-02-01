#!/usr/bin/perl 
# version 1.0.0.1 (build date 2019-02-01 00:45:00)

#
## ipflows.pm - IP flows correlation and visualization plugin
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

# Name of the plugin
package ipflows;

# Use these libraries
use lib qw (/data/nfsen/libexec); 
use NfProfile;
use NfConf;
use Time::Local;
use Sys::Syslog;
Sys::Syslog::setlogsock ('unix');
use POSIX;
# highly recommended for good style Perl programming
use strict;
use warnings;

# communication with frontend plugin
our % cmd_lookup = (
    'try' => \&RunProc,
	'getvars' => \&GetVars,
);

# This string identifies the plugin as a version 1.3.0 plugin.
our $VERSION = 130;
# Basic variables
my ($nfdump, $PROFILEDIR, $BACKEND_PLUGINDIR, $FRONTEND_PLUGINDIR);
# Log file name stored in backend plugin dir
my $logfile = "ipflows.log";
# Maximum processed flows through nfdump (this value should be set in nfsen.conf)
my $max_lines = 30000;
# Maximum time difference within the same flow on the network among various channels (this value should be set in nfsen.conf)
my $time_difference = 3000;

# Function for getting variables from frontend
sub GetVars
{
    my  $socket = shift;
    my  $opts = shift;
    my $conf = $NfConf::PluginConf{ipflows};
    my $www_dir = $$conf{'www_dir'};
	my $www_dir2 = $www_dir;
	if (chop($www_dir2) eq "/") { $www_dir = substr($www_dir, 0, -1); }
	my $frontend_tmp_dir = "$FRONTEND_PLUGINDIR/ipflows/tmp";
	my %args = ("www_dir" , $www_dir, "frontend_tmp_dir", $frontend_tmp_dir);
    Nfcomm::socket_send_ok ($socket, \%args);
}

# The main function
sub RunProc
{
    my  $socket = shift;
    my  $opts = shift;

	# get input values from frontend
    my $time_from = $$opts{'timefrom'};
    my $time_to = $$opts{'timeto'};
	my $max_flows = $$opts{'maxflows'};
    my $src_ip = $$opts{'srcip'};
    my $src_mask = $$opts{'srcmask'};
    my $src_port = $$opts{'srcport'};
    my $dst_ip = $$opts{'dstip'};
    my $dst_mask = $$opts{'dstmask'};
    my $dst_port = $$opts{'dstport'};
	my $proto = $$opts{'proto'};
	my $ipproto = $$opts{'ipproto'};
	my $processing = $$opts{'processing'};
	my $showflows = $$opts{'showflows'};

    my $current_profile = $$opts{'profile'};
    my $current_profilegroup = $$opts{'profilegroup'};

	# create filter
	my $filter = "'";
	if ($ipproto eq "IPv4") { $filter = $filter . "inet"; }
	if ($ipproto eq "IPv6") { $filter = $filter . "inet6"; }
	if ($src_ip ne "") {
        if ($filter ne "'") { $filter = $filter . " and "; }
		if ($src_mask eq "") {
        	$filter = $filter . "src ip $src_ip";
		} else {
			$filter = $filter . "src net $src_ip/$src_mask";
		}
	}
    if ($dst_ip ne "") {
        if ($filter ne "'") { $filter = $filter . " and "; }
        if ($dst_mask eq "") {
            $filter = $filter . "dst ip $dst_ip";
        } else {
            $filter = $filter . "dst net $dst_ip/$dst_mask";
        }
    }
	if ($src_port ne "") {
        if ($filter ne "'") { $filter = $filter . " and "; }
		$filter = $filter . "src port $src_port";
	}
    if ($dst_port ne "") {
        if ($filter ne "'") { $filter = $filter . " and "; }
        $filter = $filter . "dst port $dst_port";
    }
	if ($proto ne "any") {
		if ($filter ne "'") { $filter = $filter . " and "; }
		$filter = $filter . "proto $proto";
	}
	$filter = $filter . "'";
	my $element;
    my @results = ();
	my $results_count = 0;
	my $total_results_count = 0;
                         
	my %profileinfo = NfProfile::ReadProfile($current_profile, $current_profilegroup);
    my $profilepath = NfProfile::ProfilePath($current_profile, $current_profilegroup);
	my $datadir = "$PROFILEDIR/$profilepath";
	if ( $profileinfo{'status'} eq 'empty' ) {
		print STDERR "Error reading profile $profileinfo{'name'}";
		print STDERR "\n";
		return;
	}

##    my @channels = NfProfile::ProfileChannels(\%profileinfo);
	my @channels = keys %{$profileinfo{'channel'}};

	# save log records to a logfile
#    my $now = time();
#	open (MYFILE, ">>$BACKEND_PLUGINDIR/$logfile");
#	printf MYFILE "Time of the request: " . int2time($now) . "\n";
#	printf MYFILE "Profile: " . $current_profile . "\n";
#	printf MYFILE "Profile group: " . $current_profilegroup . "\n";
#	printf MYFILE "Processed channels: " . join (':', @channels) . "\n";
#	printf MYFILE "Requested time period: " . $time_from . " - " . $time_to . "\n";
#	printf MYFILE "Current filter: \"" . $filter . "\"\n";
#	printf MYFILE "Processing time (" . $processing . "): ";
#	close (MYFILE);

	my %data  = ();
	foreach (@channels) {
		my $current_channel = $_;
		my ($command, $output);
		# check and edit input values
		if (time2int($time_from) < $profileinfo{'tstart'}) {
			$time_from = int2time($profileinfo{'tstart'});
		}
		if (time2int($time_to) > $profileinfo{'tend'}) {
			$time_to = int2time($profileinfo{'tend'});
		}
        if (time2int($time_to) < $profileinfo{'tstart'}) {
            $time_to = int2time($profileinfo{'tstart'});
        }
        if (time2int($time_from) > $profileinfo{'tend'}) {
            $time_from = int2time($profileinfo{'tend'});
        }
		if ($processing ne "fast") {
			$command = "$nfdump -M $datadir/$current_channel -R . -t $time_from-$time_to -c $max_lines -o pipe -m $filter -N -q";
		} else {
			my $tmpfrom = time2file($time_from);
			my $tmpto = time2file($time_to);
			$command = "$nfdump -M $datadir/$current_channel -R $tmpfrom\:$tmpto -t $time_from-$time_to -c $max_lines -o pipe -m $filter -N -q";
		}
##		printf MYFILE "\n" . $command; 
		$output = `$command`;
		my @lines = split("\n", $output, 0);
		foreach (@lines) {
			my $line = $_;
			if (index($line, "|") == -1) {
				syslog("info", "ipflows: I have found a problem during processing stored data through nfdump. Please check your nfsen configuration/data:\n");
				syslog("info", "     " . $line . "\n");
#			    open (MYFILE, ">>$BACKEND_PLUGINDIR/$logfile");
#				printf MYFILE "I have found a problem during processing stored data through nfdump. Please check your nfsen configuration/data:\n";
#				printf MYFILE "     " . $line . "\n";
#				close (MYFILE);
#				next;
			}
			my @tmp = split("\\|", $line, 0);
			my $hashkey = floor(($tmp[1] * 1000 + $tmp[2]) / $time_difference) . "|" .
			 $tmp[5] . "|" .
			 $tmp[6] . "|" .
             $tmp[7] . "|" . 
             $tmp[8] . "|" . 
			 $tmp[9] . "|" .
			 $tmp[10] . "|" .
             $tmp[11] . "|" . 
             $tmp[12] . "|" . 
             $tmp[13] . "|" . 
			 $tmp[14] . "|" .
			 $tmp[15] . "|" .
			 $tmp[22] . "|" .
			 $tmp[23];
			my $starttime = ($tmp[1] * 1000 + $tmp[2]);
            my $endtime = ($tmp[3] * 1000 + $tmp[4]);
			my $hashdata = $starttime . "!" . $endtime . "!" . $current_channel;
			if ( exists $data{$hashkey} ) {
				$data{$hashkey} = $data{$hashkey} . "|" . $hashdata;
			} else {
				$data{$hashkey} = $hashdata;
				$total_results_count++;
			}
		}
	}

	my %sorted_data;
	foreach $element (keys %data) {
		my @tmpchannels = split("\\|", $data{$element});
		my %tmpdata;
		foreach (@tmpchannels) {
			my @tmpdata2 = split("\\!", $_);
			$tmpdata{$tmpdata2[0]} = $tmpdata2[0] . "|" . $tmpdata2[1] . "|" . $tmpdata2[2];
		}
		my $starttime = 0;
		my $endtime = 0;
		my $channels = "";
		my $value;
		my $channelscount = 0;
		foreach $value (sort keys %tmpdata) {
			$channelscount++;
			if ($starttime == 0) { $starttime = $value; }
			my @tmpdata2 = split("\\|", $tmpdata{$value});
			if ($endtime < $tmpdata2[1]) { $endtime = $tmpdata2[1]; }
			$channels = $channels . "|" . int2time2((floor($tmpdata2[0]/1000)), ($tmpdata2[0] % 1000)) .
									"|" . ($tmpdata2[1] - $tmpdata2[0]) . "|" . $tmpdata2[2];
		}
		my $duration = $endtime - $starttime;
		$sorted_data{$starttime . "|" . $duration . "|" . $element} = $channelscount . $channels; 
#
}
#each line format:  0:??? | 1:time_begin | 2:ms_begin | 3:time_end | 4:ms_end | 5:proto | 6:src_ip1 | 7:src_ip2 | 8:src_ip3 | 9:src_ip4(IPv4) | 10:src_port | 11:dst_ip1 | 12:dst_ip2 |
#                   13:dst_ip3 | 14: dst_ip4(IPv4) | 15: dst_port | 16: ??? | 17: ??? | 18: ??? | 19: ??? | 20: flag | 21: ??? | 22: packets | 23: bytes
#hashkey format:	0:time_begin/time_difference | 1:proto | 2:src_ip1 | 3:src_ip2 | 4:src_ip3 | 5:src_ip4(IPv4) | 6:src_port | 7:dst_ip1 | 8:dst_ip2 | 9:dst_ip3 | 10:dst_ip4(IPv4) |
#					11:dst_port | 12:packets | 13:bytes
#hashdata format:	0:time_begin ! 1:time_end ! 3:channel | 4:time_begin2 ! 5:time_end2 ! 6:channel2 | ...
#sortedkey format:	0:time_begin | 1:duration | hashkey format
#sorteddata format:	0:channels_count | hashdata format
#output format:		0:number | 1:time_begin | 2:duration | 3:src_ip | 4:src_port | 5:dst_ip | 6:dstport | 7:proto | 8:packets | 9:bytes | 10:channels
#channels format:	0:time_begin | 1:duration | 2:channel | ...

	# Formating results for sending them to a frontend plugin
	foreach $element (sort keys %sorted_data) {
		my @tmparray = split("\\|", $element . "|" . $sorted_data{$element}, 18);
		my $starttime = $tmparray[0];
		$starttime = int2time2(floor($starttime/1000), ($starttime % 1000));
		my $duration = $tmparray[1];
		my $proto = $tmparray[3];
		my $srcip = "";
		if (($tmparray[4] != 0) || ($tmparray[5] != 0) || ($tmparray[6] != 0)) {
			$srcip = "[" . dec2ipv6($tmparray[4], $tmparray[5], $tmparray[6], $tmparray[7]) . "]";
		} else {
			$srcip = $tmparray[7];
			$srcip = dec2dot($srcip);
		}
		my $srcport = $tmparray[8];
		my $dstip = "";
		if (($tmparray[9] != 0) || ($tmparray[10] != 0) || ($tmparray[11] != 0)) {
			$dstip = "[" . dec2ipv6($tmparray[9], $tmparray[10], $tmparray[11], $tmparray[12]) . "]";
		} else  {
			$dstip = $tmparray[12];
			$dstip = dec2dot($dstip);
		}
		my $dstport = $tmparray[13];
		my $packets = $tmparray[14];
		my $bytes = $tmparray[15];
		my $channelscount = $tmparray[16];
		my $channels = $tmparray[17];
		if (($showflows eq "all") || (($showflows eq "multi") && ($channelscount>1)) || (($showflows eq "single") && ($channelscount == 1))) {
            $results_count++;
			push (@results, $results_count . "|" . $starttime . "|" . $duration . "|" . $srcip . "|" . $srcport . "|" . $dstip . "|" . $dstport . "|" . $proto . "|" . $packets . "|" . $bytes . "|" . $channels);
		}
	}
#	if(scalar(@results) == 0) {push (@results, "NO RESULT");}
	my % args;
	$args{'records'} = \@results;

	# save log records to a logfile
#	open (MYFILE, ">>$BACKEND_PLUGINDIR/$logfile");
#	printf MYFILE (time() - $now) . " seconds\n";
#	printf MYFILE "Results: " . $total_results_count . " (" . $results_count . " shown)\n";
#	printf MYFILE "========================================\n";
#	close (MYFILE);

	# Sending results to frontend
	Nfcomm::socket_send_ok ($socket, \%args);
}

# Function for searching correct nfcapd file for inserted time
sub time2file {
	my $time = time2int($_[0]);
	$time = ($time - ($time % 300) + 300);
	$time = int2time($time);
	my @tmp1 = split("\\.", $time, 0);
	my @tmp2 = split("\\/", $tmp1[0], 0);
	my $result = $tmp2[0] . "/" . $tmp2[1] . "/" . $tmp2[2] . "/nfcapd." . $tmp2[0] . $tmp2[1] . $tmp2[2];
    @tmp2 = split("\\:", $tmp1[1], 0);
    $result = $result . $tmp2[0] . $tmp2[1];
	return $result;
}

# Fuction for converting time in YYYY:MM:DD.HH:MM:SS to int format
sub time2int {
	my @tmp1 = split("\\.", $_[0], 0);
	my @tmp2 = split("\\/", $tmp1[0], 0);
	my $year = $tmp2[0];
	if ($year > 2069) {$year = 2069;}
	if ($year < 1970) {$year = 1970;}
	my $month = $tmp2[1] - 1;
	if ($month > 11) {$month = 11;}
	my $day = $tmp2[2];
	if (($month == 0 || $month == 2 || $month == 4 || $month == 6 || $month == 7 || $month == 9 || $month == 11) && ($day > 31)) {$day = 31;}
	if (($month == 3 || $month == 5 || $month == 8 || $month == 10) && ($day > 30)) {$day = 30;}
	if (($month == 1 && ($year % 4) == 0) && ($day > 29)) {$day = 29;}
	if (($month == 1 && ($year % 4) > 0) && ($day > 28)) {$day = 28;}
	@tmp2 = split("\\:", $tmp1[1], 0);
	my $hour = $tmp2[0];
	if ($hour > 23) {$hour = 23;}
	my $min = $tmp2[1];
	if ($min > 59) {$min = 59;}
	my $sec = $tmp2[2];
	if ($sec > 59) {$sec = 59;}
	return timelocal($sec, $min, $hour, $day, $month, $year);
}

# Function for converting time from int to YYYY:MM:DD.HH:MM:SS format
sub int2time {
	return POSIX::strftime("%Y/%m/%d.%H:%M:%S", localtime($_[0]));		
}

# Function for converting time from int to YYY:MM:DD.HH:MM:SS.sss format
sub int2time2 {
    my $ret = POSIX::strftime("%Y/%m/%d.%H:%M:%S", localtime($_[0])) . ".";
	if ($_[1] < 10)		{ $ret .= "0"; }
	if ($_[1] < 100)	{ $ret .= "0"; }
	$ret .= $_[1];
	return $ret;
}

# Function for converting an IPv4 address from 32bit number to a dotted format
sub dec2dot {
	my $address = $_[0];
	my $d = $address % 256; $address -= $d; $address /= 256;
	my $c = $address % 256; $address -= $c; $address /= 256;
	my $b = $address % 256; $address -= $b; $address /= 256;
	my $a = $address;
	my $dotted="$a.$b.$c.$d";
	return $dotted;
}

# Function for converting an IPv6 address from 128bit number (4x 32bit numbers) to a dotted format
sub dec2ipv6 {
    my $res = "";
    my $tmp;
    for (my $i = 0; $i <4; $i++) {
        $tmp = sprintf("%x", $_[$i]);
        if (length($tmp) < 5) { $res = $res . "0:" . $tmp; }
        if (length($tmp) == 0) {    $res = $res . "0:0"; }
        if (length($tmp) > 4) { $res = $res . substr($tmp, 0, length($tmp) - 4) . ":" . substr($tmp, length($tmp) - 4); } 
        if ($i<3) { $res .= ":";}
    } 
    return $res;
}

# Periodic data processing function
sub run {
	my $prun = `rm -rf $FRONTEND_PLUGINDIR/ipflows/tmp/graph*`;
	syslog("info", "ipflows: periodic tmp dir cleanup: $prun");
}

#
## The Init function is called when the plugin is loaded. It's purpose is to give the plugin 
## the possibility to initialize itself. The plugin should return 1 for success or 0 for 
## failure. If the plugin fails to initialize, it's disabled and not used. Therefore, if
## you want to temporarily disable your plugin return 0 when Init is called.
sub Init {
    syslog("info", "ipflows: Init");
    $nfdump = "$NfConf::PREFIX/nfdump";
	my $conf = $NfConf::PluginConf{ipflows};
	my $tmp_max_lines = $$conf{'max_lines'};
	if ($tmp_max_lines ne "") { $max_lines = $tmp_max_lines; }
	my $tmp_time_difference = $$conf{'time_difference'};
	if ($tmp_time_difference ne "") { $time_difference = $tmp_time_difference; }
    $PROFILEDIR = "$NfConf::PROFILEDATADIR";
    $BACKEND_PLUGINDIR = "$NfConf::BACKEND_PLUGINDIR";
    $FRONTEND_PLUGINDIR = "$NfConf::FRONTEND_PLUGINDIR";
    return 1;
}

#
## The Cleanup function is called, when nfsend terminates. It's purpose is to give the
## plugin the possibility to cleanup itself. It's return value is discard.
sub Cleanup {
	run();
    syslog("info", "ipflows: Cleanup");
    # not used here
}

1;

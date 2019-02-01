<?php
# version 1.1.0 (build date 2010-12-26 20:20:00)

#
## protocols.php - IP flows correlation and visualization plugin
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

# Defined IP protocols in the plugin.

$protocols[0] = "any";
$protocols[6] = "TCP";
$protocols[17] = "UDP";
$protocols[1] = "ICMP";
$protocols[2] = "IGMP";
$protocols[47] = "GRE";
$protocols[50] = "ESP";
$protocols[58] = "ICMP6";
$protocols[41] = "IPv6";
$protocols[103] = "PIM";
$protocols[43] = "Rte6";
$protocols[44] = "Frag6";
$protocols[59] = "NOHE6";
$protocols[60] = "OPTS6";
?>

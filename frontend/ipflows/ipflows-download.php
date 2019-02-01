<?php
# version 1.0.0.1 (build date 2019-02-01 00:45:00)

#
## ipflows-download.php - IP flows correlation and visualization plugin
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

# This file is responsible for correlation graphs saving diagrams.

session_start();

include "functions.php";

$graph_text = $_SESSION['graph_text'];
$type = $_GET['type'];

write_graph($graph_text, "tmp/graph" . session_id(), $type);
 
$ImageName = "graph" . session_id() . "." . $type;
$ImageLoc = "./tmp/$ImageName";

header("Cache-Control: public, must-revalidate");
header("Content-Type: application/octet-stream");
header("Content-Length: " . filesize($ImageLoc) );
header("Content-Disposition: attachment; filename=" .$ImageName);
header("Content-Transfer-Encoding: binary\n");

$fp = fopen($ImageLoc, 'rb');
$buffer = fread($fp, filesize($ImageLoc));
fclose ($fp);
                  
print $buffer;

?>

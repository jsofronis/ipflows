// version 1.0.0.1 (build date 2019-02-01 00:45:00)

/*
#
## my.js - IP flows correlation and visualization plugin
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
*/

function verifyIP4(IPvalue) {
	if (IPvalue === "") { return ""; }
	var errorString = "";
        
	var ipPattern = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
	var ipArray = IPvalue.match(ipPattern);
        
	if (ipArray === null) {
		errorString = IPvalue+' is not a valid IP address.';
	} else {
		for (var i = 0; i < 4; i++) {
			var thisSegment = ipArray[i];
			if (thisSegment > 255) {
				errorString = IPvalue+' is not a valid IP address.';
				i = 4;
			}
			if ((i === 0) && (thisSegment > 255)) {
				errorString = IPvalue+' is a special IP address and cannot be used here.';
				i = 4;
			}
		}
	}
	return errorString;
}

function openFramePage(page) {
this.window.open(page, 'ipflowsframe');
}

function humanToTime(year, month, day, hour, minute, second) {
	var humDate = new Date(Date.UTC(year,this.stripLeadingZeroes(month),this.stripLeadingZeroes(day),this.stripLeadingZeroes(hour),this.stripLeadingZeroes(minute),this.stripLeadingZeroes(second)));
	return (humDate.getTime()/1000.0);
}

function stripLeadingZeroes(input) {
if((input.length > 1) && (input.substr(0,1) === "0")) {
		return input.substr(1);
	} else {
		return input;
	}
}

function verifyValue(myValue, myPattern, myText) {
	var errorString = "";
	if (myValue === "") { return ""; }
	var myCheck = myValue.match(myPattern);
	if (myCheck === null) { errorString = myValue + "is " + myText; }
	return errorString;
}

function setCookie(name,value,days) {
	var expires;
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		expires = "; expires="+date.toGMTString();
	}
	else { expires = ""; }
	this.document.cookie = name+"="+value+expires+"; path=/";
}

function getCookie(name) {
	var nameEQ = name + "=";
	var ca = this.document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0) ===' ') { c = c.substring(1,c.length); }
		if (c.indexOf(nameEQ) === 0) { return c.substring(nameEQ.length,c.length); }
	}
	return "";
}

function eraseCookie(name) {
	this.createCookie(name,"",-1);
}

function mark_filter(val1, val2, val3, val4, val5, val6, val7, val8, val9, val10, val11, val12, val13) {
	this.self.document.forms.post_form2.srcip.checked = val1;
	this.self.document.forms.post_form2.dstip.checked = val2;
	this.self.document.forms.post_form2.srcport.checked = val3;
	this.self.document.forms.post_form2.dstport.checked = val4;
	this.self.document.forms.post_form2.proto.checked = val5;
	this.self.document.forms.post_form2.srcclass.checked = val6;
	this.self.document.forms.post_form2.dstclass.checked = val7;
	this.self.document.forms.post_form2.ipproto.checked = val8;
	this.self.document.forms.post_form2.channels.checked = val9;
	this.self.document.forms.post_form2.time.value = val10;
	this.self.document.forms.post_form2.durat.value = val11;
	this.self.document.forms.post_form2.pkts.value = val12;
	this.self.document.forms.post_form2.bytes.value = val13;
}

function clear_filter() {
	this.mark_filter(false, false, false, false, false, false, false, false, false, 0, 0, 0, 0);
}

function filter_change() {
        var xval = this.self.document.forms.post_form.filter_list.value;
	if (xval === "") { return; }
        this.self.document.forms.post_form.filter_name.value = xval;
        var str = this.getCookie("ipflows_abc");
        var arr =  str.split("|");
        for (var i=0; i<arr.length; i++) {
                var tmp = arr[i].split(",");
                if (xval === tmp[0]) {
                        this.self.document.forms.post_form.timefrom.value = tmp[1];
                        this.self.document.forms.post_form.timeto.value = tmp[2];
                        this.self.document.forms.post_form.maxflows.value = tmp[3];
                        this.self.document.forms.post_form.srcip.value = tmp[4];
                        this.self.document.forms.post_form.srcmask.value = tmp[5];
                        this.self.document.forms.post_form.srcport.value = tmp[6];
                        this.self.document.forms.post_form.dstip.value = tmp[7];
                        this.self.document.forms.post_form.dstmask.value = tmp[8];
                        this.self.document.forms.post_form.dstport.value = tmp[9];
                        this.self.document.forms.post_form.proto.value = tmp[10];
                        this.self.document.forms.post_form.ipproto.value = tmp[11];
                        this.self.document.forms.post_form.processing.value = tmp[12];
                        this.self.document.forms.post_form.showflows.value = tmp[13];
                }
        }
}

function filter_delete() {
	this.filter_delete_all();
	this.filter_insert_all();
}

function filter_delete_all() {
	var num = this.self.document.forms.post_form.filter_list.options.length;
	var str = "";
	var deleted = this.self.document.forms.post_form.filter_list.value;
	var cok = this.getCookie("ipflows_abc");
	var arr = cok.split("|");
	for (var i = 0; i<num; i++) {
		var tmp = arr[i].split(",");
		if (tmp[0] !== deleted) {
			if (str !== "") { str += "|"; }
			str += arr[i];
		}
		this.self.document.forms.post_form.filter_list.remove(0);
	}
	this.setCookie("ipflows_abc", str, 365);
}

function filter_insert_all() {
	var str = this.getCookie("ipflows_abc");
	if (str !== "") {
		var arr = str.split("|");
		for (var i = 0; i < arr.length; i++) {
			var tmp = arr[i].split(",");
			this.self.document.forms.post_form.filter_list.options[i] = new this.Option(((i+1) + ". " + tmp[0]), tmp[0]);
		}
	}
}

function filter_insert() {
	var xval = this.self.document.forms.post_form.filter_name.value;
        if ((xval === "") || (xval === "Filter")) {
                this.window.alert("Please choose filter's name!");
                return;
        }
	var updated = 0;
        var xval2 = xval + "," +
                this.self.document.forms.post_form.timefrom.value + "," +
                this.self.document.forms.post_form.timeto.value + "," +
                this.self.document.forms.post_form.maxflows.value + "," +
                this.self.document.forms.post_form.srcip.value + "," +
                this.self.document.forms.post_form.srcmask.value + "," +
                this.self.document.forms.post_form.srcport.value + "," +
                this.self.document.forms.post_form.dstip.value + "," +
                this.self.document.forms.post_form.dstmask.value + "," +
                this.self.document.forms.post_form.dstport.value + "," +
                this.self.document.forms.post_form.proto.value + "," +
                this.self.document.forms.post_form.ipproto.value + "," +
                this.self.document.forms.post_form.processing.value + "," +
                this.self.document.forms.post_form.showflows.value;
	var str = this.getCookie("ipflows_abc");
	var newstr = "";
	if (str !== "") {
		var arr = str.split("|");
		for (var i = 0; i < arr.length; i++) {
			if (newstr !== "") { newstr += "|"; }
			var tmp = arr[i].split(",");
			if (tmp[0] !== xval) {
				newstr += arr[i];
			} else {
				newstr += xval2;
				updated = 1;
			}
		}
	} else {
		newstr = xval2;
		updated = 2;
	}
	if (updated === 0) {
		newstr += "|" + xval2;
	}
	if (updated !== 1) {
                this.self.document.forms.post_form.filter_list.options[this.self.document.forms.post_form.filter_list.options.length] = new this.Option(((this.self.document.forms.post_form.filter_list.length + 1) + ". " + xval), xval);
	}
	this.setCookie("ipflows_abc", newstr, 365);
	this.self.document.forms.post_form.filter_list.value = xval;
}

function form_check2(timewinlen) {
      var xval = this.self.document.forms.post_form2.time.value;
        if ((xval.match(/^\d{1,10}$/) === null) || (xval > timewinlen)) {
                this.window.alert(xval + " is a bad time aggregation value! (0-" + timewinlen + ")");
                return false;
        }
        xval = this.self.document.forms.post_form2.durat.value;
        if ((xval.match(/^\d{1,5}$/) === null) || (xval > 60000)) {
                this.window.alert(xval + " is a bad duration aggregation value! (0-60000)");
                return false;
        }
        xval = this.self.document.forms.post_form2.pkts.value;
        if ((xval.match(/^\d{1,4}$/) === null) || (xval > 1000)) {
                this.window.alert(xval + " is a bad packets aggregation value! (0-1000)");
                return false;
        }
        xval = this.self.document.forms.post_form2.bytes.value;
        if ((xval.match(/^\d{1,5}$/) === null) || (xval > 10000)) {
                this.window.alert(xval + " is a bad bytes aggregation value! (0-10000)");
                return false;
        }
        xval = this.self.document.forms.post_form2.maxnodes.value;
        if (xval.match(/^\d{2,4}$/) === null) {
                this.window.alert(xval + " is a bad max. nodes value! (10-9999)");
                return false;
        }
        xval = this.self.document.forms.post_form2.zoom.value;
        if (xval.match(/^\d{2,3}$/) === null) {
                this.window.alert(xval + " is a bad zoom value! (10-999)");
                return false;
        }
	return true;
}

function form_check() {
	var good_filters = 0;
	var srcmasksize = 128;
	var dstmasksize = 128;
        var xval = this.self.document.forms.post_form.srcport.value;
        if ((xval !== "") && ((xval.match(/^\d{1,5}$/) === null) || (xval > 65535))) {
                this.window.alert(xval + " is a bad source port! (0-65535)");
                return false;
        }
        if (xval !== "") { good_filters++; }
        xval = this.self.document.forms.post_form.dstport.value;
        if ((xval !== "") && ((xval.match(/^\d{1,5}$/) === null) || (xval > 65535))) {
                this.window.alert(xval + " is a bad destination port! (0-65535)");
                return false;
        }
        if (xval !== "") { good_filters++; }
        xval = this.self.document.forms.post_form.maxflows.value;
        if (xval.match(/^\d{2,4}$/) === null) {
                this.window.alert(xval + " is a bad max. flows value! (10-9999)");
                return false;
	}
	xval = this.self.document.forms.post_form.srcip.value;   
	if ((verifyIP4(xval) !== "") && (xval.match(/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/) === null)) {
	        this.window.alert('Source IP address ' + verifyIP4(xval));
	        return false;
	} else {
		if (verifyIP4(xval) === "") { srcmasksize = 32; }
	}
        if (xval !== "") { good_filters++; }
        xval = this.self.document.forms.post_form.dstip.value;
	if ((verifyIP4(xval) !== "") && (xval.match(/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/) === null)) {
	        this.window.alert('Destination IP address ' + verifyIP4(xval));
	        return false;
	} else {
		if (verifyIP4(xval) === "") { dstmasksize = 32; }
	}
        if (xval !== "") { good_filters++; }
        xval = this.self.document.forms.post_form.srcmask.value;
        if ((xval !== "") && ((xval.match(/^\d{1,3}$/) === null) || (xval > srcmasksize))) {
                this.window.alert(xval + " is a bad source mask! (0-" + srcmasksize + ")");
                return false;
        }
        if (xval !== "") { good_filters++; }
        xval = this.self.document.forms.post_form.dstmask.value;
        if ((xval !== "") && ((xval.match(/^\d{1,3}$/) === null) || (xval > dstmasksize))) {
                this.window.alert(xval + " is a bad destination mask! (0-" + dstmasksize + ")");
                return false;
        }
        if (xval !== "") { good_filters++; }
        xval = this.self.document.forms.post_form.timefrom.value;
        var fromArray = xval.match(/^(\d{4})\/(\d{2})\/(\d{2})\ (\d{2})\:(\d{2})\:(\d{2})$/);
        if (fromArray === null) {
                this.window.alert(xval + " is a bad start time value! (YYYY/MM/DD HH:MM:SS)");
                return false;
        }
        xval = this.self.document.forms.post_form.timeto.value;
        var toArray = xval.match(/^(\d{4})\/(\d{2})\/(\d{2})\ (\d{2})\:(\d{2})\:(\d{2})$/);
        if (toArray === null) {
                this.window.alert(xval + " is a bad end time value! (YYYY/MM/DD HH:MM:SS)");
                return false;
        }
        var timeWindow = this.humanToTime(toArray[1],toArray[2],toArray[3],toArray[4],toArray[5],toArray[6]) - (this.humanToTime(fromArray[1],fromArray[2],fromArray[3],fromArray[4],fromArray[5],fromArray[6]));
	var ret;
	if ((good_filters === 0) && (timeWindow > 60)) {
		ret = this.window.confirm("The time window is " + timeWindow + " seconds and you didn't insert any filter.\nDo you want really to show the data?");
		if (ret === false) { return false; }
	} else if (timeWindow > (60 * (good_filters + 1))) {
                ret = this.window.confirm("The time window is " + timeWindow + " seconds and you inserted only " + good_filters + " filter(s).\nDo you want really to show the data?");
		if (ret === false) { return false; }
	}
        return true;
}


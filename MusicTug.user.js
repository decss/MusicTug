// ==UserScript==
// @name        MusicTug
// @namespace   decss@miromax.org
// @description 
// @include     http://*http://deploy.local/*
// @require     http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js
// @require     https://raw.github.com/kvz/phpjs/master/functions/var/serialize.js
// @require     https://raw.github.com/jbrooksuk/jQuery-Timer-Plugin/master/jquery.timer.js
// @grant       GM_getValue
// @grant       GM_setValue
// @grant       GM_xmlhttpRequest
// @grant       GM_log
// @version     20141028
// ==/UserScript==

alert(1);

var log = function(log_data) {
    GM_log(log_data);
    // unsafeWindow.console.log(log_data);
    // console.log(log_data);
}

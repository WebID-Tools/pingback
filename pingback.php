<?php
//-----------------------------------------------------------------------------------------------------------------------------------
//
// Filename   : pingback.php
// Date       : 21st Apr 2011
//
// Project name: SemPB - Semantical Pingback
// Copyright 2011 fcns.eu
// Author: Andrei Sambra - andrei@fcns.eu
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// See <http://www.gnu.org/licenses/> for a description of this license.


require 'graphite.php';
require 'arc/ARC2.php';


/* You can change the following configuration options used for emails */
// email address to use as source recipient
$sender = 'postmaster@webid.fcns.eu';
// email subject
$subject = 'New Pingback!';



/* ----- Do not modify below ----- */

// function to send the actual email
if (!function_exists('send_mail')) {
    function send_mail($from,$to,$subject,$body)
    {
    	$headers = '';
	    $headers .= "From: $from\n";
	    $headers .= "Reply-to: $from\n";
	    $headers .= "Return-Path: $from\n";
	    $headers .= "Message-ID: <" . md5(uniqid(time())) . "@" . $_SERVER['SERVER_NAME'] . ">\n";
	    $headers .= "MIME-Version: 1.0\n";
	    $headers .= "Date: " . date('r', time()) . "\n";

	    mail($to,$subject,$body,$headers);
    }
}

$ret = "";
$ret .= "<div class=\"container\">\n";

// process form and send pingback
if (isset($_REQUEST['to'])) {
    $from   = $_REQUEST['from'];
    $to     = $_REQUEST['to'];
    $msg    = substr($_REQUEST['message'], 0, 256);

    // fetch the user's profile
    $fg = new Graphite();
    $fg->load($from);
    $fr = $fg->resource($from);
    $from_name = $fr->get("foaf:name");
    $ok = 0;
        
    foreach($fr->all("foaf:knows") as $friend) {
        // check if the target is in the sender's list of friends
        if ($friend == $to) {
            // fetch the target's profile
            $fg = new Graphite();
            $fg->load($to);
            $fr = $fg->resource($to);

            // skip sending mail if we didn't resolve the profile
            if ($fr->get("foaf:mbox") != '[NULL]') {
            
                $recipient = $fr->get("foaf:mbox");                       
            
                $body = "Hello " . $fr->get("foaf:name") . ". You have received a new pingback!\n\n"; 
                $body .= "From: " . $from_name . "\n"; 
                $body .= "WebID: " . $from . "\n";
                $body .= "Message (optional): " . $msg . "\n\n"; 
                $body .= "There is no need to reply to this email.";

                send_mail($sender, $recipient, $subject, $body);
                        
                $ret .= "<font color=\"green\" style=\"font-size: 1.3em;\">SUCCESS. Your pingback has been delivered!</font><br/>\n";
            } else {
                $ret .= "<font style=\"font-size: 1.3em;\"><font color=\"red\">FAILED!</font> I couldn't find any email address in the destination WebID profile!</font><br/>\n";
            }
            $ok = 1;
            break;
        }
    }
    if ($ok == 0)
        $ret .= "<font style=\"font-size: 1.3em;\"><font color=\"red\">FAILED!</font> Could not find any mention of <font color=\"#00BBFF\">" . $to . "</font> in the source WebID profile.</font><br/>\n";
}

// show form
$ret .= "<h1><font color=\"black\">Send a WebID pingback!</font></h1>\n";
$ret .= "<p>Attempt to 'ping' someone using the first foaf:mbox resource <br/>in their profile.</p>\n"; 
$ret .= "<p>The Source WebID must contain a relation of type foaf:knows, <br/>pointing to the Destination WebID .</p><br/>\n"; 

$ret .= "<form name=\"pingback\" method=\"POST\" action=\"\">\n";
$ret .= "<table border=\"0\" style=\"background-color:#fff; border:dashed 1px grey;\">\n";
$ret .= "<tr valign=\"top\"><td>Source WebID: <br/>&nbsp;</td><td><input size=\"30\" type=\"text\" name=\"from\"></td></tr>\n";
$ret .= "<tr valign=\"top\"><td>Destination WebID: <br/>&nbsp;</td><td><input size=\"30\" type=\"text\" name=\"to\" value=\"\"></td></tr>\n";
$ret .= "<tr valign=\"top\"><td>Optional Short <br/> message <br/><small>(max 256 charaters)</small></td>";
$ret .= "<td> <textarea cols=\"35\" name=\"message\" style=\"background-color:#fff; border:dashed 1px grey;\"></textarea></td></tr>\n";
$ret .= "<tr><td><br/><input type=\"submit\" name=\"submit\" value=\"Ping!\"></td><td></td></tr>\n";
$ret .= "</table>\n";
$ret .= "</form>\n";

$ret .= "<div class=\"clear\"></div>\n";
$ret .= "</div>\n";
echo $ret;

?>		      


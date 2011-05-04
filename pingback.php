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

set_include_path(get_include_path() . PATH_SEPARATOR . '../');
require '../graphite.php';
require '../arc/ARC2.php';


/* You can change the following configuration options used for emails */

$sender = 'pingback@fcns.eu'; // email address to use as source recipient
$recipient = 'yourname@host.com'; // your email address
$me = 'http://host.com/people/username/card#me'; // your webid 


/* ----- Do not modify below ----- */

// function to send the actual email
if (!function_exists('send_pingback_mail')) {
    function send_pingback_mail($from, $to, $subject, $body)
    {
    	$headers = '';
	    $headers .= "From: $from\n";
	    $headers .= "Reply-to: $from\n";
	    $headers .= "Return-Path: $from\n";
	    $headers .= "Message-ID: <" . md5(uniqid(time())) . "@" . $_SERVER['SERVER_NAME'] . ">\n";
	    $headers .= "MIME-Version: 1.0\n";
	    $headers .= "Date: " . date('r', time()) . "\n";

	    return mail($to,$subject,$body,$headers);
    }
}

$ret = "";

// process form and send pingback
if (isset($_POST['source'])) {

    $from   = trim($_POST['source']);
    $msg    = $_POST['comment'];

    // fetch the user's profile
    $fg = new Graphite();
    $fg->load($from);
    $fr = $fg->resource($from);
    $from_name = $fr->get("foaf:name");
    $match = false;
    
    foreach($fr->all("foaf:knows") as $friend) {
        // check if I am in the sender's list of friends
        if ($friend == $me) {
            $match = true;
            
            // email subject
            $subject = 'New Pingback!';

            $body = "Hello! You have received a new pingback!\n\n"; 
            $body .= "From: " . $from_name . "\n"; 
            $body .= "WebID: " . $from . "\n";
            $body .= "Message (optional): " . $msg . "\n\n"; 

            $ok = send_pingback_mail($sender, $recipient, $subject, $body);

            if ($ok)
                $ret .= "<font color=\"green\" style=\"font-size: 1.3em;\">SUCCESS. Your pingback has been accepted for delivery!</font><br/>\n";
            else
                $ret .= "<font style=\"font-size: 1.3em;\"><font color=\"red\">FAILED!</font> We could not deliver your pingback!</font>\n";           
            break;
        }
    }

    if (!$match)
        $ret .= "<font style=\"font-size: 1.3em;\"><font color=\"red\">FAILED!</font> Could not find any mention of <font color=\"#00BBFF\">" . $to . "</font> in the source WebID profile.</font><br/>\n";

}
else {
// show form
$ret .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">';
$ret .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\"  xmlns:pingback=\"http://purl.org/net/pingback/\">\n";
$ret .= "   <head>\n";
$ret .= "	<title>Pingback</title>\n";
$ret .= "	<meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" />\n";
$ret .= "   </head>\n";
$ret .= "   <body typeof=\"pingback:Container\">\n";
$ret .= "   <form method=\"post\" action=\"http://fcns.eu/people/andrei/pingback.php\">\n";
$ret .= "       <p>Your WebID: <input size=\"30\" property=\"pingback:source\" type=\"text\" name=\"source\" /></p>\n";
$ret .= "       <p>Comment (optional): <input size=\"30\" maxlength=\"256\" type=\"text\" name=\"comment\" style=\"background-color:#fff; border:dashed 1px grey;\" /></p>\n";
$ret .= "       <p><input type=\"submit\" name=\"submit\" value=\"Ping!\" /></p>\n";
$ret .= "   </form>\n";
$ret .= "   </body>\n";
$ret .= "</html>\n";
}
echo $ret;

?>		      


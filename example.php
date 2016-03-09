<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//include whois class
include(__DIR__ . '\src\Whois.php');
$whois = new \Whois();

$domain = 'google.com';

//get whois info
$whois_result = $whois->getInfo($domain);
echo $whois_result;


//check domain status
if ($whois->isAvailable($domain)) {
    echo "Domain is available\n";
} else {
    echo "Domain is registered\n";
}

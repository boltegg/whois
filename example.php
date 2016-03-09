<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//include whois class
include(__DIR__ . '\src\Whois.php');

//set domain
$domain = new \Whois('google.com');

//get whois info
$whois = $domain->info();
echo $whois;


//check domain status
if ($domain->isAvailable()) {
    echo "Domain is available\n";
} else {
    echo "Domain is registered\n";
}
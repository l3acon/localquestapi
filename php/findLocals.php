<?php
/**
 * Created by PhpStorm.
 * User: Marc
 * Date: 11/23/2014
 * Time: 11:40 AM
 */
require_once('../php/api.php');
$key = $_GET['k'];
$token = $_GET['t'];
$range = $_GET['r'];
$derp = API::findLocals($key,$token,$range);
echo $derp;


<?php
/**
 * Created by PhpStorm.
 * User: Marc
 * Date: 11/23/2014
 * Time: 11:40 AM
 */
require_once('../php/api.php');
$derp = API::findLocals('e77edf6dce3efd9e03c24718200bca859f560b2ab1a69650ef568e160dd4d1c1','4c9a36b0c1284a5fc48c9d32b8b287a9e82657312d551920751d50c6adf8930e',100.0);
echo $derp;
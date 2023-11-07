<?php

require_once('controllers/WhatsApp.php');
require_once('controllers/EmailSetting.php');
require_once('controllers/EmailConfig.php');
require_once('controllers/Mail.php');
require_once('controllers/Schedule.php');
require_once('controllers/LastPhone.php');

$class = $_REQUEST['class'];
$fn = $_REQUEST['fn'];

$class_path = "\controllers\\" . $class;

$obj = new $class_path();
$obj->init();
$obj->$fn();
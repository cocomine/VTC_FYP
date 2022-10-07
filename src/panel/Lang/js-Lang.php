<?php
/**
 * Copyright (c) 2019.
 * Create by cocomine
 */

header('X-Frame-Options: SAMEORIGIN');
header('Content-Type:text/json; charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/panel/Lang/Lang.php');

$output = array(
    'Lang' => $Lang['JS'] ?? 'Error',
    'Default' => $default['JS'] ?? 'Error'
);
echo json_encode($output);
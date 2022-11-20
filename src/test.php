<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

$date = DateTime::createFromFormat('Y-m-d', '2022-11-20', new DateTimeZone("Asia/Hong_Kong"));
var_dump($date);
$today = new DateTime('now', new DateTimeZone("Asia/Hong_Kong"));
var_dump($today);
$interval = $date->diff($today);
var_dump($interval);

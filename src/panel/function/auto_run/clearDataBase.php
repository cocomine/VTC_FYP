<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

require_once ('../config.inc.php');

$sqlcon = new mysqli(Cfg_Sql_Host, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_Sql_dbName);
$stmt = $sqlcon->prepare("TRUNCATE Block_ip");
$stmt->execute();
$stmt->prepare('DELETE FROM Toke_list WHERE UNIX_TIMESTAMP()-Time > 1209600');
$stmt->execute();
$stmt->prepare('DELETE FROM ForgetPass WHERE UNIX_TIMESTAMP()-Last_time > 3600');
$stmt->execute();
$stmt->prepare('DELETE FROM User WHERE UNIX_TIMESTAMP()-Last_Login > 86400 AND activated IS FALSE');
$stmt->execute();
$stmt->prepare('DELETE FROM notify WHERE UNIX_TIMESTAMP()-Time > 777600');
$stmt->execute();
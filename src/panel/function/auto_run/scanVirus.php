<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

use cocomine\VirusTotalApiV2;

require_once "../config.inc.php";
require_once "../../../cocomine/VirusTotalApiV2.php";

$virus = new VirusTotalApiV2(Cfg_virusTotal_key);
$sqlcon = new mysqli(Cfg_Sql_Host, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_Sql_dbName);

/* get need scan file */
$stmt = $sqlcon->prepare('SELECT ID, path FROM media WHERE Scan = FALSE LIMIT 2');
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    while ($row = $result->fetch_assoc()){
        $res = $virus->scanFile('../../'.$row['path']); //Scan

        if($res['response_code'] === 1){ //is report ready
            $report = $virus->getFileReport($res['md5']); //get report

            if($report['response_code'] === 1) {
                if ($report['positives'] <= 0) {
                    //安全
                    $stmt->prepare('UPDATE media SET Scan = TRUE WHERE ID = ?');
                    $stmt->bind_param('s', $row['ID']);
                    $stmt->execute();
                } else {
                    //不安全
                    $stmt->prepare('DELETE FROM media WHERE ID = ?');
                    $stmt->bind_param('s', $row['ID']);
                    $stmt->execute();

                    unlink($row['path']);
                }
            }
        }
    }
}




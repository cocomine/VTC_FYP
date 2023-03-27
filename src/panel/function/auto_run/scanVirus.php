<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

use cocomine\VirusTotalApiV2;

ini_set('error_log', '/volume1/web/error_log/scanVirus.log');

require(__DIR__.'/../../../../secret/config.inc.php');
require(__DIR__ . "/../../../cocomine/VirusTotalApiV2.php");

$virus = new VirusTotalApiV2(Cfg_virusTotal_key);
$sqlcon = new mysqli(Cfg_Sql_Host, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_Sql_dbName);

/* get need scan file */
$stmt = $sqlcon->prepare('SELECT ID, path FROM media WHERE Scan = FALSE LIMIT 2');
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $res = $virus->scanFile(__DIR__ .'/../../' . $row['path']); //Scan
        print_r($res);
        if ($res['response_code'] === 1) { //is report ready
            $report = $virus->getFileReport($res['md5']); //get report
            print_r($report);

            if ($report['response_code'] === 1) {
                if ($report['positives'] <= 0) {
                    //安全
                    $stmt->prepare('UPDATE media SET Scan = TRUE WHERE ID = ?');
                    $stmt->bind_param('s', $row['ID']);
                    $stmt->execute();
                    error_log($row['path'].' -> is Safety');
                } else {
                    //不安全
                    $stmt->prepare('DELETE FROM media WHERE ID = ?');
                    $stmt->bind_param('s', $row['ID']);
                    $stmt->execute();

                    unlink($row['path']);
                    error_log($row['path'].' -> is Not safety');
                }
            }else{
                error_log($row['path'].' -> scan fail. Try next Time');
            }
        }else{
            error_log($row['path'].' -> report not ready');
        }
    }
}




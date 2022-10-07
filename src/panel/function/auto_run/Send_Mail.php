<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('error_log', '/volume1/web/error_log/Send_Mail.log');

require(__DIR__."/../../vendor/autoload.php");

/* SQL */
//SQL config
$SQL_Config['dbServer'] = "192.168.0.100";                     // SQL Server ip address
$SQL_Config['$dbUser'] = "gblacklist";                     // Login username
$SQL_Config['$dbPass'] = 'wD9[-w@Ask';               // Login user password
$SQL_Config['$dbName'] = "gblacklist";                     // Databases name
//Connect to SQL
$sqlcon = new mysqli($SQL_Config['dbServer'], $SQL_Config['$dbUser'], $SQL_Config['$dbPass'], $SQL_Config['$dbName'], 3306);
if ($sqlcon->connect_errno) {
    die("Cannot connect to on this host \"{$SQL_Config['dbServer']}\" SQL Server!");
}
$sqlcon->query("SET NAMES utf8");

/* PHPMailer */
$mail = new PHPMailer(true);
//Server settings
//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                    // Enable verbose debug output
$mail->CharSet    = 'UTF-8';
$mail->isSMTP();                                            // Send using SMTP
$mail->Host       = 'smtp-relay.gmail.com';                 // Set the SMTP server to send through
$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
$mail->Username   = 'support@cocopixelmc.com';             // SMTP username
$mail->Password   = 'sakwkxnwewnjcdko';                         // SMTP password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
$mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
$mail->SMTPDebug = 3;
$mail->Helo = 'cocopixelmc.com';

/* Get Queue */
$stmt = $sqlcon->prepare("SELECT ID, Send_To, Send_From, `Subject`, Reply_To, `Body` FROM Mail_queue WHERE (Sending = false AND Fail < 10) OR (Send_Time = NULL AND Fail < 10)");
if (!$stmt->execute()) {
    die("Run SQL Error! Maybe you have n’t set it up yet?");
}
$result = $stmt->get_result();
$stmt->prepare("UPDATE Mail_queue SET Sending = 1 WHERE Sending = 0");
$stmt->execute();

/* Send Mail */
while ($row = $result->fetch_assoc()) {
    $From = explode(";", $row['Send_From']);
    $Replay = explode(";", $row['Reply_To']);
    try {
        //Recipients
        $mail->setFrom($From[0], $From[1], false);
        $mail->clearAddresses();
        $mail->addAddress($row['Send_To']);
        $mail->clearReplyTos();
        $mail->addReplyTo($Replay[0], $Replay[1]);
        $mail->base64EncodeWrapMB('utf-8');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $row['Subject'];
        $mail->Body    = $row['Body'];

        $mail->send();  //Send

        //delete data
        $update = $sqlcon->prepare("DELETE FROM Mail_queue WHERE ID = ?");
        $update->bind_param('s', $row['ID']);
        $update->execute();
        $update->close();

        error_log('Email '.$row['ID'].' is send to '.$row['Send_To']." successfully!"); //output
    } catch (Exception $e) {
        //update data
        $update = $sqlcon->prepare("UPDATE Mail_queue SET Sending = 0, Send_Time = now(), Fail = Fail+1 WHERE ID = ?");
        $update->bind_param('s', $row['ID']);
        $update->execute();
        $update->close();

        error_log('Email '.$row['ID'].' is send to '.$row['Send_To']." Fail! Next time retry."); //output
        error_log($mail->ErrorInfo);
        echo $mail->Debugoutput;
    }
}
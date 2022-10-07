/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

CREATE TABLE User (
    'UUID' CHAR(36) NOT NULL ,
    'password' CHAR(128) NOT NULL ,
    'Email' VARCHAR(100) NOT NULL UNIQUE ,
    'Name' VARCHAR(16) NOT NULL ,
    'activated_code' CHAR(16) NULL ,
    'activated' BOOLEAN NOT NULL DEFAULT FALSE ,
    '2FA' BOOLEAN NOT NULL DEFAULT FALSE ,
    '2FA_secret' CHAR(16) NULL ,
    'Last_Login' BIGINT NOT NULL ,
    'Last_IP' VARCHAR(45) NOT NULL ,
    'Language' VARCHAR(5) NOT NULL ,
    'role' CHAR(1) NOT NULL DEFAULT '1' );

CREATE TABLE 'Block_ip' (
    'IP' VARCHAR(45) NOT NULL ,
    'Last_time' BIGINT NOT NULL ,
    INDEX('IP'));

CREATE TABLE 'ForgetPass' (
    'UUID' CHAR(36) NOT NULL ,
    'Code' CHAR(32) NOT NULL ,
    'Last_time' BIGINT NOT NULL ,
    PRIMARY KEY ('UUID'));

CREATE TABLE 'Toke_list' (
    'UUID' CHAR(36) NOT NULL ,
    'Toke' CHAR(32) NOT NULL ,
    'IP' VARCHAR(45) NOT NULL ,
    'Time' BIGINT NOT NULL ,
    PRIMARY KEY ('UUID'),
    PRIMARY KEY ('Toke'));

CREATE TABLE '2FA_BackupCode' (
    'UUID' CHAR(36) NOT NULL ,
    'Code' CHAR(6) NOT NULL ,
    'used' BOOLEAN NOT NULL DEFAULT FALSE ,
    PRIMARY KEY ('UUID'),
    PRIMARY KEY ('Code'));

CREATE TABLE 'Block_login_code' (
    'code' CHAR(32) NOT NULL ,
    'Toke' CHAR(32) NOT NULL ,
    'time' BIGINT NOT NULL ,
    PRIMARY KEY ('code'));

CREATE TABLE 'Mail_queue' (
    'ID' INT NOT NULL AUTO_INCREMENT ,
    'Send_To' VARCHAR(100) NOT NULL ,
    'Send_From' VARCHAR(100) NOT NULL ,
    'Subject' VARCHAR(100) NOT NULL ,
    'Reply_To' VARCHAR(100) NOT NULL ,
    'Body' LONGTEXT NOT NULL ,
    'Fail' INT(1) NOT NULL DEFAULT 0 ,
    'Sending' BOOLEAN NOT NULL DEFAULT FALSE ,
    'Create_Time' TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    'Send_Time' DATETIME NULL ,
    PRIMARY KEY ('ID') ,
    INDEX ('Send_To'));

CREATE TABLE `gblacklist`.`Broadcast` (
    `ID` INT NOT NULL AUTO_INCREMENT ,
    `Msg` VARCHAR(200) NOT NULL ,
    `status` tinyint NOT NULL ,
    `Always_close` BOOLEAN NOT NULL DEFAULT TRUE ,
    `Time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    `Broadcast` BOOLEAN NOT NULL DEFAULT TRUE ,
    PRIMARY KEY (`ID`));
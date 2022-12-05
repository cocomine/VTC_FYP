<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace cocomine;

use Exception;
use mysqli;

/**
 * 頁面載入器
 * @package cocomine/LoadPageFactory
 * @author cocomine<https://github.com/cocomine>
 * @version 1.0
 */
class LoadPageFactory {

    /**
     * Create page factory
     * @throws Exception File is not exits
     */
    public static function createPage(string $class, string $rootPath, array $up_path): IPage{
        $path = $rootPath.str_replace('\\', '/', $class).'.php';

        if(!file_exists($path)) throw new Exception('File is not exits');
        require_once($path);

        /* create sql connect */
        $sqlcon = new mysqli(Cfg_Sql_Host, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_Sql_dbName);
        if ($sqlcon->connect_errno) {
            http_response_code(500);
            echo json_encode(array('code' => 500, 'Message' => showText("Error_Page.something_happened")));
            exit();
        }
        return new $class($sqlcon, $up_path); //create class
    }

    /**
     * Create api factory
     * @throws Exception File is not exits
     */
    public static function createApi(string $class, string $rootPath, array $up_path): IApi{
        $path = $rootPath.str_replace('\\', '/', $class).'.php';

        if(!file_exists($path)) throw new Exception('File is not exits');
        require_once($path);

        /* create sql connect */
        $sqlcon = new mysqli(Cfg_Sql_Host, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_Sql_dbName);
        if ($sqlcon->connect_errno) {
            http_response_code(500);
            echo json_encode(array('code' => 500, 'Message' => showText("Error_Page.something_happened")));
            exit();
        }
        return new $class($sqlcon, $up_path); //create class
    }
}
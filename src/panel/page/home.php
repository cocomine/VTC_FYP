<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */
namespace panel\page;

use mysqli;

/**
 * Class home
 * @package cocopixelmc\Page
 */
class home
{
    private mysqli $sqlcon;

    /**
     * home constructor.
     * sql連接
     * @param $sqlcon
     */
    function __construct($sqlcon){
        $this->sqlcon = $sqlcon;
    }

    /**
     * 輸出頁面
     * @return string 輸出頁面內容
     */
    function showPage(){
        return "
            <div class='col-12 mt-4'>
                <div class='card'>
                    <div class='card-body'>
                        Hello world!
                    </div>
                </div>
            </div>
        ";
    }

    /**
     * path輸出
     * @return string 輸出
     */
    function path(){
        return "<li><a href=\"/panel\" data-ajax=\"GET\">".showText("index.Console")."</a></li>
                        <li><span>".showText("index.home")."</span></li>";
    }
}
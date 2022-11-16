<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

class flight implements IPage {

    private mysqli $sqlcon;
    private array $upPath;

    /**
     * @param mysqli $sqlcon
     * @param array $upPath
     */
    public function __construct(mysqli $sqlcon, array $upPath) {
        $this->sqlcon = $sqlcon;
        $this->upPath = $upPath;
    }

    public function access(bool $isAuth, int $role): int {
        if(sizeof($this->upPath) != 1) return 404;

        //todo: mysql
        return 200;
    }

    public function showPage(): string {
        return <<<body
        
        body;
    }

    function post(array $data): array {
        return array();
    }

    function path(): string {
        return "<li><span><a href='/panel/'>" . showText("index.home") . "</a></span></li><li><span><a href='/panel/'>".showText('Flight.Head')."</a></span></li><li><span>".strtoupper($this->upPath[0])."</span></li>";
    }

    public function get_Title(): string {
        return strtoupper($this->upPath[0])." ".showText('Flight.Title');
    }

    public function get_Head(): string {
        return showText('Flight.Head')." ".strtoupper($this->upPath[0]);
    }


}
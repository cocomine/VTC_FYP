<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use mysqli;

class test implements \cocomine\IPage {

    private array $UpPath;
    function __construct(mysqli $sqlcon, array $UpPath) {
        //$this->sqlcon = $sqlcon;
        $this->UpPath = $UpPath;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        $Text = showText('Media.Content');
        $Text2 = showText('Media-upload.Content');

        $LangJson = json_encode(array(
            'No_media'           => $Text['No_media'],
            'Media'              => $Text['Media'] . ' %s',
            'Unknown_Error'      => showText('Error'),
            'title' => $Text['Media_Select']['title'],
            'Select' => $Text['Media_Select']['Select'],
            'upload' => array(
                'Timeout'            => $Text2['respond']['Timeout'],
                'File_name_over'     => $Text2['respond']['File_name_over'],
                'Over_size'          => $Text2['respond']['Over_size'],
                'File_type_not_mach' => $Text2['respond']['File_type_not_mach'],
                'Waiting'            => $Text2['respond']['Waiting'],
                'limit_type' => $Text2['limit_type'],
                'drag' => $Text2['drag'],
                'upload' => $Text2['upload'],
                'or' => $Text2['or'],
                'limit' => $Text2['respond.']
            )
        ));

        return <<<body
    <pre id="media-select-LangJson" class="d-none">$LangJson</pre>
    <link rel="stylesheet" href="/panel/assets/css/myself/media-select.css">
    <link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
    <div>
        <div id="show"></div>
        <button id="select">select image</button>
        <div class="date-picker">
            <input type="date" class="date-picker-toggle" min="01-10-2023" max="01-20-2023">
        </div>
        <div class="date-picker date-picker-inline">
            <input type="date" class="date-picker-toggle">
            <div class="date-calendar"></div>
        </div>
    </div>
    <script>
        require.config({
            paths:{
                'media-select': 'myself/media-select',
                'media-select.upload': 'myself/media-select.upload',
            }
        })
        loadModules(['media-select', 'media-select.upload', "myself/page/test", 'myself/datepicker'])
    </script>
    body;

    }

    /**
     * @inheritDoc
     */
    function post(array $data): array {
        return array();
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return 'test';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return 'test';
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return 'test';
    }
}
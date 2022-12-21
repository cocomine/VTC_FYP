<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

class test implements \cocomine\IPage {

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
            'No_media'=>$Text['No_media'],
            'Unknown_Error' => showText('Error'),
            'Timeout' => $Text2['respond']['Timeout'],
            'File_name_over' => $Text2['respond']['File_name_over'],
            'Over_size' => $Text2['respond']['Over_size'],
            'File_type_not_mach' => $Text2['respond']['File_type_not_mach'],
            'Waiting' => $Text2['respond']['Waiting']
        ));

        return <<<body
<pre id="media-select-LangJson" class="d-none">$LangJson</pre>
<link rel="stylesheet" href="/panel/assets/css/myself/media-select.css">
<div>
    <div id="show"></div>
    <button id="select">select image</button>
</div>
<script>
    loadModules(['myself/media-select', "myself/page/test"])
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
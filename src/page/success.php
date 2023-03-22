<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace page;

use mysqli;
use Stripe\StripeClient;

class success implements \cocomine\IPage {

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        if($isPost) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {
        return <<<body
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-center">
                        <h1 class="col-auto">預訂成功</h1>
                        <div class="w-100"></div>
                        <div class="col-auto">
                            <lottie-player src="https://assets1.lottiefiles.com/packages/lf20_zwkm4xbs.json"  background="transparent"  speed="1"  style="width: 300px; height: 300px;" autoplay></lottie-player>
                        </div>
                        <div class="w-100"></div>
                        <p class="col-auto">您的預訂已經完成，請至您的電子郵件收取確認信件。</p>
                    <div>
                </div>
            </div>
        </div>
    </div>
</div>
body;
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        return array();
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        return '<li class="breadcrumb-item"><a href="/">' . showText("index.home") . '</a></li>'
            . '<li class="breadcrumb-item active">預訂成功</li>';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return "預訂成功 | X-Travel";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return "預訂成功";
    }
}
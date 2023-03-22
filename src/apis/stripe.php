<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace apis;

use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class stripe implements \cocomine\IApi {

    private \mysqli $sqlcon;

    /**
     * @param \mysqli $sqlcon
     */
    public function __construct(\mysqli $sqlcon) {
        $this->sqlcon = $sqlcon;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role): int {
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function get() {
        http_response_code(403);
    }

    /**
     * @inheritDoc
     */
    public function post(?array $data) {
        header("Content-Type: text/json; charset=utf-8");
        \Stripe\Stripe::setApiKey(Cfg_stripe_test_key);
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        // 檢查是否符合格式
        if($data === null) {
            echo_error(400);
            return;
        }

        // 檢查來源是否來自Stripe
        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, Cfg_strip_endpoint_secret
            );
        } catch(SignatureVerificationException $e) {
            // Invalid signature
            echo '⚠️  Webhook error while validating signature.';
            http_response_code(400);
            return;
        }

        // 處理預約
        if($event->type === "invoice.payment_succeeded"){
            ob_flush();

            $stmt = $this->sqlcon->prepare("SELECT * FROM `booking` WHERE `stripe_invoice_id` = ?");

        }
    }

    /**
     * @inheritDoc
     */
    public function put(?array $data) {
        http_response_code(403);
    }

    /**
     * @inheritDoc
     */
    public function delete(?array $data) {
        http_response_code(403);
    }
}
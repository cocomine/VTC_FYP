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
            $metadata = $event->data->object->metadata;
            $pay_price = $event->data->object->amount_paid/100;
            $invoice_url = $event->data->object->hosted_invoice_url;
            $invoice_number = $event->data->object->number;
            $plan = json_decode($metadata['plan'], true);

            // 插入數據
            $stmt = $this->sqlcon->prepare("INSERT INTO Book_event (User, event_ID, pay_price, book_date, invoice_url, invoice_number) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $metadata['User'], $metadata['event_id'], $pay_price, $metadata['date'], $invoice_url, $invoice_number);
            if(!$stmt->execute()) {
                http_response_code(500);
                echo $stmt->error;
                return;
            }

            // 取得預約ID
            $stmt->prepare("SELECT LAST_INSERT_ID() AS `id`");
            if(!$stmt->execute()) {
                http_response_code(500);
                echo $stmt->error;
                return;
            }
            $book_id = $stmt->get_result()->fetch_assoc()['id'];

            // 插入預約計劃
            $stmt = $this->sqlcon->prepare("INSERT INTO Book_event_plan VALUES (?, ?, ?)");
            foreach ($plan as $item) {
                $stmt->bind_param("sss", $book_id, $item['plan'], $item['count']);
                if(!$stmt->execute()) {
                    http_response_code(500);
                    echo $stmt->error;
                    return;
                }
            }
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
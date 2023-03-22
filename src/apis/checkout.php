<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace apis;

use cocomine\IApi;
use mysqli;
use Stripe\StripeClient;

class checkout implements IApi {

    private mysqli $sqlcon;

    /**
     * @param mysqli $sqlcon
     */
    public function __construct(mysqli $sqlcon) { $this->sqlcon = $sqlcon; }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role): int {
        if(!$isAuth) return 401;
        if($role < 1) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function get() {
        http_response_code(204);
    }

    /**
     * 處理預約
     * @inheritDoc
     */
    public function post(array $data) {
        header("Content-Type: text/json");
        global $auth;

        // Check if the user has selected any plan
        if(sizeof($data['plan']) <= 0){
            http_response_code(400);
            echo json_encode([
                "code" => 400,
                "Title" => "你還沒有選擇任何計劃",
            ]);
            return;
        }

        // Check if the user has filled in the personal information
        $stmt = $this->sqlcon->prepare("SELECT COUNT(*) FROM User_detail WHERE UUID = ?");
        $stmt->bind_param("s", $auth->userdata['UUID']);
        if(!$stmt->execute()){
            http_response_code(500);
            echo json_encode([
                "code" => 500,
                'Message' => $stmt->error,
                'Title' => showText('Error_Page.something_happened')
            ]);
            return;
        }
        if($stmt->get_result()->fetch_row()[0] <= 0){
            http_response_code(400);
            echo json_encode([
                "code" => 400,
                "Title" => "你還沒有填寫個人資料",
                "Message" => "請先填寫個人資料，再預約",
            ]);
            return;
        }

        /* 這裏做輸入檢查, 但跳過 */



        /* 創建付款 */
        $stripe = new StripeClient(Cfg_stripe_test_key);
        $checkout = $stripe->checkout->sessions->create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'hkd',
                        'unit_amount' => 1000,
                        'product_data' => [
                            'name' => 'T-shirt',
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function put(array $data) {
        http_response_code(204);
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data) {
        http_response_code(204);
    }
}
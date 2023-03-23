<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace apis;

use cocomine\IApi;
use mysqli;
use Stripe\Exception\ApiErrorException;
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
        if (!$isAuth) return 401;
        if ($role < 1) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function get() {
        http_response_code(403);
    }

    /**
     * 處理預約
     * @inheritDoc
     */
    public function post(?array $data) {
        header("Content-Type: text/json");
        global $auth;

        // Check if the request is malformed
        if (!(isset($data['plan']) && is_array($data['plan']) && isset($data['eventId']) && is_numeric($data['eventId']) && isset($data['date']))) {
            http_response_code(400);
            echo json_encode([
                "code" => 400,
                "Title" => "Your request is malformed",
            ]);
            return;
        }

        // Check if the user has selected any plan
        if (sizeof($data['plan']) <= 0) {
            http_response_code(400);
            echo json_encode([
                "code" => 400,
                "Title" => "你還沒有選擇任何計劃",
            ]);
            return;
        }

        // Check if the user has filled in the personal information
        $stmt = $this->sqlcon->prepare("SELECT phone_code, phone, first_name, last_name FROM User_detail WHERE UUID = ?");
        $stmt->bind_param("s", $auth->userdata['UUID']);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode([
                "code" => 500,
                'Message' => $stmt->error,
                'Title' => showText('Error_Page.something_happened')
            ]);
            return;
        }
        $result = $stmt->get_result();
        if ($result->num_rows <= 0) {
            http_response_code(400);
            echo json_encode([
                "code" => 400,
                "Title" => "你還沒有填寫個人資料",
                "Message" => "請先填寫個人資料，再預約",
            ]);
            return;
        }
        $user_detail = $result->fetch_assoc();

        /* 取得活動名稱 */
        $stmt->prepare("SELECT name FROM Event WHERE ID = ?");
        $stmt->bind_param("i", $data['eventId']);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode([
                "code" => 500,
                'Message' => $stmt->error,
                'Title' => showText('Error_Page.something_happened')
            ]);
            return;
        }
        $event_name = $stmt->get_result()->fetch_row()[0];

        /* 取得活動計劃資料 */
        $stripe_items = [];
        $stmt->prepare("SELECT p.plan_name, p.price, s.start_time, s.end_time FROM Event_plan p, Event_schedule s 
                            WHERE s.plan = p.plan_ID AND s.Event_ID = p.Event_ID AND s.Event_ID = ? AND s.Schedule_ID =?");
        foreach ($data['plan'] as $plan) {
            $stmt->bind_param("ii", $data['eventId'], $plan['plan']);
            if (!$stmt->execute()) {
                http_response_code(500);
                echo json_encode([
                    "code" => 500,
                    'Message' => $stmt->error,
                    'Title' => showText('Error_Page.something_happened')
                ]);
                return;
            }
            $result = $stmt->get_result()->fetch_assoc();
            $stripe_items[] = [
                'price_data' => [
                    'currency' => 'hkd',
                    'unit_amount' => $result['price']*100,
                    'product_data' => [
                        'name' => $result['plan_name'],
                        'description' => $result['start_time'] . ' ~ ' . $result['end_time'],
                    ],
                ],
                'quantity' => $plan['count'],
            ];
        }

        /* 創建付款 */
        $stripe = new StripeClient(Cfg_stripe_test_key);
        try {
            $checkout = $stripe->checkout->sessions->create([
                'line_items' => $stripe_items,
                'customer_email' => $auth->userdata['Email'],
                'custom_text' => [
                    'submit' => [
                        'message' => '你正在預訂: ' . $event_name,
                    ]
                ],
                'submit_type' => 'book',
                'mode' => 'payment',
                'invoice_creation' => [
                    'enabled' => true,
                    'invoice_data' => [
                        'custom_fields' => [
                            [
                                'name' => '活動名稱',
                                'value' => $event_name
                            ],
                            [
                                'name' => '預約日期',
                                'value' => $data['date']
                            ],
                            [
                                'name' => '預約人',
                                'value' => $user_detail['first_name'] . ' ' . $user_detail['last_name']
                            ],
                            [
                                'name' => '預約人電話',
                                'value' => '+'.$user_detail['phone_code'] .' '. $user_detail['phone']
                            ],
                        ],
                        'description' => $event_name,
                        'metadata' => [
                            'event_id' => strval($data['eventId']),
                            'date' => $data['date'],
                            'plan' => json_encode($data['plan']),
                            'User' => $auth->userdata['UUID'],
                        ],
                    ]
                ],
                'success_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/success',
                'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/activity_details/' . $data['eventId']
            ]);
        } catch (ApiErrorException $e) {
            http_response_code(500);
            echo json_encode([
                "code" => 500,
                'Message' => $e->getMessage(),
                'Title' => showText('Error_Page.something_happened')
            ]);
            return;
        }

        echo json_encode([
            'code' => 200,
            'data' => [
                'url' => $checkout->url
            ],
            'Message' => '請稍等, 我們即將帶您前往付款',
            'Title' => '前往付款...'
        ]);
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
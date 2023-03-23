<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace apis;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use panel\apis\notify;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

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

        /* 處理預約 */
        if($event->type === "invoice.payment_succeeded"){
            $metadata = $event->data->object->metadata;
            $pay_price = $event->data->object->amount_paid/100;
            $invoice_url = $event->data->object->hosted_invoice_url;
            $invoice_number = $event->data->object->number;
            $plan = json_decode($metadata['plan'], true);

            // 插入預約數據
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
            $stmt->prepare("INSERT INTO Book_event_plan VALUES (?, ?, ?)");
            foreach ($plan as $item) {
                $stmt->bind_param("sss", $book_id, $item['plan'], $item['count']);
                if(!$stmt->execute()) {
                    http_response_code(500);
                    echo $stmt->error;
                    return;
                }
            }

            /* get email need data */
            //get user data
            $stmt->prepare("SELECT u.Name, u.Email, d.first_name, d.last_name, d.phone_code, d.phone 
                            FROM User u, User_detail d WHERE u.UUID = d.UUID AND u.UUID = ?");
            $stmt->bind_param('s', $metadata['User']);
            if(!$stmt->execute()) {
                http_response_code(500);
                echo $stmt->error;
                return;
            }
            $userData = $stmt->get_result()->fetch_assoc();

            //get event owner detail
            $stmt->prepare("SELECT c.phone_code , c.phone, u.Email, u.UUID FROM Event e, User_detail_collabora c, User u WHERE e.UUID = c.UUID AND e.UUID = u.UUID AND e.ID = ?");
            $stmt->bind_param('s', $metadata['event_id']);
            if(!$stmt->execute()) {
                http_response_code(500);
                echo $stmt->error;
                return;
            }
            $event_owner = $stmt->get_result()->fetch_assoc();
            
            /* 發送郵件 */
            $mail_template = file_get_contents("./stable/confirm-mail.html");

            // 替換內容
            $mail_template = str_replace("%user%", $userData['Name'], $mail_template);
            $mail_template = str_replace("%event_name%", $event->data->object->description, $mail_template);
            $mail_template = str_replace("%full_name%", $userData['first_name'] . ' ' . $userData['last_name'], $mail_template);
            $mail_template = str_replace("%phone%", '+'.$userData['phone_code'].' '.$userData['phone'], $mail_template);
            $mail_template = str_replace("%date%", $metadata['date'], $mail_template);
            $mail_template = str_replace("%pay%", $pay_price, $mail_template);
            $mail_template = str_replace("%book_url%", 'https://'.$_SERVER['HTTP_HOST'].'/reservedetail/'.$book_id, $mail_template);
            $mail_template = str_replace("%invoice_url%", $invoice_url, $mail_template);
            $mail_template = str_replace("%event_email%", $event_owner['Email'], $mail_template);
            $mail_template = str_replace("%event_phone%", '+'.$event_owner['phone_code'].' '.$event_owner['phone'], $mail_template);

            // 替換計劃
            $plan_html = '';
            $stmt->prepare("SELECT p.plan_name, s.start_time, s.end_time FROM Event_plan p, Event_schedule s WHERE p.plan_ID = s.plan AND s.Event_ID = ? AND s.Schedule_ID = ?");
            foreach ($plan as $item) {
                $stmt->bind_param('ss', $metadata['event_id'], $item['plan']);
                if(!$stmt->execute()) {
                    http_response_code(500);
                    echo $stmt->error;
                    return;
                }
                $plan_data = $stmt->get_result()->fetch_assoc();
                $plan_data['start_time'] = str_split($plan_data['start_time'], 5)[0];
                $plan_data['end_time'] = str_split($plan_data['end_time'], 5)[0];

                $plan_html .= "<tr><td>{$plan_data['plan_name']}<br>
                    <span class='text-muted'>{$plan_data['start_time']}&nbsp;&nbsp;<i class='angles-right'></i>&nbsp;&nbsp;{$plan_data['end_time']}</span></td>
                    <td>{$item['count']}</td></tr>";
            }
            $mail_template = str_replace("%plans%", $plan_html, $mail_template);

            // 替換QR code
            $mail_template = str_replace("%qr_code%", 'https://'.$_SERVER['HTTP_HOST'].'/reservedetail/'.$book_id, $mail_template);

            // 發送
            if(!SendMail($userData['Email'], $mail_template, 0, $this->sqlcon, '預約確認信')) {
                http_response_code(210);
                echo '預約成功，但發送郵件失敗。';
            };

            /* notify */
            $notify = new Notify($this->sqlcon);
            $notify_state[0] = $notify->Send_notify($metadata['User'], 'fa-solid fa-calendar-check', Notify::$Status_Success,  'https://'.$_SERVER['HTTP_HOST'].'/reservedetail/'.$book_id, '您已成功預約'.$event->data->object->description.'，請至預約詳細頁面查看詳細資訊。');
            $notify_state[1] = $notify->Send_notify($event_owner['UUID'], 'fa-solid fa-user-plus', Notify::$Status_Info,  'https://'.$_SERVER['HTTP_HOST'].'/panel/reserve/'.$metadata['event_id'].'#'.$book_id, '您的活動'.$event->data->object->description.'有新的預約，請至預約管理頁面查看詳細資訊。');
            if($notify_state[0] === false && $notify_state[1] === false) {
                http_response_code(210);
                echo '預約成功，但發送通知失敗。';
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
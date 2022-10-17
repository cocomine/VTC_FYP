<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace cocomine;

use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;

/**
 * 雙重驗證包裝套件
 * @package cocomine/TwoFAWrapper
 * @author cocomine<https://github.com/cocomine>
 * @version 1.0
 */
class TwoFAWrapper {

    private TwoFactorAuth $G2AF;
    private string $Secret;

    /**
     * 初始化物件
     * @throws TwoFactorAuthException
     */
    public function __construct() {
        $qrProvider = new BaconQrCodeProvider();
        $this->G2AF = new TwoFactorAuth(null, 6, 30, 'sha1', $qrProvider);
        $this->Secret = $this->G2AF->createSecret();
    }

    /**
     * 取得QRcode
     * @throws TwoFactorAuthException
     */
    public function getQRCode(string $name): string{
        return $this->G2AF->getQRCodeImageAsDataUri($name, $this->Secret, 250);
    }

    /**
     * 取得密匙
     * @return string
     */
    public function getSecret(): string {
        return $this->Secret;
    }

    /**
     * 驗證代碼
     * @param string $secret 密匙
     * @param string $code 代碼
     * @return bool 是否通過
     */
    public static function verifyCode(string $secret, string $code): bool {
        $G2FA = new TwoFactorAuth();
        return $G2FA->verifyCode($secret, $code);
    }
}
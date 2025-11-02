<?php

namespace App\Services\Contracts;

interface AuthServiceInterface
{
    public function requestOtp(string $telephone);
    public function verifyOtpAndLogin(string $telephone, string $otp);
    public function login(string $identifiant, string $motDePasse);
    public function logout($user);
}

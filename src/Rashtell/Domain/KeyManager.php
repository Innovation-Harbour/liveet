<?php

namespace Rashtell\Domain;

use Exception;
use \Firebase\JWT\JWT;

class KeyManager
{

    private $admink = array("k" => "urkr5i1va4ul!fy!9jzodf6x5bjf!bwb074sv2qtl46y1pl2mrmbgowpdufge9tjoubxa3987hv2851896jffji08hc87i8ck6evzfnolu1s2r!62zzo05gmcfsdbtq94bu8z5a8imfis7hi5l74punxk988iowj9xxrjdaqdhy0wmqsysiga8vy9hzkivfet1pueyxu", "slt" => 159);

    private $cshrk = array("k" => "435345645768589670!fy!9jzodf6x5bjf!7687jgh453!89678jvguvuygdyfdtfdtcghkjbefkdefhjbkfjkdsbfhsjhvdfjaeww", "slt" => 19);

    private $key = "7bfhk3b!46cxx!ii3ump54wkliusx04183ywu1a3io0hv4wiook7rm0rm8sl622e8el9ip984ajytn1n52bdxiqud780o83dbq14w1w9ucd!1t9gl2p40uibyv3kek8ekjrbxu3dz139y07obct15t5mqisoa9qxc4nl4f8!v8m68hs4zxb0iyioemum74bglryppb!c";

    public function getDigest($raw, $type = 1)
    {
        if ($type == 1) {
            $k = $this->admink;
        } else {
            $k = $this->cshrk;
        }
        $digest = substr($k["k"], 0, $k["slt"]) . $raw . substr($k["k"], $k["slt"], strlen($k["k"]));

        return  hash("sha256", $digest);;
    }

    public function validateClaim($token, $key = null)
    {
        if ($token == null) {
            return false;
        }

        try {
            $encryptedKey = hash("ripemd160", $key ?? $this->key);

            return  JWT::decode($token, $encryptedKey, array("HS256"));
        } catch (Exception $e) {
            return false;
        }
    }

    public function createClaims($claims, $key = null)
    {
        $token = array(
            "iss" => "payrollmngr",
            "aud" => "payrollmngr",
            "iat" => time(),
            "nbf" => time()
        );
        $f = array_merge($claims, $token);
        $encryptedKey = hash("ripemd160", $key ?? $this->key);

        return JWT::encode($f, $encryptedKey);
    }
}

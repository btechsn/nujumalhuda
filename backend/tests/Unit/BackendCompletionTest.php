<?php

namespace Tests\Unit;

use Modules\Core\Notifications\WebPushCipher;
use Modules\Core\Support\PhoneNumber;
use Modules\Core\Support\SimplePdf;
use Modules\Core\Support\WaveSignature;
use Modules\Mosque\Support\HijriDate;
use PHPUnit\Framework\TestCase;

class BackendCompletionTest extends TestCase
{
    public function test_senegalese_phone_becomes_e164(): void
    {
        $this->assertSame('+221771234567', PhoneNumber::e164('77 123 45 67'));
        $this->assertSame('+221771234567', PhoneNumber::e164('+221771234567'));
        $this->assertNull(PhoneNumber::e164(''));
    }

    public function test_wave_signature_rejects_a_missing_secret_match(): void
    {
        $body = '{"data":{"payment_status":"succeeded"}}';
        $timestamp = (string) time();
        $secret = 'secret-de-test';
        $signature = hash_hmac('sha256', $timestamp . $body, $secret);

        $this->assertTrue(WaveSignature::matches('t=' . $timestamp . ',v1=' . $signature, $body, $secret));
        $this->assertFalse(WaveSignature::matches('t=' . $timestamp . ',v1=mauvaise', $body, $secret));
    }

    public function test_certificate_writer_emits_a_pdf(): void
    {
        $path = sys_get_temp_dir() . '/nujum-attestation-test.pdf';
        SimplePdf::write($path, ['Nujum Al-Huda', 'Attestation']);
        $contents = file_get_contents($path);

        $this->assertIsString($contents);
        $this->assertStringStartsWith('%PDF-1.4', $contents);
        @unlink($path);
    }

    public function test_hijri_roundtrip_stays_on_the_same_gregorian_day(): void
    {
        $hijri = HijriDate::fromGregorian(2026, 9, 28);
        $back = HijriDate::toGregorian($hijri['year'], $hijri['month'], $hijri['day']);

        $this->assertSame([2026, 9, 28], [$back['year'], $back['month'], $back['day']]);
        $this->assertGreaterThanOrEqual(1, $hijri['month']);
        $this->assertLessThanOrEqual(12, $hijri['month']);
    }

    public function test_web_push_encrypts_a_payload(): void
    {
        $key = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        if ($key === false) {
            $this->markTestSkipped('La courbe prime256v1 n\'est pas disponible dans cette installation OpenSSL.');
        }
        $details = openssl_pkey_get_details($key);
        $public = "\x04"
            . str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT)
            . str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);
        $body = WebPushCipher::encrypt(
            '{"title":"Test"}',
            rtrim(strtr(base64_encode($public), '+/', '-_'), '='),
            rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '='),
        );

        $this->assertIsString($body);
        $this->assertGreaterThan(86, strlen($body));
        $this->assertNull(WebPushCipher::encrypt('x', 'abc', 'def'));
    }
}

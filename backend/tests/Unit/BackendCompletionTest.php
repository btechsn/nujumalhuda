<?php

namespace Tests\Unit;

use Modules\Core\Notifications\WebPushCipher;
use Modules\Core\Support\PhoneNumber;
use Modules\Core\Support\SimplePdf;
use Modules\Core\Support\WaveSignature;
use Modules\Mosque\Support\HijriDate;
use Modules\Mosque\Models\PrayerTime;
use Modules\Live\Models\LiveStream;
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

    public function test_effective_prayer_time_uses_manual_override_and_exposes_display_time(): void
    {
        $prayer = new PrayerTime([
            'calculated_time' => '13:05:00',
            'manual_time' => '13:15:00',
            'is_overridden' => true,
        ]);

        $this->assertSame('13:15', $prayer->getEffectiveTime());
        $this->assertSame('13:15', $prayer->display_time);

        $prayer->is_overridden = false;

        $this->assertSame('13:05', $prayer->getEffectiveTime());
    }

    public function test_live_links_are_limited_to_supported_https_social_platforms(): void
    {
        $this->assertSame('youtube', LiveStream::platformForUrl('https://youtu.be/abc123'));
        $this->assertSame('facebook', LiveStream::platformForUrl('https://www.facebook.com/example/videos/123'));
        $this->assertSame('instagram', LiveStream::platformForUrl('https://www.instagram.com/live/abc/'));
        $this->assertSame('tiktok', LiveStream::platformForUrl('https://www.tiktok.com/@nujum/live'));
        $this->assertNull(LiveStream::platformForUrl('https://youtube.com.example.org/live/abc'));
        $this->assertNull(LiveStream::platformForUrl('http://www.youtube.com/live/abc'));
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

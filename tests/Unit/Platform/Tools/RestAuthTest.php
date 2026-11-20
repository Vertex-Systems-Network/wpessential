<?php

declare(strict_types=1);

namespace {
    if (!function_exists('wp_json_encode')) {
        function wp_json_encode($value, $flags = 0) { return json_encode($value, $flags); }
    }
}

namespace WPEssential\Tests\Unit\Platform\Tools {

    use PHPUnit\Framework\TestCase;
    use WPEssential\Platform\Tools\RestAuth;

    final class RestAuthTest extends TestCase
    {
        private const SECRET = 'test-secret-test-secret-test-secret-test-secret-test-secret-1234';

        public function testSignedTokenVerifiesAndTamperingFails(): void
        {
            $token = RestAuth::sign(['iss' => 'https://a.test', 'sub' => 7, 'exp' => 2000, 'ver' => 0], self::SECRET);

            self::assertSame(7, RestAuth::verify($token, self::SECRET, 1000, 'https://a.test')['sub'] ?? null);
            self::assertNull(RestAuth::verify($token, self::SECRET, 2000, 'https://a.test'), 'expired');
            self::assertNull(RestAuth::verify($token, 'other', 1000, 'https://a.test'), 'wrong secret');
            self::assertNull(RestAuth::verify($token, self::SECRET, 1000, 'https://b.test'), 'other site');

            [$head, , $sig] = explode('.', $token);
            $forged = rtrim(strtr(base64_encode('{"iss":"https://a.test","sub":1,"exp":2000}'), '+/', '-_'), '=');
            self::assertNull(RestAuth::verify("$head.$forged.$sig", self::SECRET, 1000, 'https://a.test'), 'changed claims');

            $none = rtrim(strtr(base64_encode('{"alg":"none"}'), '+/', '-_'), '=');
            self::assertNull(RestAuth::verify("$none." . explode('.', $token)[1] . '.', self::SECRET, 1000, 'https://a.test'), 'alg none');
        }

        public function testKeyFormat(): void
        {
            self::assertSame(['0123456789ab', str_repeat('Ab3', 10) . 'Zz'], RestAuth::parseKey('wpek_0123456789ab_' . str_repeat('Ab3', 10) . 'Zz'));
            self::assertNull(RestAuth::parseKey('wpek_0123456789ab_short'));
            self::assertNull(RestAuth::parseKey('1.910da092-3fdb-4d8c-8efe-73f671851ac0.secret'));
        }
    }
}

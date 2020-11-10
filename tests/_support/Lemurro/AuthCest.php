<?php

namespace Lemurro;

use ApiTester;
use Codeception\Util\HttpCode;

class AuthCest
{
    protected string $code;
    protected string $session;

    public function _before(ApiTester $I)
    {
        if (!empty($this->session)) {
            $I->haveHttpHeader('X-SESSION-ID', $this->session);
        }
    }

    // tests
    public function getCode(ApiTester $I)
    {
        $I->sendGet('/auth/code', [
            'auth_id' => 'test@local.local',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);

        $this->code = $I->grabDataFromResponseByJsonPath('$.data.message')[0];
    }

    /**
     * @depends getCode
     */
    public function sendCode(ApiTester $I)
    {
        $I->sendPost('/auth/code', [
            'auth_id' => 'test@local.local',
            'auth_code' => $this->code,
            'device_info' => [
                'uuid' => 'WebApp',
                'platform' => 'Windows',
                'version' => 'NT 10.0',
                'manufacturer' => 'Yandex Browser',
                'model' => '20.9.3.126',
            ],
            'geoip' => [],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);

        $this->session = $I->grabDataFromResponseByJsonPath('$.data.session')[0];

        file_put_contents(codecept_output_dir('session.key'), $this->session);
    }

    /**
     * @depends sendCode
     */
    public function checkSession(ApiTester $I)
    {
        $I->sendGet('/auth/check');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"id":"' . $this->session . '"}}');
    }

    /**
     * @depends checkSession
     */
    public function getKeys(ApiTester $I)
    {
        $I->sendGet('/auth/keys');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":[]}');
    }
}

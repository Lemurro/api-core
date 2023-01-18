<?php

use ApiTester;
use Codeception\Util\HttpCode;
use Lemurro\AbstractCest;

/**
 * @psalm-suppress UndefinedMagicMethod
 * @psalm-suppress UndefinedClass
 */
class BProfileCest extends AbstractCest
{
    private string $session = '0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';

    public function getIndex(ApiTester $I): void
    {
        $I->sendGet('/profile');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);
    }

    /**
     * @depends getIndex
     */
    public function resetSession(ApiTester $I): void
    {
        $I->sendPost('/profile/session/reset', [
            'session' => $this->session,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"success":true}}');
    }
}

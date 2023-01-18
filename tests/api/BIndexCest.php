<?php

use ApiTester;
use Codeception\Util\HttpCode;

class BIndexCest
{
    /**
     * @psalm-suppress UndefinedClass
     * @psalm-suppress UndefinedMagicMethod
     */
    public function getIndex(ApiTester $I): void
    {
        $I->sendGet('/');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"version":{"android":"1","ios":"1"}}}');
    }
}

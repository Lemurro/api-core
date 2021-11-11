<?php

use Codeception\Util\HttpCode;

class BIndexCest
{
    public function getIndex(ApiTester $I)
    {
        $I->sendGet('/');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"version":{"android":"1","ios":"1"}}}');
    }
}

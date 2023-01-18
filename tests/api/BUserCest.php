<?php

use ApiTester;
use Codeception\Util\HttpCode;
use Lemurro\AbstractCest;

/**
 * @psalm-suppress UndefinedMagicMethod
 * @psalm-suppress UndefinedClass
 */
class BUserCest extends AbstractCest
{
    public function getMe(ApiTester $I): void
    {
        $I->sendGet('/user');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);
        $I->seeResponseMatchesJsonType([
            'success' => 'boolean',
            'data' => [
                'id' => 'integer|string',
                'user_id' => 'integer|string',
                'auth_id' => 'string',
                'roles' => 'array',
                'email' => 'string',
                'first_name' => 'string',
                'second_name' => 'string',
                'last_name' => 'string',
                'locked' => 'integer|string',
                'admin' => 'boolean',
                'created_at' => 'string|null',
                'updated_at' => 'string|null',
                'deleted_at' => 'string|null',
            ],
        ]);
    }
}

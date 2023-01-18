<?php

use ApiTester;
use Codeception\Util\HttpCode;
use Lemurro\AbstractCest;

/**
 * @psalm-suppress UndefinedMagicMethod
 */
class BUsersCest extends AbstractCest
{
    private string $auth_id = 'test@test.test';
    private int $record_id = 0;

    public function insertRecord(ApiTester $I): void
    {
        $I->sendPost('/users', [
            'data' => [
                'id' => '0',
                'auth_id' => $this->auth_id,
                'roles' => [
                    'guide' => ['read'],
                    'example' => ['read', 'create-update', 'delete'],
                ],
                'info_users' => [
                    'email' => $this->auth_id,
                    'last_name' => 'Test',
                    'first_name' => 'Record',
                    'second_name' => '',
                ],
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'auth_id' => $this->auth_id,
                'email' => $this->auth_id,
                'roles' => '{"guide":["read"],"example":["read","create-update","delete"]}',
                'last_name' => 'Test',
                'first_name' => 'Record',
                'second_name' => '',
                'locked' => false,
                'last_action_date' => null,
            ],
        ]);

        $this->record_id = (int) $I->grabDataFromResponseByJsonPath('$.data.id')[0];
    }

    /**
     * @depends insertRecord
     */
    public function filterByRole(ApiTester $I): void
    {
        $I->sendPost('/users/filter', [
            'data' => [
                'user_id' => '',
                'lemurro_user_fio' => '',
                'auth_id' => '',
                'lemurro_roles_type' => '1',
                'lemurro_roles' => 'example|create-update',
                'locked' => 'all',
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);
        $I->seeResponseContainsJson([
            'user_id' => $this->record_id,
        ]);
    }

    /**
     * @depends filterByRole
     */
    public function getRecord(ApiTester $I): void
    {
        $I->sendGet('/users/' . $this->record_id);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'id' => $this->record_id,
                'user_id' => $this->record_id,
            ],
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
                'created_at' => 'string|null',
                'updated_at' => 'string|null',
                'deleted_at' => 'string|null',
            ],
        ]);
    }

    /**
     * @depends getRecord
     */
    public function saveRecord(ApiTester $I): void
    {
        $I->sendPost('/users/' . $this->record_id, [
            'data' => [
                'id' => $this->record_id,
                'auth_id' => $this->auth_id,
                'roles' => [
                    'admin' => 'true',
                ],
                'info_users' => [
                    'email' => $this->auth_id,
                    'last_name' => 'Test',
                    'first_name' => 'Record',
                    'second_name' => 'Admin',
                ],
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'id' => (string) $this->record_id,
                'auth_id' => $this->auth_id,
                'roles' => '{"admin":"true"}',
                'email' => $this->auth_id,
                'last_name' => 'Test',
                'first_name' => 'Record',
                'second_name' => 'Admin',
                'locked' => false,
                'last_action_date' => null,
            ],
        ]);
    }

    /**
     * @depends saveRecord
     */
    public function filterEmpty(ApiTester $I): void
    {
        $I->sendPost('/users/filter', [
            'data' => [
                'user_id' => '',
                'lemurro_user_fio' => '',
                'auth_id' => '',
                'lemurro_roles_type' => '1',
                'lemurro_roles' => 'all',
                'locked' => 'all',
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);
        $I->seeResponseContainsJson([
            'user_id' => $this->record_id,
        ]);
    }

    /**
     * @depends saveRecord
     */
    public function filterById(ApiTester $I): void
    {
        $I->sendPost('/users/filter', [
            'data' => [
                'user_id' => $this->record_id,
                'lemurro_user_fio' => '',
                'auth_id' => '',
                'lemurro_roles_type' => '1',
                'lemurro_roles' => 'all',
                'locked' => 'all',
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'count' => 1,
                'items' => [
                    [
                        'id' => $this->record_id,
                        'user_id' => $this->record_id,
                    ]
                ],
            ],
        ]);
    }

    /**
     * @depends saveRecord
     */
    public function filterByName(ApiTester $I): void
    {
        $I->sendPost('/users/filter', [
            'data' => [
                'user_id' => '',
                'lemurro_user_fio' => 'test record',
                'auth_id' => '',
                'lemurro_roles_type' => '1',
                'lemurro_roles' => 'all',
                'locked' => 'all',
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'count' => 1,
                'items' => [
                    [
                        'id' => $this->record_id,
                        'user_id' => $this->record_id,
                    ]
                ],
            ],
        ]);
    }

    /**
     * @depends saveRecord
     */
    public function filterByAuthId(ApiTester $I): void
    {
        $I->sendPost('/users/filter', [
            'data' => [
                'user_id' => '',
                'lemurro_user_fio' => '',
                'auth_id' => $this->auth_id,
                'lemurro_roles_type' => '1',
                'lemurro_roles' => 'all',
                'locked' => 'all',
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'count' => 1,
                'items' => [
                    [
                        'id' => $this->record_id,
                        'user_id' => $this->record_id,
                    ]
                ],
            ],
        ]);
    }

    /**
     * @depends saveRecord
     */
    public function lockRecord(ApiTester $I): void
    {
        $I->sendPost('/users/' . $this->record_id . '/lock');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'id' => $this->record_id,
                'user_id' => $this->record_id,
                'locked' => true,
            ],
        ]);
    }

    /**
     * @depends lockRecord
     */
    public function filterByLocked(ApiTester $I): void
    {
        $I->sendPost('/users/filter', [
            'data' => [
                'user_id' => '',
                'lemurro_user_fio' => '',
                'auth_id' => '',
                'lemurro_roles_type' => '1',
                'lemurro_roles' => 'all',
                'locked' => '1',
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);
        $I->seeResponseContainsJson([
            'user_id' => $this->record_id,
        ]);
    }

    /**
     * @depends filterByLocked
     */
    public function unlockRecord(ApiTester $I): void
    {
        $I->sendPost('/users/' . $this->record_id . '/unlock');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'id' => $this->record_id,
                'user_id' => $this->record_id,
                'locked' => false,
            ],
        ]);
    }

    /**
     * @depends unlockRecord
     */
    public function filterByUnlocked(ApiTester $I): void
    {
        $I->sendPost('/users/filter', [
            'data' => [
                'user_id' => '',
                'lemurro_user_fio' => '',
                'auth_id' => '',
                'lemurro_roles_type' => '1',
                'lemurro_roles' => 'all',
                'locked' => '0',
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);
        $I->seeResponseContainsJson([
            'user_id' => $this->record_id,
        ]);
    }

    /**
     * @depends filterByUnlocked
     */
    public function takeOffAdmin(ApiTester $I): void
    {
        $I->sendPost('/users/' . $this->record_id, [
            'data' => [
                'id' => $this->record_id,
                'auth_id' => $this->auth_id,
                'roles' => [
                    'guide' => ['read'],
                    'example' => ['read', 'create-update', 'delete'],
                ],
                'info_users' => [
                    'email' => $this->auth_id,
                    'last_name' => 'Test',
                    'first_name' => 'Record',
                    'second_name' => '',
                ],
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'id' => (string) $this->record_id,
                'auth_id' => $this->auth_id,
                'roles' => '{"guide":["read"],"example":["read","create-update","delete"]}',
                'email' => $this->auth_id,
                'last_name' => 'Test',
                'first_name' => 'Record',
                'second_name' => '',
                'locked' => false,
                'last_action_date' => null,
            ],
        ]);
    }

    /**
     * @depends takeOffAdmin
     */
    public function loginByUser(ApiTester $I): void
    {
        $I->sendPost('/users/login_by_user', [
            'user_id' => $this->record_id,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);
        $I->seeResponseMatchesJsonType([
            'success' => 'boolean',
            'data' => [
                'session' => 'string',
            ],
        ]);
    }

    /**
     * @depends loginByUser
     */
    public function removeRecord(ApiTester $I): void
    {
        $I->sendPost('/users/' . $this->record_id . '/remove');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"id":' . $this->record_id . '}}');
    }
}

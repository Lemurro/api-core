<?php

use Codeception\Util\HttpCode;
use Lemurro\AbstractCest;

class BUsersCest extends AbstractCest
{
    private int $record_id;

    // tests
    public function insertRecord(ApiTester $I)
    {
        $I->sendPost('/users', [
            'json' => '{"id":"0","auth_id":"test@test.test","roles":{"guide":["read"],"example":["read","create-update","delete"]},"info_users":{"email":"test@test.test","last_name":"Test","first_name":"Record","second_name":""}}',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'auth_id' => 'test@test.test',
                'email' => 'test@test.test',
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
    public function filterByRole(ApiTester $I)
    {
        $I->sendPost('/users/filter', [
            'json' => '{"user_id":"","lemurro_user_fio":"","auth_id":"","lemurro_roles_type":"1","lemurro_roles":"example|create-update","locked":"all"}',
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
     * @depends filterByRole
     */
    public function getRecord(ApiTester $I)
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
                'id' => 'integer',
                'user_id' => 'integer',
                'auth_id' => 'string',
                'roles' => 'array',
                'email' => 'string',
                'first_name' => 'string',
                'second_name' => 'string',
                'last_name' => 'string',
                'locked' => 'integer',
                'created_at' => 'string|null',
                'updated_at' => 'string|null',
                'deleted_at' => 'string|null',
            ],
        ]);
    }

    /**
     * @depends getRecord
     */
    public function saveRecord(ApiTester $I)
    {
        $I->sendPost('/users/' . $this->record_id, [
            'json' => '{"id":"' . $this->record_id . '","auth_id":"test@test.test","roles":{"admin":true},"info_users":{"email":"test@test.test","last_name":"Test","first_name":"Record","second_name":"Admin"}}',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'id' => $this->record_id,
                'auth_id' => 'test@test.test',
                'roles' => '{"admin":true}',
                'email' => 'test@test.test',
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
    public function filterEmpty(ApiTester $I)
    {
        $I->sendPost('/users/filter', [
            'json' => '{"user_id":"","lemurro_user_fio":"","auth_id":"","lemurro_roles_type":"1","lemurro_roles":"all","locked":"all"}',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'count' => 3,
            ],
        ]);
    }

    /**
     * @depends saveRecord
     */
    public function filterById(ApiTester $I)
    {
        $I->sendPost('/users/filter', [
            'json' => '{"user_id":"' . $this->record_id . '","lemurro_user_fio":"","auth_id":"","lemurro_roles_type":"1","lemurro_roles":"all","locked":"all"}',
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
    public function filterByName(ApiTester $I)
    {
        $I->sendPost('/users/filter', [
            'json' => '{"user_id":"","lemurro_user_fio":"test","auth_id":"","lemurro_roles_type":"1","lemurro_roles":"all","locked":"all"}',
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
    public function filterByAuthId(ApiTester $I)
    {
        $I->sendPost('/users/filter', [
            'json' => '{"user_id":"","lemurro_user_fio":"","auth_id":"test@test.test","lemurro_roles_type":"1","lemurro_roles":"all","locked":"all"}',
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
    public function lockRecord(ApiTester $I)
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
    public function filterByLocked(ApiTester $I)
    {
        $I->sendPost('/users/filter', [
            'json' => '{"user_id":"","lemurro_user_fio":"","auth_id":"","lemurro_roles_type":"1","lemurro_roles":"all","locked":"1"}',
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
     * @depends filterByLocked
     */
    public function unlockRecord(ApiTester $I)
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
    public function filterByUnlocked(ApiTester $I)
    {
        $I->sendPost('/users/filter', [
            'json' => '{"user_id":"","lemurro_user_fio":"","auth_id":"","lemurro_roles_type":"1","lemurro_roles":"all","locked":"0"}',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'count' => 3,
            ],
        ]);
    }

    /**
     * @depends filterByUnlocked
     */
    public function loginByUser(ApiTester $I)
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
    public function removeRecord(ApiTester $I)
    {
        $I->sendPost('/users/' . $this->record_id . '/remove');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"id":"' . $this->record_id . '"}}');
    }
}
<?php

use ApiTester;
use Codeception\Util\HttpCode;
use Lemurro\AbstractCest;

/**
 * @psalm-suppress UndefinedMagicMethod
 */
class BAccessSetsCest extends AbstractCest
{
    private string $default_name = 'test access set';
    private string $modified_name = 'test access set modified';
    private int $record_id = 0;

    public function getIndex(ApiTester $I): void
    {
        $I->sendGet('/access_sets');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"count":0,"items":[]}}');
    }

    /**
     * @depends getIndex
     */
    public function insertRecord(ApiTester $I): void
    {
        $I->sendPost('/access_sets', [
            'data' => [
                'id' => '0',
                'name' => $this->default_name,
                'roles' => [
                    [
                        'guide' => ['read', 'create-update'],
                        'example' => ['read', 'delete'],
                    ],
                ],
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'name' => 'test access set',
                'roles' => [
                    'guide' => [
                        'read',
                        'create-update',
                    ],
                    'example' => [
                        'read',
                        'delete',
                    ],
                ],
            ],
        ]);

        $this->record_id = (int) $I->grabDataFromResponseByJsonPath('$.data.id')[0];
    }

    /**
     * @depends insertRecord
     */
    public function getRecord(ApiTester $I): void
    {
        $I->sendGet('/access_sets/' . $this->record_id);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'id' => $this->record_id,
                'name' => 'test access set',
                'roles' => [
                    'guide' => [
                        'read',
                        'create-update',
                    ],
                    'example' => [
                        'read',
                        'delete',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @depends getRecord
     */
    public function saveRecord(ApiTester $I): void
    {
        $I->sendPost('/access_sets/' . $this->record_id, [
            'data' => [
                'id' => $this->record_id,
                'name' => $this->modified_name,
                'roles' => [
                    [
                        'guide' => ['read', 'delete'],
                    ],
                ],
            ],
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'id' => $this->record_id,
                'name' => 'test access set modified',
                'roles' => [
                    'guide' => [
                        'read',
                        'delete',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @depends saveRecord
     */
    public function removeRecord(ApiTester $I): void
    {
        $I->sendPost('/access_sets/' . $this->record_id . '/remove');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"id":' . $this->record_id . '}}');
    }
}

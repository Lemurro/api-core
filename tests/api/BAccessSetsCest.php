<?php

use Codeception\Util\HttpCode;
use Lemurro\AbstractCest;

class BAccessSetsCest extends AbstractCest
{
    private int $record_id;

    public function getIndex(ApiTester $I)
    {
        $I->sendGet('/access_sets');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"count":0,"items":[]}}');
    }

    /**
     * @depends getIndex
     */
    public function insertRecord(ApiTester $I)
    {
        $I->sendPost('/access_sets', [
            'json' => '{"id":"0","name":"test access set","roles":{"guide":["read","create-update"],"example":["read","delete"]}}',
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
    public function getRecord(ApiTester $I)
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
    public function saveRecord(ApiTester $I)
    {
        $I->sendPost('/access_sets/' . $this->record_id, [
            'json' => '{"id":"' . $this->record_id . '","name":"test access set modified","roles":{"guide":["read","delete"]}}',
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
    public function removeRecord(ApiTester $I)
    {
        $I->sendPost('/access_sets/' . $this->record_id . '/remove');

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"data":{"id":"' . $this->record_id . '"}}');
    }
}

<?php

use Codeception\Util\HttpCode;
use Lemurro\AbstractCest;

class BFileCest extends AbstractCest
{
    private string $file_name = 'example.pdf';
    private string $temp_file_id;

    // tests
    public function upload(ApiTester $I)
    {
        $I->sendPost('/file/upload', ['inline' => 0], ['uploadfile' => codecept_data_dir($this->file_name)]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
        ]);

        $this->temp_file_id = $I->grabDataFromResponseByJsonPath('$.data.id')[0];
    }

    // /**
    //  * @depends upload
    //  */
    // public function addToStorage(ApiTester $I)
    // {

    // }

    // /**
    //  * @depends addToStorage
    //  */
    // public function downloadPrepare(ApiTester $I)
    // {
    //     $I->sendPost('/file/download/prepare', [
    //         'session' => $this->session,
    //     ]);

    //     $I->seeResponseCodeIs(HttpCode::OK); // 200
    //     $I->seeResponseIsJson();
    //     $I->seeResponseContains('{"success":true,"data":{"success":true}}');
    // }

    // /**
    //  * @depends downloadPrepare
    //  */
    // public function downloadRun(ApiTester $I)
    // {
    //     $I->sendGet('/file/download/run', [
    //         'session' => $this->session,
    //     ]);

    //     $I->seeResponseCodeIs(HttpCode::OK); // 200
    //     $I->seeResponseIsJson();
    //     $I->seeResponseContains('{"success":true,"data":{"success":true}}');
    // }
}

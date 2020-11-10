<?php

namespace Lemurro;

use ApiTester;

abstract class AbstractCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('X-SESSION-ID', file_get_contents(codecept_output_dir('session.key')));
    }
}

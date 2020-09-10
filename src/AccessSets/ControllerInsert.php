<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 10.09.2020
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\AccessSets
 */
class ControllerInsert extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    public function start(): Response
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $data = json_decode($this->request->get('json'), true, 512, JSON_THROW_ON_ERROR);

            $this->response->setData((new ActionInsert($this->dic))->run($data));
        } else {
            $this->response->setData($checker_result);
        }

        return $this->response;
    }
}

<?php

namespace Lemurro\Api\Core\Abstracts;

use Doctrine\DBAL\Connection;
use Pimple\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Промежуточный слой
 */
abstract class Middleware
{
    protected Request $request;
    protected JsonResponse $response;
    protected Container $dic;
    protected Connection $dbal;

    public function __construct(Request $request, JsonResponse $response, Container $dic)
    {
        $this->request = $request;
        $this->response = $response;
        $this->dic = $dic;
        $this->dbal = $dic['dbal'];
    }

    abstract public function execute(): JsonResponse;
}

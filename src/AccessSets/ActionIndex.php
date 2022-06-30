<?php

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Список
 */
class ActionIndex extends Action
{
    /**
     * Список
     *
     * @return array
     */
    public function run()
    {
        $sets = (array)$this->dbal->fetchAllAssociative('SELECT * FROM access_sets WHERE deleted_at IS NULL');
        $count = count($sets);

        if ($count > 0) {
            foreach ($sets as &$set) {
                if (empty($set['roles'])) {
                    $set['roles'] = [];
                } else {
                    $set['roles'] = json_decode($set['roles'], true);
                }
            }
        }

        return Response::data([
            'count' => $count,
            'items' => $sets,
        ]);
    }
}

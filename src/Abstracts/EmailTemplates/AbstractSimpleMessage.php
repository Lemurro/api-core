<?php

/**
 * Простое сообщение
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Abstracts\EmailTemplates;

/**
 * @package Lemurro\Api\Core\Abstracts\EmailTemplates
 */
abstract class AbstractSimpleMessage
{
    public static string $tpl = <<<TPL
<tr>
    <td>
        [CONTENT]
    </td>
</tr>
TPL;
}

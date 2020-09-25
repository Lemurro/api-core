<?php

/**
 * Код для входа в приложение
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Abstracts\EmailTemplates;

/**
 * @package Lemurro\Api\Core\Abstracts\EmailTemplates
 */
abstract class AbstractAuthCode
{
    public static string $tpl = <<<TPL
<tr>
    <td>
        Добро пожаловать в приложение <strong>[APP_NAME]</strong>
    </td>
</tr>
<tr>
    <td>
        Ваш код для входа: <strong>[SECRET]</strong>
    </td>
</tr>
TPL;
}

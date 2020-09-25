<?php

/**
 * Подвал для каждого письма
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Abstracts\EmailTemplates;

/**
 * @package Lemurro\Api\Core\Abstracts\EmailTemplates
 */
abstract class AbstractFooter
{
    public static string $tpl = <<<TPL
                    <tr>
                        <td>
                            <strong>С уважением,</strong><br>
                            команда Lemurro<br>
                            <a href="mailto:info@bestion.ru" style="color:#8e694d;">info@bestion.ru</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
TPL;
}

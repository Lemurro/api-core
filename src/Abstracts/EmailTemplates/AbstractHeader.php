<?php

/**
 * Шапка для каждого письма
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Abstracts\EmailTemplates;

/**
 * @package Lemurro\Api\Core\Abstracts\EmailTemplates
 */
abstract class AbstractHeader
{
    public static string $tpl = <<<TPL
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;background:#eee;">
    <tr>
        <td style="padding:20px;">
            <table border="0" cellpadding="0" cellspacing="20" align="center" style="width:600px;color:#444444;font-size:14px;font-family:arial,helvetica,sans-serif;line-height:1.5;border:5px #b28b6b solid;background:#fff;">
                <tbody>
                    <tr>
                        <td align="center"><img src="[LOGO_BASE64]" style="max-height:60px;"></td>
                    </tr>
TPL;
}

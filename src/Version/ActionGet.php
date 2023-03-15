<?php

namespace Lemurro\Api\Core\Version;

use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Получим номер версии приложения
 */
class ActionGet
{
    protected string $default = '0';

    /**
     * Получим номер версии приложения
     */
    public function run(): array
    {
        $file_path = SettingsPath::FULL_ROOT . 'version.last';

        if (!is_file($file_path) or !is_readable($file_path)) {
            return Response::data([
                'version' => $this->default,
            ]);
        }

        $content = file_get_contents($file_path);
        if ($content === false) {
            return Response::data([
                'version' => $this->default,
            ]);
        }

        $content = trim($content);
        if (empty($content)) {
            return Response::data([
                'version' => $this->default,
            ]);
        }

        return Response::data([
            'version' => $content,
        ]);
    }
}

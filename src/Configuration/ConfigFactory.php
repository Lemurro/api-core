<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 13.10.2020
 */

namespace Lemurro\Api\Core\Configuration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * @package Lemurro\Api\Core\Configuration
 */
class ConfigFactory
{
    private Processor $processor;
    private Configuration $configuration;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 13.10.2020
     */
    public function __construct()
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 13.10.2020
     */
    public function create(string $path_root): array
    {
        $default_yaml = $this->collectConfig(__DIR__ . "/../Configs");
        $override_yaml = $this->collectConfig("$path_root/app/Overrides/Configs");

        $default_values = Yaml::parse($default_yaml);
        $override_values = Yaml::parse($override_yaml);

        if (!is_countable($default_values)) {
            $default_values = [];
        }

        if (!is_countable($override_values)) {
            $override_values = [];
        }

        $config_values = [
            'config' => array_replace_recursive($default_values, $override_values),
        ];

        return $this->processor->processConfiguration($this->configuration, $config_values);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 13.10.2020
     */
    private function getFiles(string $folder): array
    {
        $files = [];

        if ($handle = opendir($folder)) {
            while (false !== ($file = readdir($handle))) {
                $filepath = "$folder/$file";

                if (
                    is_file($filepath)
                    && is_readable($filepath)
                    && 'yaml' === pathinfo($file, PATHINFO_EXTENSION)
                ) {
                    $files[] = $filepath;
                }
            }

            closedir($handle);
        }

        return $files;
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 13.10.2020
     */
    private function collectConfig(string $folder): string
    {
        $string = '';
        $files = $this->getFiles($folder);

        foreach ($files as $filepath) {
            $content = file_get_contents($filepath);

            if (!empty($content)) {
                $string .= $content . "\n";
            }
        }

        return $string;
    }
}

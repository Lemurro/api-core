<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 23.12.2020
 */

namespace Lemurro\Api\Core\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @package Lemurro\Api\Core\Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 23.12.2020
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree_builder = new TreeBuilder('config');
        $root_node = $tree_builder->getRootNode();

        $root_node->children()
            ->arrayNode('auth')->children()
                ->enumNode('type')->isRequired()->values(['email', 'phone', 'mixed'])->end()
                ->booleanNode('can_registration_users')->isRequired()->end()
                ->integerNode('attempts_per_day')->isRequired()->min(1)->end()
                ->integerNode('auth_codes_older_than_hours')->isRequired()->min(1)->end()
                ->integerNode('sessions_older_than_days')->isRequired()->min(1)->end()
                ->booleanNode('sessions_binding_to_ip')->isRequired()->end()
            ->end()->end()
            ->arrayNode('cors')->children()
                ->arrayNode('access_control_allow_origin')->isRequired()->scalarPrototype()->end()->end()
                ->booleanNode('access_control_allow_credentials')->isRequired()->end()
            ->end()->end()
            ->arrayNode('cron')->children()
                ->scalarNode('name_prefix')->isRequired()->end()
                ->scalarNode('log_file')->isRequired()->end()
                ->arrayNode('errors_emails')->scalarPrototype()->end()->end()
                ->booleanNode('file_older_tokens_enabled')->isRequired()->end()
                ->booleanNode('file_older_files_enabled')->isRequired()->end()
                ->booleanNode('data_change_logs_rotator_enabled')->isRequired()->end()
            ->end()->end()
            ->variableNode('database')->end()
            ->arrayNode('file')->children()
                ->scalarNode('path_logs')->isRequired()->end()
                ->scalarNode('path_temp')->isRequired()->end()
                ->scalarNode('path_upload')->isRequired()->end()
                ->booleanNode('full_remove')->isRequired()->end()
                ->integerNode('outdated_file_days')->isRequired()->min(1)->end()
                ->integerNode('tokens_older_than_hours')->isRequired()->min(1)->end()
                ->integerNode('allowed_size_bytes')->isRequired()->min(1)->end()
                ->scalarNode('allowed_size_formated')->isRequired()->end()
                ->scalarNode('check_file_by')->isRequired()->end()
                ->arrayNode('allowed_types')->scalarPrototype()->end()->end()
                ->arrayNode('allowed_extensions')->scalarPrototype()->end()->end()
            ->end()->end()
            ->arrayNode('general')->children()
                ->scalarNode('const_server_type_dev')->isRequired()->end()
                ->scalarNode('const_server_type_test')->isRequired()->end()
                ->scalarNode('const_server_type_prod')->isRequired()->end()
                ->scalarNode('app_name')->isRequired()->end()
                ->scalarNode('server_type')->isRequired()->end()
            ->end()->end()
            ->arrayNode('guides')->children()
                ->variableNode('classes')->end()
            ->end()->end()
            ->arrayNode('headers')
                ->isRequired()
                ->scalarPrototype()->end()
            ->end()
            ->arrayNode('mail')->children()
                ->scalarNode('app_email')->isRequired()->end()
                ->booleanNode('smtp')->isRequired()->end()
                ->scalarNode('smtp_security')->isRequired()->end()
                ->scalarNode('smtp_host')->isRequired()->end()
                ->integerNode('smtp_port')->isRequired()->min(0)->end()
                ->scalarNode('smtp_username')->isRequired()->end()
                ->scalarNode('smtp_password')->isRequired()->end()
                ->booleanNode('reserve')->isRequired()->end()
                ->scalarNode('reserve_app_email')->isRequired()->end()
                ->scalarNode('reserve_smtp_security')->isRequired()->end()
                ->scalarNode('reserve_smtp_host')->isRequired()->end()
                ->integerNode('reserve_smtp_port')->isRequired()->min(0)->end()
                ->scalarNode('reserve_smtp_username')->isRequired()->end()
                ->scalarNode('reserve_smtp_password')->isRequired()->end()
            ->end()->end()
            ->arrayNode('maintenance')->children()
                ->booleanNode('active')->isRequired()->end()
                ->scalarNode('message')->isRequired()->end()
            ->end()->end()
            ->arrayNode('sms')->children()
                ->scalarNode('smsru_api_id')->isRequired()->end()
                ->scalarNode('smsru_sender')->isRequired()->end()
            ->end()->end()
        ->end();

        return $tree_builder;
    }
}

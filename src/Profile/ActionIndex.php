<?php

namespace Lemurro\Api\Core\Profile;

use Carbon\Carbon;
use DateTime;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\LocalDateTime;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;

/**
 * Профиль пользователя
 */
class ActionIndex extends Action
{
    /**
     * @var int
     */
    private $user_id;

    /**
     * @var DateTime
     */
    private $now_datetime;

    /**
     * @var LocalDateTime
     */
    private $local_date_time;

    /**
     * ActionIndex constructor.
     *
     * @param Container $dic
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->user_id = $dic['user']['id'];
        $this->now_datetime = Carbon::now();
        $this->local_date_time = new LocalDateTime($dic);
    }

    /**
     * Профиль пользователя
     */
    public function run(): array
    {
        $sessions = $this->getSessions();

        return Response::data([
            'sessions' => $sessions,
        ]);
    }

    /**
     * Список сессий
     */
    private function getSessions(): array
    {
        $data = [];

        $sql = <<<'SQL'
            SELECT
                session,
                device_info,
                geoip,
                checked_at
            FROM sessions
            WHERE user_id = :user_id
                AND admin_entered = :admin_entered
            ORDER BY checked_at DESC
            SQL;
        $items = $this->dbal->fetchAllAssociative($sql, [
            'user_id' => $this->user_id,
            'admin_entered' => 0,
        ]);

        foreach ($items as $item) {
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $item['checked_at']);
            $diff_days = $dt->diffInDays($this->now_datetime);
            $dt_string = $dt->toDateTimeString();

            if ($diff_days === 0) {
                $date = 'сегодня';
            } elseif ($diff_days === 1) {
                $date = 'вчера';
            } else {
                $date = $this->local_date_time->get($dt_string, 'Y-m-d H:i:s', 'd.m.Y');
            }

            [$device_platform, $device_manufacturer, $device_model] = $this->getDeviceInfo($item['device_info']);
            $geo = $this->getGeoInfo($item['geoip']);

            $data[] = [
                'session' => $item['session'],
                'device_platform' => $device_platform,
                'device_manufacturer' => $device_manufacturer,
                'device_model' => $device_model,
                'geo' => $geo,
                'datetime' => $date . ' в ' . $this->local_date_time->get($dt_string, 'Y-m-d H:i:s', 'H:i'),
            ];
        }

        return $data;
    }

    /**
     * Информация об устройстве
     *
     * @param string $device_info
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 11.05.2020
     */
    private function getDeviceInfo($device_info): array
    {
        $device_info = (string) $device_info;

        $device_platform = 'Неизвестное устройство';
        $device_manufacturer = null;
        $device_model = null;

        if (empty($device_info)) {
            return [$device_platform, $device_manufacturer, $device_model];
        }

        $data = json_decode($device_info, true);

        if (!is_array($data) || empty($data)) {
            return [$device_platform, $device_manufacturer, $device_model];
        }

        if (isset($data['platform']) && !empty($data['platform'])) {
            $device_platform = $data['platform'];
        }

        if (isset($data['manufacturer']) && !empty($data['manufacturer'])) {
            $device_manufacturer = $data['manufacturer'];
        }

        if (isset($data['model']) && !empty($data['model'])) {
            $device_model = $data['model'];
        }

        return [$device_platform, $device_manufacturer, $device_model];
    }

    /**
     * Информация о геолокации
     *
     * @param string $geoip
     *
     * @return string
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 11.05.2020
     */
    private function getGeoInfo($geoip): string
    {
        $geoip = (string) $geoip;

        if (empty($geoip)) {
            return '';
        }

        $data = json_decode($geoip, true);

        if (!is_array($data) || empty($data)) {
            return '';
        }

        $geo_city = null;
        $geo_country = null;

        if (isset($data['city'], $data['city']['name_ru']) && !empty($data['city']['name_ru'])) {
            $geo_city = $data['city']['name_ru'];
        }

        if (isset($data['country'], $data['country']['name_ru']) && !empty($data['country']['name_ru'])) {
            $geo_country = $data['country']['name_ru'];
        }

        if (!empty($geo_city) && !empty($geo_country)) {
            return "$geo_city, $geo_country";
        } else if (!empty($geo_country)) {
            return $geo_country;
        }

        return '';
    }
}

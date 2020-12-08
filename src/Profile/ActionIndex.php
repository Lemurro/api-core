<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 08.12.2020
 */

namespace Lemurro\Api\Core\Profile;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\LocalDateTime;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Profile
 */
class ActionIndex extends Action
{
    private int $user_id;
    private DateTime $now_datetime;
    private LocalDateTime $local_date_time;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);

        $this->user_id = (int) $dic['user']['id'];
        $this->now_datetime = Carbon::now();
        $this->local_date_time = new LocalDateTime($dic);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run(): array
    {
        $sessions = $this->getSessions();

        return Response::data([
            'sessions' => $sessions,
        ]);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 08.12.2020
     */
    private function getSessions(): array
    {
        $data = [];

        $items = DB::table('sessions')
            ->select(
                'session',
                'device_info',
                'geoip',
                'checked_at'
            )
            ->where('user_id', '=', $this->user_id)
            ->where('admin_entered', '=', 0)
            ->orderByDesc('checked_at')
            ->get();

        if (is_countable($items)) {
            foreach ($items as $item) {
                $datetime = 'никогда';

                if (!empty($item->checked_at)) {
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $item->checked_at);
                    $diff_days = $dt->diffInDays($this->now_datetime);
                    $dt_string = $dt->toDateTimeString();

                    if ($diff_days === 0) {
                        $date = 'сегодня';
                    } elseif ($diff_days === 1) {
                        $date = 'вчера';
                    } else {
                        $date = $this->local_date_time->get($dt_string, 'Y-m-d H:i:s', 'd.m.Y');
                    }

                    $datetime = $date . ' в ' . $this->local_date_time->get($dt_string, 'Y-m-d H:i:s', 'H:i');
                }

                [$device_platform, $device_manufacturer, $device_model] = $this->getDeviceInfo($item->device_info);
                $geo = $this->getGeoInfo($item->geoip);

                $data[] = [
                    'session' => $item->session,
                    'device_platform' => $device_platform,
                    'device_manufacturer' => $device_manufacturer,
                    'device_model' => $device_model,
                    'geo' => $geo,
                    'datetime' => $datetime,
                ];
            }
        }

        return $data;
    }

    /**
     * Информация об устройстве
     *
     * @param string $device_info
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
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
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
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

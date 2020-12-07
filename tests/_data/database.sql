INSERT INTO
    users
SET
    auth_id = 'test@local.local',
    created_at = '2020-12-06 00:00:00',
    deleted_at = NULL ON DUPLICATE KEY
UPDATE
    auth_id = 'test@local.local',
    created_at = '2020-12-06 00:00:00',
    deleted_at = NULL;

INSERT INTO
    info_users
SET
    user_id = (
        SELECT
            id
        FROM
            users
        WHERE
            auth_id = 'test@local.local'
    ),
    roles = '{\"admin\": true}',
    email = 'test@local.local',
    first_name = 'Тест',
    second_name = 'Тестович',
    last_name = 'Тестов',
    created_at = '2020-12-06 00:00:00',
    deleted_at = NULL ON DUPLICATE KEY
UPDATE
    user_id = (
        SELECT
            id
        FROM
            users
        WHERE
            auth_id = 'test@local.local'
    ),
    roles = '{\"admin\": true}',
    email = 'test@local.local',
    first_name = 'Тест',
    second_name = 'Тестович',
    last_name = 'Тестов',
    created_at = '2020-12-06 00:00:00',
    deleted_at = NULL;

INSERT INTO
    sessions
SET
    SESSION = '0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
    ip = NULL,
    user_id = (
        SELECT
            id
        FROM
            users
        WHERE
            auth_id = 'test@local.local'
    ),
    device_info = '{"uuid": "WebApp", "model": "20.9.3.126", "version": "NT 10.0", "platform": "Windows", "manufacturer": "Yandex Browser"}',
    geoip = NULL,
    admin_entered = 0,
    created_at = NOW() ON DUPLICATE KEY
UPDATE
    SESSION = '0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
    ip = NULL,
    user_id = (
        SELECT
            id
        FROM
            users
        WHERE
            auth_id = 'test@local.local'
    ),
    device_info = '{"uuid": "WebApp", "model": "20.9.3.126", "version": "NT 10.0", "platform": "Windows", "manufacturer": "Yandex Browser"}',
    geoip = NULL,
    admin_entered = 0,
    created_at = NOW();
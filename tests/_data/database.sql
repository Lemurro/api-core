DELETE FROM `access_sets`;
DELETE FROM `auth_codes`;
DELETE FROM `auth_codes_lasts`;
DELETE FROM `data_change_logs`;
DELETE FROM `files`;
DELETE FROM `files_downloads`;
DELETE FROM `history_registrations`;
DELETE FROM `info_users`;
DELETE FROM `sessions`;
DELETE FROM `users`;

INSERT INTO `info_users` VALUES (1,1,'{\"admin\": true}',NULL,'для','cli-скриптов','Пользователь','2018-04-24 00:00:00','2019-04-30 07:10:38',NULL),(2,2,'{\"admin\": true}','test@local.local','Тест','Тестович','Тестов','2018-04-24 00:00:00','2020-08-18 09:37:47',NULL);
INSERT INTO `sessions` VALUES (1,'0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000', null, 2, '{"uuid": "WebApp", "model": "20.9.3.126", "version": "NT 10.0", "platform": "Windows", "manufacturer": "Yandex Browser"}', null, 0, '2020-11-06 10:08:33','2020-11-06 10:08:34');
INSERT INTO `users` VALUES (1,'lemurro@lemurro',0,NULL,NULL,NULL),(2,'test@local.local',0,'2018-04-24 00:00:00','2020-08-18 09:37:47',NULL);
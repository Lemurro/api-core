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

LOCK TABLES `info_users` WRITE;
/*!40000 ALTER TABLE `info_users` DISABLE KEYS */;
INSERT INTO `info_users` VALUES (1,1,'{\"admin\": true}',NULL,'для','cli-скриптов','Пользователь',NOW(),NOW(),NULL),(2,2,'{\"admin\": true}','test@local.local','Тест','Тестович','Тестов',NOW(),NOW(),NULL);
/*!40000 ALTER TABLE `info_users` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES (1,'0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',null,2,'{"uuid": "WebApp", "model": "20.9.3.126", "version": "NT 10.0", "platform": "Windows", "manufacturer": "Yandex Browser"}',null,0,NOW(),NOW());
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'lemurro@lemurro',0,NULL,NULL,NULL),(2,'test@local.local',0,NOW(),NOW(),NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
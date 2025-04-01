CREATE TABLE logger_entry (id int NOT NULL AUTO_INCREMENT PRIMARY KEY, created_at_ts int NOT NULL DEFAULT 0) ENGINE InnoDB DEFAULT CHARSET utf8 /* nc9h03h103h023 */;
ALTER TABLE logger_entry ADD COLUMN user_full_id varchar(255) /* c9h20c0293c03ch */;
ALTER TABLE logger_entry ADD COLUMN object_full_id varchar(255) NOT NULL /* nc8h0203hc00932hc9 */;
ALTER TABLE logger_entry ADD COLUMN serialized_object text /* ncwiej09ej23jc932 */;
ALTER TABLE logger_entry ADD COLUMN user_ip varchar(255) /* cu0230c0320c33 */;
ALTER TABLE logger_entry ADD COLUMN comment text /* c3n98ch203f0j239j */;
ALTER TABLE logger_entry ADD KEY object_full_id (object_full_id)  /* 9xbu932923h9x23 */;
ALTER TABLE logger_entry ADD KEY created_at_ts_user_full_id (created_at_ts, user_full_id)  /* 9x92032023j03j3 */;
ALTER TABLE logger_entry ADD COLUMN request_uri_with_server_name varchar(2000) DEFAULT '' /* j012j29jdh932h39 */;
ALTER TABLE logger_entry ADD COLUMN http_user_agent varchar(255) DEFAULT '' /* 9h1928hd083h931 */;
ALTER TABLE logger_entry ADD KEY user_full_id (user_full_id)  /* h8d732g32d9g3 */;
ALTER TABLE logger_entry MODIFY serialized_object mediumtext /* nc9348h0gh8490g */;
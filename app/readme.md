## 0. General

During local development the following values in the *.env* file are encouraged for a more optimal process:
- APP_ENV=local
- APP_DEBUG=true
- APP_DEBUG_QUERIES=true

With these values every response from the system will include additional information for insights and debugging.
*IMPORTANT*: In the production environment the debug setting must be set to false, otherwise the format of the responses
will be different than the expected by the consumers of the API. Additionally, the system will expose the internal workings
of the system.

For the production environment the values must be:
- APP_ENV=production
- APP_DEBUG=false
- APP_DEBUG_QUERIES=false

For any sandbox/QA/testing non-local environments the values can be:
- APP_ENV=sandbox
- APP_DEBUG=false
- APP_DEBUG_QUERIES=false

## 1. Installation

Please run the following command to generate the required keys below. The values will need to be added in the .env file.
- APP_KEY
- APP_PRIVATE_KEY
- APP_INDEX_KEY

Console command:
```
php artisan ot:generate-app-keys
```

## 2. Migrations
The following statements must be executed manually to the "OT Booking System" Databases in all environments.
The "Outbound Feeds" system does not have a dedicated Database, but it's using the Booking System Database.

Running *any* migrations from the "Outbound Feeds" system will result in the insertion of entries in the `migrations` table.
Subsequently, please *do not* run any migrations.

If a migration rollback is executed from the side of the "OT Booking System" will result in *errors* since "Outbound Feeds" migrations
do not exist in that system. The necessary migrations for the following three tables should be created in the "OT Booking System".

The SQL Create statements for new tables required for the Outbound Feeds, are the following:

*Important*: Before using the create statements, please check first if the tables already exist in the database:
```
SELECT *
FROM information_schema.tables
WHERE table_schema = 'booking_system'
AND table_name in ('feeds_api_audit_logs', 'feeds_ip_trace', 'feeds_system_logs');
```

### Table feeds_api_audit_logs
```
CREATE TABLE `feeds_api_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip_trace_id` bigint(20) unsigned DEFAULT NULL,
  `consumer_id` int(10) unsigned DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `type` enum('request','response') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `feeds_api_audit_logs_consumer_id_foreign` (`consumer_id`),
  KEY `feeds_api_audit_logs_ip_trace_id_foreign` (`ip_trace_id`),
  CONSTRAINT `feeds_api_audit_logs_consumer_id_foreign` FOREIGN KEY (`consumer_id`) REFERENCES `consumers` (`id`),
  CONSTRAINT `feeds_api_audit_logs_ip_trace_id_foreign` FOREIGN KEY (`ip_trace_id`) REFERENCES `feeds_ip_trace` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table feeds_system_logs
```
CREATE TABLE `feeds_system_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `feeds_system_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table feeds_ip_trace
```
CREATE TABLE `feeds_ip_trace` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `consumer_id` int(10) unsigned DEFAULT NULL,
  `request_method` enum('POST','GET','HEAD','PUT','DELETE','CONNECT','OPTIONS','TRACE','PATCH') COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_parameters` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `feeds_ip_trace_consumer_id_foreign` (`consumer_id`),
  CONSTRAINT `feeds_ip_trace_consumer_id_foreign` FOREIGN KEY (`consumer_id`) REFERENCES `consumers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

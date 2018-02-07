<?php

//Database
$DATABASE_URL = "localhost";
$DATABASE_REPLICAS_URL = "localhost";
$DATABASE_USERNAME = "root";
$DATABASE_PASSWORD = "root";

//CoreUser Database
$COREUSER_DATABASE_URL = $DATABASE_URL;
$COREUSER_DATABASE_USERNAME = $DATABASE_USERNAME;
$COREUSER_DATABASE_PASSWORD = $DATABASE_PASSWORD;
$COREUSER_PASSWORD_SECRET = "#$%";





//Language
$LANGUAGES = array("cn", "en", "jp");

//TimeZone
//America/Los_Angeles
//Asia/Shanghai
$TIME_ZONE = "America/Los_Angeles";

//S3
$S3_KEY = "************";
$S3_SECRET = "************";
$S3_REGION = "cn-north-1";
$S3_BUCKET = "magikidroboticlabtest";
$S3_URL = "https://$S3_BUCKET.s3.cn-north-1.amazonaws.com.cn/";

//Connect DBName
$DB_NAME = "HotPot";

//region
$REGION = "China";
//$REGION = "US";

//Redis
//$RDS_URL = "localhost";
//$REDIS_DB = "1";

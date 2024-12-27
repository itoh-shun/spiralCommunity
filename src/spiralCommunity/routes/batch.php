
<?php
$pathPrefix = defined("BASE_PATH") ? BASE_PATH : "";
require_once $pathPrefix . "framework/autoload_static.php";
require_once "spiralCommunity/autoload_static.php";

/** components */
use framework\Batch\BatchScheduler;

$batchScheduler = new BatchScheduler();

// $batchScheduler->addJob((new HogeBatch())->everyMinute());

$batchScheduler->runJobs();

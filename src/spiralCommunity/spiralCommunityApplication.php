<?php

namespace spiralCommunity;

use framework\Application;
use framework\Library\SpiralConnecter\SpiralRedis;

class spiralCommunityApplication extends Application
{
    public function __construct()
    {
        config_path("spiralCommunity/config/app");
        parent::__construct();
    }

    public function boot()
    {
        //\SpiralDB::setCache(new SpiralRedis());
    }
}

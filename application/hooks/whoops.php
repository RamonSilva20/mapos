<?php

class WhoopsHook
{
    public function bootWhoops()
    {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());
        $whoops->register();
    }
}

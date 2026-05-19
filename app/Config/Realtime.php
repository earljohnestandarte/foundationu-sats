<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Realtime extends BaseConfig
{
    public string $websocketHost = '0.0.0.0';
    public int $websocketPort = 8080;
    public string $publishHost = '127.0.0.1';
    public int $publishPort = 8081;
    public string $secret = 'local-dev-realtime-secret';
    public string $browserUrl = '';

    public function __construct()
    {
        parent::__construct();

        $this->websocketHost = (string) env('realtime.websocketHost', $this->websocketHost);
        $this->websocketPort = (int) env('realtime.websocketPort', $this->websocketPort);
        $this->publishHost = (string) env('realtime.publishHost', $this->publishHost);
        $this->publishPort = (int) env('realtime.publishPort', $this->publishPort);
        $this->secret = (string) env('realtime.secret', $this->secret);
        $this->browserUrl = trim((string) env('realtime.browserUrl', $this->browserUrl));
    }
}

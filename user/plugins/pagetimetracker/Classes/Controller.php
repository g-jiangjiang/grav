<?php

namespace Grav\Plugin\PageTimeTracker;

use Grav\Common\Grav;
use Grav\Common\Page\Page;

class Controller
{
    public function registerRoutes()
    {
        $grav = Grav::instance();
        $router = $grav['router'];

        $router->post('/time-tracker/log', function ($params) {
            $this->logTime();
        });
    }

    private function logTime()
    {
        $grav = Grav::instance();
        $request = $grav['request'];
        $response = $grav['response'];

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateData($data)) {
            $response->code = 400;
            $response->body = json_encode(['error' => 'Invalid data']);
            return;
        }

        $storage = new Storage();
        $storage->log($data);

        $response->code = 200;
        $response->body = json_encode(['success' => true]);
    }

    private function validateData($data)
    {
        if (!isset($data['url'], $data['duration'])) {
            return false;
        }

        if (!is_string($data['url']) || !is_numeric($data['duration'])) {
            return false;
        }

        if ($data['duration'] < 0 || $data['duration'] > 3600) {
            return false;
        }

        return true;
    }
}

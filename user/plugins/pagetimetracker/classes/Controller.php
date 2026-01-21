<?php

namespace Grav\Plugin\PageTimeTracker\Classes;

use Grav\Common\Grav;
use Grav\Common\Page\Page;

class Controller
{
    protected $storage;

    public function __construct()
    {
        $this->storage = new Storage();
    }

    public function logAction()
    {
        $grav = Grav::instance();
        $request = $grav['request'];

        if (!$request->isPost()) {
            $this->sendResponse(405, ['error' => 'Method not allowed']);
            return;
        }

        $data = $request->post('data');

        if (!$data) {
            $this->sendResponse(400, ['error' => 'No data provided']);
            return;
        }

        $parsedData = json_decode($data, true);

        if (!$this->validateData($parsedData)) {
            $this->sendResponse(400, ['error' => 'Invalid data format']);
            return;
        }

        try {
            $this->storage->save($parsedData);
            $this->sendResponse(200, ['success' => true]);
        } catch (\Exception $e) {
            $this->sendResponse(500, ['error' => 'Internal server error']);
        }
    }

    protected function validateData($data)
    {
        if (!is_array($data)) {
            return false;
        }

        if (!isset($data['path']) || !is_string($data['path'])) {
            return false;
        }

        if (!isset($data['duration']) || !is_numeric($data['duration']) || $data['duration'] < 0) {
            return false;
        }

        if (!isset($data['timestamp']) || !is_numeric($data['timestamp'])) {
            return false;
        }

        return true;
    }

    protected function sendResponse($statusCode, $data)
    {
        $grav = Grav::instance();
        $response = $grav['response'];

        $response->code($statusCode);
        $response->body(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        $grav->close();
    }
}
<?php

namespace Grav\Plugin\PageTimeTracker;

use Grav\Common\Grav;

class Storage
{
    private $dataDir;

    public function __construct()
    {
        $this->dataDir = Grav::instance()['locator']->findResource('user://data/pagetimetracker', true, true);
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }

    public function log($data)
    {
        $date = date('Y-m-d');
        $file = $this->dataDir . '/' . $date . '.json';

        $logData = [
            'url' => $data['url'],
            'duration' => (int)$data['duration'],
            'timestamp' => time()
        ];

        $this->appendToFile($file, $logData);
    }

    private function appendToFile($file, $data)
    {
        $lockFile = $file . '.lock';

        while (file_exists($lockFile)) {
            usleep(10000);
        }

        touch($lockFile);

        try {
            $logs = [];
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if ($content) {
                    $logs = json_decode($content, true);
                }
            }

            $logs[] = $data;
            file_put_contents($file, json_encode($logs, JSON_PRETTY_PRINT));
        } finally {
            unlink($lockFile);
        }
    }

    public function getTopPages($date, $limit = 5)
    {
        $file = $this->dataDir . '/' . $date . '.json';
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        if (!$content) {
            return [];
        }

        $logs = json_decode($content, true);
        $pageStats = [];

        foreach ($logs as $log) {
            $url = $log['url'];
            if (!isset($pageStats[$url])) {
                $pageStats[$url] = [
                    'duration' => 0,
                    'count' => 0
                ];
            }
            $pageStats[$url]['duration'] += $log['duration'];
            $pageStats[$url]['count']++;
        }

        uasort($pageStats, function ($a, $b) {
            return $b['duration'] <=> $a['duration'];
        });

        return array_slice($pageStats, 0, $limit);
    }
}

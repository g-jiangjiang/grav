<?php

namespace Grav\Plugin\PageTimeTracker\Classes;

use Grav\Common\Grav;

class Storage
{
    protected $storagePath;

    public function __construct()
    {
        $grav = Grav::instance();
        $this->storagePath = $grav['locator']->findResource('log://pagetimetracker', true, true);
        
        // 确保存储目录存在
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function save($data)
    {
        $date = date('Y-m-d');
        $filePath = $this->storagePath . DIRECTORY_SEPARATOR . $date . '.json';
        
        // 获取文件锁
        $lockFile = $filePath . '.lock';
        $lockHandle = fopen($lockFile, 'w');
        
        if ($lockHandle) {
            // 尝试获取独占锁
            if (flock($lockHandle, LOCK_EX)) {
                try {
                    // 读取现有数据
                    $existingData = $this->readFile($filePath);
                    
                    // 添加新数据
                    $existingData[] = $data;
                    
                    // 写回文件
                    $this->writeFile($filePath, $existingData);
                } finally {
                    // 释放锁
                    flock($lockHandle, LOCK_UN);
                    fclose($lockHandle);
                    
                    // 删除锁文件
                    if (file_exists($lockFile)) {
                        unlink($lockFile);
                    }
                }
            } else {
                fclose($lockHandle);
                throw new \Exception('Could not acquire file lock');
            }
        } else {
            throw new \Exception('Could not create lock file');
        }
    }

    public function getTodayData()
    {
        $date = date('Y-m-d');
        $filePath = $this->storagePath . DIRECTORY_SEPARATOR . $date . '.json';
        
        return $this->readFile($filePath);
    }

    public function getTopPages($limit = 5)
    {
        $data = $this->getTodayData();
        $pageStats = [];
        
        // 统计每个页面的总访问时长
        foreach ($data as $entry) {
            $path = $entry['path'];
            if (!isset($pageStats[$path])) {
                $pageStats[$path] = 0;
            }
            $pageStats[$path] += $entry['duration'];
        }
        
        // 按访问时长排序
        arsort($pageStats);
        
        // 取前N个
        $topPages = array_slice($pageStats, 0, $limit, true);
        
        // 转换为数组格式
        $result = [];
        foreach ($topPages as $path => $duration) {
            $result[] = [
                'path' => $path,
                'duration' => $duration
            ];
        }
        
        return $result;
    }

    protected function readFile($filePath)
    {
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            if ($content) {
                $data = json_decode($content, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        }
        
        return [];
    }

    protected function writeFile($filePath, $data)
    {
        $content = json_encode($data, JSON_PRETTY_PRINT);
        return file_put_contents($filePath, $content);
    }
}
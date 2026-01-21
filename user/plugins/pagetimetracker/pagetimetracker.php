<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Plugin\PageTimeTracker\Controller;

class PageTimeTrackerPlugin extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->enable([
                'onAdminDashboardCreated' => ['onAdminDashboardCreated', 0],
            ]);
        } else {
            $this->enable([
                'onPageInitialized' => ['onPageInitialized', 0],
            ]);
        }

        $controller = new Controller();
        $controller->registerRoutes();
    }

    public function onPageInitialized()
    {
        $this->grav['assets']->addJs('plugin://pagetimetracker/assets/tracker.js');
    }

    public function onAdminDashboardCreated($event)
    {
        $widget = [
            'id' => 'pagetimetracker',
            'title' => '今日 Top 5 访问页面',
            'content' => $this->renderWidget(),
            'size' => 'medium',
            'context' => 'dashboard'
        ];

        $event['dashboard']->addWidget($widget);
    }

    private function renderWidget()
    {
        $storage = new \Grav\Plugin\PageTimeTracker\Storage();
        $today = date('Y-m-d');
        $data = $storage->getTopPages($today, 5);

        $html = '<table class="table table-striped">';
        $html .= '<thead><tr><th>页面</th><th>访问时长 (秒)</th><th>访问次数</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($data as $page => $info) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($page) . '</td>';
            $html .= '<td>' . $info['duration'] . '</td>';
            $html .= '<td>' . $info['count'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }
}

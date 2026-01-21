<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Plugin\PageTimeTracker\Classes\Controller;

class PageTimeTrackerPlugin extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onAdminDashboard' => ['onAdminDashboard', 0],
        ];
    }

    public function onAdminDashboard($event)
    {
        $twig = $this->grav['twig'];
        $storage = new \Grav\Plugin\PageTimeTracker\Classes\Storage();
        $topPages = $storage->getTopPages(5);
        
        $widget = [
            'title' => '今日 Top 5 访问页面',
            'content' => $twig->processTemplate('partials/dashboard-widget.html.twig', ['topPages' => $topPages]),
            'size' => 'medium',
        ];
        
        $event['dashboard']->addWidget($widget);
    }

    public function onPluginsInitialized()
    {
        // 检查插件是否启用
        if (!$this->config->get('plugins.pagetimetracker.enabled')) {
            return;
        }

        // 处理API路由
        $this->handleApiRoutes();
    }

    public function onTwigTemplatePaths($event)
    {
        $event['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    public function onTwigSiteVariables($event)
    {
        $twig = $event['twig'];
        $twig->display('partials/tracker.html.twig');
    }

    protected function handleApiRoutes()
    {
        $grav = Grav::instance();
        $uri = $grav['uri'];
        $path = $uri->path();

        // 匹配API路由
        if ($path === '/time-tracker/log') {
            $controller = new Controller();
            $controller->logAction();
        }
    }

    public function getDefaultConfiguration()
    {
        return [
            'enabled' => true,
        ];
    }
}
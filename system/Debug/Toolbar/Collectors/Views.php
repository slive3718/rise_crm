<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Toolbar\Collectors;

use CodeIgniter\View\RendererInterface;

/**
 * Views collector
 */
class Views extends BaseCollector
{
    /**
     * Whether this collector has data that can
     * be displayed in the Timeline.
     *
     * @var bool
     */
    protected $hasTimeline = true;

    /**
     * Whether this collector needs to display
     * content in a tab or not.
     *
     * @var bool
     */
    protected $hasTabContent = false;

    /**
     * Whether this collector needs to display
     * a label or not.
     *
     * @var bool
     */
    protected $hasLabel = true;

    /**
     * Whether this collector has data that
     * should be shown in the Vars tab.
     *
     * @var bool
     */
    protected $hasVarData = true;

    /**
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'Views';

    /**
     * Instance of the shared Renderer service
     *
     * @var RendererInterface|null
     */
    protected $viewer;

    /**
     * Views counter
     *
     * @var array
     */
    protected $views = [];

    private function initViewer(): void
    {
        $this->viewer ??= service('renderer');
    }

    /**
     * Child classes should implement this to return the timeline data
     * formatted for correct usage.
     */
    protected function formatTimelineData(): array
    {
        $this->initViewer();

        $data = [];

        $rows = $this->viewer->getPerformanceData();

        foreach ($rows as $info) {
            $data[] = [
                'name'      => 'View: ' . $info['view'],
                'component' => 'Views',
                'start'     => $info['start'],
                'duration'  => $info['end'] - $info['start'],
            ];
        }

        return $data;
    }

    /**
     * Gets a collection of data that should be shown in the 'Vars' tab.
     * The format is an array of sections, each with their own array
     * of key/value pairs:
     *
     *  $data = [
     *      'section 1' => [
     *          'foo' => 'bar,
     *          'bar' => 'baz'
     *      ],
     *      'section 2' => [
     *          'foo' => 'bar,
     *          'bar' => 'baz'
     *      ],
     *  ];
     */
    public function getVarData(): array
    {
        $this->initViewer();

        return [
            'View Data' => $this->viewer->getData(),
        ];
    }

    /**
     * Returns a count of all views.
     */
    public function getBadgeValue(): int
    {
        $this->initViewer();

        return count($this->viewer->getPerformanceData());
    }

    /**
     * Display the icon.
     *
     * Icon from http://icons8.com - 1em package
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADeSURBVEhL7ZSxDcIwEEWNYA0YgGmgyAaJLTcUaaBzQQEVjMEabBQxAdw53zTHiThEovGTfnE/9rsoRUxhKLOmaa6Uh7X2+UvguLCzVxN1XW9x4EYHzik033Hp3X0LO+DaQG8MDQcuq6qao4qkHuMgQggLvkPLjqh00ZgFDBacMJYFkuwFlH1mshdkZ5JPJERA9JpI6xNCBESvibQ+IURA9JpI6xNCBESvibQ+IURA9DTsuHTOrVFFxixgB/eUFlU8uKJ0eDBFOu/9EvoeKnlJS2/08Tc8NOwQ8sIfMeYFjqKDjdU2sp4AAAAASUVORK5CYII=';
    }
}

<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

use Tuleap\CrossTracker\CrossTrackerReportDao;
use Tuleap\CrossTracker\REST\ResourcesInjector;
use Tuleap\CrossTracker\Widget\ProjectCrossTrackerSearch;
use Tuleap\Dashboard\Project\ProjectDashboardController;
use Tuleap\Dashboard\User\UserDashboardController;
use Tuleap\Layout\IncludeAssets;
use Tuleap\Request\CurrentPage;
use Tuleap\Tracker\ProjectDeletionEvent;

require_once 'autoload.php';
require_once 'constants.php';

class crosstrackerPlugin extends Plugin
{
    public function __construct($id)
    {
        parent::__construct($id);
        $this->setScope(self::SCOPE_SYSTEM);

        bindtextdomain('tuleap-crosstracker', __DIR__.'/../site-content');
    }

    public function getHooksAndCallbacks()
    {
        if (defined('TRACKER_BASE_URL')) {
            $this->addHook('widget_instance');
            $this->addHook('widgets');
            $this->addHook(Event::REST_RESOURCES);
            $this->addHook(Event::BURNING_PARROT_GET_STYLESHEETS);
            $this->addHook(ProjectDeletionEvent::NAME);
            $this->addHook(TRACKER_EVENT_PROJECT_CREATION_TRACKERS_REQUIRED);
        }

        return parent::getHooksAndCallbacks();
    }

    /**
     * @see Plugin::getDependencies()
     */
    public function getDependencies()
    {
        return array('tracker');
    }

    /**
     * @return Tuleap\CrossTracker\Plugin\PluginInfo
     */
    public function getPluginInfo()
    {
        if (! $this->pluginInfo) {
            $this->pluginInfo = new Tuleap\CrossTracker\Plugin\PluginInfo($this);
        }

        return $this->pluginInfo;
    }

    public function widgets(array $params)
    {
        switch ($params['owner_type']) {
            case UserDashboardController::LEGACY_DASHBOARD_TYPE:
                $params['codendi_widgets'][] = ProjectCrossTrackerSearch::NAME;
                break;

            case ProjectDashboardController::LEGACY_DASHBOARD_TYPE:
                $params['codendi_widgets'][] = ProjectCrossTrackerSearch::NAME;
                break;
        }
    }

    public function widgetInstance(array $params)
    {
        if ($params['widget'] === ProjectCrossTrackerSearch::NAME) {
            $params['instance'] = new ProjectCrossTrackerSearch();
        }
    }

    public function uninstall()
    {
        $this->removeOrphanWidgets(array(ProjectCrossTrackerSearch::NAME));
    }

    public function trackerProjectDeletion(ProjectDeletionEvent $event)
    {
        $dao = new CrossTrackerReportDao();
        $dao->deleteTrackersByGroupId($event->getProjectId());
    }

    /** @see Event::REST_RESOURCES */
    public function restResources(array $params)
    {
        $injector = new ResourcesInjector();
        $injector->populate($params['restler']);
    }

    /** @see TRACKER_EVENT_PROJECT_CREATION_TRACKERS_REQUIRED */
    public function trackerEventProjectCreationTrackersRequired(array $params)
    {
        $dao = new CrossTrackerReportDao();
        foreach ($dao->searchTrackersIdUsedByCrossTrackerByProjectId($params['project_id']) as $row) {
            $params['tracker_ids_list'][] = $row['id'];
        }
    }

    /** @see \Event::BURNING_PARROT_GET_STYLESHEETS */
    public function burningParrotGetStylesheets(array $params)
    {
        $current_page = new CurrentPage();

        if ($current_page->isDashboard()) {
            $theme_include_assets = new IncludeAssets(
                CROSSTRACKER_BASE_DIR . '/www/themes/BurningParrot/assets',
                $this->getThemePath() . '/assets'
            );
            $variant = $params['variant'];
            $params['stylesheets'][] = $theme_include_assets->getFileURL('style-' . $variant->getName() . '.css');
        }
    }
}

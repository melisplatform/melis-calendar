<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Controller\DashboardPlugins;

use MelisCore\Controller\DashboardPlugins\MelisCoreDashboardTemplatingPlugin;
use Zend\View\Model\ViewModel;


class MelisCalendarEventsPlugin extends MelisCoreDashboardTemplatingPlugin
{
    public function __construct()
    {
        $this->pluginModule = 'meliscalendar';
        parent::__construct();
    }
    
    public function calendarEvents()
    {
        $view = new ViewModel();
        $view->setTemplate('melis-calendar/dashboard/calendar-events');
        return $view;
    }
}
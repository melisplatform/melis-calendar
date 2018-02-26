<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Dashboard controller for MelisCalendar
 * 
 * Used to render dashboard components in MelisPlatform Back Office
 *
 */
class DashboardController extends AbstractActionController
{
    /**
     * Shows the calendar dashboard plugin
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function calendarAction()
    {
        $melisKey = $this->params()->fromRoute('melisKey', '');
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        return $view;
    }
    /**
     * Rending the dashboard data
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function renderDashboardDataAction()
    {
        $melisKey       = "";
        $view           = new ViewModel();
        $view->melisKey = $melisKey;

        return $view;
    }
}
<?php
namespace MelisCalendar\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * This class renders Melis CMS Dashboard
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
}
<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Form\Factory; 

use Zend\Form\Element\Text;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Melis Calendar Draggble Input Input Element
 */
class MelisCalendarDraggableInputFactory extends Text implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $formElementManager)
    { 
        $element = new Text;
        // Removing label of the form element
        $element->setLabel('');
        // Set Data Draggable to TRUE as Default value
        $element->setAttribute('data-draggable', true);
        // added melis-draggable-input for draggable input
        $element->setAttribute('class', 'form-control input-lg melis-draggable-input');
        
        return $element;
    }
}


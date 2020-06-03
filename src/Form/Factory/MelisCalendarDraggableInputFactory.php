<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Form\Factory; 

use Psr\Container\ContainerInterface;
use Laminas\Form\Element\Text;

/**
 * Melis Calendar Draggble Input Input Element
 */
class MelisCalendarDraggableInputFactory 
{
    public function __invoke(ContainerInterface $container, $requestedName)
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


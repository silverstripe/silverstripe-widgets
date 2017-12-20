<?php

namespace SilverStripe\Widgets\Model;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\SS_List;
use SilverStripe\Versioned\Versioned;

/**
 * Represents a set of widgets shown on a page.
 */
class WidgetArea extends DataObject
{
    private static $has_many = [
        "Widgets" => Widget::class
    ];

    private static $owns = [
        'Widgets',
    ];

    private static $cascade_deletes = [
        'Widgets',
    ];

    private static $extensions = [
        Versioned::class,
    ];

    private static $table_name = 'WidgetArea';

    public $template = __CLASS__;

    /**
     * Used in template instead of {@link Widgets()} to wrap each widget in its
     * controller, making it easier to access and process form logic and
     * actions stored in {@link WidgetController}.
     *
     * @return SS_List - Collection of {@link WidgetController} instances.
     */
    public function WidgetControllers()
    {
        $controllers = new ArrayList();
        $items = $this->ItemsToRender();
        if (!is_null($items)) {
            foreach ($items as $widget) {
                /** @var Widget $widget */

                /** @var Controller $controller */
                $controller = $widget->getController();

                $controller->doInit();
                $controllers->push($controller);
            }
        }
        return $controllers;
    }

    /**
     * @return HasManyList
     */
    public function Items()
    {
        return $this->Widgets();
    }

    /**
     * @return HasManyList
     */
    public function ItemsToRender()
    {
        return $this->Items()->filter('Enabled', 1);
    }

    /**
     * @return string - HTML
     */
    public function forTemplate()
    {
        return $this->renderWith($this->template);
    }

    /**
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}

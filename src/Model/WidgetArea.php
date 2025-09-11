<?php

namespace SilverStripe\Widgets\Model;

use SilverStripe\Control\Controller;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Model\List\SS_List;
use SilverStripe\Versioned\Versioned;

/**
 * Represents a set of widgets shown on a page.
 */
class WidgetArea extends DataObject
{
    private static array $has_many = [
        "Widgets" => Widget::class
    ];

    private static array $owns = [
        'Widgets',
    ];

    private static array $cascade_deletes = [
        'Widgets',
    ];

    private static array $extensions = [
        Versioned::class,
    ];

    private static string $table_name = 'WidgetArea';

    public string $template = __CLASS__;

    /**
     * Used in template instead of {@link Widgets()} to wrap each widget in its
     * controller, making it easier to access and process form logic and
     * actions stored in {@link WidgetController}.
     *
     * @return SS_List - Collection of {@link WidgetController} instances.
     */
    public function WidgetControllers(): SS_List
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
    public function Items(): HasManyList
    {
        return $this->Widgets();
    }

    /**
     * @return HasManyList
     */
    public function ItemsToRender(): HasManyList
    {
        return $this->Items()->filter('Enabled', 1);
    }

    /**
     * @return string - HTML
     */
    public function forTemplate(): string
    {
        return $this->renderWith($this->template);
    }

    /**
     *
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }
}

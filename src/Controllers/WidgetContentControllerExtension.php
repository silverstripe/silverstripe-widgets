<?php

namespace SilverStripe\Widgets\Controllers;

use SilverStripe\Core\Extension;
use SilverStripe\Widgets\Model\WidgetArea;

/**
 * Add this to ContentController to enable widgets
 *
 * @package widgets
 */
class WidgetContentControllerExtension extends Extension
{
    /**
     *
     * @var array
     */
    private static $allowed_actions = array(
        'handleWidget'
    );

    /**
     * Handles widgets attached to a page through one or more {@link WidgetArea}
     * elements.
     *
     * Iterated through each $has_one relation with a {@link WidgetArea} and
     * looks for connected widgets by their database identifier.
     *
     * Assumes URLs in the following format: <URLSegment>/widget/<Widget-ID>.
     *
     * @return RequestHandler
     */
    public function handleWidget()
    {
        $SQL_id = $this->owner->getRequest()->param('ID');
        if (!$SQL_id) {
            return false;
        }
        /** @var SiteTree $widgetOwner */
        $widgetOwner = $this->owner->data();
        while ($widgetOwner->InheritSideBar && $widgetOwner->Parent()->exists()) {
            $widgetOwner = $widgetOwner->Parent();
        }

        // find WidgetArea relations
        $widgetAreaRelations = array();
        $hasOnes = $widgetOwner->hasOne();

        if (!$hasOnes) {
            return false;
        }

        foreach ($hasOnes as $hasOneName => $hasOneClass) {
            if ($hasOneClass == WidgetArea::class || is_subclass_of($hasOneClass, WidgetArea::class)) {
                $widgetAreaRelations[] = $hasOneName;
            }
        }

        // find widget
        $widget = null;

        foreach ($widgetAreaRelations as $widgetAreaRelation) {
            if ($widget) {
                break;
            }

            $widget = $widgetOwner->$widgetAreaRelation()->Widgets()
                ->filter('ID', $SQL_id)
                ->First();
        }

        if (!$widget) {
            user_error('No widget found', E_USER_ERROR);
        }

        return $widget->getController();
    }
}

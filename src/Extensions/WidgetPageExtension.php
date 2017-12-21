<?php

namespace SilverStripe\Widgets\Extensions;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Widgets\Forms\WidgetAreaEditor;
use SilverStripe\Widgets\Model\WidgetArea;

/**
 * Adds a single {@link WidgetArea} called "SideBar" to {@link Page} classes.
 * Adjust your templates to render the resulting
 * {@link WidgetArea} as required, through the $SideBarView placeholder.
 *
 * This extension is just an example on how to use the widgets functionality,
 * feel free to create your own relationships, naming conventions, etc.
 * without using this class.
 */
class WidgetPageExtension extends DataExtension
{
    private static $db = [
        'InheritSideBar' => 'Boolean',
    ];

    private static $defaults = [
        'InheritSideBar' => true
    ];

    private static $has_one = [
        'SideBar' => WidgetArea::class,
    ];

    private static $owns = [
        'SideBar',
    ];

    private static $cascade_deletes = [
        'SideBar',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab(
            "Root.Widgets",
            new CheckboxField("InheritSideBar", _t(__CLASS__ . '.INHERITSIDEBAR', 'Inherit Sidebar From Parent'))
        );
        $fields->addFieldToTab(
            "Root.Widgets",
            new WidgetAreaEditor("SideBar")
        );
    }

    /**
     * @return WidgetArea
     */
    public function SideBarView()
    {
        if ($this->owner->InheritSideBar
            && ($parent = $this->owner->getParent())
            && $parent->hasMethod('SideBarView')
        ) {
            return $parent->SideBarView();
        } elseif ($this->owner->SideBar()->exists()) {
            return $this->owner->SideBar();
        }
    }

    public function onBeforeDuplicate($duplicatePage)
    {
        if ($this->owner->hasField('SideBarID')) {
            $sideBar = $this->owner->getComponent('SideBar');
            $duplicateWidgetArea = $sideBar->duplicate();

            foreach ($sideBar->Items() as $originalWidget) {
                $widget = $originalWidget->duplicate(false);
                $widget->ParentID = $duplicateWidgetArea->ID;
                $widget->write();
            }

            $duplicatePage->SideBarID = $duplicateWidgetArea->ID;
        }

        return $duplicatePage;
    }
}

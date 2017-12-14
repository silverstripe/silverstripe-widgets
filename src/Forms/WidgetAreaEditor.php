<?php

namespace SilverStripe\Widgets\Forms;

use Exception;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\View\Requirements;
use SilverStripe\Widgets\Model\Widget;

/**
 * Special field type for selecting and configuring widgets on a page.
 *
 * @package widgets
 */
class WidgetAreaEditor extends FormField
{
    /**
     * @param string $name
     * @param array $widgetClasses
     * @param int $maxWidgets
     */
    public function __construct($name, $widgetClasses = array(Widget::class), $maxWidgets = 0)
    {
        $this->MaxWidgets = $maxWidgets;
        $this->widgetClasses = $widgetClasses;

        parent::__construct($name);
    }

    /**
     * @param array $properties
     *
     * @return string - HTML
     */
    public function FieldHolder($properties = array())
    {
        Requirements::css('silverstripe/widgets:css/WidgetAreaEditor.css');
        Requirements::javascript('silverstripe/widgets:javascript/WidgetAreaEditor.js');

        return $this->renderWith(WidgetAreaEditor::class);
    }

    /**
     *
     * @return ArrayList
     */
    public function AvailableWidgets()
    {
        $widgets= new ArrayList();

        foreach ($this->widgetClasses as $widgetClass) {
            $classes = ClassInfo::subclassesFor($widgetClass);

            if (isset($classes[strtolower(Widget::class)])) {
                unset($classes[strtolower(Widget::class)]);
            } elseif (isset($classes[0]) && $classes[0] == Widget::class) {
                unset($classes[0]);
            }

            foreach ($classes as $class) {
                $available = Config::inst()->get($class, 'only_available_in');

                if (!empty($available) && is_array($available)) {
                    if (in_array($this->Name, $available)) {
                        $widgets->push(singleton($class));
                    }
                } else {
                    $widgets->push(singleton($class));
                }
            }
        }

        return $widgets;
    }

    /**
     * @return HasManyList
     */
    public function UsedWidgets()
    {
        // Call class_exists() to load Widget.php earlier and avoid a segfault
        class_exists(Widget::class);

        $relationName = $this->name;
        $widgets = $this->form->getRecord()->getComponent($relationName)->Items();

        return $widgets;
    }

    /**
     * @return string
     */
    public function IdxField()
    {
        return $this->id() . 'ID';
    }

    /**
     *
     * @return int
     */
    public function Value()
    {
        $relationName = $this->name;

        return $this->form->getRecord()->getComponent($relationName)->ID;
    }

    /**
     * @param DataObjectInterface $record
     * @throws Exception if no form could be retrieved
     */
    public function saveInto(DataObjectInterface $record)
    {
        $name = $this->name;
        $idName = $name . "ID";

        $widgetarea = $record->getComponent($name);
        $widgetarea->write();

        $record->$idName = $widgetarea->ID;

        $widgets = $widgetarea->Items();

        // store the field IDs and delete the missing fields
        // alternatively, we could delete all the fields and re add them
        $missingWidgets = array();

        if ($widgets) {
            foreach ($widgets as $existingWidget) {
                $missingWidgets[$existingWidget->ID] = $existingWidget;
            }
        }

        if (!$this->getForm()) {
            throw new Exception("no form");
        }

        $widgetData = $this->getForm()->getController()->getRequest()->requestVar('Widget');
        if ($widgetData && isset($widgetData[$this->getName()])) {
            $widgetAreaData = $widgetData[$this->getName()];

            foreach ($widgetAreaData as $newWidgetID => $newWidgetData) {
                // Sometimes the id is "new-1" or similar, ensure this doesn't get into the query
                if (!is_numeric($newWidgetID)) {
                    $newWidgetID = 0;
                }

                $widget = null;
                if ($newWidgetID) {
                    // \"ParentID\" = '0' is for the new page
                    $widget = Widget::get()
                        ->filter('ParentID', array(0, $record->$name()->ID))
                        ->byID($newWidgetID);

                    // check if we are updating an existing widget
                    if ($widget && isset($missingWidgets[$widget->ID])) {
                        unset($missingWidgets[$widget->ID]);
                    }
                }

                // unsantise the class name
                if (empty($newWidgetData['Type'])) {
                    $newWidgetData['Type'] = '';
                }
                $newWidgetData['Type'] = str_replace('_', '\\', $newWidgetData['Type']);

                // create a new object
                if (!$widget
                    && !empty($newWidgetData['Type'])
                    && class_exists($newWidgetData['Type'])
                    && is_subclass_of($newWidgetData['Type'], Widget::class)
                ) {
                    $widget = Injector::inst()->create($newWidgetData['Type']);
                    $widget->ID = 0;
                    $widget->ParentID = $record->$name()->ID;
                }

                if ($widget) {
                    if ($widget->ParentID == 0) {
                        $widget->ParentID = $record->$name()->ID;
                    }
                    $widget->populateFromPostData($newWidgetData);
                }
            }
        }

        // remove the fields not saved
        if ($missingWidgets) {
            foreach ($missingWidgets as $removedWidget) {
                if (isset($removedWidget) && is_numeric($removedWidget->ID)) {
                    $removedWidget->delete();
                }
            }
        }
    }
}

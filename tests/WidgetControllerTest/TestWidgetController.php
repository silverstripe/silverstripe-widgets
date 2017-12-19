<?php

namespace SilverStripe\Widgets\Tests\WidgetControllerTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextField;
use SilverStripe\Widgets\Model\WidgetController;

/**
 * @package widgets
 * @subpackage tests
 */
class TestWidgetController extends WidgetController implements TestOnly
{
    private static $allowed_actions = array(
        'Form'
    );

    public function Form()
    {
        $widgetform = new Form(
            $this,
            __FUNCTION__,
            new FieldList(
                new TextField('TestValue')
            ),
            new FieldList(
                new FormAction('doAction')
            )
        );

        return $widgetform;
    }

    public function doAction($data, $form)
    {
        return sprintf(
            'TestValue: %s\nWidget ID: %d',
            $data['TestValue'],
            $this->widget->ID
        );
    }
}

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
    private static array $allowed_actions = [
        'Form',
    ];

    public function Form()
    {
        return new Form(
            $this,
            __FUNCTION__,
            new FieldList(
                new TextField('TestValue')
            ),
            new FieldList(
                new FormAction('doAction')
            )
        );
    }

    public function doAction(array $data, mixed $form): string
    {
        return sprintf(
            'TestValue: %s\nWidget ID: %d',
            $data['TestValue'],
            $this->widget->ID
        );
    }
}

<?php
/**
 * @package widgets
 * @subpackage tests
 */
class WidgetControllerTest extends FunctionalTest
{
    protected static $fixture_file = 'WidgetControllerTest.yml';

    protected $extraDataObjects = array(
        'WidgetControllerTestPage',
        'WidgetControllerTest_Widget',
    );
    
    public function testWidgetFormRendering()
    {
        $page = $this->objFromFixture('WidgetControllerTestPage', 'page1');
        $page->publish('Stage', 'Live');
        
        $widget = $this->objFromFixture('WidgetControllerTest_Widget', 'widget1');
        
        $response = $this->get($page->URLSegment);
        
        $formAction = sprintf('%s/widget/%d/Form', $page->URLSegment, $widget->ID);
        $this->assertContains(
            $formAction,
            $response->getBody(),
            "Widget forms are rendered through WidgetArea templates"
        );
    }
    
    public function testWidgetFormSubmission()
    {
        $page = $this->objFromFixture('WidgetControllerTestPage', 'page1');
        $page->publish('Stage', 'Live');
        
        $widget = $this->objFromFixture('WidgetControllerTest_Widget', 'widget1');
        
        $response = $this->get($page->URLSegment);
        $response = $this->submitForm('Form_Form', null, array('TestValue'=>'Updated'));

        $this->assertContains(
            'TestValue: Updated',
            $response->getBody(),
            "Form values are submitted to correct widget form"
        );
        $this->assertContains(
            sprintf('Widget ID: %d', $widget->ID),
            $response->getBody(),
            "Widget form acts on correct widget, as identified in the URL"
        );
    }
}

/**
 * @package widgets
 * @subpackage tests
 */
class WidgetControllerTest_Widget extends Widget implements TestOnly
{
    private static $db = array(
        'TestValue' => 'Text'
    );
}

/**
 * @package widgets
 * @subpackage tests
 */
class WidgetControllerTest_WidgetController extends WidgetController implements TestOnly
{
    private static $allowed_actions = array(
        'Form'
    );

    public function Form()
    {
        $widgetform = new Form(
            $this,
            'Form',
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
        return sprintf('TestValue: %s\nWidget ID: %d',
            $data['TestValue'],
            $this->widget->ID
        );
    }
}

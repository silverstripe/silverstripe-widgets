<?php

namespace SilverStripe\Widgets\Tests;

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\Form;
use SilverStripe\Widgets\Tests\WidgetControllerTest\TestPage;
use SilverStripe\Widgets\Tests\WidgetControllerTest\TestWidget;

/**
 * @package widgets
 * @subpackage tests
 */
class WidgetControllerTest extends FunctionalTest
{
    protected static $fixture_file = 'WidgetControllerTest.yml';

    protected static $extra_dataobjects = [
        TestPage::class,
        TestWidget::class,
    ];

    public function testWidgetFormRendering()
    {
        $page = $this->objFromFixture(TestPage::class, 'page1');
        $page->copyVersionToStage('Stage', 'Live');

        $widget = $this->objFromFixture(TestWidget::class, 'widget1');

        $response = $this->get($page->URLSegment);

        $formAction = sprintf('%s/widget/%d/%s', $page->URLSegment, $widget->ID, 'Form');
        $this->assertContains(
            $formAction,
            $response->getBody(),
            "Widget forms are rendered through WidgetArea templates"
        );
    }

    public function testWidgetFormSubmission()
    {
        $page = $this->objFromFixture(TestPage::class, 'page1');
        $page->copyVersionToStage('Stage', 'Live');

        $widget = $this->objFromFixture(TestWidget::class, 'widget1');

        $response = $this->get($page->URLSegment);
        $response = $this->submitForm('Form_Form', null, array('TestValue' => 'Updated'));

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

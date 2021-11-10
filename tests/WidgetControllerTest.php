<?php

namespace SilverStripe\Widgets\Tests;

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Widgets\Tests\WidgetControllerTest\TestPage;
use SilverStripe\Widgets\Tests\WidgetControllerTest\TestWidget;

class WidgetControllerTest extends FunctionalTest
{
    protected static $fixture_file = 'WidgetControllerTest.yml';

    protected static $extra_dataobjects = [
        TestPage::class,
        TestWidget::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->actWithPermission('ADMIN', function () {
            $this->objFromFixture(TestPage::class, 'page1')->publishRecursive();
        });
    }

    public function testWidgetFormRendering()
    {
        $page = $this->objFromFixture(TestPage::class, 'page1');
        $widget = $this->objFromFixture(TestWidget::class, 'widget1');

        $response = $this->get($page->URLSegment);

        $formAction = sprintf('%s/widget/%d/%s', $page->URLSegment, $widget->ID, 'Form');
        $this->assertStringContainsString(
            $formAction,
            $response->getBody(),
            "Widget forms are rendered through WidgetArea templates"
        );
    }

    public function testWidgetFormSubmission()
    {
        $page = $this->objFromFixture(TestPage::class, 'page1');
        $widget = $this->objFromFixture(TestWidget::class, 'widget1');

        $this->get($page->URLSegment);
        $response = $this->submitForm('Form_Form', null, array('TestValue' => 'Updated'));

        $this->assertStringContainsString(
            'TestValue: Updated',
            $response->getBody(),
            "Form values are submitted to correct widget form"
        );
        $this->assertStringContainsString(
            sprintf('Widget ID: %d', $widget->ID),
            $response->getBody(),
            "Widget form acts on correct widget, as identified in the URL"
        );
    }
}

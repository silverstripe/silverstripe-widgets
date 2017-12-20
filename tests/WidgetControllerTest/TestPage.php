<?php

namespace SilverStripe\Widgets\Tests\WidgetControllerTest;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Widgets\Model\WidgetArea;

class TestPage extends Page implements TestOnly
{
    private static $table_name = 'TestPage';

    private static $has_one = [
        'WidgetControllerTestSidebar' => WidgetArea::class,
    ];

    private static $owns = [
        'WidgetControllerTestSidebar',
    ];
}

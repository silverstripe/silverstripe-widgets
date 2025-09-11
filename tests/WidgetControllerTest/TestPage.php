<?php

namespace SilverStripe\Widgets\Tests\WidgetControllerTest;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Widgets\Model\WidgetArea;

class TestPage extends Page implements TestOnly
{
    private static string $table_name = 'TestPage';

    private static array $has_one = [
        'WidgetControllerTestSidebar' => WidgetArea::class,
    ];

    private static array $owns = [
        'WidgetControllerTestSidebar',
    ];
}

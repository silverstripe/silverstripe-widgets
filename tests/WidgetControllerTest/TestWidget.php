<?php

namespace SilverStripe\Widgets\Tests\WidgetControllerTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Widgets\Model\Widget;

class TestWidget extends Widget implements TestOnly
{
    private static string $table_name = 'WidgetControllerTest_TestWidget';

    private static array $db = [
        'TestValue' => 'Text',
    ];
}

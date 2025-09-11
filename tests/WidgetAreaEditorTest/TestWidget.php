<?php

namespace SilverStripe\Widgets\Tests\WidgetAreaEditorTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Widgets\Model\Widget;

class TestWidget extends Widget implements TestOnly
{
    private static string $table_name = 'WidgetAreaEditorTest_TestWidget';
    private static string $cmsTitle = "Test widget";
    private static string $description = "Test widget";
}

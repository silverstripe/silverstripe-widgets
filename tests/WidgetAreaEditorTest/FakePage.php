<?php

namespace SilverStripe\Widgets\Tests\WidgetAreaEditorTest;

use Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Widgets\Model\WidgetArea;

class FakePage extends Page implements TestOnly
{
    private static string $table_name = 'FakePage';

    private static array $has_one = [
        "BottomBar" => WidgetArea::class
    ];
}

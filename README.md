# Widgets Module

[![Build Status](https://secure.travis-ci.org/silverstripe/silverstripe-widgets.png?branch=1.1)](http://travis-ci.org/silverstripe/silverstripe-widgets)

## Overview

[Widgets](http://silverstripe.org/widgets) are small pieces of functionality such as showing the latest comments or Flickr photos. They normally display on
the sidebar of your website. To check out a what a [Widget](http://silverstripe.org/widgets) can do watch the
[Widget video](http://silverstripe.com/assets/screencasts/SilverStripe-Blog-DragDrop-Widgets.swf) and try out the
[demo site](http://demo.silverstripe.org/)

## Requirements

 * SilverStripe 3.2


### Installation

Install the module through [composer](http://getcomposer.org):

```
$ composer require silverstripe/widgets
```

You'll also need to run `dev/build`.

## Documentation

See the [docs/en](docs/en/introduction.md) folder.

## Versioning

This library follows [Semver](http://semver.org). According to Semver, you will be able to upgrade to any minor or patch version of this library without any breaking changes to the public API. Semver also requires that we clearly define the public API for this library.

All methods, with `public` visibility, are part of the public API. All other methods are not part of the public API. Where possible, we'll try to keep `protected` methods backwards-compatible in minor/patch versions, but if you're overriding methods then please test your work before upgrading.

## Reporting Issues

Please [create an issue](http://github.com/silverstripe/silverstripe-widgets/issues) for any bugs you've found, or features you're missing.


## Releasing a widget

Follow the [standard procedures defined for releasing a SilverStripe module](http://doc.silverstripe.org/framework/en/3.1/topics/module-development).

Here is a composer template you can use.

You need to finish off / change:

 * name (eg: `yourorganisation/silverstripe-widget-carousel`)
 * description
 * keywords
 * license
 * author
 * installer-name (eg: `widgets_carousel`)

```json
{
    "name": "",
    "description": "",
    "type": "silverstripe-module",
    "keywords" : ["widget"],
    "require": {
        "silverstripe/framework": "3.*",
        "silverstripe/cms": "3.*"
    },
    "license": "BSD-2-Clause",
    "authors": [
        {
            "name": "",
            "email": ""
        }
    ],
    "extra" : {
        "installer-name": "widgets_"
    }
}
```

## Extending and Customizing

### Rendering a $Widget Individually

To call a single Widget in a page - without adding a widget area in the CMS for you to add / delete the widgets, you can
define a merge variable in the Page Controller and include it in the Page Template.

This example creates an RSSWidget with the SilverStripe blog feed.

	:::php
	public function SilverStripeFeed() {
		$widget = new RSSWidget();
		$widget->RssUrl = "http://feeds.feedburner.com/silverstripe-blog";
		return $widget->renderWith("WidgetHolder");
	}

To render the widget, simply include $SilverStripeFeed in your template:

	  $SilverStripeFeed


As directed in the definition of SilverStripeFeed(), the Widget will be rendered through the WidgetHolder template. This
is pre-defined at `framework/templates/WidgetHolder.ss` and simply consists of:

	:::ss
	<div class="WidgetHolder">
		<h3>$Title</h3>
		$Content
	</div>


You can override the WidgetHolder.ss and Widget.ss templates in your theme too by adding WidgetHolder and Widget
templates to `themes/myThemeName/templates/Includes/`

### Changing the title of your widget

To change the title of your widget, you need to override the Title() method. By default, this simply returns the $title
variable. For example, to set your widgets title to 'Hello World!', you could use:

**widgets_yourWidget/YourWidgetWidget.php**

	:::php
	public function Title() {
		return "Hello World!";
	}


but, you can do exactly the same by setting your $title variable.

A more common reason for overriding Title() is to allow the title to be set in the CMS. Say you had a text field in your
widget called WidgetTitle, that you wish to use as your title. If nothing is set, then you'll use your default title.
This is similar to the RSS Widget in the blog module.

	:::php
	public function Title() {
		return $this->WidgetTitle ? $this->WidgetTitle : self::$title;
	}


This returns the value inputted in the CMS, if it's set or what is in the $title variable if it isn't.

### Forms within Widgets

To implement a form inside a widget, you need to implement a custom controller for your widget to return this form. Make
sure that your controller follows the usual naming conventions, and it will be automatically picked up by the
`WidgetArea` rendering in your *Page.ss* template.

**mysite/code/MyWidget.php**

	:::php
	class MyWidget extends Widget {
	  private static $db = array(
	    'TestValue' => 'Text'
	  );
	}

	class MyWidget_Controller extends WidgetController {
	  public function MyFormName() {
	    return new Form(
	      $this,
	      'MyFormName',
	      new FieldList(
	        new TextField('TestValue')
	      ),
	      new FieldList(
	        new FormAction('doAction')
	      )
	    );
	  }

	  public function doAction($data, $form) {
	    // $this->widget points to the widget
	  }
	}


To output this form, modify your widget template.

**mysite/templates/MyWidget.ss**

	:::ss
	$Content
	$MyFormName

**Note:** The necessary controller actions are only present in subclasses of `Page_Controller`. To use
widget forms in other controller subclasses, have a look at *ContentController->handleWidget()* and
*ContentController::$url_handlers*.

## But what if I have widgets on my blog currently??

If you currently have a blog installed, the widget fields are going to double up on those pages (as the blog extends the
Page class). One way to fix this is to comment out line 30 in BlogHolder.php and remove the DB entry by running a
`http://www.mysite.com/db/build`.

**blog/code/BlogHolder.php**

	:::php
	<?php
	class BlogHolder extends Page {

	      ........
		static $has_one = array(
		//	"Sidebar" => "WidgetArea", COMMENT OUT
			'Newsletter' => 'NewsletterType'
	      .......
		public function getCMSFields() {
			$fields = parent::getCMSFields();
			$fields->removeFieldFromTab("Root.Content","Content");
		//	$fields->addFieldToTab("Root.Widgets", new WidgetAreaEditor("Sidebar")); COMMENT OUT

		........


Then you can use the Widget area you defined on Page.php

## Contributing

### Translations

Translations of the natural language strings are managed through a
third party translation interface, transifex.com.
Newly added strings will be periodically uploaded there for translation,
and any new translations will be merged back to the project source code.

Please use [https://www.transifex.com/projects/p/silverstripe-widgets/](https://www.transifex.com/projects/p/silverstripe-widgets/) to contribute translations,
rather than sending pull requests with YAML files.

See the ["i18n" topic](http://doc.silverstripe.org/framework/en/trunk/topics/i18n) on doc.silverstripe.org for more details.
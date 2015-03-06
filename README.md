# Widgets Module

[![Build Status](https://secure.travis-ci.org/silverstripe/silverstripe-widgets.png?branch=master)](http://travis-ci.org/silverstripe/silverstripe-widgets)

## Introduction

[Widgets](http://silverstripe.org/widgets) are small pieces of functionality such as showing the latest comments or Flickr photos. They normally display on
the sidebar of your website. To check out a what a [Widget](http://silverstripe.org/widgets) can do watch the
[Widget video](http://silverstripe.com/assets/screencasts/SilverStripe-Blog-DragDrop-Widgets.swf) and try out the
[demo site](http://demo.silverstripe.org/)

## Requirements

 * SilverStripe 3.1

### Installation

Install the module through [composer](http://getcomposer.org):

	composer require silverstripe/widgets

Widgets are essentially database relations to other models, mostly page types.
By default, they're not added to any of your own models. The easiest and most common
way to get started would be to create a single collection of widgets under the 
name "SideBar" on your `Page` class. This is handled by an extension which you
can enable through your `config.yml`:

	:::yml
	Page:
	  extensions:
	    - WidgetPageExtension

Run a `dev/build`, and adjust your templates to include the resulting sidebar view.
The placeholder is called `$SideBarView`, and loops through all widgets assigned
to the current page.

Alternatively, you can add one or more widget collections to your own page types.
Here's an example on how to just add widgets to a `MyPage` type, and call it
`MyWidgetArea` instead.

### Installing a widget

By following the "Packaging" rules below, widgets are easily installed. This example uses the Blog module which by default has widgets already enabled.
 
* Install the [blog module](http://www.silverstripe.org/blog-module/).
* Download the widget and unzip to the main folder of your SilverStripe website, e.g. to `/widget_<widget-name>/`. The folder
will contain a few files, which generally won't need editing or reading.
* Run `http://my-website.com/dev/build`
* Login to the CMS and go to the 'Blog' page. Choose the "widgets" tab and click the new widget to activate it.
* Your blog will now have the widget shown


### Adding widgets to other pages

You have to do a couple things to get a Widget to work on a page.

* Install the Widgets Module, see above.
* Add a WidgetArea field to your Page. 
* Add a new tab to the CMS with a WidgetAreaEditor field for managing the widgets. 
e.g.

**mysite/code/Page.php**

	class Page extends SiteTree {
	...
	    private static $has_one = array(
				"MyWidgetArea" => "WidgetArea",
	    );
		
	  public function getCMSFields() {
			$fields = parent::getCMSFields();
			$fields->addFieldToTab("Root.Widgets", new WidgetAreaEditor("MyWidgetArea"));
			return $fields;
	  }
	}

In this case, you need to alter your templates to include the `$MyWidgetArea` placeholder.

## Writing your own widgets

To create a Widget you need at least three files - a php file containing the class, a template file of the same name and
a config file called *_config.php* (if you dont need any config options for the widget to work then you can make it
blank). Each widget should be in its own folder like widgets_widgetName/

After installing or creating a new widget, **make sure to run db/build?flush=1** at the end of the URL, *before*
attempting to use it.

The class should extend the Widget class, and must specify three config variables:

* `title`: The title that will appear in the rendered widget (eg Photos). This can be customised by the CMS admin
* `cmsTitle`: a more descriptive title that will appear in the cms editor (eg Flickr Photos)
* `description`: a short description that will appear in the cms editor (eg This widget shows photos from
Flickr). The class may also specify functions to be used in the template like a page type can.

If a Widget has configurable options, then it can specify a number of database fields to store these options in via the
static $db array, and also specify a getCMSFields function that returns a !FieldList, much the same way as a page type
does.

An example widget is below:

**FlickrWidget.php**

	:::php
	<?php
	class FlickrWidget extends Widget {
		private static $db = array(
			"User" => "Varchar",
			"Photoset" => "Varchar",
			"Tags" => "Varchar",
			"NumberToShow" => "Int"
		);
		
	
		private static $defaults = array(
			"NumberToShow" => 8
		);
	
		private static $title = "Photos";
		private static $cmsTitle = "Flickr Photos";
		private static $description = "Shows flickr photos.";
		
		public function Photos() {
			Requirements::javascript(THIRDPARTY_DIR . "/prototype/prototype.js");
			Requirements::javascript(THIRDPARTY_DIR . "/scriptaculous/effects.js");
			Requirements::javascript("mashups/javascript/lightbox.js");
			Requirements::css("mashups/css/lightbox.css");
			
			$flickr = new FlickrService();
			if($this->Photoset == "") {
				$photos = $flickr->getPhotos($this->Tags, $this->User, $this->NumberToShow, 1);
			} else {
				$photos = $flickr->getPhotoSet($this->Photoset, $this->User, $this->NumberToShow, 1);
			}
			
			$output = new ArrayList();
			foreach($photos->PhotoItems as $photo) {
				$output->push(new ArrayData(array(
					"Title" => $photo->title,
					"Link" => "http://farm1.static.flickr.com/" . $photo->image_path .".jpg",
					"Image" => "http://farm1.static.flickr.com/" .$photo->image_path. "_s.jpg"
				)));
			}
			return $output;
		}
	
		public function getCMSFields() {
			return new FieldList(
				new TextField("User", "User"),
				new TextField("PhotoSet", "Photo Set"),
				new TextField("Tags", "Tags"),
				new NumericField("NumberToShow", "Number to Show")
			);
		}
	}


**FlickrWidget.ss**

	:::ss
	<% control Photos %>
		<a href="$Link" rel="lightbox" title="$Title"><img src="$Image" alt="$Title" /></a>
	<% end_control %>


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
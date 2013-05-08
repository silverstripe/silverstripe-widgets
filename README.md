# Widgets Module

[![Build Status](https://secure.travis-ci.org/silverstripe/silverstripe-widgets.png?branch=master)](http://travis-ci.org/silverstripe/silverstripe-widgets)

## Introduction

[Widgets](http://silverstripe.org/widgets) are small pieces of functionality such as showing the latest Comments or Flickr Photos. They normally display on
the sidebar of your website. To check out a what a [Widget](http://silverstripe.org/widgets) can do watch the
[Widget video](http://silverstripe.com/assets/screencasts/SilverStripe-Blog-DragDrop-Widgets.swf) and try out the
[demo site](http://demo.silverstripe.org/)

## Requirements

 * SilverStripe 3.1

## How to Use A Widget

### Downloading and Contributing Widgets

*  To download widgets visit [Widgets section](http://silverstripe.org/widgets)
*  Upload widgets you want to share to
[http://silverstripe.org/widgets/manage/add](http://silverstripe.org/widgets/manage/add). Make sure you read the
packaging instructions at the bottom of the page about how to make your widget package.


### Installing the Widgets Module

Download and unzip the [Widgets Module](http://www.silverstripe.org/widgets-module/) to the main folder of your website and ensure the folder is named `widgets`. 


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
	...
	}


* Then in your Template you need to call $MyWidgetArea wherever you want to render the widget

e.g. using the simple theme, add the `$MyWidgetArea` variable above the closing `</aside>` 

**themes/simple/templates/Includes/Sidebar.ss**

	<aside>
		<% if Menu(2) %>
		...	
		<% end_if %>
		$MyWidgetArea
	</aside>


## Writing your own widgets

To create a Widget you need at least three files - a php file containing the class, a template file of the same name and
a config file called *_config.php* (if you dont need any config options for the widget to work then you can make it
blank). Each widget should be in its own folder like widgets_widgetName/

After installing or creating a new widget, **make sure to run db/build?flush=1** at the end of the URL, *before*
attempting to use it.

The class should extend the Widget class, and must specify three static variables - $title, the title that will appear
in the rendered widget (eg Photos), $cmsTitle, a more descriptive title that will appear in the cms editor (eg Flickr
Photos), and $description, a short description that will appear in the cms editor (eg This widget shows photos from
Flickr). The class may also specify functions to be used in the template like a page type can.

If a Widget has configurable options, then it can specify a number of database fields to store these options in via the
static $db array, and also specify a getCMSFields function that returns a !FieldList, much the same way as a page type
does.

An example widget is below:

**FlickrWidget.php**

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
			
			$output = new DataObjectSet();
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
	
	?>


**FlickrWidget.ss**

	<% control Photos %>
		<a href="$Link" rel="lightbox" title="$Title"><img src="$Image" alt="$Title" /></a>
	<% end_control %>


## Extending and Customizing

### Rendering a $Widget Individually

To call a single Widget in a page - without adding a widget area in the CMS for you to add / delete the widgets, you can
define a merge variable in the Page Controller and include it in the Page Template. 

This example creates an RSSWidget with the SilverStripe blog feed.

	<?php
		public function SilverStripeFeed() {
			$widget = new RSSWidget();
			$widget->RssUrl = "http://feeds.feedburner.com/silverstripe-blog";
			return $widget->renderWith("WidgetHolder");
		}
	?>


To render the widget, simply include $SilverStripeFeed in your template:

	  $SilverStripeFeed


As directed in the definition of SilverStripeFeed(), the Widget will be rendered through the WidgetHolder template. This
is pre-defined at `framework/templates/WidgetHolder.ss` and simply consists of: 

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

	public function Title() {
		return "Hello World!";
	}


but, you can do exactly the same by setting your $title variable.

A more common reason for overriding Title() is to allow the title to be set in the CMS. Say you had a text field in your
widget called WidgetTitle, that you wish to use as your title. If nothing is set, then you'll use your default title.
This is similar to the RSS Widget in the blog module.

	public function Title() {
		return $this->WidgetTitle ? $this->WidgetTitle : self::$title;
	}


This returns the value inputted in the CMS, if it's set or what is in the $title variable if it isn't.

### Forms within Widgets

To implement a form inside a widget, you need to implement a custom controller for your widget to return this form. Make
sure that your controller follows the usual naming conventions, and it will be automatically picked up by the
`WidgetArea` rendering in your *Page.ss* template.

**mysite/code/MyWidget.php**

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

## Releasing Your Widget

### Packaging

For a widget to be put in our official widget database they must follow this convention - If the name of your widget was
"YourName" then:

#### File Structure for your widget

You should have a folder called widget_YourName in the top level (the one with framework, cms..) with all your files. See
the example below. Your widget **MUST** have at least 1 Template file, 1 PHP file, the README File
[(Example)](http://open.silverstripe.com/browser/modules/widgets/twitter/trunk/README)and an _config.php file for
configuration. If you dont need any config options for the widget to work then you still need an _config.php by you can
make it blank

The decision over whether to configure a widget in _config.php or in the CMS is important:

*  If the setting is the kind of thing that a website author, familiar with common business apps such as Word and
Outlook, would understand - then make it configurable in the CMS.
*  If the setting is the kind of thing that the person setting up the website - doing the design and/or development -
would understand, then make it configurable in the _config.php file.

This way, the CMS remains an application designed for content authors, and not developers. 

*widget_name/_config.php*

	<?php /*  */ ?>


**Example Widget Structure**

![](_images/widget_demo.gif)


#### How to make the Package

*  Make a tar.gz file called widgets_YourName-0.1.tar.gz (where 0.1 is the version number).
     * Ensure when you "unzip" the compressed file it has everything the "widgets_YourName" folder with everything inside
it.
*  If made official, it will be given these locations at silverstripe.com:
    * SVN location: http://svn.silverstripe.com/open/modules/widgets/flickr/trunk
    * Official download: http://www.silverstripe.com/assets/downloads/widgets/widgets_flickr-0.1.1.tar.gz

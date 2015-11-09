# Getting Started

The easiest way to install is by using [Composer](https://getcomposer.org):

```sh
$ composer require silverstripe/widgets
```

You'll also need to run `dev/build`.

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


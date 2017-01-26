# Contributing

Contributions are welcome! Create an issue, explaining a bug or proposal. Submit pull requests if you feel brave. Speak to me on [Twitter](https://twitter.com/assertchris).

## Releasing a widget

Follow the [standard procedures defined for releasing a SilverStripe module](https://docs.silverstripe.org/en/4/developer_guides/extending/how_tos/publish_a_module).

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
    },
    "autoload": {
        "psr-4": {
            "Yourname\\MyWidget\\": "src/"
        }
    }
}
```

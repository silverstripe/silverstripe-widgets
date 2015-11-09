# Contributing

Contributions are welcome! Create an issue, explaining a bug or proposal. Submit pull requests if you feel brave. Speak to me on [Twitter](https://twitter.com/assertchris).

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
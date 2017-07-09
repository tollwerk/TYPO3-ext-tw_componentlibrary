# tw_componentlibrary

> Component library features for your TYPO3 project

About
-----

The purpose of this TYPO3 extension is to

1. encourage and support the development of self-contained, re-usable functional and design modules ("**components**") in your TYPO3 project and
2. expose these components via an API so that **component library, testing and styleguide systems** like [Fractal](http://fractal.build) can [extract and render](https://github.com/tollwerk/fractal-typo3) your components individually and independently of your TYPO3 frontend.

The extension distiguishes 3 main types of components that can be exposed to external systems:

* **TypoScript component**: Requires a [TypoScript](https://docs.typo3.org/typo3cms/TyposcriptReference/) path with an object definition to render (e.g. `lib.menu` defined as `HMENU`).
* **Fluid template component**: Requires a [Fluid template file](https://github.com/TYPO3/Fluid) (e.g. an Extbase / Fluid partial) and an optional set of rendering parameters / variables.
* **Extbase controller component**: Requires an [Extbase controller](https://docs.typo3.org/typo3cms/ExtbaseGuide/Extbase/Step3Documentation/ActionController.html), a controller action to call and possibly a list of parameters to pass to the controller action. 

The extension **doesn't impose any restrictions regarding your TypoScript, Fluid template or directory layout** except that every component you want to expose must be individually addressable. That is, you cannot expose e.g. just a part of a rendered Fluid template as a component. In this case, you need to outsource the desired part into a partial file of its own and use that one instead.

The extension adds an Extbase CLI command that lets you **discover the declared components in JSON format**:
 
```bash
typo3/cli_dispatch.phpsh extbase component:discover
```

Use these information however it makes sense for you. For instance, the [Fractal-TYPO3 bridge](https://github.com/tollwerk/fractal-typo3) builds and displays a component library out of the JSON data.

Usage
-----

### Installation

Install the extension into your composer mode TYPO3 installation:

```bash
cd /path/to/site/root
composer require tollwerk/tw-componentlibrary
```

Alternatively, [download the latest source package](https://github.com/tollwerk/TYPO3-ext-tw_componentlibrary/releases) and extract its contents to `typo3conf/ext/tw_componentlibrary` under your TYPO3 root directory.

Use the extension manager to enable the "**tollwerk TYPO3 Component Library**".


### Declaring components

Follow these steps to declare and expose components for your TYPO3 instance:

1. Create and install an empty TYPO3 extension (or pick an existing one) that is going to hold your component definitions. You can have multiple of such **component provider extensions**. If you're using and maintaining custom extensions anyway, I recommend using these for providing components on an per-extension basis.
2. Create a `Components` directory inside the provider extension's root directory. In case you're running [TYPO3 in composer mode](https://wiki.typo3.org/Composer), make sure this directory is properly mapped to the `Component` namespace. You might have to add something like this to your main `composer.json` file (replace vendor, extension key and paths with appropriate local values):
    ```json
    {
        "autoload": {
            "psr-4": {
                "Vendor\\ExtKey\\Component\\": "web/typo3conf/ext/ext_key/Components/"
            }
        }
    }
    ```
3. Especially if you're going to have a lot of components it's advisable to organise them in a hierarchical structure. Create a suitable directory layout below your `Components` directory to reflect this hierarchy. Use [UpperCamelCase](https://en.wikipedia.org/wiki/Camel_case) directory names without spaces, underscores and any other special characters — external systems can use the word boundaries for inserting spaces when creating e.g. a navigation tree. Your directory layout could look something like this:
    ```bash
    ext_key
    `-- Components
        |-- Composite
        |   `-- Form
        `-- Generic
            |-- Form
            `-- Typography
    ```
4. Start creating **component declarations** by creating class files in your directory layout. Each file must declare exactly **one class extending one of the main component type base classes** (see below). The file and class names must be identical, should be in [UpperCamelCase](https://en.wikipedia.org/wiki/Camel_case) and must end with the suffix `…Component` (i.e. `…Component.php` for the file name). The part before `…Component` will be used as the component name later on. In addition to a base version of a component you may provide **variants** of that very component by adding an underscore and an appendage to their file and class names. System like Fractal may use this for a grouped display of component variants:
    ```bash
    ext_key
    `-- Components
        `-- Generic
            `-- Form
                |-- ButtonComponent.php
                |-- Button_IconLeftComponent.php
                |-- Button_IconRightComponent.php
                |-- Button_LinkComponent.php
                |-- Button_LinkIconLeftComponent.php
                `-- Button_LinkIconRightComponent.php
    ```
5. A component declaration class must implement the `configure()` method to specify the individual component properties. While each component type brings its specific set of properties there are also some instructions [common to all component types](#common-component-instructions).     

#### TypoScript components

#### Fluid template component

#### Extbase controller component

#### Common component instructions

### Extracting components

License
-------

Copyright © 2017 [Joschi Kuphal][author-url] / joschi@kuphal.net. Licensed under the terms of the  [GPL v2](LICENSE.txt) license.

[author-url]: https://jkphl.is

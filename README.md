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
* **Extbase plugin component**: Requires an [Extbase controller](https://docs.typo3.org/typo3cms/ExtbaseGuide/Extbase/Step3Documentation/ActionController.html), a controller action to call and possibly a list of parameters to pass to the controller action. 

The extension **doesn't impose any restrictions regarding your TypoScript, Fluid template or directory layout** except that every component you want to expose must be individually addressable. That is, you cannot expose e.g. just a part of a rendered Fluid template as a component. In this case, you need to outsource the desired part into a partial file of its own and use that one instead.

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
5. A component declaration class must implement the `configure()` method to specify the component properties. While each component type brings its specific set of properties (see below) the majority of instructions is [common to all component types](#common-properties).     

#### TypoScript component

Use the `setTypoScriptKey()` method to specify the TypoScript object that should be rendered for creating the component output. The key will be evaluated for the page ID specified by the `$page` property (see [common properties](#common-properties)).

```php
<?php

namespace Vendor\ExtKey\Component;

use Tollwerk\TwComponentlibrary\Component\TypoScriptComponent;

/**
 * Example TypoScript component
 */
class ExampleTypoScriptComponent extends TypoScriptComponent
{
    /**
     * Configure the component
     */
    protected function configure()
    {
        $this->setTypoScriptKey('lib.example');
    }
}
```

#### Fluid template component

Use the `setTemplate()` method to specify the Fluid template file to be rendered for creating the component output. Use `setParameter()` to specify rendering parameters and their values (works just like `$this->view->assign('param', 'value')` in Extbase controller actions). The rendering parameters are not restricted in any way — feel free to pass in domain objects or database query results.

```php
<?php

namespace Vendor\ExtKey\Component;

use Tollwerk\TwComponentlibrary\Component\FluidTemplateComponent;

/**
 * Example Fluid template component
 */
class ExampleFluidTemplateComponent extends FluidTemplateComponent
{
    /**
     * Configure the component
     */
    protected function configure()
    {
        $this->setTemplate('EXT:ext_kex/Resources/Private/Partials/Component.html');
        $this->setParameter('param', 'value');
    }
}
```

#### Extbase plugin component

To configure an Extbase plugin component, use the `setExtbaseConfiguration()` method to specify the plugin name, the controller class name and the controller action to be called. The output will be rendered using the regular Fluid template associated with the controller action. You can specify action arguments via `setControllerActionArgument()`.

```php
<?php

namespace Vendor\ExtKey\Component;

use Tollwerk\TwComponentlibrary\Component\ExtbaseComponent;

/**
 * Example Extbase plugin component
 */
class ExampleExtbaseComponent extends ExtbaseComponent
{
    /**
     * Configure the component
     */
    protected function configure()
    {
        $this->setExtbaseConfiguration('PluginName', MyCustomController::class, 'action');
        $this->setControllerActionArgument('param', [1, 2, 3]);
    }
}
```

#### Common properties

There's a bunch of component properties and methods that are common to all component types. Some of them are controlled via [TypoScript constants](#typoscript-constants), others by overriding [component class properties](#component-properties) or calling [shared configuration methods](#configuration-methods).
 
##### TypoScript constants

Use the TypoScript constants to globally configure the HTML documents wrapped around your components during rendering for external systems. You can add base files, web fonts and libraries this way (`global.css`, jQuery, etc.). All resources can be referenced absolutely (starting with `http://` or `https://`), relatively (`/fileadmin/css/...`) or using an extension key prefix (`EXT:ext_key/Resources/...`).

| Constant        | Type   | Default | Description                                                                                                                    |
|:----------------|:-------|:--------|:-------------------------------------------------------------------------------------------------------------------------------|
| `stylesheets`   | String | Empty   | Comma separated list of CSS stylesheets to be included **for all components**.                                                 |
| `headerScripts` | String | Empty        | Comma separated list of JavaScript resources to be included in the `<head>` section **for all components**.                    |
| `footerScripts` | String | Empty        | Comma separated list of JavaScript resources to be included right before the closing `</body>` element **for all components**. |
`plugin.tx_twcomponentlibrary.settings`

##### Component properties

There are a couple of **protected** object properties you can override in your component classes to alter the default behaviour.

| Property             | Type   | Default | Description                                                                          |
|:---------------------|:-------|:--------|:-------------------------------------------------------------------------------------|
| `$page`              | `int`    | `1`     | TYPO3 page ID used when requesting the component (might affect TypoScript inclusion) |
| `$typeNum`           | `int`    | `0`     | `type` parameter used when requesting the component                                  |
| `$sysLanguage`       | `int`    | `0`     | System language UID used when requesting the component.                              |
| `$languageParameter` | `string` | `"L"`   | Language parameter name (used as GET variable)                                       |
| `$label`             | `string` | Empty   | Alternative label for the component (might be used by external systems)              |
|`$status`            | `string` | `"wip"` | Arbitrary component status label for use in external systems |
|`$request`            | `\TYPO3\CMS\Extbase\Mvc\Web\Request` | `\TYPO3\CMS\Extbase\Mvc\Web\Request` | Web request object used for requesting the component. You can add arguments with `$request->setArgument('name', 'value')` |
|`$preview`            | `\Tollwerk\TwComponentlibrary\Component\Preview\TemplateInterface` | `\Tollwerk\TwComponentlibrary\Component\Preview\BasicTemplate` | Preview template used for rendering the component for external systems. Supports a couple of configuration methods on its own, [see below](#preview-templates). |

Example usage:

```php
<?php

namespace Vendor\ExtKey\Component;

use Tollwerk\TwComponentlibrary\Component\FluidTemplateComponent;

/**
 * Example Fluid template component
 */
class ExampleFluidTemplateComponent extends FluidTemplateComponent
{
    /**
     * Component status
     *
     * @var int
     */
    protected $status = self::STATUS_READY;
    
    /**
     * Label
     *
     * @var string
     */
    protected $label = 'Button with icon';
    
    /**
     * Configure the component
     */
    protected function configure()
    {
        $this->setTemplate('EXT:ext_kex/Resources/Private/Partials/Button/Icon.html');
    }
}
```

##### Configuration methods

Use these following methods to further configure your components.

```php
<?php

namespace Tollwerk\TwComponentlibrary\Component;

use \Tollwerk\TwComponentlibrary\Component\ComponentInterface;

/**
 * Abstract component
 */
abstract class AbstractComponent implements ComponentInterface
{
    /**
     * Add a notice
     * 
     * Fractal displays the notice in the "Notes" tab
     *
     * @param string $notice Notice
     */
    protected function addNotice($notice) {}
    
    /**
     * Set a custom preview template
     * 
     * Overrides the default preview template facilitating the `stylesheets`, `headerScript` and `footerScript` TypoScript constants
     *
     * @param TemplateInterface|string|null $preview Preview template
     */
    protected function setPreview($preview) {}
}
```
 
##### Preview templates
  
By default, the builtin `BasicTemplate` is used for rendering components for external systems. You can use your custom template as long as you implement the `TemplateInterface`. The default `BasicTemplates` supports a couple of configuration methods:
 
```php
<?php

namespace Tollwerk\TwComponentlibrary\Component\Preview;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Basic preview template
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
class BasicTemplate implements TemplateInterface
{
    /**
     * Add a CSS stylesheet
     * 
     * Will be added in the `<head>` section of the preview template
     *
     * @param string $url CSS stylesheet URL
     */
    public function addStylesheet($url){}

    /**
     * Add a header JavaScript
     * 
     * Will be added in the `<head>` section of the preview template
     *
     * @param string $url Header JavaScript URL
     */
    public function addHeaderScript($url){}

    /**
     * Add a header inclusion resource
     * 
     * Path to a file to be included in the in the `<head>` section of
     * the preview template. Make sure to wrap the content e.g. in a    
     * `<script>` or `<style>` element. 
     *
     * @param string $path Header inclusion path
     */
    public function addHeaderInclude($path) {}

    /**
     * Add a footer JavaScript
     * 
     * Will be added just before the closing `</body>` element of the preview template
     *
     * @param string $path Footer JavaScript URL
     */
    public function addFooterScript($url) {}

    /**
     * Add a footer inclusion resource
     * 
     * Path to a file to be included in the in the `<head>` section of
     * the preview template. Make sure to wrap the content e.g. in a    
     * `<script>` or `<style>` element.
     *
     * @param string $path Footer inclusion path
     */
    public function addFooterInclude($path) {}
}
```
  
Example usage:

```php
<?php

namespace Vendor\ExtKey\Component;

use Tollwerk\TwComponentlibrary\Component\FluidTemplateComponent;

/**
 * Example Fluid template component
 */
class ExampleFluidTemplateComponent extends FluidTemplateComponent
{
    /**
     * Configure the component
     */
    protected function configure()
    {
        $this->setTemplate('EXT:ext_kex/Resources/Private/Partials/Component.html');
        
        // Configure the preview template
        $this->preview->addHeaderInclude('fileadmin/js/icons-loader.html');
        $this->preview->addStylesheet('EXT:ext_key/Resources/Public/Css/example.min.css');
    }
}
```

### Extracting components

The extension adds an Extbase CLI command that lets you **discover the declared components in JSON format**:
 
```bash
typo3/cli_dispatch.phpsh extbase component:discover
```

Sample result:

```json
[
    {
         "status": "wip",
         "name": "My Widget",
         "variant": null,
         "label": "Alternative component label",
         "class": "Vendor\\ExtKey\\Component\\MyWidgetComponent",
         "type": "fluid",
         "valid": true,
         "parameters": [],
         "config": "EXT:ext_key/Resources/Private/Partials/Widget.html",
         "template": "<f:link.action action=\"...\">...</f:link.action>",
         "extension": "t3s",
         "preview": "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>{{ _target.label }} \u2014 Preview Layout</title></head><body>{{{ yield }}}</body></html>",
         "request": {
             "method": "GET",
             "arguments": {
                 "L": 0,
                 "id": 1
             }
         },
         "path": [
             "Demo"
         ]
     } /*, ... */
 ]
```

Use these information however it makes sense for you. For instance, the [Fractal-TYPO3 bridge](https://github.com/tollwerk/fractal-typo3) builds an explorable component library out of the JSON data.

### Rendering components

The component library extension introduces the new `type` parameter value `2400` used for calling TYPO3 as a rendering engine for single components. For instance,
 
 `http://example.com/?type=2400&tx_twcomponentlibrary_component%5Bcomponent%5D=Vendor%5CExtKey%5CComponent%5CMyWidgetComponent`
 
will exclusively render the component `\Vendor\ExtKey\Component\MyWidgetComponent` and return the generated source code without surrounding page level HTML. 

License
-------

Copyright © 2017 [Joschi Kuphal][author-url] / joschi@kuphal.net. Licensed under the terms of the  [GPL v2](LICENSE.txt) license.

[author-url]: https://jkphl.is

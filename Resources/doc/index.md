Using ReactBundle
===================

*Where we explain how to install and start using ReactBundle*

Installation
------------

First and foremost, note that you have a complete example with React, Webpack and Symfony Standard Edition at [Limenius/symfony-react-sandbox](https://github.com/Limenius/symfony-react-sandbox) ready for you. Feel free to clone it, run it, experiment, and copy the pieces you need to your project. Being this bundle a frontend-oriented bundle, you are expected to have a compatible frontend setup.

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

    $ composer require limenius/react-bundle

This command requires you to have Composer installed globally, as explained
in the *installation chapter* of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding the following line in the `app/AppKernel.php`
file of your project:

```php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Limenius\ReactBundle\LimeniusReactBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3: (optional) Configure the bundle

The bundle comes with a sensible default configuration, which is listed below. If you skip this step, these defaults will be used.
```yaml
    limenius_react:
        # Other options are "server_side" and "client_side"
        default_rendering: "both"

        serverside_rendering:
            # In case of error in server-side rendering, throw exception
            fail_loud: false

            # Replay every console.log message produced during server-side rendering
            # in the JavaScript console
            # Note that if enabled it will throw a (harmless) React warning
            trace: false

            # Mode can be `"phpexecjs"` (to execute Js from PHP using PhpExecJs),
            # or `"external"` (to rely on an external node.js server)
            # Default is `"phpexecjs"`
            mode: "phpexecjs"

            # Location of the server bundle, that contains React and React on Rails.
            # null will default to `%kernel.root_dir%/Resources/webpack/server-bundle.js`
            # Only used with mode `phpexecjs`
            server_bundle_path: null

            # Only used with mode `external`
            # Location of the socket to communicate with a dummy node.js server.
            # Socket type must be acceptable by php function stream_socket_client. Example unix://node.sock, tcp://127.0.0.1:5000  
            # More info: http://php.net/manual/en/function.stream-socket-client.php
            # Example of node server:
            # https://github.com/Limenius/symfony-react-sandbox/blob/master/app/Resources/node-server/server.js
            # null will default to `unix://%kernel.project_dir%/var/node.sock`
            server_socket_path: null

            cache:
                enabled: false
                # name of your app, it is the key of the cache where the snapshot will be stored.
                key: "app"
```

## JavaScript and Webpack Set Up

In order to use React components you need to register them in your JavaScript. This bundle makes use of the React On Rails npm package to render React Components (don't worry, you don't need to write any Ruby code! ;) ).

```bash
npm install react-on-rails
```

Your code exposing a react component would look like this:

```js
import ReactOnRails from 'react-on-rails';
import RecipesApp from './RecipesAppServer';

ReactOnRails.register({ RecipesApp });
```

Where RecipesApp is the component we want to register in this example.

Note that it is very likely that you will need separated entry points for your server-side and client-side components, for dealing with things like routing. This is a common issue with any universal (isomorphic) application. Again, see the sandbox for an example of how to deal with this.

If you use server-side rendering, you are also expected to have a Webpack bundle for it, containing React, React on Rails and your JavaScript code that will be used to evaluate your component.

Take a look at [the webpack configuration in the symfony-react-sandbox](https://github.com/Limenius/symfony-react-sandbox/blob/master/webpack.config.serverside.js) for more information.

If not configured otherwise this bundle will try to find your server side JavaScript bundle in `app/Resources/webpack/server-bundle.js`

## Start using the bundle

You can insert React components in your Twig templates with:

```twig
{{ react_component('RecipesApp', {'props': props}) }}
```

Where `RecipesApp` is, in this case, the name of our component, and `props` are the props for your component. Props can either be a JSON encoded string or an array.

For instance, a controller action that will produce a valid props could be:

```php
/**
 * @Route("/recipes", name="recipes")
 */
public function homeAction(Request $request)
{
    $serializer = $this->get('serializer');
    return $this->render('recipe/home.html.twig', [
        'props' => $serializer->serialize(
            ['recipes' => $this->get('recipe.manager')->findAll()->recipes], 'json')
    ]);
}
```

## Server-side, client-side or both?

You can choose whether your React components will be rendered only client-side, only server-side or both, either in the configuration as stated above or per twig tag basis.

If you set the option `rendering` of the twig call, you can override your config (default is to render both server-side and client-side).

```twig
{{ react_component('RecipesApp', {'props': props, 'rendering': 'client_side'}) }}
```

Will render the component only client-side, whereas the following code

```twig
{{ react_component('RecipesApp', {'props': props, 'rendering': 'server_side'}) }}
```

... will render the component only server-side (and as a result the dynamic components won't work).

Or both (default):

```twig
{{ react_component('RecipesApp', {'props': props, 'rendering': 'both'}) }}
```

You can explore these options by looking at the generated HTML code.

## Debugging

One important point when running server-side JavaScript code from PHP is the management of debug messages thrown by `console.log`. ReactBundle, inspired React on Rails, has means to replay `console.log` messages into the JavaScript console of your browser.

To enable tracing, you can set a config parameter, as stated above, or you can set it in your template in this way:

```twig
{{ react_component('RecipesApp', {'props': props, 'trace': true}) }}
```

Note that in this case you will probably see a React warning like

*"Warning: render(): Target node has markup rendered by React, but there are unrelated nodes as well. This is most commonly caused by white-space inserted around server-rendered markup."*

This warning is harmlesss and will go away when you disable trace in production. It means that when rendering the component client-side and comparing with the server-side equivalent, React has found extra characters. Those characters are your debug messages, so don't worry about it.

## Server-Side modes

This bundle supports two modes of using server-side rendering:

* Using [PhpExecJs](https://github.com/nacmartin/phpexecjs) to auto-detect a JavaScript environment (call node.js via terminal command or use V8Js PHP) and run JavaScript code through it. This is more friendly for development, as every time you change your code it will have effect immediatly, but it is also more slow, because for every request the server bundle containing React must be copied either to a file (if your runtime is node.js) or via memcpy (if you have the V8Js PHP extension enabled) and re-interpreted. It is more **suited for development**, or in environments where you can cache everything.

* Using an external node.js server ([Example](https://github.com/Limenius/symfony-react-sandbox/tree/master/app/Resources/node-server/server.js)). It will use a dummy server, that knows nothing about your logic to render React for you. This is faster but introduces more operational complexity (you have to keep the node server running). For this reason it is more **suited for production**.

## Redux

If you're using [Redux](http://redux.js.org/) you could use the bundle to hydrate your store's:

Use `redux_store` in your twig file before you render your components depending on your store:

```twig
{{ redux_store('MySharedReduxStore', initialState ) }}
{{ react_component('RecipesApp') }}
```
`MySharedReduxStore` here is the identifier you're using in your javascript to get the store. The `initialState` can either be a JSON encoded string or an array.

Then, expose your store in your bundle, just like your exposed your components:

```js
import ReactOnRails from 'react-on-rails';
import RecipesApp from './RecipesAppServer';
import configureStore from './store/configureStore';

ReactOnRails.registerStore({ configureStore });
ReactOnRails.register({ RecipesApp });
```

Finally use `ReactOnRails.getStore` where you would have used your the object you passed into `registerStore`.

```js
// Get hydrated store
const store = ReactOnRails.getStore('MySharedReduxStore');

return (
  <Provider store={store}>
    <Scorecard />
  </Provider>
);
```

Make sure you use the same identifier here (`MySharedReduxStore`) as you used in your twig file to set up the store.

You have an example in the [Sandbox](https://github.com/Limenius/symfony-react-sandbox).

## Using asset versioning

If you are using [webpack encore](https://github.com/symfony/webpack-encore) you may be using assets versioning using a [json manifest file](https://symfony.com/blog/new-in-symfony-3-3-manifest-based-asset-versioning).
In this case, having to change your configuration is very bothersome and should be done automatically using your `manifest.json` file. This is how to do it:

### Create a custom renderer

```php
<?php

namespace App\Renderer;

use Limenius\ReactRenderer\Renderer\PhpExecJsReactRenderer;
use Symfony\Component\Asset\Packages;

class CustomPhpExecJsReactRenderer extends PhpExecJsReactRenderer
{
    /**
     * @param Packages $packages
     * @param string   $serverBundlePath
     */
    public function setPackage(Packages $packages, string $serverBundlePath)
    {
        $this->serverBundlePath .= $packages->getUrl($serverBundlePath);
    }
}
```

### Update your services configuration to override the default service

```yaml
services:
    limenius_react.react_renderer:
        class: App\Renderer\CustomPhpExecJsReactRenderer
        arguments:
            - '%kernel.project_dir%/public' # here you set the base path
            - '%limenius_react.fail_loud%'
            - '@limenius_react.context_provider'
            - '@logger'
        calls:
            - [setPackage, ['@assets.packages', 'build/js/server-bundle.js']]
```

Some things to keep in mind:

- the value `build/js/server-bundle.js` is the same path you would use for an assets render in twig
- the `server_bundle_path` configuration becomes useless after this manipulation
- this does not consider the behavior with a node server rendering

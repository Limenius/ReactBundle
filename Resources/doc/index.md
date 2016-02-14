Using ReactBundle
===================

*Where we explain how to install and start using ReactBundle*

Installation
------------

First and foremost, note that you have a complete example with React, Webpack and Symfony Standard Edition at [Limenius/symfony-react-sandbox](https://github.com/Limenius/symfony-react-sandbox) ready for you. Feel free to clone it, run it, experiment, and copy the pieces you need to your project. Being this bundle a fontend-oriented bundle, you are expected to have a compatible frontend setup.

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

    $ composer require limenius/react-bundle

This command requires you to have Composer installed globally, as explained
in the *installation chapter* of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding the following line in the `app/AppKernel.php`
file of your project:

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                new Limenius\ReactBundle(),
            );

            // ...
        }

        // ...
    }

### Step 3: (optional) Configure the bundle

The bundle comes with a sensible default configuration, which is listed below. If you skip this step, these defaults will be used.

    limenius_react:
        # Other options are "only-serverside" and "only-clientside"
        default_rendering: "both"
        serverside_rendering:
            # In case of error in server-side rendering, throw exception
            fail_loud: false
            # Replay every console.log message produced during server-side rendering
            # in the JavaScript console
            # Note that if enabled it will throw a (harmless) React warning
            trace: false
            # Location of the node binary. Defaults to the result of running `env node`
            node_binary_path: null
            # Location of the server bundle, that contains React and React on Rails.
            # null will default to `app/Resources/webpack/server-bundle.js`
            server_bundle_path: null

## JavaScript and Webpack Set Up

In order to use React components you need to register them in your JavaScript. This bundle makes use of the React On Rails npm package to render React Components (don't worry, you don't need to write any Ruby code! ;) ).

Your code exposing a react component would look like this:

    import ReactOnRails from 'react-on-rails';
    import RecipesApp from './RecipesAppServer';
    
    ReactOnRails.register({ RecipesApp });

Where RecipesApp is the component we want to register in this example.

Note that it is very likely that you will need separated entry points for your server-side and client-side components, for dealing with things like routing. This is a common issue with any universal (isomorphic) application. Again, see the sandbox for an example of how to deal with this.

If you use server-side rendering, you are also expected to have a Webpack bundle for it, containing React, React on Rails and your JavaScript code that will be used to evaluate your component.

Take a look at [the webpack configuration in the symfony-react-sandbox](https://github.com/Limenius/symfony-react-sandbox/blob/master/webpack.config.serverside.js) for more information.

If not configured otherwise this bundle will try to find your server side JavaScript bundle in `app/Resources/webpack/server-bundle.js`

## Start using the bundle

You can insert React components in your Twig templates with:

    {{ react_component('RecipesApp', {'props': props}) }}

Where `RecipesApp` is, in this case, the name of our component, and `props` is a JSON encoded string with the array of your strings.

For instance, a controller action that will produce a valid props could be:

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

## Server-side, client-side or both?

You can choose whether your React components will be rendered only client-side, only server-side or both, either in the configuration as stated above or per twig tag basis.

If you set the option `rendering` of the twig call, you can override your config (default is to render both server-side and client-side).

    {{ react_component('RecipesApp', {'props': props, 'rendering': 'client-side'}) }}

Will render the component only client-side, whereas the following code

    {{ react_component('RecipesApp', {'props': props, 'rendering': 'server-side'}) }}

... will render the component only server-side (and as a result the dynamic components won't work).

Or both (default):

    {{ react_component('RecipesApp', {'props': props, 'rendering': 'server-side'}) }}

You can explore these options by looking at the generated HTML code.

## Debugging

One imporant point when running server-side JavaScript code from PHP is the management of debug messages thrown by `console.log`. ReactBundle, inspired React on Rails, has means to replay `console.log` messages into the JavaScript console of your browser.

To enable tracing, you can set a config parameter, as stated above, or you can set it in your template in this way:

    {{ react_component('RecipesApp', {'props': props, 'trace': true}) }}

Note that in this case you will probably see a React warning like

*"Warning: render(): Target node has markup rendered by React, but there are unrelated nodes as well. This is most commonly caused by white-space inserted around server-rendered markup."*

This warning is harmlesss and will go away when you disable trace in production. It means that when rendering the component client-side and comparing with the server-side equivalent, React has found extra characters. Those characters are your debug messages, so don't worry about it.





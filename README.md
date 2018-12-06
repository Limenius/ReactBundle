# ReactBundle

ReactBundle integrates [ReactRenderer](https://github.com/Limenius/ReactRenderer) with Symfony. This lets you implement React.js client and server-side rendering in your Symfony projects, allowing the development of universal (isomorphic) applications.

**Note**: If you are new to React.js, please note that this bundle is not by any means required to use React with Symfony. This allows you to do some advanced features such as Server Side Rendering, or injecting components directly from Twig tags.

Features include:

* Prerender server-side React components for SEO, faster page loading, and users that have disabled JavaScript.
* Twig integration.
* Client-side render will take the server-side rendered DOM, recognize it, and take control over it without rendering again the component until needed.
* Error and debug management for server and client side code.
* Simple integration with Webpack.

[![Latest Stable Version](https://poser.pugx.org/limenius/react-bundle/v/stable)](https://packagist.org/packages/limenius/react-bundle)
[![Latest Unstable Version](https://poser.pugx.org/limenius/react-bundle/v/unstable)](https://packagist.org/packages/limenius/react-bundle)
[![License](https://poser.pugx.org/limenius/react-bundle/license)](https://packagist.org/packages/limenius/react-bundle)

# Example

For a complete example, with a sensible Webpack set up and a sample application to start with, check out [Symfony React Sandbox](https://github.com/Limenius/symfony-react-sandbox).

# Documentation

The documentation for this bundle is available in the `Resources/doc` directory of the bundle:

* Read the [LimeniusReactBundle documentation](https://github.com/Limenius/ReactBundle/blob/master/Resources/doc/index.md)

# Installation

All the installation instructions are located in the documentation.

# License

This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE.md

# Credits

ReactBundle is heavily inspired by the great [React On Rails](https://github.com/shakacode/react_on_rails), and uses its npm package to render React components.

The installation instructions have been adapted from [https://github.com/KnpLabs/KnpMenuBundle](https://github.com/KnpLabs/KnpMenuBundle). Because they were great.

# With Silex

Silex was discontinued in June 2018. However, if you wish to use ReactRenderer with Silex, check out @teameh [Silex React Renderer Service Provider](https://github.com/teameh/silex-react-renderer-provider).

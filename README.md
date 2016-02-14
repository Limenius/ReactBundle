# ReactBundle

ReactBundle lets you implement React.js client and server-side rendering in your Symfony projects, allowing the development of universal (isomorphic) applications.

Features include:

* Prerrender server-side React components for SEO, faster page loading, and users that have disabled JavaScript.
* Twig integration.
* Client-side render will take the server-side rendered DOM, recognize it, and take control over it without rendering again the component until needed.
* Error and debug management for server and client side code.
* Simple integration with Webpack.

# Example

For a complete example, with a sensible webpack set up and a sample application to start with, check out [Symfony React Sandbox](https://github.com/Limenius/symfony-react-sandbox).

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

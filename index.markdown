---
layout: base
title: ExposeRoutingBundle
gh_link: https://github.com/Bazinga/ExposeRoutingBundle
gh_repo: git://github.com/Bazinga/ExposeRoutingBundle.git
---

# ExposeRoutingBundle

Port of the incredible plugin [chCmsExposeRoutingPlugin](https://github.com/themouette/chCmsExposeRoutingPlugin).

## Installation

Add this bundle as a submodule:

    git submodule add git://github.com/Bazinga/ExposeRoutingBundle.git vendor/bundles/Bazinga/ExposeRoutingBundle

Register the namespace in `app/autoload.php`:

{% highlight php %}
<?php// app/autoload.php
$loader->registerNamespaces(array(
    // ...
    'Bazinga' => __DIR__.'/../vendor/bundles',
));
{% endhighlight %}

Register the bundle in `app/AppKernel.php`:

{% highlight php %}
<?php// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Bazinga\ExposeRoutingBundle\BazingaExposeRoutingBundle(),
    );
}
{% endhighlight %}

Register the routing in `app/config/routing.yml`:

{% highlight yaml %}
# app/config/routing.yml
_bazinga_exposerouting:
    resource: "@BazingaExposeRoutingBundle/Resources/config/routing/routing.xml"
{% endhighlight %}

Publish assets:

    php app/console assets:install --symlink web


Usage
-----

Just add these two lines in your layout:

{% highlight html+django %}
<script type="text/javascript" src="{{ "{{ asset('bundles/bazingaexposerouting/js/routing.js')" }} }}"></script>
<script type="text/javascript" src="{{ "{{ path('bazinga_exposerouting_js')" }} }}"></script>
{% endhighlight %}


It's as simple as calling: `Routing.generate('route_id', /* your params */)`.

Imagine some route definitions:

{% highlight yaml %}
# app/config/routing.yml
my_route_to_expose:
    pattern:  /foo/{id}/bar
    defaults:  { _controller: HelloBundle:Hello:index }
    options:
        expose: true

my_route_to_expose_with_defaults:
    pattern:  /blog/{page}
    defaults: { _controller: AcmeBlogBundle:Blog:index, page: 1 }
    options:
        expose: true
{% endhighlight %}

You can do:

{% highlight js %}
Routing.generate('my_route_to_expose', { id: 10 });
// will result in /foo/10/bar

Routing.generate('my_route_to_expose', { "id": 10, "foo": "bar" });
// will result in /foo/10/bar?foo=bar

$.get(Routing.generate('my_route_to_expose', { "id": 10, "foo": "bar" }));
// will call /foo/10/bar?foo=bar

Routing.generate('my_route_to_expose_with_defaults');
// will result in /blog/1

Routing.generate('my_route_to_expose_with_defaults', { id: 2 });
// will result in /blog/2

Routing.generate('my_route_to_expose_with_defaults', { "foo": "bar" });
// will result in /blog/1?foo=bar

Routing.generate('my_route_to_expose_with_defaults', { id: 2, "foo": "bar" });
// will result in /blog/2?foo=bar
{% endhighlight %}


Moreover, you can configure a list of routes to expose in `app/config/config.yml`:

{% highlight yaml %}
# app/config/config.yml
bazinga_expose_routing:
    routes_to_expose: [ route_1, route_2, ... ]
{% endhighlight %}

These routes will be added to the exposed routes.

You can prevent to expose a route by configuring it as below:

{% highlight yaml %}
# app/config/routing.yml
my_very_secret_route:
    pattern: /admin
    defaults: { _controller: HelloBundle:Admin:index }
    options:
        expose: false
{% endhighlight %}


Credits
-------

* William DURAND (Bazinga) as main author.
* Julien MUETTON (Carpe Hora) for the inspiration.

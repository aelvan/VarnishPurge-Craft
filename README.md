Varnish Purge for Craft
=====
Craft plugin for purging Varnish when elements are saved. 

Installation
---
1. Download and extract the contents of the zip. Copy the /varnishpurge folder to your Craft plugin folder. 
2. Enable the Varnish Purge plugin in Craft (Settings > Plugins).
3. Configure the plugin in your general.php config file (see "Configuration" below).
4. Let the purge commence.

Configuration
---
You need to configure Varnish Purge in order for it to do anything. Add the following settings to your
config/general.php file:

    'varnishUrl' => 'http://your-varnish-server.com/',
    'varnishPurgeEnabled' => true,
    'varnishPurgeRelated' => true,
    'varnishLogAll' => 0,
    
The `varnishUrl` setting can also be an array if you are running a multi language site:
    
    'varnishUrl' => array(
        'no' => 'http://your-varnish-server.com/no/',
        'en' => 'http://your-varnish-server.com/en/',
    ), 

####varnishUrl
The url to your varnish server. Usually this is your site url, but it could be different if you don't purge
through a private connection, or if you use the IP directly to bypass CloudFlare or similar services. If your
site url and the varnish url is different, make sure you handle this in your VCL file.

####varnishPurgeEnabled
Enables or disables the Varnish Purge plugin. You'd normally want to disable it in your dev environments and
enable it in your prod environment.

####varnishPurgeRelated
Enables or disables purging of related urls when an element is saved. This should normally be enabled to make sure
that all relevant urls are updated, but could be disabled on high traffic websites to make sure the cache stays as warm
as possible.

####varnishLogAll
When set to `1` some additional logging is forced even if devMode is disabled. Useful for debugging in production 
environments without having to enable devMode.

How it works
---
When an element is saved, the plugin collects the urls *that it thinks need to be updated*, and creates a new task
that sends purge requests to the Varnish server for each url.

If `varnishPurgeRelated` is disabled, only the url for the element itself, or the owners url if the element is
a Matrix block, is purged. 

If `varnishPurgeRelated` is enabled, the urls for all related elements are also purged. If the saved element is
an entry, all directly related entry urls, all related category urls, and all urls for elements related through
an entries Matrix blocks, is purged. If the saved element is an asset, all urls for elements related to that
asset, either directly or through a Matrix block, is purged. And so on. The element types taken into account is
entries, categories, matrix blocks and assets.

The plugin also adds a new element action to entries and categories for purging individual elements manually.
When doing this, related elements are not purged, only the selected elements.

Setting HTTP headers in your templates
---
Varnish uses the HTTP headers sent by Craft to determine if/how to cache a request. You can configure this in
your webserver, but I find it more flexible to do it in my templates. I usually have a config variable
named `addExpiryHeaders` in my config, which is enabled only in production (or you'll get issues with the browser caching
your pages unnecessarily), and the following code in my layout template: 

    {% if craft.config.addExpiryHeaders %}
        {% if expiryTime is not defined %}{% set expiryTime = '+1 day' %}{% endif %}
        
        {% set expires = now | date_modify(expiryTime) %}
        {% header "Cache-Control: max-age=" ~ (expires.timestamp - now.timestamp) %}
        {% header "Pragma: cache" %}
        {% header "Expires: " ~ expires.rfc1123() %}
        {% header "X-Remove-Cache-Control: 1" %}
    {% endif %}
    
In your individual page templates, you can then override the expiry time of page types like this:   

    {% set expiryTime = '+60 mins' %}

If you want to cache static assets with Varnish, you need to set the appropriate cache headers in your webserver.

Configuring Varnish
---
If you plan to run a Varnish server, you really need to get your hands dirty and learn
[VCL](https://www.varnish-cache.org/docs/trunk/users-guide/vcl.html). There're no shortcuts
unfortunately. :) Have a look at [this gist](https://gist.github.com/aelvan/eba03969f91c1bd51c40) for an example VCL file.
It's based on the [Varnish 4.0 template made by Mattias Geniar](https://github.com/mattiasgeniar/varnish-4.0-configuration-templates) 
with some adjustments for Craft. 
  
Alternatives
---
A good alternative to this plugin is [Josh Angell's CacheMonster plugin](https://github.com/supercool/Cache-Monster/). It takes
a different approach, using the result of the {% cache %} tag to find the urls that needs to be purged, and also provides a feature to
warm the cache. 

This is a great solution if you're using {% cache %}, but most of the sites I'm using Varnish on is very content
heavy and I've opted not to use it. When you have tens of thousands of element criterias that needs to be updated when content is 
updated, it's really a problem. 
  
Price, license and support
---
The plugin is released under the MIT license, meaning you can do what ever you want with it as long as you don't 
blame me. **It's free**, which means there is absolutely no support included, but you might get it anyway. 
Just post an issue here on github if you have one, and I'll see what I can do. :)

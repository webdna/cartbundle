# Cart Bundle plugin for Craft CMS

Ability to show products in the cart as a bundle (ideal for use with [Multi Add](https://github.com/engram-design/MultiAdd))

## Installation

To install Cart Bundle, follow these steps:

1. Download & unzip the file and place the `cartbundle` directory into your `craft/plugins` directory
2.  -OR- do a `git clone https://github.com/webdna/cartbundle.git` directly into your `craft/plugins` folder.  You can then update it with `git pull`
3. Install plugin in the Craft Control Panel under Settings > Plugins
4. The plugin folder should be named `cartbundle` for Craft to see it.  GitHub recently started appending `-master` (the branch name) to the name of the folder for zip file downloads.

Cart Bundle works on Craft 2.4.x and Craft 2.5.x.

## Cart Bundle Overview

Cart Bundle is a simple plugin that allows you to show products as grouped (bundled) in the cart/checkout. It works by adding some extra information in the `options` array in cart line items.

This plugin works great along with the [MultiAdd plugin](https://github.com/engram-design/MultiAdd).

**NOTE: This is plugin is only designed for output purposes it does not change the purchasable data**

## Documentation

[See the wiki](https://github.com/webdna/cartbundle/wiki)

## Cart Bundle Roadmap

- Remove weird id obscuring in favour of [hash security](https://craftcms.com/docs/templating/filters#hash)
- MORE FEATURES!

## Cart Bundle Changelog

[See releases](https://github.com/webdna/cartbundle/releases)

---

Brought to you by [webdna](https://webdna.co.uk)

Hat tip to [Josh Crawford](https://github.com/engram-design)

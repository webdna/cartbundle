# Cart Bundle plugin for Craft CMS

Ability to show products in the cart as a bundle (ideal for use with [Multi Add](https://github.com/engram-design/MultiAdd))

## Installation

To install Cart Bundle, follow these steps:

1. Download & unzip the file and place the `cartbundle` directory into your `craft/plugins` directory
2.  -OR- do a `git clone ???` directly into your `craft/plugins` folder.  You can then update it with `git pull`
3.  -OR- install with Composer via `composer require /cartbundle`
4. Install plugin in the Craft Control Panel under Settings > Plugins
5. The plugin folder should be named `cartbundle` for Craft to see it.  GitHub recently started appending `-master` (the branch name) to the name of the folder for zip file downloads.

Cart Bundle works on Craft 2.4.x and Craft 2.5.x.

## Cart Bundle Overview

Cart Bundle is a simple plugin that allows you to show products as grouped (bundled) in the cart/checkout. It works by adding some extra information in the `options` array in cart line items.

This plugin works great along with the [MultiAdd plugin](https://github.com/engram-design/MultiAdd). Big hat tip to [Josh Crawford](https://github.com/engram-design)

*Examples below are for vanilla Craft without the MultiAdd plugin.*

**NOTE: This is plugin is only designed for output purposes it does not change the purchasable data**

## Configuring Cart Bundle

### Settings

* `Bundle ID Handle (bundleIdHandle)` - This is the handle used in the item options array in the cart to group bundle items together `Default: bundleId`
* `Bundle ID Salt (bundleIdSalt)` - Salt used to encrypt the bundle ID for slight obscurity `Default: appId`
* `Bundle SKU Handle (bundleSkyHandle)` - The field handle to use for the bundle SKU. If left blank Bundle item SKUs will be combined `Default: e.g. ITEM1SKU-ITEM2SKU-ITEM3SKU`

### Twig Filters

* `cartBundleIdEncrypt` - Used to encrypt the bundle ID for use in the cart `Usage: bundleEntry.id | cartBundleIdEncrypt`
* `cartBundle` - The main function, when passed the cart lineItems it iterates through all the items to bundle products together. It then returns the lineItems with the new bundle products and removes individual products that are part of a bundle `Usage: cart.lineItems | cartBundle`

### Variables

* `cartItemsCount` - Returns the number of items in the cart after bundling appropriate items `Usage: craft.cartBundle.cartItemsCount`

### Hooks

* `modifyCartBundles` - allow manipulation of the bundles before they are added into lineItems

```
  // Arbitrary example of removing first bundle
  public function modifyCartBundles( &$bundles ) {

    if ( count( $bundles ) ) {
      unset( $bundles[ 0 ] );
    }

  }
```


## Using Cart Bundle

#### Add to cart

The following are the extra fields that are required in the add to cart form, as detailed here: https://craftcommerce.com/docs/add-to-cart#line-item-options-and-notes

In the below example code the handle `bundleId` is used, this can be changed in the settings as detailed above.

The example shows `entry.id` being used for the `bundleId`. This ID essentially the element you would like as the bundle container, this can be any element e.g. entry, category, product etc. The `bundleName` will the the description of the line item.

```twig

  <input type="hidden" name="options[bundleId]" value="{{ entry.id|cartBundleIdEncrypt }}" />
  <input type="hidden" name="options[bundleName]" value="{{ entry.title }}" />

```

#### Cart Display

The cart is bundled up using the `|cartBundle` filter. This is solely a front end process for display purposes, it does not change what is purchased through Craft Commerce.

The filter will group all items together that have the same options and container element (bundle). It will not effect items that are added without the `bundleId` and `bundleName` options.

Example below will quickly dump out the data for you to see what is available

```twig

{# Setup the cart variable #}
{% set cart = craft.commerce.cart %}

{# Bundle up the items for use in display #}
{% set cartItems = cart.lineItems|cartBundle %}

{% if cartItems|length %}
  <ul>
    {% for item in cartItems %}
      <li>
        Item: {{ item.description }}<br />
        {# See if we are dealing with a bundled item or a regular item #}
        {% if item.options.bundle is defined %}
          {# Dump data of the bundle (the container element) #}
          <pre>
            {{ dump( item.options.bundle ) }}
          </pre>

          {# Loop through all the items that are bundled together #}
          {% if item.options.bundleItems is defined and item.options.bundleItems|length %}
            <ul>
              {% for bundleItem in item.options.bundleItems %}
                <li>
                  {# Dump out bundle data #}
                  Item: {{ bundleItem.purchasable.product.title }}
                  <pre>
                    {{ dump( bundleItem ) }}
                  </pre>
                </li>
              {% endfor %}
            </ul>
          {% endif %}
        {% endif %}

        {# Dump out price #}
        Price: {{ item.price|currency( cart.currency ) }}
      </li>
    {% endfor %}
  </ul>
{% endif %}
```

## Cart Bundle Roadmap

Some things to do, and ideas for potential features:

* Release it

## Cart Bundle Changelog

### 1.0.0 -- 2017.01.17

* Initial release

Brought to you by [webdna](http://webdna.co.uk)

Hat tip to [Josh Crawford](https://github.com/engram-design)

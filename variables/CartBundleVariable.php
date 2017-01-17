<?php
/**
 * Cart Bundle plugin for Craft CMS
 *
 * Cart Bundle Variable
 *
 * @author    Nathaniel Hammond - @nfourtythree - webdna
 * @copyright Copyright (c) 2017 webdna
 * @link      https://webdna.co.uk
 * @package   CartBundle
 * @since     1.0.0
 */

namespace Craft;

class CartBundleVariable
{
    /**
     *  cartItemsCount returns the actual count of items
     *  after bundling items together
     *
     *     {{ craft.cartBundle.cartItemsCount }}
     *
     */
    public function cartItemsCount()
    {
      return craft()->cartBundle->cartItemsCount();
    }
}

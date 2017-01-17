<?php
/**
 * Cart Bundle plugin for Craft CMS
 *
 * Cart Bundle Twig Extension
 *
 *
 * @author    Nathaniel Hammond - @nfourtythree - webdna
 * @copyright Copyright (c) 2017 webdna
 * @link      https://webdna.co.uk
 * @package   CartBundle
 * @since     1.0.0
 */

namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;

class CartBundleTwigExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'CartBundle';
    }

    /**
     * Returns an array of Twig filters, used in Twig templates via:
     *
     *      {{ cart.lineItems | cartBundle }}
     *      {{ bundle.id | cartBundleIdEncrypt }}
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'cartBundle' => new \Twig_Filter_Method($this, 'bundleItems'),
            'cartBundleIdEncrypt' => new \Twig_Filter_Method($this, 'encryptId'),
        );
    }

    /**
     * Returns an array of Twig functions, used in Twig templates via:
     *
     *      {% set this = someFunction('something') %}
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'someFunction' => new \Twig_Function_Method($this, 'someInternalFunction'),
        );
    }

    /**
     * Encrypt ID for use in templates
     *
      * @return string
     */
    public function encryptId( $id )
    {

      return craft()->cartBundle->encryptId( $id );

    }

    /**
     * Create line items with bundles
     *
      * @return string
     */
    public function bundleItems( $lineItems )
    {
      return craft()->cartBundle->bundleItems( $lineItems );

    }
}

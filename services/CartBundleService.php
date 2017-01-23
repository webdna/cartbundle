<?php
/**
 * Cart Bundle plugin for Craft CMS
 *
 * CartBundle Service
 *
 * @author    Nathaniel Hammond - @nfourtythree - webdna
 * @copyright Copyright (c) 2017 webdna
 * @link      https://webdna.co.uk
 * @package   CartBundle
 * @since     1.0.0
 */

namespace Craft;

class CartBundleService extends BaseApplicationComponent
{
  protected $pluginHandle = 'cartBundle';
  protected $plugin;
  protected $settings;
  protected $bundles = array();
  protected $bundled = false;
  protected $bundledLineItems = array();

  /**
  * Contstructor function
  * Setup everything we will need in the service
  */
  public function __construct()
  {
    $this->plugin = craft()->plugins->getPlugin( $this->pluginHandle );
    $this->settings = $this->plugin->getSettings();
  }

  /**
  * Cart Items Count return the "actual" count of number of items in the cart aftering bundling
  */
  public function cartItemsCount()
  {
    // Get the current cart
    $cart = craft()->commerce_cart->getCart();
    $cartItemsCount = count( $cart->lineItems );

    // Only worry about doing more processing if there are enough items in the cart to bundle
    if ( $cartItemsCount > 1 ) {
      $lineItems = $this->bundleItems( $cart->lineItems );
      $cartItemsCount = count( $lineItems );
    }

    return $cartItemsCount;
  }

  /**
  * Bundle items together where applicable
  */
  public function bundleItems( $lineItems )
  {
    if ( is_array( $lineItems ) and count( $lineItems ) and !$this->bundled ) {

      // Loop through line items creating bundle info
      foreach ( $lineItems as $itemKey => $item ) {

        $added = $this->_addToBundles( $item );

        // Remove bundle items from main item array
        if ( $added ) {
          unset( $lineItems[ $itemKey ] );
        }
      }

      // Added bundle line items
      $lineItems = $this->_addBundleLineItems( $lineItems );
      $this->bundledLineItems = $lineItems;

    } elseif ( $this->bundled ) {
      $lineItems = $this->bundledLineItems;
    }

    // Set that we have already bundled the items
    $this->bundled = true;
    return $lineItems;
  }

  /**
  * Add bundle line items back into the main line items array
  */
  private function _addBundleLineItems( $lineItems = array() )
  {
    if ( count( $this->bundles ) ) {

      // Hook: [modifyCartBundles] allow user to manipulate the bundles before they are added into lineItems
      craft()->plugins->call( 'modifyCartBundles', array( &$this->bundles ) );

      foreach ( $this->bundles as $bundleId => $bundle ) {

        // Setup new line item model
        $newLineItem = new Commerce_LineItemModel;

        // With default values
        $newLineItem->price = 0;
        $newLineItem->qty = 1;
        $newLineItem->salePrice = 0;

        // Get bundle data and make it avaiable in the front end
        $elementType = craft()->elements->getElementTypeById( $bundleId );
        $bundleEntry = craft()->elements->getCriteria( $elementType, array( 'id' => $bundleId ) )->first();

        $itemOptions = array(
          'bundle' => $bundleEntry,
          'bundleLineItemId' => '',
          'bundleItems' => array()
        );

        if ( count( $bundle ) ) {

          // Set the quantity of the bundle container
          // For the moment all items in the bundle will have the same qty
          $newLineItem->qty = $bundle[ 0 ]->qty;

          // Get new totals
          foreach ( $bundle as $item ) {
            // Concatenate lineItem ids to form an ID for the bundle
            $itemOptions[ 'bundleLineItemId' ] .= ( !$itemOptions[ 'bundleLineItemId' ] ) ? $item->id : '-' . $item->id;

            $newLineItem->price += $item->price;
            $newLineItem->salePrice += $item->salePrice;

            // Make sure item data is still available
            $itemOptions[ 'bundleItems' ][] = $item;
          }
        }

        $newLineItem->options = $itemOptions;

        if ( $bundleEntry ) {
          // Create bundle SKU
          $sku = $this->_createBundleSku( $bundleEntry, $bundle );

          // Create snapshot data
          $snapshot = array(
              'price'         => $newLineItem->price,
              'salePrice'     => $newLineItem->salePrice,
              'sku'           => $sku,
              'description'   => $bundleEntry->title,
              'cpEditUrl'     => '#',
              'options'       => $newLineItem->options
          );

          $newLineItem->total = $newLineItem->getTotal();
          $newLineItem->snapshot = $snapshot;
        }


        // Add bundle back into main line items array
        $lineItems[] = $newLineItem;
      }
    }

    return $lineItems;
  }

  /**
  * Create bundle sku
  */
  private function _createBundleSku( $bundleEntry, $bundle )
  {
    if ( $this->settings->bundleSkuHandle and $bundleEntry and isset( $bundleEntry->{$this->settings->bundleSkuHandle} ) ) {
      $sku = $bundleEntry->{$this->settings->bundleSkuHandle};
    } else {
      $skuParts = array();

      foreach ( $bundle as $item ) {
        $skuParts[] = $item->sku;
      }

      $sku = join('-', $skuParts);
    }

    return $sku;
  }

  /**
  * Add item to the bundles list
  *
  * @return boolean ( true if the item has been added, false if not )
  */
  private function _addToBundles( $item )
  {
    if ( $this->_isBundleItem( $item ) ) {
      $bundleId = $item->options[ $this->settings->bundleIdHandle ];

      // Decrypt the bundle id
      $bundleId = $this->decrypt( $bundleId );

      if ( array_key_exists( $bundleId, $this->bundles ) ) {
        $this->bundles[ $bundleId ][] = $item;
      } else {
        $this->bundles[ $bundleId ] =  array( $item );
      }

      return true;
    }

    return false;
  }

  /**
  * Test to see if the item belongs to a bundle
  */
  private function _isBundleItem( $item )
  {
    if ( isset( $item->options ) and count( $item->options ) and isset( $item->options[ $this->settings->bundleIdHandle ] ) ) {
      return true;
    }

    return false;
  }

  /**
   * Encrypt bundle ID for use in templates / cart
   *
  */
  public function encryptId( $id = false )
  {
    if ( $id ) {

      return $this->encrypt( $id );

    } else {

      throw new Exception(Craft::t( 'Please provide a valid ID' ));

    }
  }

  /**
   * Decrypt bundle ID for use in templates / cart
   *
  */
  public function decryptId( $id = false )
  {
    if ( $id ) {

      return $this->decrypt( $id );

    } else {

      throw new Exception(Craft::t( 'Please provide a valid ID' ));

    }
  }

  /**
  * Encrypt data using the Salt from plugin settings
  */
  protected function encrypt( $string )
  {
    $key = $this->settings->bundleIdSalt;
    return rtrim(strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key)))), '+/', '-_'), '=');
  }

  /**
  * Decrypt data using the Salt from plugin settings
  */
  protected function decrypt( $string )
  {
    $key = $this->settings->bundleIdSalt;
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT)), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
  }

}

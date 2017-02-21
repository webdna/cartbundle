<?php
/**
 * Cart Bundle plugin for Craft CMS
 *
 * CartBundle_Cart Controller
 *
 * @author    Nathaniel Hammond - @nfourtythree - webdna
 * @copyright Copyright (c) 2017 webdna
 * @link      https://webdna.co.uk
 * @package   CartBundle
 * @since     1.1.0
 */

namespace Craft;

class CartBundle_CartController extends BaseController
{
  
  protected $allowAnonymous = array(
    'actionUpdateLineItem',
  );

  /**
   * Update Line Item - sits infront of the Craft Commerce update line
   * item to handle bundle qty updates
   *
   * This has ended up being a direct copy (with edits) as unfortunately it isn't in a
   * service that can be called multiple times
   */
  public function actionUpdateLineItem()
  {
    $this->requirePostRequest();

    $cart = craft()->commerce_cart->getCart();
    $lineItemIds = explode( '-', craft()->request->getPost('lineItemId') );
    $qty = craft()->request->getPost('qty', 0);
    $note = craft()->request->getPost('note');

    $cart->setContentFromPost('fields');

    $updatedLines = 0;
    $errorLines = 0;

    if ( !empty( $lineItemIds ) ) {
      foreach ( $lineItemIds as $lineItemId ) {
        $lineItem = null;
        foreach ($cart->getLineItems() as $item)
        {
            if ($item->id == $lineItemId)
            {
                $lineItem = $item;
                break;
            }
        }

        // Fail silently if its not their line item or it doesn't exist.
        if (!$lineItem || !$lineItem->id || ($cart->id != $lineItem->orderId))
        {
          break;
        }

        $lineItem->qty = $qty;
        $lineItem->note = $note;

        // If the options param exists, set it
        if (!is_null(craft()->request->getPost('options')))
        {
            $options = craft()->request->getPost('options', []);
            ksort($options);
            $lineItem->options = $options;
            $lineItem->optionsSignature = md5(json_encode($options));
        }

        if (craft()->commerce_lineItems->updateLineItem($cart, $lineItem, $error))
        {
          $updatedLines += 1;
        }
        else
        {
          $errorLines += 1;
        }
      }
    }

    craft()->userSession->setNotice( Craft::t( 'Updated {updatedLines} Lines.' , array( 'updatedLines' => $updatedLines ) ) );

    if (craft()->request->isAjaxRequest)
    {
      $this->returnJson( array( 'success' => true, 'cart' => $this->cartArray( $cart ) ) );
    }
    $this->redirectToPostedUrl();

  }
}

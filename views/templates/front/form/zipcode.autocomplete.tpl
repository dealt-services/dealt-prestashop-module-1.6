<div style="display: flex; margin-top: 8px">
  <div class="input-wrapper" style="flex-grow: 1">
    <input id="dealt-zipcode-autocomplete" class="form-control form-control-input" type="text" name="myCountry"
      placeholder="{l s="Post code" d='Modules.Dealtmodule.Shop'}">
  </div>
  <input class="btn btn-primary" id="dealt-offer-submit" type="submit"
    value="{l s='Add service' d='Modules.Dealtmodule.Shop'}" data-dealt-offer-id={$offer.dealtOfferId}
    data-dealt-offer-unit-price={$offer.unitPrice} data-dealt-product-id={$binding.productId}
    data-dealt-product-attribute-id={$binding.productAttributeId}
    {if $binding.cartRef != null}data-dealt-cart-ref="true" {/if}
    {if $binding.cartProduct != null}data-dealt-cart-product="true" {/if} disabled>
</div>
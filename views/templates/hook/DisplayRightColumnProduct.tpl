{assign var=attached value=($binding.cartProduct != null && $binding.cartRef != null)}
{assign var=offerImage value=$offer.image}

<div class="card card-block loading" id="dealt-offer-card">
    <h4>{l s="New : simplify your life !" d='Modules.Dealtmodule.Shop'}</h4>
    <br />
    <article id="dealt-offer-error" class="alert alert-danger" role="alert" data-alert="danger" style="display: none;">
    </article>
    <div style="display: flex; align-items: stretch;">
        <div
                style="flex: 1; display: block; border-radius: 5px; background-size: cover; background-image: url('{if $offerImage}{$offerImage}{else}{$urls.no_picture_image.bySize.medium_default.url}{/if}')">
        </div>

        <div style="padding: 0 0 0 15px; display: flex; justify-content: center; flex-direction: column; flex: 2">
            <h5 style="margin: 0; margin-bottom: 5px;">
                {$offer.title}
            </h5>
            <p>{($offer.description[$language['id']])|strip_tags}</p>
            <div
                    style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end; margin-top: 20px">
                <h1>+ <span id="dealt-offer-price">{$offer.price}</span>
                </h1>
            </div>
        </div>
    </div>
    <br />
    <div class="row" style="padding: 0 15px">
        <div>
            {if $attached}
                <strong style="color: #24b9d7">
                    {l s="Service already associated with this product in your cart." d='Modules.Dealtmodule.Shop'}

                </strong>
            {else}
                <i>
                    {l s="Check the availability of the service : " d='Modules.Dealtmodule.Shop'}
                </i>
            {/if}

        </div>
        <div {if $attached}style="display: none;" {/if}>
            {include file="../front/form/zipcode.autocomplete.tpl"}
        </div>
    </div>
    <br />
</div>
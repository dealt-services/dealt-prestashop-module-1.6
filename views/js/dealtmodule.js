$(document).ready(function () {
    displayDealtBlock();

});

$(document).on('click', '.color_pick', function (e) {
    displayDealtBlock();
});
$(document).on('change', '.attribute_select', function (e) {
    displayDealtBlock();
});

$(document).on('click', '.attribute_radio', function (e) {
    displayDealtBlock();
});
$(document).ready(function () {
    $('#dealt_id_offer').closest('.form-group').append(
        '<div class="col-lg-4"><button type="button" name="generate" class="btn btn-default" onclick="uuidv4()" ">Generate UUID v4</button></div>'
    );
});
function uuidv4() {
    let uuidv4= ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
    $("#dealt_id_offer").empty().val(uuidv4);
    return true;
}
function displayDealtBlock() {
    var $form = $('input[name=id_product_attribute]').closest('form');
    var query = $form.serialize() + '&show=1&action=update_dealt_block';
    $.ajax({
        type: "POST",
        async: true,
        dataType: "html",
        url: dealt_module_ajax_uri,
        data: query + '&advdealttoken=' + dealt_module_ajax_token + '&action=update_dealt_block' +
            '&id_customer=' + dealt_module_customer +
            '&id_currency=' + dealt_module_currency + '&id_shop=' + dealt_module_shop +
            '&id_cart=' + dealt_module_cart + '&id_lang=' + dealt_module_lang,
        success: function (resp) {

            $('.js-dealt-block-container').html(resp);
        }
    });
}

function actionAjaxAddOffer(elm) {
    let offer_id = $(elm).attr('data-dealt-offer-uuid');
    let zip_code = $(elm).closest('div').find('#dealt-zipcode-autocomplete').val();


    if (zip_code) {
        $.ajax({
            type: "POST",
            async: true,
            dataType: "json",
            url: dealt_module_ajax_uri,
            data: 'advdealttoken=' + dealt_module_ajax_token + '&action=check_offer_availability' +
                '&id_offer=' + offer_id + '&zip_code=' + zip_code,
            success: function (resp) {
                console.log(resp);
                if (resp.status == 200 && resp.response.available === true) {

                    actionAjaxAddToCart(resp.arguments.id_offer);
                }
                if (typeof resp.response.reason !== 'undefined') {
                    $('#dealt-offer-error').text(resp.response.reason).show();
                }
            }
        });
    }
    return false;
}

function actionAjaxAddToCart(id_offer) {
    var $form = $('input[name=id_product_attribute]').closest('form');
    ajaxCart.add( $('#product_page_product_id').val(), $('#idCombination').val(), false, null, $('#quantity_wanted').val(), null);
    $.ajax({
        type: "POST",
        async: true,
        dataType: "json",
        url: dealt_module_ajax_uri,
        data: $form.serialize()+'&advdealttoken=' + dealt_module_ajax_token + '&action=add_to_cart' +
            '&id_offer=' + id_offer+'&id_cart=' + dealt_module_cart,
        success: function (resp) {
            // $('#add_to_cart button[type=submit]').click();

        }
    });
}
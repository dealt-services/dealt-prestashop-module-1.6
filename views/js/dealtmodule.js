$(document).ready(function () {
    displayDealtBlock();

});

$(document).on('click', '.color_pick', function(e){
    displayDealtBlock();
});
$(document).on('change', '.attribute_select', function(e){
    displayDealtBlock();
});

$(document).on('click', '.attribute_radio', function(e){
    displayDealtBlock();
});

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
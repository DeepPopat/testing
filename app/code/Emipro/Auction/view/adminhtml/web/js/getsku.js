require(['jquery', 'jquery/ui'], function ($) {

    $(document).ready(function () {
       $(document).on('keyup',"input[name='product_id']",function () {
            $("input[name='product_id']").autocomplete({
                source: window.availableTags,
            });
        });
    });
});
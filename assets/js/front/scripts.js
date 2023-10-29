jQuery(document).ready(function($){

    $('.aleproperty_add_to_wishlist').on('click',function(e){
        e.preventDefault();
        var id = $(this).data('property-id');

        var aleproperty_add_to_wishlist = {
            success: function(){
                $('#post-'+ id +' .aleproperty_add_to_wishlist').hide(0,function(){
                    $('#post-'+ id +' .succesfull_added').delay(700).show();
                });
            }
        }

        $('#aleproperty_add_to_wishlist_form_'+id).ajaxSubmit(aleproperty_add_to_wishlist);
    });

    
    $('.aleproperty_remove_property').on('click',function(e){
        e.preventDefault();

        var id = $(this).data('property-id');

        $.ajax({
            url: $(this).attr('href'),
            type: "POST",
            data: {
                ale_property_id: $(this).data('property-id'),
                ale_user_id: $(this).data('user-id'),
                action: "aleproperty_remove_wishlist",
            },
            dataType: "html",
            success: function(result){
                $('#post-' + id).hide();
            }
        });
    });
});
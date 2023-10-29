jQuery(document).ready(function($){

    $('#aleproperty_booking_submit').on('click',function(e){

        e.preventDefault();

        $.ajax({
            url: aleproperty_bookingform_var.ajaxurl,
            type: 'post',
            data: {
                action: 'booking_form',
                nonce: aleproperty_bookingform_var.nonce,
                name: $('#aleproperty_name').val(),
                email: $('#aleproperty_email').val(),
                phone: $('#aleproperty_phone').val(),
                price: $('#aleproperty_price').val(),
                location: $('#aleproperty_location').val(),
                agent: $('#aleproperty_agent').val(),
            },
            success: function(data){
                $('#aleproperty_result').html(data);
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });

    });
});
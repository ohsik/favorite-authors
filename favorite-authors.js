jQuery(document).ready( function($) {
    // Add favorite authors 
    $(document).on('click', '#fav_author_button', function(){
        var clicked_id = $(this).data('fav-id');
        console.log(clicked_id);
        
        $.ajax({
            type: 'POST',
            url: fav_authors_ajax_object.ajax_url,
            data: {
                action: 'get_fav_author_id',
                clicked_author_id: clicked_id,
                security: fav_authors_ajax_object.ajax_nonce
            },
            error: function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
                return false;
            },
            success: function(data) {
                console.log(data + ' Added!');
                $('#fav_author_button').html('Unfavorite');
                $('.fav_authors').removeClass('add-fav');
                $('.fav_authors').addClass('rmv-fav');
                $('#fav_author_button').attr('id', 'fav_author_rmove_button');
            }
        });
    });
    
    // Remove favorite authors
    $(document).on('click', '#fav_author_rmove_button', function(){
        var clicked_id = $(this).data('author-id');
        console.log(clicked_id);
        
        $.ajax({
            type: 'POST',
            url: fav_authors_ajax_object.ajax_url,
            data: {
                action: 'remove_fav_author_id',
                clicked_author_id: clicked_id,
                security: fav_authors_ajax_object.ajax_nonce
            },
            error: function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
                return false;
            },
            success: function(data) {
                console.log(data + ' Deleted!');
                $('#fav_author_rmove_button').html('<span class="dashicons dashicons-star-filled"></span> Favorite');
                $('.fav_authors').removeClass('rmv-fav');
                $('.fav_authors').addClass('add-fav');
                $('#fav_author_rmove_button').attr('id', 'fav_author_button');
                $('*[data-author-id=' + clicked_id + ']').closest('li').fadeOut();
            }
        });
    });
    
});
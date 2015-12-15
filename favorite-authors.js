jQuery(document).ready( function($) {
    // Add favorite authors 
    $(document).on('click', '#fav_author_button', function(){
        var clicked_id = $(this).data('author-id');
        //console.log(clicked_id);
        
        if(typeof clicked_id === 'undefined'){
            console.log('author ID is not passed to add.');
            return false;
        }
        
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
                //console.log(data + ' Added!');
                $('[data-author-id=' + clicked_id + ']').html('<span class="dashicons dashicons-yes"></span> Favorited!');
                $('[data-author-id=' + clicked_id + ']').attr('id', 'fav_author_rmove_button');
                $('[data-author-id=' + clicked_id + ']').removeClass('add-fav');
                $('[data-author-id=' + clicked_id + ']').addClass('rmv-fav');
            }
        });
    });
    
    // Remove favorite authors
    $(document).on('click', '#fav_author_rmove_button', function(){
        var clicked_id = $(this).data('author-id');
        //console.log(clicked_id);
    
        if(typeof clicked_id === 'undefined'){
            console.log('author ID is not passed to remove.');
            return false;
        }
     
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
                //console.log(data + ' Deleted!');
                $('[data-author-id=' + clicked_id + ']').html('<span class="dashicons dashicons-star-filled"></span> Favorite');
                $('[data-author-id=' + clicked_id + ']').attr('id', 'fav_author_button');
                $('[data-author-id=' + clicked_id + ']').removeClass('rmv-fav');
                $('[data-author-id=' + clicked_id + ']').addClass('add-fav');
            }
        });
    });
    
    // Pagination on favorit author list
    $(document).on('click', '#fav_pagi', function(){
        var clicked_id = $(this).data('fav-pid');
        //console.log(clicked_id);
    
        if(typeof clicked_id === 'undefined'){
            console.log('Page ID is not passed.');
            return false;
        }
     
        $.ajax({
            type: 'POST',
            url: fav_authors_ajax_object.ajax_url,
            data: {
                action: 'fav_au_pagi',
                clicked_author_id: clicked_id,
                security: fav_authors_ajax_object.ajax_nonce
            },
            error: function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
                return false;
            },
            success: function(data) {
                //console.log(data + ' Next page');
                $('#fav-authors-list').html('');
                $(document).scrollTop(0);
                $('#fav-authors-list').append(data);
                $('[data-fav-pid]').removeClass('fa_current');
                $('[data-fav-pid=' + clicked_id + ']').addClass('fa_current');
            }
        });
    });
    
});
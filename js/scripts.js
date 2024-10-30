/**
 * Javascript
 */

jQuery(document).ready(function($) {

    /**
     * AJAX update status
     */
    $('.editable_ping_comment').click(function(e) {

        postId = $(this).val();
        postField = $(this).data('type') + '_status';
        postFieldValue = ( $(this).is(":checked") ? 'open' : 'closed' );

        var resultStatus = $('#result_' + $(this).data('type') +'_' + postId);
        resultStatus.show();

        $.ajax({
            url : ajaxurl,
            type : 'post',
            data : {
                action : 'update_pings_comments_status',
                postId : postId,
                postField : postField,
                postFieldValue : postFieldValue,
            },
            success : function( response ) {
                resultStatus.html('Saved.');
                resultStatus.fadeOut(1000);
            }
        });

    });

    /**
     * Disabling checkboxes not to be includes in search get request
     */
    $('#posts-filter').submit( function() {
        $('.prevent-submit').attr('disabled', true);
    });

});


jQuery(document).ready(function($) {
    $('#deploy').on('click', function(e) {
        e.preventDefault();
        $('#deploy_message').html('Deploying...');
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'deploy',
                nonce: ajax_object.nonce
            },
            success: function(response) {
                let json = JSON.parse(response.data); 
                if(typeof json.message !== 'undefined') {
                    $('#deploy_message').html(json.message);
                }
                console.log(json);
            },
            error: function(error) {
                alert('Error: ' + error.responseText);
            }
        });
    });

});

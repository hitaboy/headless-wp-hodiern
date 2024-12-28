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
                if(response.data === '') {
                    $('#deploy_message').html('Done! Wait 5 minutes then select a build to apply.');
                }else{
                    let json = JSON.parse(response.data); 
                    if(typeof json.message !== 'undefined') {
                        $('#deploy_message').html(json.message);
                    }
                }
            },
            error: function(error) {
                alert('Error: ' + error.responseText);
            }
        });
    });

    $('#apply_build').on('click', function(e) {
        e.preventDefault();
        $('#apply_build_message').html('Applying...');
        let build = $("[data-key=field_676dde168edb9]").find('input').val();
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'apply_build',
                nonce: ajax_object.nonce,
                build: build
            },
            success: function(response) {
                console.log(response);
                $('#apply_build_message').html('Done!');
            },
            error: function(error) {
                alert('Error: ' + error.responseText);
            }
        });
    });

});

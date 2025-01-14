let state = 'idle';
let init_builds = 0;
let all_files = [];
jQuery(document).ready(function($) {
    
    function fetchRecentBuildZips() {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_recent_build_zips',
                nonce: ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    let files = response.data;
                    all_files = files;
                    if(init_builds === 0) {
                        init_builds = files.length;
                    }
                    if(files.length > init_builds) {
                        state = 'new build available';
                    }
                    let list = $('#recent_build_zips_list');
                    list.empty();
                    files.forEach((file, i) => {
                        if(i==0 && files.length == init_builds && state === 'deploying') {
                            list.append(`<li style="display: flex; justify-content: space-between; align-items: center; background: #f0f0f1; border-radius: 3px; padding: 5px; border: 1px solid #888;">
                                <a href="${file.guid}" target="_blank">${file.post_title}</a>
                                <div>${file.post_date}</div>
                                <div class="button button-primary">Please wait...</div>
                                <div id="apply_build_`+i+`" style="color: #ccc; font-style: italic;"></div>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="32" height="32"><path fill="#2271B1" stroke="#2271B1" stroke-width="15" transform-origin="center" d="m148 84.7 13.8-8-10-17.3-13.8 8a50 50 0 0 0-27.4-15.9v-16h-20v16A50 50 0 0 0 63 67.4l-13.8-8-10 17.3 13.8 8a50 50 0 0 0 0 31.7l-13.8 8 10 17.3 13.8-8a50 50 0 0 0 27.5 15.9v16h20v-16a50 50 0 0 0 27.4-15.9l13.8 8 10-17.3-13.8-8a50 50 0 0 0 0-31.7Zm-47.5 50.8a35 35 0 1 1 0-70 35 35 0 0 1 0 70Z"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="2" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>
                                </li>`);
                        }
                        if(i==0 && files.length > init_builds){
                            list.append(`<li style="display: flex; justify-content: space-between; align-items: center; background: #f0f0f1; border-radius: 3px; padding: 5px; border: 1px solid #888;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#008800" viewBox="0 0 256 256"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                <a href="${file.guid}" target="_blank">${file.post_title}</a>
                                <div>${file.post_date}</div>
                                <div class="button button-primary" onClick="apply_build(`+i+`);">Apply build</div>
                                <div id="apply_build__`+i+`" style="color: #ccc; font-style: italic;"></div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M223.16,68.42l-16-32A8,8,0,0,0,200,32H56a8,8,0,0,0-7.16,4.42l-16,32A8.08,8.08,0,0,0,32,72V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V72A8.08,8.08,0,0,0,223.16,68.42ZM60.94,48H195.06l8,16H52.94ZM208,208H48V80H208V208Zm-42.34-61.66a8,8,0,0,1,0,11.32l-32,32a8,8,0,0,1-11.32,0l-32-32a8,8,0,0,1,11.32-11.32L120,164.69V104a8,8,0,0,1,16,0v60.69l18.34-18.35A8,8,0,0,1,165.66,146.34Z"></path></svg>
                                </li>`);
                        }else{
                            list.append(`<li style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; border-radius: 3px; padding: 5px; border: 1px solid #888;">
                                <a href="${file.guid}" target="_blank">${file.post_title}</a>
                                <div>${file.post_date}</div>
                                <div class="button button-primary" onClick="apply_build(`+i+`);">Apply build</div>
                                <div id="apply_build__`+i+`" style="color: #ccc; font-style: italic;"></div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#666666" viewBox="0 0 256 256"><path d="M223.16,68.42l-16-32A8,8,0,0,0,200,32H56a8,8,0,0,0-7.16,4.42l-16,32A8.08,8.08,0,0,0,32,72V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V72A8.08,8.08,0,0,0,223.16,68.42ZM60.94,48H195.06l8,16H52.94ZM208,208H48V80H208V208Zm-42.34-61.66a8,8,0,0,1,0,11.32l-32,32a8,8,0,0,1-11.32,0l-32-32a8,8,0,0,1,11.32-11.32L120,164.69V104a8,8,0,0,1,16,0v60.69l18.34-18.35A8,8,0,0,1,165.66,146.34Z"></path></svg>
                                </li>`);
                        }
                    });
                } else {
                    console.error('Error fetching recent build zips:', response.data);
                }
            },
            error: function(error) {
                console.error('Error fetching recent build zips:', error.responseText);
            }
        });
    }

    // Fetch the list initially
    fetchRecentBuildZips();

    // Set an interval to fetch the list every 5 seconds
    setInterval(fetchRecentBuildZips, 5000);

    $.ajax({
        url: ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'fetch_github_metrics',
            nonce: ajax_object.nonce
        },
        success: function(response) {
            if (response.success) {
                let metrics = response.data;
                
                let max_usage = 2000*60*1000;
                let percent_usage = metrics[0].timing.billable.UBUNTU.total_ms*100/max_usage;
                let html = ''
                // html += '<pre>' + JSON.stringify(metrics, null, 2) + '</pre>';
                html += '<div style="position: relative; background: #f0f0f1; padding: 10px; border-radius: 3px; border: 1px solid #888;">';
                html += '<span style="position: relative; z-index: 10; padding: 2px; border-radius: 3px; background: rgba(256,256,256,0.8);">Github Free Usage: ' + percent_usage+ '%</span>';
                html += '<div style="background: #2271b1; z-index: 5; position: absolute; top: 0; left: 0; height: 100%; width: '+(percent_usage)+'%; filter:"></div></div>';
                let container = $('#github_metrics_container');
                container.html(html);
            } else {
                alert('Error: ' + response.data);
            }
        },
        error: function(error) {
            alert('Error: ' + error.responseText);
        }
    });

    $('#deploy').on('click', function(e) {
        e.preventDefault();
        state = 'deploying';
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

});

function apply_build(i) {
    
    jQuery('#apply_build_'+i).html('Applying...');
    
    let build = all_files[i].guid;
    jQuery.ajax({
        url: ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'apply_build',
            nonce: ajax_object.nonce,
            build: build
        },
        success: function(response) {
            console.log(response);
            jQuery('#apply_build_'+i).html('Done!');
        },
        error: function(error) {
            alert('Error: ' + error.responseText);
        }
    });
    
};
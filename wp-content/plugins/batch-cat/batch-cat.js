jQuery(document).ready(function($){
    function bcat_get_posts()
    {
        var posts = $('input:checkbox[name="postid[]"]:checked');
        var postids = new Array();
        for (var i=0;i<posts.length;i++) {
            var post = posts[i];
            postids.push(post.value);
        }
        return postids
    }

    function bcat_get_cats()
    {
        var cats = $('input:checkbox[name="catid[]"]:checked');
        var catids = new Array();
        for (var i=0;i<cats.length;i++) {
            var cat = cats[i];
            catids.push(cat.value);
        }
        return catids;
    }

    function bcat_check_all(postids, catids)
    {
        if (postids.length == 0) {
            alert('Please select at least one post !');
            return false;
        }
        if (catids.length == 0) {
            alert('Please select at least one category !');
            return false;
        } else {
            if (1 != confirm('Are you sure to make this changement ?')) {
                return false;
            }
        }
        return true;
    }

    function bcat_submit_action(action)
    {
        var postids = bcat_get_posts();
        var catids = bcat_get_cats();
        if (bcat_check_all(postids, catids) === false) {
            return false;
        }

        // Send the AJAX request
        var poststr = postids.join(',');
        var catstr = catids.join(',');
        var data = {
            action: action,
            post_ids: poststr,
            cat_ids: catstr
        };
        $.post(ajaxurl, data, function(response) {
            if (0 != response) {
                alert('Failed to change categories: '+response);
            }
            // Make URL query string
            var search = location.search;
            var searcharr = search.split('&');
            var hasparam = search.indexOf('cats=');
            if (hasparam >= 0) {
                for (var i=0;i<searcharr.length;i++) {
                    if (searcharr[i].indexOf('cats=') == 0) {
                        searcharr[i] = 'cats='+catstr;
                    }
                } 
            } else {
                searcharr.push('cats='+catstr);
            }
            var url = location.pathname+searcharr.join('&');
            location.href = url;
        });

        return true;
    }

    // Manipulate the category dropdown list
    $('#slct_category').prepend('<option value="">Category</option>');
    if (!location.href.match(/cat=([1-9]+)/)) {
        $('#slct_category')[0].selectedIndex = 0;
    }

    // Manipulate the sort type
    if (!location.href.match(/sort=(post_date|modify_date)/)) {
        $('#slct_sortby')[0].selectedIndex = 0;
    } else if (location.href.match(/sort=post_date/)) {
        $('#slct_sortby')[0].selectedIndex = 0;
    } else if (location.href.match(/sort=modify_date/)) {
        $('#slct_sortby')[0].selectedIndex = 1;
    }

    // Manipulate the order type
    if (location.href.match(/order=asc/)) {
        $('#rdo_order_1')[0].checked = true;
    } else {
        $('#rdo_order_2')[0].checked = true;
    }

    // Manipulate the selected categories
    if (location.href.match(/cats=/)) {
        var searcharr = location.search.split('&');
        for (var i=0;i<searcharr.length;i++) {
            if (searcharr[i].indexOf('cats=') == 0) {
                var cats = searcharr[i].substring(searcharr[i].indexOf('=')+1,searcharr[i].length).split(',');
                for (var j=0;j<cats.length;j++) {
                    $('input:checkbox[name="catid[]"]').each(function(){
                        if ($(this)[0].value == cats[j]) {
                            $(this)[0].checked = true;
                        }
                    });
                }
                break;
            }
        }
    }

    // Add hook to the search button
    $('#btn_bcat_search').click(function(){
        var basepath = location.pathname;
        var paramstr = location.search.substring(location.search.indexOf('?')+1, location.search.length);
        var arrparam = paramstr.split('&');
        var cat = $('#slct_category').val();
        var sort = $('#slct_sortby').val();
        var order = $("input:radio[name='rdo_order']:checked").val();
        var keyword = urlencode($('#input_keyword').val());

        var url = basepath+'?'+arrparam[0];
        url += '&cat='+cat;
        url += '&sort='+sort;
        url += '&order='+order;
        url += '&s='+keyword;

        location.href = url;
    });

    // Add hook to the toggle button on the top left corner of the posts table
    $('#toggle_posts').change(function(){
        $('input:checkbox[name="postid[]"]').each(function(){
            $(this)[0].checked = $('#toggle_posts')[0].checked;
        });
    });

    // Add hook to the toggle button on the top left corner of the categories table
    $('#toggle_categories').change(function(){
        $('input:checkbox[name="catid[]"]').each(function(){
            $(this)[0].checked = $('#toggle_categories')[0].checked;
        });
    });

    // Add hook to the submit button for changing categories
    $('#btn_set_cats').click(function(){
        return bcat_submit_action('bcat_set_category');
    });
    $('#btn_add_cats').click(function(){
        return bcat_submit_action('bcat_add_category');
    });
    $('#btn_del_cats').click(function(){
        return bcat_submit_action('bcat_del_category');
    });
});

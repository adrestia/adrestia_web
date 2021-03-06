function element_in_scroll(elem) {
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}

$(document).scroll(function(e){
    handleScroll(e);
});

function handleScroll(e) {
    var elem = ".row.post:last";
    var offset = parseInt($(elem).attr('data-post-count')) + 1;
    if(element_in_scroll(elem)) {
        $(document).unbind('scroll');
        $.ajax({
            type: "POST",
            url: "/more/" + $("#sorting").text().toLowerCase(),
            data: { offset: offset }
        }).done(function( response ) {
            var posts = $.parseJSON(response);
            if (posts.length !== 0) {
                var offset = $(elem).attr('data-post-count');
                for(post of posts) {
                    $(elem).after(make_post(post, ++offset));
                }
                $(document).scroll(handleScroll);
            } else {
                $(elem).after("<div class=\"no-more\"><h5> There doesn't appear to be anything left!</h5></div>");
            }
        }).error(function(response) {
            alert(response);
        });
    }
}

function formatDate(date) {
    var year = date.getFullYear(),
        month = date.getMonth() + 1, // months are zero indexed
        day = date.getDate(),
        hour = date.getHours(),
        minute = date.getMinutes(),
        second = date.getSeconds(),
        hourFormatted = hour, // hour returned in 24 hour format
        minuteFormatted = minute < 10 ? "0" + minute : minute;

    return year + "-" + month + "-" + day + " " + hourFormatted + ":" +
            minuteFormatted;
}

function timeDifference(current, previous) {

    var msPerMinute = 60 * 1000;
    var msPerHour = msPerMinute * 60;
    var msPerDay = msPerHour * 24;
    var msPerMonth = msPerDay * 30;
    var msPerYear = msPerDay * 365;

    var elapsed = current - previous;

    if (elapsed < msPerMinute) {
         return Math.round(elapsed/1000) + ' seconds ago';   
    } else if (elapsed < msPerHour) {
         return Math.round(elapsed/msPerMinute) + ' minutes ago';   
    } else if (elapsed < msPerDay ) {
         return Math.round(elapsed/msPerHour ) + ' hours ago';   
    } else if (elapsed < msPerMonth) {
        return Math.round(elapsed/msPerDay) + ' days ago';   
    } else if (elapsed < msPerYear) {
        return Math.round(elapsed/msPerMonth) + ' months ago';   
    } else {
        return Math.round(elapsed/msPerYear ) + ' years ago';   
    }
}

function correctDate(date) {
    return new Date(date.replace(/([^-]+)-([^-]+)-([^-]+)(.+)/g, function(match, p1, p2, p3, p4, offset, string) { return p1 + '/' + p2 + '/' + p3 + p4 }).replace('T',' '));
}

function make_post(post, index) {
    // Make the post URL
    var post_url = "/posts/" + post.p_id;
    
    // Get the body of the post
    if(post.p_body.length > 150) {
        var body = "<p class=\"post_body\">" + post.p_body.slice(0, 140) + "<a href=" + post_url + "> More...</a></p>";
    } else {
        var body = "<p class=\"post_body\">" + post.p_body + "</p>";
    }
    
    var upvote = post.l_id !== undefined && post.l_is_like ? "blue" : "";
    var downvote = post.l_id !== undefined && !post.l_is_like ? "pink" : "";
    
    // HTML for the post 
    var post = "<div class=\"row post\" data-post-id=" + post.p_id + " data-post-count=" + index + "> \
                    <div class=\"small-11 columns body\"> \
                        " + body  + " \
                        <a href=\"#\" class=\"post_report\" data-post-id=" + post.id + ">Report</a> \
                        <a href=" + post_url + " class=\"view_post\">Comments (" + post.comments + ")</a> \
                        <span class=\"post_time\" data-post-id=" + post.id + " title=" + formatDate(correctDate(post.p_created)) + ">" + timeDifference(new Date(), correctDate(post.p_created)) + "</span> \
                    </div> \
                    <div class=\"small-1 columns score\"> \
                        <i class=\"fi-arrow-up post_upvote " + upvote + "\" data-post-id=" + post.p_id + "></i><br> \
                        <span class=\"score_number\" data-post-id=" + post.p_id + ">" + (post.p_upvotes - post.p_downvotes) +"</span><br> \
                        <i class=\"fi-arrow-down post_downvote " + downvote + "\" data-post-id=" + post.p_id + "></i><br> \
                    </div> \
                </div>"
    
    return post;
}
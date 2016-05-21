$(".post_upvote").on('click', function() {
    var upvote = $(this); 
    var post_id = $(this).attr('data-post-id');
  
    $.post("/posts/upvote", { post_id: post_id })
    .done(function(data) {
        if(data.status === 200) {
            $(".score_number[data-post-id=" + post_id + "]").text(data.score);
            upvote.toggleClass('blue');
            $(".post_downvote[data-post-id=" + post_id + "]").removeClass('pink');
        } else {
            alert(data.message);
        }
    })
    .error(function(data) {
        console.error(data.message);
    })
})

$(".post_downvote").on('click', function() {
    var downvote = $(this);
    var post_id = $(this).attr('data-post-id');
  
    $.post("/posts/downvote", { post_id: post_id })
    .done(function(data) {
        if(data.status === 200) {
            $(".score_number[data-post-id=" + post_id + "]").text(data.score);
            downvote.toggleClass('pink');
            $(".post_upvote[data-post-id=" + post_id + "]").removeClass('blue');
        } else {
            alert(data.message);
        }
    })
    .error(function(data) {
        console.error(data.message);
    })
})

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
    console.log(offset);
    if(element_in_scroll(elem)) {
        $(document).unbind('scroll');
        $.ajax({
            type: "POST",
            url: document.location.href,
            data: { offset: offset }
        }).done(function( response ) {
            var posts = $.parseJSON(response.posts);
            if (posts.length !== 0) {
                var offset = $(elem).attr('data-post-count');
                for(post of posts) {
                    $(elem).after(make_post(post, ++offset));
                }
                $(document).scroll(handleScroll);
            } else {
                $(elem).after("<div class=\"no-more\"><h5> There doesn't appear to be anything left!</h5></div>");
            }
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
    var post_url = "/posts/" + post.id;
    
    // Get the body of the post
    if(post.body.length > 150) {
        var body = "<p class=\"post_body\">" + post.body.slice(0, 140) + "<a href=" + post_url + "> More...</a></p>";
    } else {
        var body = "<p class=\"post_body\">" + post.body + "</p>";
    }
    
    var upvote = post.likes.length > 0 && post.likes[0].is_like ? "blue" : "";
    var downvote = post.likes.length > 0 && !post.likes[0].is_like ? "pink" : "";
    
    // HTML for the post 
    var post = "<div class=\"row post\" data-post-id=" + post.id + " data-post-count=" + index + "> \
                    <div class=\"small-11 columns body\"> \
                        " + body  + " \
                        <a href=\"#\" class=\"post_report\" data-post-id=" + post.id + ">Report</a> \
                        <a href=" + post_url + " class=\"view_post\">Comments " + post.comments.length + "</a> \
                        <span class=\"post_time\" data-post-id=" + post.id + " title=" + formatDate(correctDate(post.created)) + ">" + timeDifference(new Date(), correctDate(post.created)) + "</span> \
                    </div> \
                    <div class=\"small-1 columns score\"> \
                        <i class=\"fi-arrow-up post_upvote " + upvote + "\" data-post-id=" + post.id + "></i><br> \
                        <span class=\"score_number\" data-post-id=" + post.id + ">" + (post.upvotes - post.downvotes) +"</span><br> \
                        <i class=\"fi-arrow-down post_downvote " + downvote + "\" data-post-id=" + post.id + "></i><br> \
                    </div> \
                </div>"
    
    return post;
}
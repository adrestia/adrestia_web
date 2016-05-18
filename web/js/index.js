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
    if (element_in_scroll(".row.post:last")) {
        //console.log("At bottom!");
        $(document).unbind('scroll');
        $.ajax({
            type: "POST",
            url: document.location.href,
            data: { text_filter:  $('#text_filter').attr('value'), index_count:$('#index_count').attr('value'),json: "true" }
        }).done(function( msg ) {
            $(".errors tbody ").append(msg.html);
            $('#index_count').attr('value',msg.index_count);
            if (msg.count != 0) {
                $(document).scroll(function(e){
                    //callback to the method to check if the user 
                    // scrolled to the last element of your list/feed 
                })
            }
        });
    }
});
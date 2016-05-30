$(function() {
    $("#post_submit").prop('disabled', true);
});

$("#post_form").submit(function(event) {
    var body = $("#textarea");
    $.post("/posts/new", { body: body.val() })
    .done(function( data ) {
        window.location = "/posts/" + data.post_id;
    })
    .error(function(xhr, status, error) {
        var data = JSON.parse(xhr.responseText);
        $("#post_error_text").text("Error: " + data.message);
    });
    event.preventDefault();
});

$("#textarea").bind('input propertychange', function(event) {
    var remaining = 1024 - $(this).val().length - ($(this).val().match(/\n/g)||[]).length;
    $("#c_count").html("Remaining: " + remaining);
  
    if(remaining <= 0) {
        $("#c_count").removeClass().addClass('alert');
        $("#post_submit").prop('disabled', false);
    } else if(remaining < 100) {
        $("#c_count").removeClass().addClass('warning');
        $("#post_submit").prop('disabled', false);
    } else if(remaining > 974) {
        $("#c_count").html("Keep typing (" + (remaining - 974) + ")");
        $("#post_submit").prop('disabled', true);
    } else {
        $("#c_count").removeClass();
        $("#post_submit").prop('disabled', false);
    }
  
    if(remaining < 0) {
        $("#post_submit").prop('disabled', true);
    }
});

$(document).on('click', ".post_upvote", function() {
    var upvote = $(this); 
    var post_id = $(this).attr('data-post-id');
  
    var score = parseInt($(".score_number[data-post-id=" + post_id + "]").text());
    var classes = upvote.toggleClass('blue');
    if(classes.hasClass('blue')) {
        $(".score_number[data-post-id=" + post_id + "]").text(score + 1);
    } else {
        $(".score_number[data-post-id=" + post_id + "]").text(score - 1);
    }
    if($(".post_downvote[data-post-id=" + post_id + "]").hasClass('pink')) {
        $(".score_number[data-post-id=" + post_id + "]").text(score + 2);
        $(".post_downvote[data-post-id=" + post_id + "]").removeClass('pink');
    }
  
    $.post("/posts/upvote", { post_id: post_id })
    .done(function(data) {
        if(data.status !== 200) {
            alert(data.message);
        }
    })
    .error(function(xhr, status, error) {
        var data = JSON.parse(xhr.responseText);
        console.error(data.message);
    })
});

$(document).on('click', ".post_downvote", function() {
    var downvote = $(this);
    var post_id = $(this).attr('data-post-id');
  
    var score = parseInt($(".score_number[data-post-id=" + post_id + "]").text());
    var classes = downvote.toggleClass('pink');
    if(classes.hasClass('pink')) {
        $(".score_number[data-post-id=" + post_id + "]").text(score - 1);
    } else {
        $(".score_number[data-post-id=" + post_id + "]").text(score + 1);
    }
    if($(".post_upvote[data-post-id=" + post_id + "]").hasClass('blue')) {
        $(".score_number[data-post-id=" + post_id + "]").text(score - 2);
        $(".post_upvote[data-post-id=" + post_id + "]").removeClass('blue');
    }
  
    $.post("/posts/downvote", { post_id: post_id })
    .done(function(data) {
        if(data.status !== 200) {
            alert(data.message);
        }
    })
    .error(function(xhr, status, error) {
        var data = JSON.parse(xhr.responseText);
        console.error(data.message);
    })
});

$(document).on('click', ".post_remove", function() {
    var confirmed = confirm("Are you sure you want to delete this post?");
    if(confirmed) {
        var post_id = $(this).attr('data-post-id');
    
        $.ajax({
            url: '/posts/remove',
            type: 'DELETE',
            data: { post_id: post_id },
            success: function(data) {
                if(data.status === 200) {
                    window.location = "/";
                } else {
                    alert(data.message);
                }
            },
            error: function(xhr, status, error) {
                var data = JSON.parse(xhr.responseText);
                console.error(data.message);
            }
        });
    }
});
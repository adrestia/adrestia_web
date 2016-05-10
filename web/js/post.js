$(function() {
  $("#post_submit_success").hide();
  $("#post_submit_error").hide();
});

$("#post_form").submit(function(event) {
  var body = $("textarea");
  $.post("/posts/new", { body: body.val() })
    .done(function( data ) {
      if(data.status === 200) {

        $("#post_submit_success").show();
        setTimeout(function() {
          $("#post_submit_success").fadeOut();
        }, 2000);
        body.val("");
      } else {
        $("#post_submit_error").show();
        setTimeout(function() {
          $("#post_submit_error").fadeOut();
        }, 2000);
      }
    })
    .error(function( data ) {
      console.error(data);
    });
  event.preventDefault();
});

$(document).on('click', ".post_upvote", function() {
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
});

$(document).on('click', ".post_downvote", function() {
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
        error: function(data) {
          console.error(data.message);
        }
    });
  }
});
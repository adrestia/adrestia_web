$(function() {
  $("#post_submit_success").hide();
  $("#post_submit_error").hide();
});

$("#post_form").submit(function() {
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

$(".upvote").on('click', function() {
  var post_id = $(this).attr('data-post-id');
  
  $.post("/upvote", { post_id: post_id })
    .done(function(data) {
      $(".score_number").text(data.score);
      $(".upvote").toggleClass('blue');
      $(".downvote").removeClass('pink');
    })
    .error(function(data) {
      console.error(data.message);
    })
})

$(".downvote").on('click', function() {
  var post_id = $(this).attr('data-post-id');
  
  $.post("/downvote", { post_id: post_id })
    .done(function(data) {
      $(".score_number").text(data.score);
      $(".downvote").toggleClass('pink');
      $(".upvote").removeClass('blue');
    })
    .error(function(data) {
      console.error(data.message);
    })
})
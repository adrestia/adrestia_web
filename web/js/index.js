//var elem = new Foundation.Tabs($("#sort_selector"));

$(".upvote").on('click', function() {
  var upvote = $(this); 
  var post_id = $(this).attr('data-post-id');
  
  $.post("/upvote", { post_id: post_id })
    .done(function(data) {
      if(data.status === 200) {
        $(".score_number[data-post-id=" + post_id + "]").text(data.score);
        upvote.toggleClass('blue');
        $(".downvote[data-post-id=" + post_id + "]").removeClass('pink');
      } else {
        alert(data.message);
      }
    })
    .error(function(data) {
      console.error(data.message);
    })
})

$(".downvote").on('click', function() {
  var downvote = $(this);
  var post_id = $(this).attr('data-post-id');
  
  $.post("/downvote", { post_id: post_id })
    .done(function(data) {
      if(data.status === 200) {
        $(".score_number[data-post-id=" + post_id + "]").text(data.score);
        downvote.toggleClass('pink');
        $(".upvote[data-post-id=" + post_id + "]").removeClass('blue');
      } else {
        alert(data.message);
      }
    })
    .error(function(data) {
      console.error(data.message);
    })
})
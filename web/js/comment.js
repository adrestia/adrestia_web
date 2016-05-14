$("#comment_form").submit(function(event) {
  var body = $("#comment_body");
  console.log(body);
  $.post("/comments", {body: body.val(), post_id: body.attr('data-post-id') })
    .done(function(data) {
      if(data.status === 200) {
        
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; 

        var yyyy = today.getFullYear();
        var H = today.getHours();
        var m = today.getMinutes();
        var i = today.getSeconds();
        if(dd<10) {
          dd='0'+dd;
        } 
        if(mm<10) {
          mm='0'+mm;
        } 
        if(i<10) {
          i='0'+i;
        }
        var now = yyyy+'-'+mm+'-'+dd+' '+H+':'+m+':'+i;
        
        if(data.is_op) {
          var comment = $(".post_comment").before("<div class=\"row\"> \
                                       <div class=\"small-10 medium-8 small-centered columns\"> \
                                          <div class=\"op_row row\"> \
                                            <div class=\"small-12 columns\"> \
                                              <div class=\"row comment op\" data-comment-id=\"" + data.comment_id + "\"> \
                                                   <div class=\"small-11 columns body\"> \
                                                       <p class=\"comment_body\">" + body.val() + "</p> \
                                                       <a href=\"#\" class=\"comment_report\" data-comment-id=\"" + data.comment_id + "\">Report</a> \
                                                       <span class=\"comment_time\" data-post-id=\"" + data.comment_id + "\" title=\"" + now + "\">Just now</span> \
                                                   </div> \
                                                   <div class=\"small-1 columns score\"> \
                                                       <i class=\"fi-arrow-up comment_upvote\" data-comment-id=\"" + data.comment_id + "\"></i><br> \
                                                       <span class=\"score_number\" data-comment-id=\"" + data.comment_id + "\">0</span><br> \
                                                       <i class=\"fi-arrow-down comment_downvote\" data-comment-id=\"" + data.comment_id + "\"></i><br> \
                                                   </div> \
                                               </div> \
                                            </div> \
                                          </div> \
                                        </div> \
                                      </div>");
        } else {
          var comment = $(".post_comment").before("<div class=\"row\"> \
                                       <div class=\"small-10 medium-8 small-centered columns\"> \
                                          <div class=\"row comment\" data-comment-id=\"" + data.comment_id + "\"> \
                                               <div class=\"small-11 columns body\"> \
                                                   <p class=\"comment_body\">" + body.val() + "</p> \
                                                   <a href=\"#\" class=\"comment_report\" data-comment-id=\"" + data.comment_id + "\">Report</a> \
                                                   <span class=\"comment_time\" data-post-id=\"" + data.comment_id + "\" title=\"" + now + "\">Just now</span> \
                                               </div> \
                                               <div class=\"small-1 columns score\"> \
                                                   <i class=\"fi-arrow-up comment_upvote\" data-comment-id=\"" + data.comment_id + "\"></i><br> \
                                                   <span class=\"score_number\" data-comment-id=\"" + data.comment_id + "\">0</span><br> \
                                                   <i class=\"fi-arrow-down comment_downvote\" data-comment-id=\"" + data.comment_id + "\"></i><br> \
                                               </div> \
                                           </div> \
                                        </div> \
                                      </div>");
        }
        body.val("");
      } else {
        $("#comment_submit_error").show();
        setTimeout(function() {
          $("#comment_submit_error").fadeOut();
        }, 2000);
        //console.error("Error");
      }
    })
    .error(function( data ) {
      console.error(data);
    });
  event.preventDefault();
});

$(document).on('click', ".comment_upvote", function() {
  var upvote = $(this); 
  var comment_id = $(this).attr('data-comment-id');
  
  $.post("/comments/upvote", { comment_id: comment_id })
    .done(function(data) {
      if(data.status === 200) {
        $(".score_number[data-comment-id=" + comment_id + "]").text(data.score);
        upvote.toggleClass('blue');
        $(".comment_downvote[data-comment-id=" + comment_id + "]").removeClass('pink');
      } else {
        alert(data.message);
      }
    })
    .error(function(data) {
      console.error(data.message);
    })
});

$(document).on('click', ".comment_downvote", function() {
  var downvote = $(this);
  var comment_id = $(this).attr('data-comment-id');
  
  $.post("/comments/downvote", { comment_id: comment_id })
    .done(function(data) {
      if(data.status === 200) {
        $(".score_number[data-comment-id=" + comment_id + "]").text(data.score);
        downvote.toggleClass('pink');
        $(".comment_upvote[data-comment-id=" + comment_id + "]").removeClass('blue');
      } else {
        alert(data.message);
      }
    })
    .error(function(data) {
      console.error(data.message);
    })
});

$(document).on('click', ".comment_remove", function() {
  var confirmed = confirm("Are you sure you want to delete this comment?");
  if(confirmed) {
    var comment_id = $(this).attr('data-comment-id');
    
    $.ajax({
        url: '/comments/remove',
        type: 'DELETE',
        data: { comment_id: comment_id },
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
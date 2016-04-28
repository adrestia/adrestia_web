$(function() {
  $("#post_submit_success").hide();
  $("#post_submit_error").hide();
});

$("#post_form").submit(function() {
  var body = $("textarea");
  $.post("posts", { body: body.val() })
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
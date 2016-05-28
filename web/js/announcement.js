$("#post_form").submit(function(event) {
  var body = $("#textarea");
  $.post("/announcements/new", { body: body.val() })
    .done(function( data ) {
        window.location = "/announcements/" + data.announcement_id;
    })
    .error(function( data ) {
      console.error(data);
    });
  event.preventDefault();
});

$("#textarea").bind('input propertychange', function(event) {
  var remaining = 4096 - $(this).val().length - ($(this).val().match(/\n/g)||[]).length;
  $("#c_count").html(remaining);
  
  if(remaining <= 0) {
    $("#c_count").removeClass().addClass('alert');
  } else if(remaining < 100) {
    $("#c_count").removeClass().addClass('warning');
  } else {
    $("#c_count").removeClass();
  }
  
  if(remaining < 0) {
    $("#post_submit").prop('disabled', true);
  } else {
    $("#post_submit").prop('disabled', false);
  }
});
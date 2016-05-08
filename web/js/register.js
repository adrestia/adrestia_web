$(function() {
  if($('#user_college option:selected').text() !== "Select College") {
    var college = $("#user_college option:selected").text();
    $.post('/suffix', { college: college })
    .done(function(data) {
      if(data.status === 200) {
        $('#email_suffix').html("@" + data.suffix);
        $("#user_email").prop('disabled', false);
        $("#user_plainPassword_first").prop('disabled', false);
        $("#user_plainPassword_second").prop('disabled', false);
      } else {
        alert(data.message);
      }
    })
    .error(function(data) {
      alert(data.message);
    });
    $("#user_email").prop('disabled', false);
    $("#user_plainPassword_first").prop('disabled', false);
    $("#user_plainPassword_second").prop('disabled', false);
  }
})
$('#user_college').change(function() {
  var college = $("#user_college option:selected").text();
  $.post('/suffix', { college: college })
  .done(function(data) {
    if(data.status === 200) {
      $('#email_suffix').html("@" + data.suffix);
      $("#user_email").prop('disabled', false);
      $("#user_plainPassword_first").prop('disabled', false);
      $("#user_plainPassword_second").prop('disabled', false);
    } else {
      alert(data.message);
    }
  })
  .error(function(data) {
    alert(data.message);
  });
});

$("#user_plainPassword_second").on('input', function() {
  if($(this).val() === $("#user_plainPassword_first").val()) {
    $("#submit").prop('disabled', false);
  } else {
    $("#submit").prop('disabled', true);
  }
})
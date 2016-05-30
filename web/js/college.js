$('#user_college').change(function() {
  var college = $("#user_college option:selected");
  window.location = "/colleges/" + college.val();
});

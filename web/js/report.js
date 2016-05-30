$(document).on('click', ".post_report", function() {
    $("#report_modal").attr('data-post-id', $(this).attr('data-post-id'));
    $("#report_modal").foundation('open');
})

$(document).on('click', ".report_item", function() {
    var reason_id = $(this).attr('data-reason-id');
    var post_id = $(this).closest('div').attr('data-post-id');
    
    $.post("/report/post", { reason_id: reason_id, post_id: post_id })
    .done(function(data) {
        $("#report_submit_status").text("Success");
        $("#report_submit_message").text(data.message);
        $("#reportSubmitModal").foundation('open');
    })
    .error(function(xhr, status, error) {
        var data = JSON.parse(xhr.responseText);
        $("#report_submit_status").text("Error");
        $("#report_submit_message").text(data.message);
        $("#reportSubmitModal").foundation('open');
    })
});


$(document).ready(function () {

    $('#global_alert').hide();



    $dropdown_button = "<div class=\"dropdown\">\n" +
        "  <button class=\"btn dropdown-toggle\" type=\"button\" id=\"dropdownMenuButton\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">\n" +
        "    <i class=\"fa fa-cog\" aria-hidden=\"true\"></i>\n\n" +
        "  </button>\n" +
        "  <div class=\"dropdown-menu\" aria-labelledby=\"dropdownMenuButton\">\n" +
        "    <a class=\"dropdown-item edit-item\" ><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i> Edit</a>\n" +
        "    <a class=\"dropdown-item delete-item\" ><i class=\"fa fa-trash\" aria-hidden=\"true\"></i> Delete</a>\n" +
        "  </div>\n" +
        "</div>"


    var backlog_table = $('#backlog_table').DataTable({
        "ajax": "api.php",
        "columns": [
            {"data": null, "defaultContent": $dropdown_button},
            {"data": "id"},
            {"data": "requestor_id"},
            {"data": "tool_name"},
            {"data": "type"},
            {"data": "description"},
            {"data": "priority"},
            {"data": "tester"},
            {"data": "date_filed"},
            {"data": "date_closed"},
            {"data": "fix_confirm"},
            {"data": "image_name"},
            {"data": "status"},
        ],
        "columnDefs": [
            {"orderable": false, "targets": [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]},
            {"orderable": true, "targets": [1]},
            {
                "targets": 0,
                "data": null,
                "orderable": false,
            }
        ],
        "dom": '<"top"p<"pull-left"f><"pull-left"l><"toolbar">>ti<"bottom"><"clear">',
        initComplete: function () {
            $("div.toolbar")
                .html('<button type="button" class="classicbutton" data-toggle="modal" data-target="#id_create_update_form">Add New</button>');
        },
        language: {search: "",
            searchPlaceholder: "Search"},
        rowCallback: function(row, data, index) {
            if (data.fix_confirm == 1) {
                $('td:eq(10)', row).replaceWith("<td>Yes</td>");
            }else {
                $('td:eq(10)', row).replaceWith("<td>No</td>");
            }
        }
    });

    /* attach a submit handler to the form */
    $("#update_create_form").submit(function (event) {

        /* stop form from submitting normally */
        event.preventDefault();

        var formData = new FormData($(this)[0]);

        formData.append("formdata", $(this).serialize())

        console.log(formData)

        $.ajax({
            url: 'api.php', // url where to submit the request
            type: "POST", // type of action POST || GET
            dataType: 'json', // data type
            data: formData, // post data || get data
            success: function (result) {
                // you can see the result from the console
                // tab of the developer tools
                console.log("success");
                $('#id_create_update_form').modal('toggle');
                $('#update_create_form').trigger("reset");
                backlog_table.ajax.reload();
            },
            error: function (xhr, resp, text) {
                $('#id_create_update_form').modal('toggle');
                $('#update_create_form').trigger("reset");
                $('.custom-error').html(text);
                $('#global_alert').show();
            },
            cache: false,
            contentType: false,
            processData: false
        })

        $("#id_submit_button").html('Save');


    });

    $("#delete_form").submit(function (event) {

        console.log("here in delete");

        /* stop form from submitting normally */
        event.preventDefault();

        // var id = $('#id_input').val();

        var formData = new FormData($(this)[0]);

        formData.append("formdata", $(this).serialize())



        $.ajax({
            url: 'api.php', // url where to submit the request
            type: "POST", // type of action POST || GET
            dataType: 'json', // data type
            data: formData, // post data || get data
            success: function (result) {
                // you can see the result from the console
                // tab of the developer tools
                console.log("success");
                $('#id_delete_modal').modal('toggle');
                $('#delete_form').trigger("reset");
                backlog_table.ajax.reload();
            },
            error: function (xhr, resp, text) {
                $('#id_delete_modal').modal('toggle');
                $('#delete_form').trigger("reset");
                $('.custom-error').html(text);
                $('#global_alert').show();

            },
            cache: false,
            contentType: false,
            processData: false
        })

    });


    $("body").on("click", ".delete-item", function () {

        console.log("delete");


        var id = $(this).closest('tr').find('td:eq(1)').text()


        $("<input type='hidden' value='" + id + "' />")
            .attr("id", "id_input")
            .attr("name", "del_id")
            .appendTo("#delete_input");

        $('#id_delete_modal').modal('toggle');


    });

    $("body").on("click", ".edit-item", function () {

        console.log("success");


        var id = $(this).closest('tr').find('td:eq(1)').text()
        var tool = $(this).closest('tr').find('td:eq(3)').text()
        var type = $(this).closest('tr').find('td:eq(4)').text()
        var description = $(this).closest('tr').find('td:eq(5)').text()
        var priority = $(this).closest('tr').find('td:eq(6)').text()
        var tester = $(this).closest('tr').find('td:eq(7)').text()
        var status = $(this).closest('tr').find('td:eq(12)').text()


        $("#update_create_form").find("select[name='tool_name']").val(tool);
        $("#update_create_form").find("select[name='type']").val(type);
        $("#update_create_form").find("textarea[name='description']").val(description);
        $("#update_create_form").find("select[name='priority']").val(priority);
        $("#update_create_form").find("input[name='tester']").val(tester);
        $("#update_create_form").find("select[name='status']").val(status);
        // $("#update_create_form").find(".edit-id").val(id);

        $("<input type='hidden' value='" + id + "' />")
            .attr("id", "id_input")
            .attr("name", "id")
            .appendTo("#update_create_form");

        $("#id_submit_button").html('Update');

        $('#id_create_update_form').modal('toggle');



    });

    $('.backlog_modal').on('hidden.bs.modal', function (e) {
        $('form').trigger("reset");
        $("#id_input").remove();
        $("#id_submit_button").html('Save');
    })

    $('div.dataTables_filter input').addClass('round search');
    $('#backlog_table_length').addClass('selectWrapper');
    $('div.dataTables_length select').addClass('selectBox');

    $("#global_alert_dismiss").on('click', function (){
        $('#global_alert').hide();
    })
});
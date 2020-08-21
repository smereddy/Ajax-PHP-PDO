$(document).ready(function() {
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
        'ajax': 'api.php',
        'columns': [{
            'data': null,
            'defaultContent': $dropdown_button
        },
            {
                'data': 'id'
            },
            {
                'data': 'requestor_id'
            },
            {
                'data': 'tool_name'
            },
            {
                'data': 'type'
            },
            {
                'data': 'description'
            },
            {
                'data': 'priority'
            },
            {
                'data': 'tester'
            },
            {
                'data': 'date_filed'
            },
            {
                'data': 'date_closed'
            },
            {
                'data': 'fix_confirm'
            },
            {
                'data': 'image_name'
            },
            {
                'data': 'status'
            },
        ],
        'columnDefs': [{
            'orderable': false,
            'targets': [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
        },
            {
                'orderable': true,
                'targets': [1]
            },
            {
                'targets': [0],
                'data': null,
                'orderable': false
            }
        ],
        'dom': "<'top'p<'pull-left'f><'pull-left'l><'toolbar'>>ti<'bottom'><'clear'>",
        initComplete: function() {
            $('div.toolbar')
                .html("<button type='button' class='classicbutton' data-toggle='modal' data-target='#id_create_update_form'>Add New</button>");
        },
        language: {
            search: "",
            searchPlaceholder: "Search"
        },
        rowCallback: function(row, data, index) {
            if (data.fix_confirm == 1) {
                $('td:eq(10)', row).replaceWith("<td>Yes</td>");
            } else {
                $('td:eq(10)', row).replaceWith("<td>No</td>");
            }

            if (data.image_name) {
                $('td:eq(11)', row).replaceWith("<td><a href=\"javascript:void(0);\" src=\"" + data.image_name + "\" class=\"preview_image_click\">\n" +
                    "    Click to Preview\n" +
                    "</a></td>");
            }
            if (data.requestor_id) {
                $('td:eq(2)', row).replaceWith("<td>Jane Doe</td>");
            } else {
                $('td:eq(2)', row).replaceWith("<td>John Doe</td>");
            }
        }
    });

    $('table').find('a').click(function() {
        var original = parseInt($(this).parents('tr').find('td:nth-child(4)').text()) + 5;

        $(this).parents('tr').find('input[type=text]').val(original);
        e.preventDefault();
    });

    $(document).on('click', 'a.preview_image_click', function(e) {
        e.preventDefault();

        $('#imagepreview').attr('src', $(this).attr('src'));
        $('#imagemodal').modal('show');
    });

    $('#update_create_form').submit(function(event) {
        let formData = new FormData($(this)[0]);

        event.preventDefault();
        formData.append('formdata', $(this).serialize());
        formData.append('file', $('#upload')[0].files[0]);

        postdata(formData, '#id_create_update_form', '#update_create_form');
        $('#id_submit_button').html('Save');
    });

    $('#delete_form').submit(function(event) {
        let formData = new FormData($(this)[0]);

        event.preventDefault();
        formData.append('formdata', $(this).serialize())
        postdata(formData, '#id_delete_modal', '#delete_form');
    });


    $('body').on('click', '.delete-item', function() {
        const id = $(this).closest('tr').find('td:eq(1)').text();

        $("<input type='hidden' value='" + id + "' />")
            .attr("id", "id_input")
            .attr("name", "del_id")
            .appendTo("#delete_input");

        $('#id_delete_modal').modal('toggle');
    });

    $('body').on('click', '.edit-item', function() {
        const formId = '#update_create_form';
        const id = $(this).closest('tr').find('td:eq(1)').text();
        const tool = $(this).closest('tr').find('td:eq(3)').text();
        const type = $(this).closest('tr').find('td:eq(4)').text();
        const description = $(this).closest('tr').find('td:eq(5)').text();
        const priority = $(this).closest('tr').find('td:eq(6)').text();
        const tester = $(this).closest('tr').find('td:eq(7)').text();
        const status = $(this).closest('tr').find('td:eq(12)').text();


        $(formId).find("select[name='tool_name']").val(tool);
        $(formId).find("select[name='type']").val(type);
        $(formId).find("textarea[name='description']").val(description);
        $(formId).find("select[name='priority']").val(priority);
        $(formId).find("input[name='tester']").val(tester);
        $(formId).find("select[name='status']").val(status);

        $("<input type='hidden' value='" + id + "' />")
            .attr("id", "id_input")
            .attr("name", "id")
            .appendTo(formId);

        $('#id_submit_button').html('Update');
        $('#id_create_update_form').modal('toggle');
    });

    $('.backlog_modal').on('hidden.bs.modal', function(e) {
        $('form').trigger('reset');
        $('#id_input').remove();
        $('#id_submit_button').html('Save');
        clearDropzone();
    })

    $('div.dataTables_filter input').addClass('round search');
    $('#backlog_table_length').addClass('selectWrapper');
    $('div.dataTables_length select').addClass('selectBox');

    $('#global_alert_dismiss').on('click', function() {
        $('#global_alert').hide();
    })

    function postdata(formData, modal, form) {
        $.ajax({
            url: 'api.php',
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(result) {
                afterPost(modal, form);
                backlog_table.ajax.reload();
            },
            error: function(xhr, resp, text) {
                afterPost(modal, form);
                var error = JSON.parse(xhr.responseText);
                $('.custom-error').html(error["error"]);
                $('#global_alert').show();

            },
            cache: false,
            contentType: false,
            processData: false
        })
    }

    function afterPost(modal, form) {
        $(modal).modal('toggle');
        $(form).trigger('reset');
        clearDropzone();
    }

    function showFileName(name) {
        var imgNameField = $("#uploaded_img_name");
        $('#uploaded_img_name').html(name);
        $("#uploaded_img_name").show();
    }


    $('#upload').on('change', function(e) {
        showFileName(e.target.files[0].name);
    })

    $("#upload_link").on('click', function(e) {
        console.log(document.getElementById("upload").value);
        e.preventDefault();
        $("#upload:hidden").trigger('click');
    });

    $('#drop_zone')
        .on('dragover', false)

        .on('drop', function(e) {
            $("#upload")[0].files = e.originalEvent.dataTransfer.files;
            showFileName(e.originalEvent.dataTransfer.files[0].name);
            return false;
        });

    function clearDropzone(modal, form) {
        $('#upload').val("");
        $('#uploaded_img_name').css('display', 'none');
    }
});
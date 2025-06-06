$(document).ready(function() {
    $('.btn-view-diagnosis').on('click', function() {
        let repairRequestId = $(this).data('request-id');
        let modal = $('#viewRequestModal');

        let formData = {
            'repair_request_id': repairRequestId,
            'action': 'view_repair_request_finished'
        };

        $.ajax({
            url: '../../api/repair_request.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    modal.modal('show');
                    modal.find('#deviceBrand').text(response.data.brand);
                    modal.find('#deviceModel').text(response.data.model);
                    modal.find('#deviceDescription').text(response.data.device_description);

                    modal.find('#requestDate').text(response.data.request_date);
                    modal.find('#problemDescription').text(response.data.problem_description);

                    modal.find('#diagnosis').text(response.data.diagnosis);
                    modal.find('#diagnosedBy').text(response.data.user_diagnosed_name);
                    modal.find('#diagnosisDate').text(response.data.diagnosis_date);
                    modal.find('#solutionDescription').text(response.data.solution_description);
                }                
            },
            error: function(xhr, status, error) {
                alert('Error al obtener el diagnóstico. Por favor, inténtalo de nuevo más tarde.');
            }
        });
    });
    
    $('.btn-finish-request').on('click', function() {
        let repairRequestId = $(this).data('request-id');
        let modal = $('#createDiagnosticModal');

        modal.find('#diagnostic_repair_request_id').val(repairRequestId);
        modal.find('#diagnosis_form').val('');
        modal.find('#diagnosis_date_form').val('');
        modal.find('#solution_description_form').val('');        

        modal.modal('show');
    });

    $('#diagnosticForm').on('submit', function(event) {
        event.preventDefault();

        let formData = {
            'repair_request_id': $('#diagnostic_repair_request_id').val(),
            'diagnostic_repair_request_id': $('#diagnostic_repair_request_id').val(),
            'diagnosis_form': $('#diagnosis_form').val(),
            'diagnosis_date_form': $('#diagnosis_date_form').val(),
            'solution_description_form': $('#solution_description_form').val(),
            'action': $('#diagnostic_form_action').val()
        };

        $.ajax({
            url: '../../api/repair_request.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then((event) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                alert('Error al enviar el diagnóstico. Por favor, inténtalo de nuevo más tarde.');
            }
        });
        
    });

    $('.btn-next-status').on('click', function() {
        $repairRequestId = $(this).data('request-id');
        modal = $('#confirmAdvanceModal');
        modal.find('#advance_repair_request_id').val($repairRequestId);

        modal.modal('show');
    });

    $('#advanceStateForm').on('submit', function(e) {
        e.preventDefault();
        let formData = {
            'repair_request_id': $('#advance_repair_request_id').val(),
            'action': 'advance_repair_request_status'
        };

        $.ajax({
            url: '../../api/repair_request.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then((event) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {              
                alert('Error al avanzar el estado de la solicitud. Por favor, inténtalo de nuevo más tarde.');
            }
        });
    });
    
    $('#btn-add-repair-request').on('click', function() {      
        let modal = $('#createRepairRequestModal');
        modal.modal('show');
    });

    $('#user_id_select').on('change', function() {
        let userId = $(this).val();
        let modal = $('#createRepairRequestModal');
        let selectDevice = modal.find('#device_id_select');

        if (userId) {
            $.ajax({
                url: '../../api/repair_request.php',
                type: 'POST',
                data: { user_id: userId, action: 'get_user_devices' },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        selectDevice.empty();
                        selectDevice.append('<option value="">Seleccione un dispositivo</option>');
                        response.data.forEach(function(device) {
                            selectDevice.append('<option value="' + device.id + '">' + device.description + ' ' + device.brand + ' ' + device.model + '</option>');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error al cargar los dispositivos del usuario. Por favor, inténtalo de nuevo más tarde.');
                }
            });
        } else {
            selectDevice.empty();
            selectDevice.append('<option value="">Seleccione un dispositivo</option>');
        }
    });

    $('#createRequestForm').on('submit', function(event) {
        event.preventDefault();

        let formDataArray = $('#createRequestForm').serializeArray();
        let formData = {};

        formDataArray.forEach(function (field) {
            formData[field.name] = field.value;
        });

        formData['action'] = 'create_repair_request';

        $.ajax({
            url: '../../api/repair_request.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then((event) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                alert('Error al crear la solicitud de reparación. Por favor, inténtalo de nuevo más tarde.');
            }
        });
    });

    $('.btn-edit-request').on('click', function() {
        let repairRequestId = $(this).data('request-id');
        let modal = $('#editRepairRequestModal');

        $.ajax({
            url: '../../api/repair_request.php',
            type: 'POST',
            data: { repair_request_id: repairRequestId, action: 'get_repair_request' },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    modal.modal('show');
                    modal.find('#edit_repair_request_id').val(response.data.repair_request_id);
                    modal.find('#edit_device_id_select').val(response.data.device_id);
                    modal.find('#edit_problem_description').val(response.data.problem_description);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                alert('Error al obtener la solicitud de reparación. Por favor, inténtalo de nuevo más tarde.');
            }
        });
    });

    $('#editRepairRequestForm').on('submit', function(event) {
        event.preventDefault();

        let formDataArray = $('#editRepairRequestForm').serializeArray();
        let formData = {};

        formDataArray.forEach(function (field) {
            formData[field.name] = field.value;
        });

        formData['action'] = 'edit_repair_request';

        $.ajax({
            url: '../../api/repair_request.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then((event) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {                
                alert('Error al editar la solicitud de reparación. Por favor, inténtalo de nuevo más tarde.');
            }
        });
    });

    $('.btn-delete-request').on('click', function() {
        let repairRequestId = $(this).data('request-id');
        let modal = $('#deleteRequestModal');

        modal.find('#delete_repair_request_id').val(repairRequestId);
        modal.modal('show');
    });

    $('#deleteRequestForm').on('submit', function(event) {
        event.preventDefault();

        let formData = {
            'repair_request_id': $('#delete_repair_request_id').val(),
            'action': 'delete_repair_request'
        };

        $.ajax({
            url: '../../api/repair_request.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then((event) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {                
                alert('Error al eliminar la solicitud de reparación. Por favor, inténtalo de nuevo más tarde.');
            }
        });
    });
});
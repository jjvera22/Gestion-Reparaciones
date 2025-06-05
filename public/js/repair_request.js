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

        let formData = {
            'repair_request_id': repairRequestId,
            'action': 'finish_repair_request'
        };

        modal.find('#diagnostic_repair_request_id').val(repairRequestId);
        modal.find('#diagnosis_form').val('');
        modal.find('#diagnosed_by_select').val('');
        modal.find('#diagnosis_date_form').val('');
        modal.find('#solution_description_form').val('');        

        modal.modal('show');
    });
});
$('#logout-btn').on('click', function() {
    let formData = {
        action: 'logout'
    };

    $.ajax({
        type: 'POST',
        url: '/GestionTaller/api/auth.php',
        data: formData,
        dataType: 'json',
        success: function(response) {            
            if (response.status == 'success') {
                window.location.href = response.redirect;
            } else if (response.status == 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function(xhr) {
            alert('Login failed: ' + xhr.responseText);
        }
    }); 
});
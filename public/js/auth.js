$('#login-form').on('submit', function(event) {
    event.preventDefault();

    let formData = {
        email: $('#email').val(),
        password: $('#password').val(),
        action: 'login' 
    };

    $.ajax({
        type: 'POST',
        url: '/GestionTaller/api/auth.php',
        data: formData,
        dataType: 'json',
        success: function(response) {
            console.log(response);
            
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

$('.select-profile-btn').on('click', function() {
    console.log('Profile selection clicked');
    
    let profileId = $(this).data('profile-id');
    let formData = {
        profileId: profileId,
        action: 'selectProfile'
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
            alert('Profile selection failed: ' + xhr.responseText);
        }
    });
});
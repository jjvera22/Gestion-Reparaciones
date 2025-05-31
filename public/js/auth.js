$('#login-form').on('submit', function(event) {
    event.preventDefault();

    var formData = {
        username: $('#email').val(),
        password: $('#password').val(),
        action: 'login' 
    };

    $.ajax({
        type: 'POST',
        url: '/api/auth.php',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        success: function(response) {
            if (response.status == 'success') {
                window.location.href = response.redirect;
            } else if (response.status == 'error') {
                alert('Login failed: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Login failed: ' + xhr.responseText);
        }
    });
});
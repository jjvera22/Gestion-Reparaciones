console.log('hola mundo');

$('#register-form').on('submit', function(event) {
    event.preventDefault();
    let validationErrors = validateForm();

    if (validationErrors) {
        return;
    }

    let formData = {
        name: $('#name').val(),
        email: $('#email').val(),
        password: $('#password').val(),
        confirmPassword: $('#confirm_password').val(),
        phone: $('#phone').val(),
        address: $('#address').val(),
        action: 'register'  
    };

    $.ajax({
        type: 'POST',
        url: '/GestionTaller/api/auth.php',
        data: formData,
        dataType: 'json',
        success: function(response) {
            console.log(response);
            
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro exitoso',
                    text: response.message
                }).then((result) => {
                    if (result.isConfirmed || result.isDismissed) {
                        window.location.href = response.redirect;
                    }
                });
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

// Function to validate the form inputs
function validateForm() {
    let validationErrors = false;

    // Limpiar mensajes de validación previos
    $('.validate').hide();
    $('.validate').text('');

    // Validar que la contraseña tenga al menos 8 caracteres
    let password = $('#password').val();
    if (password.length < 8) {
        validationErrors = true;
        $('#password-validate').text('La contraseña debe tener al menos 8 caracteres');
        $('#password-validate').show();
    }

    // Validar que las contraseñas coincidan
    let confirmPassword = $('#confirm_password').val();
    if (password !== confirmPassword) {
        validationErrors = true;
        $('#confirm-password-validate').text('Las contraseñas no coinciden');
        $('#confirm-password-validate').show();
    }

    // Validar que el nombre no esté vacío
    let name = $('#name').val();
    if (name.trim() === '') {
        validationErrors = true;
        $('#name-validate').text('El nombre no puede estar vacío');
        $('#name-validate').show();
    }

    // Validar que el email no esté vacío y tenga un formato válido
    let email = $('#email').val();
    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email.trim() === '') {
        validationErrors = true;
        $('#email-validate').text('El email no puede estar vacío');
        $('#email-validate').show();
    } else if (!emailPattern.test(email)) {
        validationErrors = true;
        $('#email-validate').text('El email no es válido');
        $('#email-validate').show();
    }

    // Validar el campo de número de teléfono 
    let phone = $('#phone').val();
    let phonePattern = /^\d{10}$/;
    if (phone.trim() === '') {
        validationErrors = true;
        $('#phone-validate').text('El número de teléfono no puede estar vacío');
        $('#phone-validate').show();
    } else if (!phonePattern.test(phone)) {
        validationErrors = true;
        $('#phone-validate').text('El número de teléfono debe tener 10 dígitos');
        $('#phone-validate').show();
    }

    // Validar que la dirección no esté vacía
    let address = $('#address').val();
    if (address.trim() === '') {
        validationErrors = true;
        $('#address-validate').text('La dirección no puede estar vacía');
        $('#address-validate').show();
    }

    return validationErrors;
}
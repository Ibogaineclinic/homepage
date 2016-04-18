var ContactForm = function () {

    return {

        //Contact Form
        initContactForm: function () {
            // Validation
            $("#sky-form3").validate({
                // Rules for form validation
                rules: {
                    name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    message: {
                        required: true,
                        minlength: 10
                    },
                    phone: {
                        required: true,
                        maxlength: 14,
                        minlength: 10
                    },
                },

                // Messages for form validation
                messages: {
                    name: {
                        required: 'Please enter your name',
                    },
                    email: {
                        required: 'Please enter your email address',
                        email: 'Please enter a VALID email address'
                    },
                    message: {
                        required: 'Please enter your message'
                    },
                    phone: {
                        required: 'Please enter your phone number'
                    },
                },

                // Ajax form submition                  
                submitHandler: function (form) {
                    $(form).ajaxSubmit({
                        beforeSend: function () {
                            $('#sky-form3 input[type="submit"]').attr('disabled', true);
                        },
                        success: function () {
                            $("#sky-form3").addClass('submited');
                        }
                    });
                },

                // Do not change code below
                errorPlacement: function (error, element) {
                    error.insertAfter(element.parent());
                }
            });
        }

    };

}();
$(function () {
    App.init();
    ContactForm.initContactForm();
});
$("#sky-form3").validate({
  submitHandler: function(form) {
    $.ajax({
      url: "//formspree.io/ben@ibogaineclinic.com", 
      method: "POST",
      data: {
        name: $(form).find("input[name='name']").val(),
        _replyto: $(form).find("input[name='_replyto']").val(),
        phone: $(form).find("input[name='phone']").val(),
        message: $(form).find("textarea[name='message']").val()
      },
      dataType: "json",
        success: function() {
        $("#submit-success").fadeIn();
        $("#contact-form").fadeOut();
      },
      error: function() {
        $("#submit-errors").fadeIn();        
      }
    });
  }
});


        <script>
            var contactform =  document.getElementById('sky-form3');
            contactform.setAttribute('action', '//formspree.io/' + 'ben' + '@' + 'ibogaineclinic' + '.' + 'com');
        </script>
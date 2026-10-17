/*
 * Client-side form validation using Bootstrap's built-in styles
 * The browser checks the rules written in the HTML (required, pattern, min...)
 * before the form is sent. The server (PHP) checks everything again too,
 * because browser validation alone can be bypassed
 */

// Bootstrap client-side validation
// Find every form marked with the class "needs-validation"
document.querySelectorAll('.needs-validation').forEach(form => {
  form.addEventListener('submit', e => {
    // If any field is invalid, stop the form from being submitted
    if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    // Show the red/green feedback messages under each field
    form.classList.add('was-validated');
  });
});

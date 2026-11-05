document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registrationForm');
  if (form) {
    form.addEventListener('submit', (event) => {
      if (!form.reportValidity()) {
        event.preventDefault();
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', function () {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');

  if (loginForm) {
    loginForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch(this.action, {
          method: 'POST',
          body: formData,
        });
        const data = await response.json();

        if (data.success) {
          window.location.href = '/openspace/src/index.php';
        } else {
          alert(data.message);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
      }
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch(this.action, {
          method: 'POST',
          body: formData,
        });
        const data = await response.json();
        console.log('Server response:', data);

        if (data.success) {
          window.location.href = '/openspace/src/index.php';
        } else {
          alert(data.message);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
      }
    });
  }
});

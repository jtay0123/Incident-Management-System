function showLogin() {
    document.getElementById('loginCard').style.display = 'block';
    document.getElementById('resetPasswordCard').style.display = 'none';
    document.getElementById('successCard').style.display = 'none';
}

function showResetPassword(){
    document.getElementById('loginCard').style.display = 'none';
    document.getElementById('resetPasswordCard').style.display = 'block';
    document.getElementById('successCard').style.display = 'none';
}

function showSuccess() {
    document.getElementById('loginCard').style.display = 'none';
    document.getElementById('resetPasswordCard').style.display = 'none';
    document.getElementById('successCard').style.display = 'block';
}

function handleLogin(event) {
    event.preventDefault();
    const form = document.getElementById('login-form');
    const formData = new FormData(form);
    const errorDiv = document.getElementById('login-error');
    
    // Hide any previous errors
    errorDiv.style.display = 'none';
    
    // Disable submit button to prevent double submission
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Logging in...';
    
    // Use the form's action attribute for the URL
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Invalid response from server');
            });
        }
    })
    .then(data => {
        if (data.status === 'success') {
            // Show success message briefly before redirect
            errorDiv.className = 'alert alert-success';
            errorDiv.textContent = 'Login successful! Redirecting...';
            errorDiv.style.display = 'block';
            
            // Redirect after a brief delay
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 100);
        } else {
            // Show error message
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = data.message || 'Login failed. Please try again.';
            errorDiv.style.display = 'block';
            
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorDiv = document.createElement('div'); 
        errorDiv.className = 'alert alert-danger';
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
        
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}
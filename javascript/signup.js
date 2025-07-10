document.addEventListener('DOMContentLoaded', () => {
    const authCard = document.querySelector('.auth-card');
    const tabButtons = document.querySelectorAll('.tab-button');
    const formsWrapper = document.querySelector('.forms-wrapper');
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const switchFormLinks = document.querySelectorAll('.switch-form');

    const loginActualForm = document.getElementById('loginActualForm');
    const signupActualForm = document.getElementById('signupActualForm');

    // --- Tab Switching & Sliding Logic ---
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const formToShow = button.dataset.form;

            // Update active tab styles
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            // Trigger sliding animation
            if (formToShow === 'signup') {
                authCard.classList.add('signup-active');
            } else {
                authCard.classList.remove('signup-active');
            }

            // Update active form for semantic purposes (and opacity if used)
            loginForm.classList.remove('active');
            signupForm.classList.remove('active');
            document.getElementById(formToShow + 'Form').classList.add('active');

            // Clear any previous validation messages when switching forms
            clearAllValidationErrors();
        });
    });

    // Handle "Sign Up here" / "Login here" links
    switchFormLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default link behavior
            const formToShow = link.dataset.form;
            // Find and click the corresponding tab button
            const targetTabButton = document.querySelector(`.tab-button[data-form="${formToShow}"]`);
            if (targetTabButton) {
                targetTabButton.click();
            }
        });
    });

    // --- Client-Side Form Validation ---

    function showError(elementId, message) {
        const errorDiv = document.getElementById(elementId);
        if (errorDiv) {
            errorDiv.textContent = message;
            // Add error class to the input for styling
            const input = errorDiv.previousElementSibling; // Assuming input is directly before error-message div
            if (input && input.tagName === 'INPUT') {
                input.classList.add('error');
            }
        }
    }

    function clearError(elementId) {
        const errorDiv = document.getElementById(elementId);
        if (errorDiv) {
            errorDiv.textContent = '';
            const input = errorDiv.previousElementSibling;
            if (input && input.tagName === 'INPUT') {
                input.classList.remove('error');
            }
        }
    }

    function clearAllValidationErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('input.error').forEach(el => el.classList.remove('error'));
    }

    // Login Form Validation
    loginActualForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Prevent default form submission

        let isValid = true;
        clearAllValidationErrors(); // Clear previous errors

        const email = loginActualForm.elements.loginEmail.value.trim();
        const password = loginActualForm.elements.loginPassword.value.trim();

        if (email === '') {
            showError('loginEmailError', 'Email is required.');
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('loginEmailError', 'Please enter a valid email address.');
            isValid = false;
        }

        if (password === '') {
            showError('loginPasswordError', 'Password is required.');
            isValid = false;
        }

        if (isValid) {
            // Simulate successful login
            alert('Login successful! (Simulated)');
            // In a real application, you would send data to a backend here:
            // console.log('Login Data:', { email, password });
            // loginActualForm.submit(); // Or fetch('/api/login', { method: 'POST', body: JSON.stringify({ email, password }) });
            loginActualForm.reset(); // Clear form on success
            clearAllValidationErrors(); // Clear errors after successful submission
        } else {
            alert('Please correct the errors in the form.');
        }
    });

    // Signup Form Validation
    signupActualForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Prevent default form submission

        let isValid = true;
        clearAllValidationErrors(); // Clear previous errors

        const name = signupActualForm.elements.signupName.value.trim();
        const email = signupActualForm.elements.signupEmail.value.trim();
        const password = signupActualForm.elements.signupPassword.value.trim();
        const confirmPassword = signupActualForm.elements.signupConfirmPassword.value.trim();

        if (name === '') {
            showError('signupNameError', 'Full name is required.');
            isValid = false;
        }

        if (email === '') {
            showError('signupEmailError', 'Email is required.');
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('signupEmailError', 'Please enter a valid email address.');
            isValid = false;
        }

        if (password === '') {
            showError('signupPasswordError', 'Password is required.');
            isValid = false;
        } else if (password.length < 6) {
            showError('signupPasswordError', 'Password must be at least 6 characters long.');
            isValid = false;
        }

        if (confirmPassword === '') {
            showError('signupConfirmPasswordError', 'Confirm password is required.');
            isValid = false;
        } else if (password !== confirmPassword) {
            showError('signupConfirmPasswordError', 'Passwords do not match.');
            isValid = false;
        }

        if (isValid) {
            // Simulate successful signup
            alert('Sign up successful! (Simulated)');
            // In a real application, you would send data to a backend here:
            // console.log('Signup Data:', { name, email, password });
            // signupActualForm.submit(); // Or fetch('/api/signup', { method: 'POST', body: JSON.stringify({ name, email, password }) });
            signupActualForm.reset(); // Clear form on success
            clearAllValidationErrors(); // Clear errors after successful submission
        } else {
            alert('Please correct the errors in the form.');
        }
    });

    // Clear error messages on input focus/typing
    document.querySelectorAll('.form-group input').forEach(input => {
        input.addEventListener('input', (e) => {
            const errorId = e.target.id + 'Error';
            clearError(errorId);
        });
    });
});
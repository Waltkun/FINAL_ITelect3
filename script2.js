const wrapper = document.getElementById('wrapper');
const signInForm = document.getElementById('signIn');
const signUpForm = document.getElementById('signUp');
const loginFormElement = document.getElementById('loginForm');
const registerFormElement = document.getElementById('registerForm');
const loginErrorBox = document.getElementById('loginError');
const registerErrorBox = document.getElementById('registerError');

function showRegister(isRegister) {
    wrapper.classList.toggle('active', isRegister);
    signUpForm.inert = !isRegister;
    signInForm.inert = isRegister;
    
    // Hide errors when switching forms
    if(loginErrorBox) loginErrorBox.style.display = 'none';
    if(registerErrorBox) registerErrorBox.style.display = 'none';
}

document.getElementById('signUpButton').addEventListener('click', () => showRegister(true));
document.getElementById('signInButton').addEventListener('click', () => showRegister(false));

// PASSWORD SHOW/HIDE
document.querySelectorAll('.toggle-pw').forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// HANDLE URL PARAMETERS (Animations & Errors)
const urlParams = new URLSearchParams(window.location.search);

// Success Registration
if (urlParams.get('registered') === '1') {
    wrapper.classList.add('no-anim');
    showRegister(true);
    wrapper.offsetHeight; 
    wrapper.classList.remove('no-anim');
    setTimeout(() => showRegister(false), 900);
    history.replaceState(null, '', window.location.pathname);
}

// ERROR HANDLING
const error = urlParams.get('error');
const type = urlParams.get('type');

if (error) {
    if (type === 'register') {
        showRegister(true);
        registerErrorBox.style.display = 'block';
        
        if (error === 'match') registerErrorBox.innerText = "Passwords do not match.";
        if (error === 'exists') registerErrorBox.innerText = "Email address already exists.";
        if (error === 'db') registerErrorBox.innerText = "Database error. Please try again.";

        // Trigger Shake Animation
        registerFormElement.classList.add('shake');
        setTimeout(() => registerFormElement.classList.remove('shake'), 500);
        
    } else if (error === 'invalid') {
        showRegister(false);
        loginErrorBox.style.display = 'block';
        loginErrorBox.innerText = "Incorrect Email or Password.";

        // Trigger Shake Animation
        loginFormElement.classList.add('shake');
        setTimeout(() => loginFormElement.classList.remove('shake'), 500);
    }
    
    // Remove error from URL so refreshing doesn't replay the shake
    setTimeout(() => {
        history.replaceState(null, '', window.location.pathname);
    }, 1000);
}
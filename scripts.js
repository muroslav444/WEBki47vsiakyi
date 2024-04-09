// Fetch the username when the page loads
document.addEventListener('DOMContentLoaded', function () {
    // Add event listener for the Google login button
    const googleLoginBtn = document.getElementById('google-login-btn');
    if (googleLoginBtn) {
        googleLoginBtn.addEventListener('click', function (event) {
            event.preventDefault();
            initiateGoogleLogin();
        });
    }

    // Fetch the username when the page loads
    fetchUsername();
});

// Function to initiate Google login
async function initiateGoogleLogin() {
    try {
        const response = await fetch('google-login.php?google_login=true');
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            // Redirect to Google login page
            window.location.href = data.authUrl;
        } else {
            console.error('Error in initiateGoogleLogin:', data.message || 'An error occurred');
            // Display error to the user (modify this based on your UI)
            alert('Error during Google login initiation. Please try again later.');
        }
    } catch (error) {
        console.error('Error during Google login initiation:', error);
        // Display error to the user (modify this based on your UI)
        alert('An unexpected error occurred. Please try again later.');
    }
}

// Function to fetch and update the username
async function fetchUsername() {
    try {
        const response = await fetch('login.php');
        if (!response.ok) {
            console.error('Network response was not ok');
            return;
        }

        const data = await response.json();

        if (data.success) {
            // Update the username element on the page
            const usernameElement = document.getElementById('username');
            if (usernameElement) {
                usernameElement.textContent = data.username;
            }

            // Show the logout link
            const logoutLink = document.getElementById('logout-link');
            const loginRegisterLink = document.getElementById('login-register-link');
            if (logoutLink) {
                logoutLink.style.display = 'grid';
                loginRegisterLink.style.display = 'none';
            }
        } else {
            console.error('Error in fetchUsername:', data.message || 'An error occurred');

            // Hide the logout link
            const logoutLink = document.getElementById('logout-link');
            if (logoutLink) {
                logoutLink.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('User not logged in, can\'t get username');
    }
}

// Add an event listener for the logout link
document.addEventListener('DOMContentLoaded', function () {
    const logoutLink = document.getElementById('logout-link');
    if (logoutLink) {
        logoutLink.addEventListener('click', function (event) {
            event.preventDefault();
            logoutUser();
        });
    }
});

// logoutUser function
function logoutUser() {
    fetch('logout.php', {
        method: 'GET',
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Redirect to home page after logout
            window.location.href = 'index';
        } else {
            console.error('Error during logout:', data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error during logout:', error);
    });
}

// functions for both login and register pages
function clearForm(formId) {
  var form = document.getElementById(formId);
  if (form) {
    var inputs = form.querySelectorAll('input');
    inputs.forEach(function (input) {
      input.value = '';
    });
  }
}

function showMessage(formId, message, isError) {
  var authMessage = document.getElementById('auth-message-' + formId);
  var authMessageContent = document.getElementById('auth-message-content-' + formId);

  if (authMessage) {
    authMessageContent.innerHTML = message;
    authMessage.className = 'auth-message ' + (isError ? 'error' : 'success');
    authMessage.style.display = 'flex';

    setTimeout(function () {
      authMessage.style.opacity = '0';
      setTimeout(function () {
        authMessage.style.display = 'none';
        authMessage.style.opacity = '1';
      }, 300);
    }, 6000); // Set to disappear after 6 seconds
  }
}

// Login page functions

function loginUser() {
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;

    if (!username || !password) {
        showMessage('login', 'Please enter both username and password', true);
        return;
    }

    fetch('login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username,
            password,
        }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage('login', 'Login successful! Redirecting to homepage...', false);
            // Redirect to homepage after 2 seconds
            setTimeout(function () {
                window.location.href = 'index';
            }, 2000);
        } else {
            showMessage('login', data.message || 'An error occurred', true);
        }
    })
    .catch(error => {
        console.error('Error during login:', error);
        showMessage('login', 'An error occurred', true);
    });
}

// Register page functions
async function registerUser() {
    const username = document.getElementById('register-username').value;
    const password = document.getElementById('register-password').value;

    if (username.length < 3 || username.length > 16 || password.length < 8 || password.length > 16) {
        showMessage('register', 'Username must be 3-16 characters and password must be 8-16 characters', true);
        return;
    }

    try {
        const response = await fetch('register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username,
                password,
            }),
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        console.log(data); // Log the response if needed

        if (data.success) {
            showMessage('register', 'Registration successful! Redirecting to login...', false);

            // Redirect to login page after 2 seconds
            setTimeout(function () {
                window.location.href = 'login';
            }, 2000);
        } else {
            showMessage('register', data.message || 'An error occurred', true);
        }
    } catch (error) {
        console.error('Error during registration:', error);
        showMessage('register', 'An error occurred', true);
    }
}
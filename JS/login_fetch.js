document.addEventListener('DOMContentLoaded', function () {

    // --- Constantes e Variáveis Globais para Bloqueio de Login ---
    const MAX_LOGIN_ATTEMPTS = 3;
    const LOCKOUT_DURATION_SECONDS = 30;
    const STORAGE_KEY_FAILED_ATTEMPTS = 'loginFailedAttempts';
    const STORAGE_KEY_LOCKOUT_UNTIL = 'loginLockoutUntil';
    let lockoutCountdownIntervalId = null;

    // --- Seletores de Elementos DOM (pegos uma vez para reutilização) ---
    const formLogin = document.getElementById('formLogin');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const btnLogin = document.getElementById('btnLogin');
    const mensagemLoginApi = document.getElementById('mensagemLoginApi'); // Para feedback de login/lockout
    const pageNotificationToastEl = document.getElementById('pageNotificationToast'); // Para toasts de URL
    const pageNotificationToastBodyEl = document.getElementById('pageNotificationToastBody'); // Para toasts de URL

    // --- Funções Auxiliares para o Bloqueio de Login ---
    function updateLockoutMessage(secondsRemaining) {
        if (mensagemLoginApi) {
            if (secondsRemaining > 0) {
                mensagemLoginApi.textContent = `Muitas tentativas incorretas. Por favor, aguarde ${secondsRemaining} segundos.`;
                mensagemLoginApi.style.color = 'orange';
            } else {
                mensagemLoginApi.textContent = 'Você pode tentar fazer login novamente.';
                mensagemLoginApi.style.color = '';
            }
        }
    }

    function setLoginFormDisabled(disabled) {
        if (btnLogin) btnLogin.disabled = disabled;
        if (emailInput) emailInput.disabled = disabled;
        if (passwordInput) passwordInput.disabled = disabled;
    }

    function clearLockoutStateAndEnableForm() {
        sessionStorage.removeItem(STORAGE_KEY_FAILED_ATTEMPTS);
        sessionStorage.removeItem(STORAGE_KEY_LOCKOUT_UNTIL);
        if (lockoutCountdownIntervalId) {
            clearInterval(lockoutCountdownIntervalId);
            lockoutCountdownIntervalId = null;
        }
        setLoginFormDisabled(false);
        // Limpa a mensagem de lockout se ela estiver sendo exibida pelo mecanismo de lockout
        if (mensagemLoginApi && mensagemLoginApi.textContent.startsWith("Muitas tentativas")) {
            mensagemLoginApi.textContent = '';
        }
    }

    function checkAndManageLockout() {
        const lockoutEndTime = parseInt(sessionStorage.getItem(STORAGE_KEY_LOCKOUT_UNTIL) || '0');
        const now = Date.now();

        if (lockoutEndTime > now) {
            setLoginFormDisabled(true);
            const secondsRemaining = Math.ceil((lockoutEndTime - now) / 1000);
            updateLockoutMessage(secondsRemaining);

            if (lockoutCountdownIntervalId) clearInterval(lockoutCountdownIntervalId);

            lockoutCountdownIntervalId = setInterval(() => {
                const newNow = Date.now();
                const newSecondsRemaining = Math.ceil((lockoutEndTime - newNow) / 1000);
                if (newSecondsRemaining <= 0) {
                    clearLockoutStateAndEnableForm();
                    updateLockoutMessage(0);
                } else {
                    updateLockoutMessage(newSecondsRemaining);
                }
            }, 1000);
            return true; // Está bloqueado
        } else {
            if (sessionStorage.getItem(STORAGE_KEY_LOCKOUT_UNTIL)) { // Se havia um lockout que expirou
                clearLockoutStateAndEnableForm();
            } else {
                setLoginFormDisabled(false); // Garante que o formulário está habilitado
            }
            return false; // Não está bloqueado
        }
    }

    // --- Lógica Principal ---

    // 1. Tratar Toasts de Notificação da Página (via parâmetros de URL)
    const urlParams = new URLSearchParams(window.location.search);
    let notificationMessage = null;

    if (urlParams.has('expirado') && urlParams.get('expirado') === '1') {
        notificationMessage = "Sua sessão expirou por inatividade. Faça login novamente.";
    } else if (urlParams.has('erro_acesso') && urlParams.get('erro_acesso') === '1') {
        notificationMessage = "Você precisa estar autenticado para acessar esta página.";
    }

    if (notificationMessage) {
        if (pageNotificationToastEl && pageNotificationToastBodyEl) {
            pageNotificationToastBodyEl.textContent = notificationMessage;
            try {
                const bootstrapToast = new bootstrap.Toast(pageNotificationToastEl, { delay: 7000 });
                bootstrapToast.show();
                // Opcional: Limpar parâmetros da URL
                window.history.replaceState({}, document.title, window.location.pathname + window.location.hash);
            } catch (e) {
                console.error("login_fetch.js: Erro ao inicializar ou mostrar o pageNotificationToast:", e);
            }
        } else {
            console.warn("login_fetch.js: Elementos HTML para o pageNotificationToast não foram encontrados!");
        }
    }

    // 2. Verificar e Gerenciar Estado de Bloqueio ao Carregar a Página
    if (checkAndManageLockout()) {
        // Se estiver bloqueado, a UI e o timer já foram configurados.
    }

    // 3. Lógica para o Formulário de Login
    if (formLogin) {
        formLogin.addEventListener('submit', async function (event) {
            event.preventDefault();

            // Re-verificar o bloqueio antes de cada tentativa de submissão
            if (checkAndManageLockout()) {
                return; // Interrompe se estiver bloqueado
            }

            const currentEmailValue = emailInput.value.trim(); // Pega valor atual do campo
            const currentPasswordValue = passwordInput.value; // Pega valor atual do campo

            if (mensagemLoginApi) {
                mensagemLoginApi.textContent = '';
                mensagemLoginApi.style.color = 'red'; // Padrão para mensagens de erro de login
            }

            if (currentEmailValue === '' || currentPasswordValue === '') {
                if (mensagemLoginApi) {
                    mensagemLoginApi.textContent = 'Por favor, preencha email e senha.';
                }
                return;
            }

            const dadosLogin = {
                email: currentEmailValue,
                senha: currentPasswordValue
            };

            setLoginFormDisabled(true); // Desabilita o formulário durante a requisição
            if (mensagemLoginApi) {
                mensagemLoginApi.textContent = 'Processando...';
                mensagemLoginApi.style.color = ''; // Cor neutra para "Processando"
            }

            try {
                const response = await fetch('../../api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(dadosLogin)
                });

                const resultado = await response.json();

                if (response.ok && resultado.status === "sucesso") {
                    clearLockoutStateAndEnableForm(); // Limpa falhas e bloqueio no sucesso
                    if (mensagemLoginApi) {
                        mensagemLoginApi.textContent = resultado.mensagem;
                        mensagemLoginApi.style.color = 'green';
                    }
                    if (resultado.usuario) {
                        localStorage.setItem('usuarioLogado', JSON.stringify(resultado.usuario));
                    }
                    localStorage.setItem('isLogado', 'true');
                    window.location.href = '../../autenticado.php';
                } else {
                    // Login falhou (API retornou erro)
                    let failedAttempts = parseInt(sessionStorage.getItem(STORAGE_KEY_FAILED_ATTEMPTS) || '0');
                    failedAttempts++;
                    sessionStorage.setItem(STORAGE_KEY_FAILED_ATTEMPTS, failedAttempts.toString());

                    if (failedAttempts >= MAX_LOGIN_ATTEMPTS) {
                        const lockoutEndTime = Date.now() + (LOCKOUT_DURATION_SECONDS * 1000);
                        sessionStorage.setItem(STORAGE_KEY_LOCKOUT_UNTIL, lockoutEndTime.toString());
                        checkAndManageLockout(); // Aplica o bloqueio e inicia a contagem regressiva
                        // A função checkAndManageLockout já desabilita o formulário.
                    } else {
                        if (mensagemLoginApi) {
                            mensagemLoginApi.textContent = (resultado.mensagem || `Erro: ${response.status}`) +
                                ` (Tentativa ${failedAttempts} de ${MAX_LOGIN_ATTEMPTS})`;
                            mensagemLoginApi.style.color = 'red';
                        }
                        setLoginFormDisabled(false); // Habilita para próxima tentativa se não bloqueado
                    }
                }
            } catch (error) { // Erro de rede/fetch
                console.error('Erro na requisição de login:', error);
                if (mensagemLoginApi) {
                    mensagemLoginApi.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
                    mensagemLoginApi.style.color = 'red';
                }
                setLoginFormDisabled(false); // Habilita em caso de erro de rede
            }
            // Não há mais o 'finally' para habilitar o botão, pois o estado de bloqueio
            // ou as condições de erro/sucesso já cuidam disso.
        });
    }
});
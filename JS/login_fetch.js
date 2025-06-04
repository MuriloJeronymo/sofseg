document.addEventListener('DOMContentLoaded', function () {
    // Lógica para verificar parâmetros da URL e mostrar toast de notificação da página
    const urlParams = new URLSearchParams(window.location.search);
    let notificationMessage = null;

    if (urlParams.has('expirado') && urlParams.get('expirado') === '1') {
        notificationMessage = "Sua sessão expirou por inatividade. Faça login novamente.";
    } else if (urlParams.has('erro_acesso') && urlParams.get('erro_acesso') === '1') {
        notificationMessage = "Você precisa estar autenticado para acessar esta página.";
    }

    if (notificationMessage) {
        const toastElement = document.getElementById('pageNotificationToast');
        const toastBody = document.getElementById('pageNotificationToastBody');

        if (toastElement && toastBody) {
            toastBody.textContent = notificationMessage;
            try {
                const bootstrapToast = new bootstrap.Toast(toastElement, { delay: 7000 });
                bootstrapToast.show();
                // Opcional: Limpar parâmetros da URL após exibir o toast
                window.history.replaceState({}, document.title, window.location.pathname + window.location.hash);
            } catch (e) {
                console.error("login_fetch.js: Erro ao inicializar ou mostrar o toast Bootstrap:", e);
            }
        } else {
            console.warn("login_fetch.js: Elementos HTML para o pageNotificationToast não foram encontrados!");
        }
    }
    // Removido o console.log para "nenhum parâmetro encontrado" para um console mais limpo em uso normal.

    // Lógica para o formulário de login
    const formLogin = document.getElementById('formLogin');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const btnLogin = document.getElementById('btnLogin');
    const mensagemLoginApi = document.getElementById('mensagemLoginApi'); // Div para mensagens de feedback da tentativa de login

    if (formLogin) {
        formLogin.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (mensagemLoginApi) {
                mensagemLoginApi.textContent = '';
                mensagemLoginApi.style.color = 'red';
            }

            const email = emailInput.value.trim();
            const senha = passwordInput.value;

            if (email === '' || senha === '') {
                if (mensagemLoginApi) {
                    mensagemLoginApi.textContent = 'Por favor, preencha email e senha.';
                }
                return;
            }

            const dadosLogin = {
                email: email,
                senha: senha
            };

            if (btnLogin) btnLogin.disabled = true;
            if (mensagemLoginApi) {
                mensagemLoginApi.textContent = 'Processando...';
                //mensagemLoginApi.style.color = 'inherit'; // Resetar cor - branco
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
                    if (mensagemLoginApi) {
                        mensagemLoginApi.textContent = resultado.mensagem;
                        mensagemLoginApi.style.color = 'green';
                    }
                    if (resultado.usuario) {
                        localStorage.setItem('usuarioLogado', JSON.stringify(resultado.usuario));
                    }
                    localStorage.setItem('isLogado', 'true');
                    window.location.href = '../../autenticado.php';
                } else { // Erro da API (credenciais erradas, conta não ativa, etc.)
                    if (mensagemLoginApi) {
                        mensagemLoginApi.textContent = resultado.mensagem || `Erro: ${response.status}`;
                    }
                    // O bloco de toast redundante para erros da API foi removido.
                }
            } catch (error) { // Erro de rede/fetch
                console.error('Erro na requisição de login:', error);
                if (mensagemLoginApi) {
                    mensagemLoginApi.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
                }
            } finally {
                if (btnLogin) btnLogin.disabled = false;
            }
        });
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const formulario = document.getElementById('formulario');
    const emailInput = document.getElementById('email');
    const telefoneInput = document.getElementById('phone');
    const nomeInput = document.getElementById('nome');
    const senhaInput = document.getElementById('senha');
    const confirmarSenhaInput = document.getElementById('confirmarSenha');

    // Mensagens específicas para validações mais detalhadas
    const emailMensagem = document.getElementById('email-mensagem');
    const telefoneMensagem = document.getElementById('phone-mensagem'); // Corrigido para 'phone-mensagem'
    const nomeMensagem = document.getElementById('nome-mensagem');
    const senhaMensagem = document.getElementById('senha-mensagem'); //mensagem-senha
    const confirmarSenhaMensagem = document.getElementById('mensagem-confirmar');

    // Mensagem geral da API
    const mensagemGeralAPI = document.getElementById('mensagem-geral-api');

    // Lógica para mostrar/ocultar senha
    const toggleSenha = document.getElementById('toggleSenha');
    const toggleConfirmar = document.getElementById('toggleConfirmar');

    if (toggleSenha) {
        toggleSenha.addEventListener('click', function () {
            const tipo = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', tipo);
            this.classList.toggle('bi-eye-slash-fill'); // Alterna o ícone
        });
    }

    if (toggleConfirmar) {
        toggleConfirmar.addEventListener('click', function () {
            const tipo = confirmarSenhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmarSenhaInput.setAttribute('type', tipo);
            this.classList.toggle('bi-eye-slash-fill'); // Alterna o ícone
        });
    }

    if (formulario) {
        formulario.addEventListener('submit', async function (event) {
            event.preventDefault(); // Impede o envio tradicional do formulário

            // Limpa mensagens anteriores
            if (mensagemGeralAPI) mensagemGeralAPI.textContent = '';
            if (mensagemGeralAPI) mensagemGeralAPI.className = 'text-center py-2'; // Reset class
            if (senhaMensagem) senhaMensagem.textContent = '';
            if (confirmarSenhaMensagem) confirmarSenhaMensagem.textContent = '';


            const email = emailInput.value.trim();
            const telefone = telefoneInput.value.trim();
            const nome = nomeInput.value.trim();
            const senha = senhaInput.value; // Não faz trim na senha
            const confirmarSenha = confirmarSenhaInput.value;

            // Validação básica no lado do cliente
            if (senha !== confirmarSenha) {
                if (confirmarSenhaMensagem) {
                    confirmarSenhaMensagem.textContent = 'As senhas não coincidem!';
                    confirmarSenhaMensagem.style.color = 'red';
                } else if (mensagemGeralAPI) {
                    mensagemGeralAPI.textContent = 'As senhas não coincidem!';
                    mensagemGeralAPI.style.color = 'red';
                }
                return;
            }

            // A validação de "required" já é feita pelo HTML.

            const dadosUsuario = {
                nome: nome,
                email: email,
                telefone: telefone,
                senha: senha
            };

            // Desabilita o botão para evitar múltiplos envios
            const botaoCadastro = document.getElementById('botao-cadastro');
            if (botaoCadastro) botaoCadastro.disabled = true;
            if (mensagemGeralAPI) mensagemGeralAPI.textContent = 'Processando...';


            try {

                const response = await fetch('/ProjetoSoftwareSeguro/api/registrar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json' // É bom especificar o que você espera também
                    },
                    body: JSON.stringify(dadosUsuario)
                });

                const resultado = await response.json();

                if (response.ok) { // Status 200-299
                    if (mensagemGeralAPI) {
                        mensagemGeralAPI.textContent = resultado.mensagem || 'Cadastro realizado com sucesso!';
                        mensagemGeralAPI.className = 'text-center py-2 text-success'; // Adiciona uma classe para sucesso
                    }
                    formulario.reset(); // Limpa o formulário
                    // Da pra redirecionar o usuário após um tempo
                    setTimeout(() => {
                        window.location.href = '/ProjetoSoftwareSeguro/autenticacao/html/cadastro_sucesso.html';
                    }, 2000);
                } else {
                    // Erros tratados pela API (4xx, 5xx)
                    if (mensagemGeralAPI) {
                        mensagemGeralAPI.textContent = resultado.mensagem || `Erro: ${response.status}`;
                        mensagemGeralAPI.className = 'text-center py-2 text-danger'; // Adiciona uma classe para erro
                    }
                }
            } catch (error) {
                console.error('Erro na requisição de cadastro:', error);
                if (mensagemGeralAPI) {
                    mensagemGeralAPI.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
                    mensagemGeralAPI.className = 'text-center py-2 text-danger';
                }
            } finally {
                // Reabilita o botão
                if (botaoCadastro) botaoCadastro.disabled = false;
            }
        });
    }
});
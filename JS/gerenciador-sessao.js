// Adicionar este código ao dashboard.html e autenticado.html

// Variável para armazenar o ID do intervalo e poder pará-lo depois
let sessionCheckInterval;

// Função para verificar o status da sessão na API
async function verificarSessao() {
    try {
        const response = await fetch('/ProjetoSoftwareSeguro/api/checar-sessao.php', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            // Se a requisição falhar, para de verificar para evitar erros em loop
            clearInterval(sessionCheckInterval);
            console.error('Falha ao verificar a sessão.');
            return;
        }

        const resultado = await response.json();

        if (resultado.autenticado === false) {
            // Se a API diz que não estamos mais autenticados, a sessão expirou
            clearInterval(sessionCheckInterval); // Para o verificador
            alert("Sua sessão expirou por inatividade. Você será redirecionado para a página de login.");
            window.location.href = '/ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html?expirado=1';
        }
        // Se 'autenticado' for true, não faz nada e continua verificando.

    } catch (error) {
        clearInterval(sessionCheckInterval);
        console.error('Erro de rede ao verificar sessão:', error);
    }
}

// Inicia a verificação periódica (a cada 1 minuto = 60000 milissegundos)
// O document.addEventListener é usado para garantir que o script só rode após a página carregar
document.addEventListener('DOMContentLoaded', () => {
    sessionCheckInterval = setInterval(verificarSessao, 60000);
});
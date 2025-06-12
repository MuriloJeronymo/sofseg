// Função para sanitizar entradas e prevenir XSS (Cross-Site Scripting)
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str; // O browser escapa o conteúdo, tratando-o como texto puro
    return temp.innerHTML; // Retorna a string segura
}

// Definição dos campos e suas regras de validação via Expressão Regular
const campos = [{
    id: 'email',
    regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    errorMessage: 'E-mail inválido!'
}, {
    id: 'phone',
    regex: /^\(\d{2}\) ?\d{5}-\d{4}$/, // Regex ajustada para aceitar espaço opcional
    errorMessage: 'Telefone inválido! Use o formato (xx)xxxxx-xxxx'
}, {
    id: 'nome',
    regex: /^[A-Za-zÀ-ÿ\s]{3,}$/, // Exige no mínimo 3 caracteres
    errorMessage: 'Nome inválido. Não use números ou símbolos.'
}, {
    id: 'senha',
    regex: /^(?=.*\d).{8,}$/,
    errorMessage: 'Senha deve ter no mín. 8 caracteres e 1 número.'
}];

// Adiciona o listener de validação para cada campo definido acima
campos.forEach(campo => {
    const input = document.getElementById(campo.id);
    const message = document.getElementById(`${campo.id}-mensagem`);

    if (input) {
        // Função que lida com a validação baseada em regex
        const handleValidation = () => {
            const sanitizedValue = sanitizeHTML(input.value.trim());

            if (sanitizedValue === '') {
                input.className = 'input100 vazio';
                message.style.display = 'none';
                message.style.opacity = '0';
                return;
            }

            if (campo.regex.test(sanitizedValue)) {
                input.className = 'input100 valido';
                message.style.display = 'none';
                message.style.opacity = '0';
            } else {
                input.className = 'input100 invalido';
                message.textContent = campo.errorMessage;
                message.style.display = 'flex';
                message.style.opacity = '1';
            }
        };
        
        input.addEventListener('blur', handleValidation);
    }
});

// =================================================================
// Validação Específica para "Confirmar Senha"
// =================================================================
const senhaInput = document.getElementById('senha');
const confirmarSenhaInput = document.getElementById('confirmarSenha');
const confirmarSenhaMessage = document.getElementById('confirmarSenha-mensagem');

if (confirmarSenhaInput && senhaInput) {
    const handleConfirmPasswordValidation = () => {
        const senhaValue = sanitizeHTML(senhaInput.value);
        const confirmarSenhaValue = sanitizeHTML(confirmarSenhaInput.value);

        if (confirmarSenhaValue === '') {
            confirmarSenhaInput.className = 'input100 vazio';
            confirmarSenhaMessage.style.display = 'none';
            confirmarSenhaMessage.style.opacity = '0';
            return;
        }

        if (senhaValue !== confirmarSenhaValue) {
            confirmarSenhaInput.className = 'input100 invalido';
            confirmarSenhaMessage.textContent = 'As senhas não coincidem.';
            confirmarSenhaMessage.style.display = 'flex';
            confirmarSenhaMessage.style.opacity = '1';
        } else {
            confirmarSenhaInput.className = 'input100 valido';
            confirmarSenhaMessage.style.display = 'none';
            confirmarSenhaMessage.style.opacity = '0';
        }
    };

    confirmarSenhaInput.addEventListener('blur', handleConfirmPasswordValidation);
    // Valida a confirmação também quando o campo de senha original é alterado
    senhaInput.addEventListener('blur', handleConfirmPasswordValidation);
}


// =================================================================
// Máscara de Telefone
// =================================================================
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function (e) {
        // Remove tudo que não for dígito
        let value = phoneInput.value.replace(/\D/g, '');
        // Limita a 11 dígitos
        if (value.length > 11) value = value.slice(0, 11);

        // Aplica a máscara (xx) xxxxx-xxxx
        const formatado = value
            .replace(/^(\d{2})(\d)/, '($1) $2') // Coloca parênteses e espaço
            .replace(/(\d{5})(\d)/, '$1-$2'); // Coloca o hífen

        phoneInput.value = formatado;
    });
}
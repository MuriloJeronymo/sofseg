/**
 * Objeto para encapsular a funcionalidade de criptografia AES com CryptoJS.
 */
const CriptoLogin = {
    // ATENÇÃO: A chave e o IV devem ser os mesmos usados no seu backend (PHP).
    // Guarde-os de forma segura e não os exponha desnecessariamente.
    // O ideal é que esta chave seja carregada de uma variável de ambiente no servidor.
    // Para este exemplo, usaremos valores fixos.
    secretKey: CryptoJS.enc.Hex.parse('00112233445566778899aabbccddeeff00112233445566778899aabbccddeeff'), // Chave de 256 bits
    iv: CryptoJS.enc.Hex.parse('fedcba9876543210fedcba9876543210'), // IV de 128 bits

    /**
     * Criptografa um dado usando AES.
     * @param {string} plainText O texto plano a ser criptografado.
     * @returns {string} O texto criptografado em formato Base64.
     */
    encrypt: function(plainText) {
        if (!plainText) {
            return null;
        }

        const encrypted = CryptoJS.AES.encrypt(plainText, this.secretKey, {
            iv: this.iv,
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7
        });

        return encrypted.toString(); // Retorna a string em Base64
    }
};
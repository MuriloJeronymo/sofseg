use bookshell;
CREATE TABLE livros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255),
  autor VARCHAR(255),
  capa VARCHAR(255),
  disponibilidade BOOLEAN DEFAULT TRUE
);

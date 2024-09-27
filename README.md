# **Hackaton Application**

### **Equipe**

- **Matheus Nunes Almeida Werneck Telles**  
  RM: 352550 | Discord: mwtelles

- **Lucas Gelhen Rigon**  
  RM: 353080 | Discord: lucasrigon

- **Ricardo Luis Machado**  
  RM: 352550 | Discord: ricardolmachado

- **Otávio Reis Perkles**  
  RM: 352963 | Discord: operkles

- **Jonathan Oliveira**  
  RM: 352718 | Discord: jonathansro

---

### **Link do Vídeo de Apresentação**

- [Vídeo de Apresentação do Projeto](https://www.youtube.com/watch?v=tyaATDrL64g)

---

### **Documentação do Projeto**

- **Documento de Design Orientado ao Domínio (DDD):**  
  [Link para o Documento DDD](https://drive.google.com/file/d/10VubX8GmiquyQiEDsmouCFny8xEA1WPd/view?usp=drive_link)

---

## **Subir o Ambiente**

Siga os passos abaixo para configurar e subir o ambiente de desenvolvimento:

1. Clone o repositório para sua máquina local.

   ```bash
   git clone https://github.com/FIAP-Tech-Chalenge/hackaton-application.git
   cd hackaton-application
   ```

2. Suba o ambiente Docker:

   ```bash
   docker-compose up -d
   ```

3. Entre no container do PHP:

   ```bash
   docker exec -it php_hackaton bash
   ```

4. Dentro do container, execute os seguintes comandos:

   - Instale as dependências do projeto:

     ```bash
     composer install
     ```

   - Crie as tabelas no banco de dados:

     ```bash
     php artisan migrate
     ```

   - Copie o arquivo de exemplo de configuração do ambiente:

     ```bash
     cp .env.example .env
     ```

   - Configure o banco de dados no arquivo `.env` conforme necessário.
   - Gere a chave da aplicação:

     ```bash
     php artisan key:generate
     ```

---

## **Configuração de Filas com Horizon**

1. Instale e configure o Horizon para gerenciar filas:

   ```bash
   php artisan horizon:install
   ```

2. Inicie o Horizon para processar as filas:

   ```bash
   php artisan horizon
   ```

**Nota:** O Redis é necessário para o funcionamento do Horizon.

---

## **Cobertura de Testes**

Para gerar o relatório de cobertura de código, execute o seguinte comando:

```bash
vendor/bin/phpunit --coverage-html storage/app/public/coverage-report/
```

O relatório será gerado na pasta `storage/app/public/coverage-report/` e pode ser acessado via navegador.

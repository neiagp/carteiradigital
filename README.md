# Carteira Digital

Este projeto é uma aplicação Laravel que simula uma carteira digital, permitindo funcionalidades como depósito, transferência, reversão de transações e cálculo de saldo.

## Funcionalidades

- **Depósito**: Permite que o usuário adicione saldo à sua conta.
- **Transferência**: Realiza transferências entre contas de usuários.
- **Reversão de Transações**: Permite reverter transações realizadas.
- **Cálculo de Saldo**: Calcula o saldo atual de um usuário com base nas transações realizadas.

## Requisitos

- PHP >= 8.0
- Composer
- Banco de Dados (MySQL, PostgreSQL, etc.)
- Laravel >= 9.x

## Instalação

1. Clone o repositório:
   ```bash
   git clone <url-do-repositorio>
   cd CarteiraDigital
   ```

2. Instale as dependências do projeto:
   ```bash
   composer install
   ```

3. Configure o arquivo `.env`:
   - Copie o arquivo `.env.example` para `.env`:
     ```bash
     cp .env.example .env
     ```
   - Configure as variáveis de ambiente, como conexão com o banco de dados.

4. Gere a chave da aplicação:
   ```bash
   php artisan key:generate
   ```

5. Execute as migrações e seeders:
   ```bash
   php artisan migrate --seed
   ```

## Uso

### Endpoints da API

1. **Depósito**
   - **Rota**: `POST /api/depositar`
   - **Parâmetros**:
     - `valor` (float): Valor a ser depositado.
   - **Resposta**:
     ```json
     {
       "mensagem": "Depósito realizado com sucesso."
     }
     ```

2. **Transferência**
   - **Rota**: `POST /api/transferir`
   - **Parâmetros**:
     - `destinatario_id` (int): ID do destinatário.
     - `valor` (float): Valor a ser transferido.
   - **Resposta**:
     ```json
     {
       "mensagem": "Transferência realizada com sucesso."
     }
     ```

3. **Reversão de Transação**
   - **Rota**: `POST /api/reverter/{id}`
   - **Parâmetros**:
     - `id` (int): ID da transação a ser revertida.
   - **Resposta**:
     ```json
     {
       "mensagem": "Transação revertida com sucesso."
     }
     ```

4. **Cálculo de Saldo**
   - **Rota**: `GET /api/saldo/{id}`
   - **Parâmetros**:
     - `id` (int): ID do usuário.
   - **Resposta**:
     ```json
     {
       "saldo": 150.00
     }
     ```

## Testes

### Testes de Funcionalidade
Os testes de funcionalidade estão localizados em `tests/Feature/TransacaoControllerTest.php`.

Para executar os testes:
```bash
php artisan test tests/Feature/TransacaoControllerTest.php
```

### Testes Unitários
Os testes unitários estão localizados em `tests/Unit/TransacaoControllerTest.php`.

Para executar os testes:
```bash
php artisan test tests/Unit/TransacaoControllerTest.php
```

## Estrutura do Projeto

- **`app/Http/Controllers/Api/TransacaoController.php`**: Controlador principal para gerenciar transações.
- **`app/Models/Transacao.php`**: Modelo que representa as transações no banco de dados.
- **`tests/Feature`**: Testes de funcionalidade para endpoints da API.
- **`tests/Unit`**: Testes unitários para métodos específicos.

## Contribuição

1. Faça um fork do repositório.
2. Crie uma branch para sua feature:
   ```bash
   git checkout -b minha-feature
   ```
3. Faça commit das suas alterações:
   ```bash
   git commit -m "Minha nova feature"
   ```
4. Envie para o repositório remoto:
   ```bash
   git push origin minha-feature
   ```
5. Abra um Pull Request.

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

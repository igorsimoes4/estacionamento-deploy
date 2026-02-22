# Sistema de Gerenciamento de Estacionamento

Aplicacao web em Laravel para operacao completa de estacionamento: controle de entrada e saida, checkout com metodos de pagamento, mensalistas, relatorios e contabilidade.

## Status
Em desenvolvimento ativo.

## Stack Tecnica
- PHP 8.1+
- Laravel 9
- Livewire 2.12
- Laravel AdminLTE
- MySQL
- DomPDF (PDF)

## Funcionalidades Principais

### Operacao de veiculos
- Cadastro, edicao e finalizacao de veiculos.
- Calculo de preco por tipo de veiculo (carro, moto, caminhonete).
- Tela principal em Livewire com filtros, ordenacao e atualizacao dinamica.
- Alocacao de vaga por setor (automatico ou manual) com mapa visual do patio.
- API para entrada/saida integrada com cancela/catraca.
- Endpoint de ingestao ANPR/OCR para leitura automatica de placa.

### Reservas e patio
- Reserva antecipada de vaga com check-in operacional.
- Controle de status da vaga: livre, reservada, ocupada, bloqueada e manutencao.
- Dashboard de ocupacao em tempo real por setor.

### Precificacao dinamica
- Regras por:
  - horario
  - dia da semana
  - tipo de veiculo
  - faixa de lotacao
- Multiplicador e acrescimo fixo configuraveis.

### Checkout e pagamentos
- Metodos: dinheiro, Pix, cartao credito/debito e boleto.
- Gateways configuraveis:
  - PagBank
  - Cielo
  - Stone
  - Rede
  - Getnet
- Pix com payload copia e cola e suporte a QR code.
- Boleto com geracao automatica (PagBank/Cielo) e fallback manual quando necessario.

### Mensalistas
- CRUD completo de mensalistas.
- Acesso separado para mensalista (`/mensalista/login`).
- Portal do mensalista com dados da assinatura e download de boleto.
- Boleto do mensalista com codigo de barras no PDF.
- Cobranca recorrente automatica por competencia.
- Controle de inadimplencia com multa, juros e bloqueio de acesso.

### Relatorios e contabilidade
- Relatorios PDF/CSV para operacao, faturamento e ocupacao.
- Modulo de contabilidade para lancamentos e consolidacao financeira.
- Controle de caixa por turno (abertura, movimentacoes, sangria, fechamento e divergencia).
- Conciliação de pagamentos por webhook (Pix/cartao/boleto).
- Emissao fiscal (NFS-e/NFC-e) com registro de status por transacao.

### Seguranca e governanca
- Permissoes por perfil administrativo:
  - admin
  - operador
  - financeiro
- Auditoria completa de requisicoes e alteracoes de dados.
- Central de notificacoes (fila de e-mail/WhatsApp).

### Saude e continuidade
- Health checks persistidos (banco, storage, fila, integracoes).
- Rotina de backup operacional automatizada.

### Configuracoes
- Configuracoes separadas em:
  - Estacionamento
  - Pagamentos
  - Precos
- Menu organizado em submenus no painel.

## Estrutura de acesso
- Login administrativo: `/login`
- Painel administrativo: `/painel`
- Login mensalista: `/mensalista/login`
- Portal mensalista: `/mensalista/painel`

## Requisitos
- PHP 8.1 ou superior
- Composer
- MySQL 8+
- Node.js (opcional, se for compilar assets)

## Instalacao

1. Clone o repositorio:
```bash
git clone https://github.com/igorsimoes4/estacionamento.git
cd estacionamento
```

2. Instale dependencias PHP:
```bash
composer install
```

3. Configure ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

4. Ajuste banco no `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estacionamento
DB_USERNAME=root
DB_PASSWORD=
```

5. Execute migrations:
```bash
php artisan migrate
```

6. Inicie a aplicacao:
```bash
php artisan serve
```

Aplicacao disponivel em: `http://localhost:8000`

## Configuracao de pagamentos
Acesse no painel:
- `Configuracoes -> Pagamentos`

Preencha credenciais e ambiente (`sandbox` ou `production`) para cada gateway.

## API de integracao (v1)
- `POST /api/payments/webhooks/{provider}` webhook de conciliacao.
- `GET /api/v1/status` status operacional.
- `POST /api/v1/gate/entry` registrar entrada via integracao.
- `POST /api/v1/gate/exit` registrar saida via integracao.
- `POST /api/v1/anpr/ingest` ingestao ANPR/OCR.
- `GET /api/v1/health` saude da aplicacao (com `?run=1` para executar checks).

Autenticacao das rotas `api/v1/*`:
- header `Authorization: Bearer <INTEGRATION_API_TOKEN>`
ou
- header `X-Integration-Token: <INTEGRATION_API_TOKEN>`

## Comandos uteis
```bash
php artisan optimize:clear
php artisan route:list
php artisan migrate:status
php artisan parking:billing-run
php artisan parking:delinquency-run
php artisan parking:notifications-run --limit=200
php artisan system:health-check
php artisan system:backup-run
```

## Agendamento (cron)
No servidor, configure:
```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

## Observacoes
- Alguns provedores podem exigir homologacao e parametros adicionais em producao.
- Quando o gateway nao retorna boleto online, o sistema gera boleto em modo manual para manter a operacao.

## Autor
Igor Simoes da Silveira

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

### Relatorios e contabilidade
- Relatorios PDF/CSV para operacao, faturamento e ocupacao.
- Modulo de contabilidade para lancamentos e consolidacao financeira.

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

## Comandos uteis
```bash
php artisan optimize:clear
php artisan route:list
php artisan migrate:status
```

## Observacoes
- Alguns provedores podem exigir homologacao e parametros adicionais em producao.
- Quando o gateway nao retorna boleto online, o sistema gera boleto em modo manual para manter a operacao.

## Autor
Igor Simoes da Silveira

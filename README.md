# Parking Management System

The Parking Management System is a web platform that provides endpoints for efficiently managing parking. This system allows users to access information about available parking spaces, record their entries and exits from the parking area, and make payments. The application was developed based on the Laravel framework, ensuring high performance and reliability.

![Development Badge](http://img.shields.io/static/v1?label=STATUS&message=IN%20DEVELOPMENT&color=GREEN&style=for-the-badge)

| :placard: Vitrine.Dev | [Visit My Profile](https://cursos.alura.com.br/vitrinedev/igor01silveira) |
| -------------  | --- |
| :sparkles: Name        | **Parking Management System** |
| :label: Technologies | php, laravel |

![Parking Management System](https://github.com/igorsimoes4/estacionamento/blob/master/cover.png?raw=true#vitrinedev)

## Project Details
✔️ Techniques and Technologies Used
- `Laravel`
- `PHP`
- `MySQL`
- `Visual Studio Code`
- `Object-Oriented`
- `PHPUnit`

## Depoimentos Routes

![Depoimentos Routes](https://github.com/igorsimoes4/jornadamilhas/assets/41714117/faffa45b-768d-45ca-a8da-7e0a4b6339ff)

# Parking Routes Documentation

## Redirect to Dashboard

Redirects the homepage to the dashboard.

- **URL:** `/`
- **Method:** `GET`
- **Response:**
  - **Status Code:** 302 Found
  - **Location:** `/painel`

## Dashboard

Displays the parking management dashboard.

- **URL:** `/painel`
- **Method:** `GET`
- **Response:**
  - **Status Code:** 200 OK
  - **Content:** HTML page with dashboard information and features.

## Manage Cars

Manages car information in the parking system.

- **URL:** `/painel/cars`
- **Method:** `GET`
- **Response:**
  - **Status Code:** 200 OK
  - **Content:** HTML page listing registered cars.

- **URL:** `/painel/cars/{car}`
- **Method:** `GET`
- **Response:**
  - **Status Code:** 200 OK
  - **Content:** HTML page displaying specific car details.

- **URL:** `/painel/cars/showmodal/{car}`
- **Method:** `GET`
- **Response:**
  - **Status Code:** 200 OK
  - **Content:** HTML modal dialog displaying additional car information.

## Print Payment Receipt

Allows printing a payment receipt for a specific car.

- **URL:** `/painel/pembayaran/print`
- **Method:** `POST`
- **Data Parameters:** JSON object containing car data for which the receipt will be printed.
- **Response:**
  - **Status Code:** 200 OK
  - **Content:** HTML page with the payment receipt ready for printing.

## Settings

Manages system settings in the parking system.

- **URL:** `/painel/settings`
- **Method:** `GET`
- **Response:**
  - **Status Code:** 200 OK
  - **Content:** HTML page with system settings and options.

---

**Status Codes:**

- 200 OK: The request was successful.
- 302 Found: The request was redirected to another page.

Please note that this documentation assumes the use of the provided route names and controller names (e.g., `EstacionamentoController`, `CarsController`, `PembayaranController`, `SettingController`). Make sure to replace these with the actual names used in your Laravel application.

## Access the Project

You can access the complete source code of the project on [GitHub](https://github.com/igorsimoes4/estacionamento).

## How to Run the Project

### Prerequisites

Before proceeding, make sure you have the following technologies installed in your development environment:

- [PHP 7.4](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [MySQL 8.0](https://www.mysql.com/)
- [Visual Studio Code](https://code.visualstudio.com/) (or your preferred IDE)

### Step 1: Clone the Repository

Clone the project repository to your local environment using the following Git command:

```bash
git clone https://github.com/igorsimoes4/estacionamento.git
```
### Step 2: Install Dependencies

Navigate to the project directory and install Composer dependencies by running:Passo 2: Instalar as dependências

```bash
cd estacionamento
composer install
```

### Step 3: Configure the Environment

Make a copy of the .env.example file and rename it to .env. Then, update the database configurations in the .env file with your local credentials:

```bash
DB_CONNECTION=mysql
DB_HOST=seu-host
DB_PORT=seu-port
DB_DATABASE=seu-database
DB_USERNAME=seu-usuario
DB_PASSWORD=sua-senha
```

## Step 4: Run Migrations

With the environment configured, create the necessary tables in the database by running the migrations:

```bash
php artisan migrate
```

## Step 5: Run the Server

Finally, start the local development server with the command:

```bash
php artisan serve
```

The project will be available at http://localhost:8000.

Now you can access and test the Parking Management System locally.

# Author

[<img loading="lazy" src="https://avatars.githubusercontent.com/u/41714117?v=4" width=115><br><sub>Igor Simões da Silveira</sub>](https://github.com/igorsimoes4) 

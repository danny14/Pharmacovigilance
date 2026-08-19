<div align="center">
  <h1>💊 Pharmacovigilance Alert System</h1>
  <p><strong>Advanced Compounding Pharmacy Notification & Audit Module</strong></p>

  <!-- Badges -->
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue.js" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
</div>

<br/>

## 📖 Overview

A complete Pharmacovigilance module that enables a compounding pharmacy to securely search, identify, and notify customers who purchased a medication associated with a compromised lot number. Designed with enterprise-grade **Clean Architecture**, API-first philosophy, and strict audit logging.

---

## ✨ Features & Bonus Implemented

- 🔐 **Secure Authentication & RBAC (Bonus):** Stateless API token authentication using Laravel Sanctum. Features Role-Based Access Control (`admin` vs `viewer`), hiding sensitive alert actions from non-privileged users.
- 🔎 **Smart Search:** Retrieve orders by medication lot number with dynamic date-range filtering.
- ✉️ **Bulk Alerting (Bonus):** Select multiple compromised orders and dispatch recall emails instantly.
- 📱 **SMS Notifications (Bonus):** Seamlessly integrates SMS text message dispatch alongside email alerts.
- 📊 **CSV Export (Bonus):** Download search results into a clean, properly formatted `.csv` spreadsheet.
- 📜 **Audit Trail (Bonus):** Database-level logging of every alert sent. Real-time visual history log embedded in the Dashboard.
- 📚 **Swagger API Docs (Bonus):** Auto-generated API documentation using modern PHP 8 Attributes (`/api/documentation`).
- 🧪 **Unit Testing (Bonus):** Comprehensive feature and unit tests with PHPUnit, Mockery, and in-memory SQLite databases for supreme reliability.

---

## 🚀 Setup Instructions (Docker Environment)

To ensure this application runs perfectly on any machine without requiring local PHP or MySQL installations, we provide a fully containerized Docker setup.

### 📋 Prerequisites
- **Docker** & **Docker Compose** installed.
- **Git** installed.

### 🛠️ Step-by-Step Installation

**1. Clone the repository**
```bash
git clone <your-repo-url>
cd Pharmacovigilance
```

**2. Start the Docker containers**
```bash
docker-compose up -d --build
```

**3. Install Backend Dependencies (PHP)**
```bash
docker-compose exec app composer install
```

**4. Environment Setup**
```bash
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
```

**5. Install Frontend Dependencies & Compile Assets**  
*(Note: We use `--legacy-peer-deps` due to a strict Vite version peer-dependency in vue-router).*
```bash
docker-compose exec app npm install --legacy-peer-deps
docker-compose exec app npm run build
```

**6. Run Database Migrations & Seeders**  
Wait 5-10 seconds for the MySQL database container to become healthy, then run:
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

**7. Access the Application**
- Open your browser: `http://localhost:8000`
- **Test Credentials (Admin):** Username: `admin` | Password: `secret` *(Full access)*
- **Test Credentials (Viewer):** Username: `viewer` | Password: `secret` *(Read-only)*

---

## 🏛️ Design Decisions & Architecture

- **Clean Architecture (Use Cases):** Business logic has been strictly decoupled from Controllers into isolated `UseCase` classes (e.g., `SearchOrdersByLotUseCase`, `ExportOrdersByLotUseCase`). This ensures the code is highly testable, adheres to the Single Responsibility Principle, and prevents "fat controllers".
- **RESTful API First:** The backend behaves as a pure JSON API. The frontend and backend communicate strictly via Axios. 
- **Vue.js SPA integration:** The frontend is a modern Single Page Application powered by Vue 3. It mounts on a single Blade file (`app.blade.php`) and handles its own routing via `vue-router`.
- **Database Transactions:** To prevent data inconsistencies, the bulk alert dispatch process is wrapped in a `DB::transaction()`. If an email fails to send, the audit record is safely rolled back.

---

## 🧠 Assumptions

1. **Authentication Role:** It is assumed the user interacting with the Dashboard is an internal Pharmacy Administrator/Pharmacist. Therefore, Laravel Sanctum is used for seamless, stateless access.
2. **Local Email Testing:** In the local development environment (`APP_ENV=local`), emails are **not** dispatched to real addresses to prevent spamming customers. Instead, Laravel logs the raw email output to `storage/logs/laravel.log` (`MAIL_MAILER=log`). 
   - *To test real emails locally, update your `.env` file with Mailtrap or standard SMTP credentials and restart the container.*



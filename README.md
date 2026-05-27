# Bira 🚀

Bira is a powerful, Agile project management and issue tracking tool (inspired by Jira), built with [Laravel](https://laravel.com/) and [Tailwind CSS](https://tailwindcss.com/). It offers a comprehensive suite of tools for teams to manage tasks, plan sprints, estimate efforts using Planning Poker, and track their progress over time.

## ✨ Features

- **Agile Boards**: Customizable boards with columns, drag-and-drop functionality, and advanced task tracking.
- **Sprint Management**: Plan, start, complete, and deliver sprints. Manage your backlog effectively.
- **Planning Poker**: Real-time collaborative estimation sessions to size tasks accurately with your team.
- **Reporting & Metrics**: Built-in burndown charts and velocity reports for data-driven decisions.
- **Time Tracking**: Log time spent on individual tasks and view activities in a calendar interface.
- **Team Management**: Create teams, manage roles, and organize users into sub-teams for larger organizations.
- **Customizable Workflows**: Adjust board modes, task priorities, custom tags, and item types according to your team's needs.

## 🛠️ Tech Stack

- **Backend**: Laravel 12.0, PHP 8.2+
- **Frontend**: Tailwind CSS, Vite, Blade Templates, JavaScript
- **Database**: MySQL / SQLite (configured via `.env`)

## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & npm

### Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd Bira/bira
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node dependencies:**
   ```bash
   npm install
   ```

4. **Environment Setup:**
   Copy the example environment file and configure your database settings.
   ```bash
   cp .env.example .env
   ```

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

7. **Start the Development Server:**
   You can run everything concurrently using the built-in Composer script:
   ```bash
   composer run dev
   ```
   *Alternatively, run `php artisan serve` and `npm run dev` in separate terminals.*

## 📂 Core Project Structure

The Laravel application is located inside the `bira/` directory.

- `bira/app/Models` - Contains Eloquent models (`Board`, `Sprint`, `PokerSession`, `WorkItem`, etc.)
- `bira/app/Http/Controllers` - Handlers for various features like Sprints, Teams, Boards, and Planning Poker.
- `bira/routes/web.php` - Application routes and endpoints.
- `bira/resources/views` - Blade templates for the application's user interface.

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

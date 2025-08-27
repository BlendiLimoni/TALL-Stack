# Smart Workspace Manager

Smart Workspace Manager is a powerful, intuitive project management tool designed to help teams organize tasks, track progress, and collaborate seamlessly. Built on the TALL stack (Tailwind CSS, Alpine.js, Laravel, and Livewire), it offers a modern, reactive user experience.

## Features

- **Team Management:** Create teams, invite members, and manage roles.
- **Project Boards:** Organize projects with customizable Kanban boards.
- **Task Management:** Create, assign, and track tasks with priorities, statuses, and deadlines.
- **Drag & Drop Interface:** Intuitively manage your workflow by dragging tasks between columns.
- **Real-time Updates:** See changes from your teammates instantly without refreshing the page.
- **Activity Feed:** Keep a pulse on project updates with a detailed activity log.

## Tech Stack

- **Backend:** Laravel 11
- **Frontend:** Livewire 3, Tailwind CSS, Alpine.js
- **Database:** SQLite (default), MySQL, PostgreSQL
- **Authentication:** Laravel Jetstream

## Getting Started

### Prerequisites

- PHP >= 8.2
- Composer
- Node.js & NPM
- A database server (e.g., MySQL, PostgreSQL)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/smart-workspace-manager.git
   cd smart-workspace-manager
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Setup your environment:**
   ```bash
   cp .env.example .env
   ```
   *Update your database credentials and other settings in the `.env` file.*

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Run database migrations:**
   ```bash
   php artisan migrate
   ```

6. **Build frontend assets:**
   ```bash
   npm run dev
   ```

7. **Start the development server:**
   ```bash
   php artisan serve
   ```

### Quick Demo

To quickly explore the application without creating a user, you can use the built-in demo feature:

1.  Start the application (`php artisan serve`).
2.  Navigate to `/demo` in your browser.

This will log you in as a demo user with a pre-populated project board.

## Testing

The application includes a suite of tests to ensure code quality and stability. To run the tests, use the following command:

```bash
php artisan test
```

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue to discuss your ideas.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
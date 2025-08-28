# Smart Workspace Manager

Smart Workspace Manager is a powerful, intuitive project management tool designed to help teams organize tasks, track progress, and collaborate seamlessly. Built on the TALL stack (Tailwind CSS, Alpine.js, Laravel, and Livewire), it offers a modern, reactive user experience with advanced productivity features.

## ✨ Features

### 🏢 **Team & Project Management**

-   **Team Management:** Create teams, invite members, and manage roles with granular permissions
-   **Project Boards:** Organize projects with customizable Kanban boards and multiple view options
-   **Project Templates:** Create reusable project templates with predefined tasks and columns
-   **Project Tags & Categories:** Organize projects with color-coded tags and filtering
-   **Project Goals & Budgets:** Set project goals, budgets, and track progress against targets

### 📋 **Advanced Task Management**

-   **Enhanced Tasks:** Create, assign, and track tasks with priorities, statuses, and deadlines
-   **Subtasks:** Break down complex tasks into manageable subtasks with completion tracking
-   **Task Dependencies:** Set task dependencies to manage workflow sequences
-   **Task Labels:** Organize tasks with custom labels and tags
-   **Progress Tracking:** Visual completion percentage based on subtasks and custom criteria
-   **Comments & Collaboration:** Add comments and collaborate on tasks in real-time

### ⏱️ **Time Tracking & Productivity**

-   **Built-in Time Tracker:** Start/stop time tracking for tasks with detailed time entries
-   **Time Analytics:** Analyze time spent per task, project, and team member
-   **Productivity Metrics:** Track completion rates, average task times, and team efficiency
-   **Estimated vs Actual Time:** Compare time estimates with actual time spent

### 📊 **Analytics & Reporting**

-   **Interactive Dashboard:** Overview of team performance with key metrics and charts
-   **Team Reports:** Comprehensive reporting on task completion, time tracking, and productivity
-   **Custom Date Ranges:** Filter reports by specific time periods
-   **Export Capabilities:** Export data for external analysis (planned)

### 🎯 **Enhanced User Experience**

-   **Drag & Drop Interface:** Intuitively manage your workflow by dragging tasks between columns
-   **Real-time Updates:** See changes from your teammates instantly without refreshing the page
-   **Activity Feed:** Keep a pulse on project updates with a detailed activity log
-   **Dark Mode Support:** Toggle between light and dark themes
-   **Responsive Design:** Works seamlessly on desktop, tablet, and mobile devices
-   **Advanced Search:** Full-text search across tasks, projects, and comments

## 🛠️ Tech Stack

-   **Backend:** Laravel 12 (latest)
-   **Frontend:** Livewire 3, Tailwind CSS, Alpine.js
-   **Database:** SQLite (default), MySQL, PostgreSQL
-   **Search Engine:** TNTSearch for full-text search
-   **Authentication:** Laravel Jetstream with team management
-   **Testing:** PHPUnit with comprehensive Feature tests
-   **Time Tracking:** Built-in time tracking system
-   **Real-time Updates:** Livewire for reactive interfaces

## 📈 Recent Improvements & New Features

### ✅ **What Was Added:**

1. **Enhanced Task System**

    - Subtasks with completion tracking
    - Task comments and collaboration
    - Time estimation vs actual time tracking
    - Task labels and dependencies
    - Progress percentage calculations

2. **Comprehensive Time Tracking**

    - Start/stop time tracking per task
    - Detailed time entry logs
    - User productivity analytics
    - Time reporting and insights

3. **Advanced Dashboard**

    - Team performance metrics
    - Task completion statistics
    - Overdue task monitoring
    - Quick action shortcuts
    - Recent activity summaries

4. **Reporting & Analytics**

    - Team productivity reports
    - Time tracking analysis
    - Task completion trends
    - Custom date range filtering

5. **Enhanced Project Management**

    - Project templates for reusability
    - Project status tracking (planning, active, completed, etc.)
    - Budget and goal setting
    - Custom project fields

6. **Technical Improvements**
    - Fixed duplicate migration issues
    - Enhanced database schema
    - Improved error handling
    - Better model relationships
    - Comprehensive test coverage

### 🚀 **Performance & Code Quality**

-   Optimized database queries with eager loading
-   Proper model relationships and constraints
-   Enhanced error handling and validation
-   Improved code organization and documentation
-   Added comprehensive tests

## 🏁 Getting Started

### Prerequisites

-   PHP >= 8.2
-   Composer
-   Node.js & NPM
-   A database server (e.g., MySQL, PostgreSQL) or SQLite (default)

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

    _Update your database credentials and other settings in the `.env` file._

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

1. Start the application (`php artisan serve`).
2. Navigate to `/demo` in your browser.

This will log you in as a demo user with a pre-populated project board showcasing all features.

## 🎯 Usage Examples

### Starting a New Project

1. Navigate to the Dashboard
2. Click "Create Project" from Quick Actions
3. Choose a template or start from scratch
4. Set project goals, budget, and team members
5. Begin adding tasks and organizing your Kanban board

### Time Tracking Workflow

1. Open any task from the Kanban board
2. Click "Start Time Tracking"
3. Work on the task
4. Click "Stop Time Tracking" when done
5. View time reports in the Reports section

### Team Collaboration

1. Assign tasks to team members
2. Add comments for clarification
3. Break complex tasks into subtasks
4. Monitor progress through the dashboard
5. Use activity logs to stay updated

## 🧪 Testing

The application includes a comprehensive test suite to ensure code quality and stability:

```bash
php artisan test
```

Run specific test suites:

```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## 🎨 Customization

### Adding Custom Task Fields

The system supports custom fields through JSON columns. Extend the Task model and add custom validation as needed.

### Creating Project Templates

Create reusable project templates with predefined tasks, columns, and settings to speed up project initialization.

### Theming

The application uses Tailwind CSS with dark mode support. Customize the theme by modifying the Tailwind configuration.

## 🔧 Configuration

### Search Configuration

The application uses TNTSearch for full-text search. Configure search settings in `config/scout.php`:

```php
'tntsearch' => [
    'storage' => storage_path('framework/search'),
    'fuzziness' => env('TNTSEARCH_FUZZINESS', false),
    'maxDocs' => env('TNTSEARCH_MAX_DOCS', 500),
],
```

### Time Tracking

Time tracking is enabled by default. Configure time tracking behavior in your models or add custom time tracking rules.

## 📚 API Documentation

The application includes API endpoints for programmatic access (future enhancement). Authentication is handled through Laravel Sanctum.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue to discuss your ideas.

### Development Guidelines

1. Follow PSR-12 coding standards
2. Write tests for new features
3. Update documentation as needed
4. Use meaningful commit messages

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/your-username/smart-workspace-manager/issues) page
2. Create a new issue with detailed information
3. Join our community discussions

## 🎉 Acknowledgments

-   Built with the amazing [TALL Stack](https://tallstack.dev/)
-   Inspired by modern project management tools
-   Thanks to the Laravel and Livewire communities

---

**Made with ❤️ using the TALL Stack**

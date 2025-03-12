# Chronohancer

A powerful time tracking application built with Laravel and Livewire that helps you manage your time, projects, and tasks efficiently.

## 📋 Overview

Chronohancer is a comprehensive time tracking solution designed to help individuals and teams monitor and manage their time effectively. The application allows users to create timers for different tasks, organize them by projects, tag them for better categorization, and generate detailed time logs and reports.

## 🚀 Features

- **Timer Management**: Create, start, and stop timers for tracking time spent on various tasks
- **Project Organization**: Group timers and time logs by projects
- **Tagging System**: Categorize timers and time logs with customizable tags
- **Time Logs**: Detailed records of time spent on tasks with descriptions
- **Dashboard**: Visual overview of time distribution across projects
- **Weekly View**: Analyze time spent during specific periods
- **User Authentication**: Secure multi-user system with personal workspaces
- **Responsive Design**: Works seamlessly on desktop and mobile devices

## 🏗️ System Architecture

Chronohancer follows the Laravel MVC architecture with Livewire components for reactive UI updates:

### Core Components

1. **Models**: Define the data structure and relationships
   - User: Authentication and user management
   - Project: Organize work into logical groups
   - Timer: Track time for specific tasks
   - TimeLog: Record completed time entries
   - Tag: Categorize timers and time logs

2. **Livewire Components**: Handle UI interactions and business logic
   - Dashboard: Overview and statistics
   - Timers: Timer creation and management
   - TimeLogs: Log entry and reporting
   - Projects: Project management
   - Settings: User preferences and configuration

3. **JavaScript Services**: Client-side functionality
   - TimerManager: Handles real-time timer updates in the browser

### Data Flow

1. User creates/starts a timer through the Timers component
2. The timer is stored in the database and starts running
3. TimerManager.js updates the timer display in real-time
4. When stopped, a TimeLog entry is created with the duration
5. Dashboard and reports aggregate this data for visualization

## 🛠️ Technologies

### Backend
- **PHP 8.2+**: Core programming language
- **Laravel 12**: PHP web application framework
- **Livewire 3**: Full-stack framework for dynamic interfaces
- **Volt**: Laravel Livewire template compiler
- **MySQL/PostgreSQL**: Database (configurable)

### Frontend
- **Livewire Flux**: UI component library
- **TailwindCSS 4**: Utility-first CSS framework
- **Alpine.js**: (via Livewire) Minimal JavaScript framework
- **Vite**: Next-generation frontend tooling

### Development Tools
- **Laravel Pint**: PHP code style fixer
- **Laravel Sail**: Docker development environment
- **Pest**: Testing framework

## 📁 Directory Structure

```
chronohancer/
├── app/                      # Application code
│   ├── Http/                 # HTTP layer (controllers, middleware)
│   ├── Livewire/             # Livewire components
│   │   ├── Actions/          # Reusable actions
│   │   ├── Auth/             # Authentication components
│   │   └── Settings/         # User settings components
│   ├── Models/               # Eloquent models
│   └── Providers/            # Service providers
├── bootstrap/                # Application bootstrap files
├── config/                   # Configuration files
├── database/                 # Database migrations and seeders
│   ├── factories/            # Model factories for testing
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Publicly accessible files
├── resources/                # Frontend resources
│   ├── css/                  # CSS files
│   ├── js/                   # JavaScript files
│   └── views/                # Blade templates
│       ├── components/       # Reusable view components
│       ├── layouts/          # Layout templates
│       └── livewire/         # Livewire component views
├── routes/                   # Route definitions
├── storage/                  # Application storage
└── tests/                    # Test files
```

## 🔧 Setup and Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL or PostgreSQL database

### Installation Steps

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/chronohancer.git
   cd chronohancer
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install JavaScript dependencies:
   ```bash
   npm install
   ```

4. Create environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=chronohancer
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. Run database migrations:
   ```bash
   php artisan migrate
   ```

8. Build frontend assets:
   ```bash
   npm run build
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

10. Visit `http://localhost:8000` in your browser

### Using Laravel Sail (Docker)

Alternatively, you can use Laravel Sail for a containerized development environment:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

## 🧪 Testing

Run the test suite with Pest:

```bash
php artisan test
```

Or with code coverage report:

```bash
php artisan test --coverage
```

## 📝 Usage Examples

### Creating a Timer

```php
// Create a new timer programmatically
$timer = Timer::create([
    'user_id' => auth()->id(),
    'project_id' => $project->id,
    'name' => 'Development Task',
    'description' => 'Working on new feature',
    'is_running' => true,
]);

// Create a time log entry
TimeLog::create([
    'timer_id' => $timer->id,
    'user_id' => auth()->id(),
    'project_id' => $project->id,
    'start_time' => now(),
    'description' => 'Initial development work',
]);
```

### Tagging System

```php
// Create or find a tag
$tag = Tag::findOrCreateForUser('priority', auth()->id(), '#ff5500');

// Attach tag to a timer
$timer->tags()->attach($tag->id);

// Attach multiple tags
$timer->tags()->attach([$tag1->id, $tag2->id]);
```

### Generating Reports

```php
// Get time logs for a specific period
$timeLogs = TimeLog::where('user_id', auth()->id())
    ->whereBetween('start_time', [
        $startDate,
        $endDate
    ])
    ->with(['project', 'tags'])
    ->get();

// Calculate total time by project
$projectTotals = $timeLogs->groupBy('project_id')
    ->map(function ($logs) {
        return $logs->sum('duration_minutes');
    });
```

## 🚧 Limitations and Future Enhancements

### Current Limitations

- No team collaboration features yet
- Limited reporting and export options
- No mobile app (web responsive only)
- No offline support

### Planned Enhancements

- Team workspaces with shared projects
- Advanced reporting with export to CSV/PDF
- API for third-party integrations
- Mobile applications (iOS/Android)
- Calendar integration
- Invoice generation
- Time estimation and comparison

## 👥 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add some amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use type hints and docblocks

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgements

- [Laravel](https://laravel.com/) - The PHP framework used
- [Livewire](https://livewire.laravel.com/) - Full-stack framework
- [TailwindCSS](https://tailwindcss.com/) - CSS framework
- [Flux](https://flux.laravel.com/) - UI component library
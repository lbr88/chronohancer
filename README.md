# Chronohancer

A powerful time tracking application built with Laravel and Livewire that helps you manage your time, projects, and tasks efficiently.

## 📋 Overview

Chronohancer is a comprehensive time tracking solution designed to help individuals and teams monitor and manage their time effectively. The application allows users to create timers for different tasks, organize them by projects, tag them for better categorization, and generate detailed time logs and reports.

## 🚀 Features

- **Timer Management**: Create, start, pause, and stop timers for tracking time spent on various tasks
- **Project Organization**: Group timers and time logs by projects with customizable colors
- **Tagging System**: Categorize timers and time logs with customizable tags
- **Time Logs**: Detailed records of time spent on tasks with descriptions
- **Dashboard**: Visual overview of time distribution across projects
- **Weekly View**: Analyze time spent during specific periods
- **User Authentication**: Secure multi-user system with personal workspaces
- **Time Format Preferences**: Customize how time is displayed (human-readable, HH:MM, or HH:MM:SS)
- **Responsive Design**: Works seamlessly on desktop and mobile devices

## 🏗️ System Architecture

Chronohancer follows the Laravel MVC architecture with Livewire components for reactive UI updates:

### Core Components

1. **Models**: Define the data structure and relationships
   - User: Authentication and user management with time format preferences
   - Project: Organize work into logical groups with custom colors and soft delete support
   - Timer: Track time for specific tasks with pause functionality
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

## 🐳 Docker Deployment

### Using the Docker Image

Chronohancer can be deployed using Docker. The project includes a Dockerfile in the `kubernetes` directory that builds a production-ready image.

```bash
# Build the Docker image locally
docker build -t chronohancer -f kubernetes/Dockerfile .

# Run the container
docker run -p 9000:9000 chronohancer
```

### Using Docker Compose

For local development and testing, you can use the included Docker Compose configuration:

```bash
# Start all services (app, nginx, mysql, redis)
docker-compose up -d

# View logs
docker-compose logs -f

# Stop all services
docker-compose down
```

The Docker Compose setup includes:
- The Laravel application using the Dockerfile
- Nginx web server
- MySQL database
- Redis for caching and sessions

All necessary environment variables are pre-configured in the docker-compose.yml file.

### GitHub Container Registry

This project is configured with GitHub Actions to automatically build and push Docker images to GitHub Container Registry (GHCR) when changes are pushed to the main branch.

#### Accessing the Docker Image

```bash
# Pull the latest image
docker pull ghcr.io/yourusername/chronohancer:latest

# Pull a specific version by commit SHA
docker pull ghcr.io/yourusername/chronohancer:sha-abc123
```

#### GitHub Actions Workflows

The project includes seven GitHub Actions workflows:

1. **Laravel Test** (`.github/workflows/laravel-test.yml`):
   - Runs on pushes to main and pull requests
   - Sets up PHP, MySQL, and dependencies
   - Runs the test suite to ensure code quality

2. **Code Quality** (`.github/workflows/code-quality.yml`):
   - Runs on pushes to main and pull requests
   - Uses Laravel Pint to check code style against PSR-12 standards
   - Automatically fixes code style issues in pull requests
   - Ensures consistent code formatting across the project

3. **Security Scan** (`.github/workflows/security-scan.yml`):
   - Runs on pushes to main, pull requests, and weekly schedule
   - Performs security checks using PHP Security Checker, Composer Audit, and NPM Audit
   - Uses OWASP Dependency-Check to identify vulnerabilities in dependencies
   - Generates and uploads security reports as artifacts
   - Helps identify and address security vulnerabilities early

4. **Docker Build and Push** (`.github/workflows/docker-build-push.yml`):
   - Runs on pushes to main and pull requests
   - Builds the Docker image using the Dockerfile in the kubernetes directory
   - Tags the image with 'latest' and the commit SHA
   - Pushes the image to GitHub Container Registry (only for pushes to main, not pull requests)

5. **Kubernetes Deploy** (`.github/workflows/kubernetes-deploy.yml`):
   - Runs after successful completion of the Docker Build and Push workflow
   - Only triggers on pushes to the main branch
   - Sets up kubectl and Helm
   - Deploys the application to a Kubernetes cluster using the Helm chart
   - Requires a Kubernetes configuration secret (KUBE_CONFIG) to be set in the repository settings

6. **Create Release** (`.github/workflows/create-release.yml`):
   - Triggers when a new version tag is pushed (e.g., v1.0.0)
   - Automatically creates a GitHub release with the tag name
   - Generates release notes based on commits since the previous release
   - Provides a consistent way to create releases

7. **Update Helm Chart Version** (`.github/workflows/update-helm-chart.yml`):
   - Triggers when a new release is published
   - Automatically updates the version in the Helm chart's Chart.yaml file
   - Commits and pushes the changes back to the repository
   - Ensures the Helm chart version stays in sync with application releases

#### Setting Up Repository Access

To use the GitHub Container Registry image in your deployment:

1. Ensure your GitHub account or organization has proper access to the package
2. Authenticate with GitHub Container Registry:
   ```bash
   echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin
   ```
3. Pull and deploy the image as needed

#### Setting Up GitHub Secrets

For the GitHub Actions workflows to function properly, you need to set up the following secrets in your repository settings:

1. **GITHUB_TOKEN**: Automatically provided by GitHub, used for authentication with GitHub Container Registry.

2. **KUBE_CONFIG**: Required for the Kubernetes deployment workflow. This should contain your Kubernetes cluster configuration in base64 encoded format. You can generate it with:
   ```bash
   cat ~/.kube/config | base64
   ```

To add these secrets:
1. Go to your GitHub repository
2. Navigate to Settings > Secrets and variables > Actions
3. Click "New repository secret"
4. Add the secrets with the appropriate names and values

#### Release Management

The project includes a convenient script for managing releases:

**make-release.sh**
- A bash script to simplify the release process
- When run without parameters, it shows the latest tag:
  ```bash
  ./make-release.sh
  # Output: Latest tag: v1.2.3
  ```
- When run with a version parameter, it creates and pushes a new tag:
  ```bash
  ./make-release.sh v1.3.0
  # Creates and pushes tag v1.3.0
  ```
- The script validates that the version follows the correct format (vX.Y.Z)
- After pushing a new tag, the GitHub Actions workflow automatically creates a release

This script works in conjunction with the Create Release and Update Helm Chart Version workflows to automate the release process.

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

### Creating a Project with Custom Color

```php
// Create a new project with a custom color
$project = Project::create([
    'user_id' => auth()->id(),
    'name' => 'Website Redesign',
    'description' => 'Redesigning the company website',
    'color' => '#4ade80', // Green color
]);
```

### Creating a Timer

```php
// Create a new timer programmatically
$timer = Timer::create([
    'user_id' => auth()->id(),
    'project_id' => $project->id,
    'name' => 'Development Task',
    'description' => 'Working on new feature',
    'is_running' => true,
    'is_paused' => false,
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

### Pausing a Timer

```php
// Pause a running timer
$timer->update([
    'is_running' => true,
    'is_paused' => true,
]);
```

### Setting User Time Format Preference

```php
// Update user's time format preference
$user->update([
    'time_format' => 'human', // Options: 'human', 'hm', 'hms'
]);
```

### Soft Deleting a Project

```php
// Soft delete a project (can be restored later)
$project->delete(); // Uses soft delete

// Restore a soft-deleted project
$project = Project::withTrashed()->find($projectId);
$project->restore();
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

## 🆕 Recent Updates

- **Project Soft Deletes**: Projects can now be soft-deleted and restored later, preserving all associated data
- **Timer Pause Functionality**: Timers can now be paused and resumed, providing more flexibility in time tracking
- **Time Format Preferences**: Users can choose between different time display formats (human-readable, HH:MM, or HH:MM:SS)
- **Project Colors**: Projects can now have custom colors for better visual organization and identification

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
- Project archiving and batch operations
- Advanced timer controls (scheduled timers, reminders)
- Custom dashboard widgets and layouts

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
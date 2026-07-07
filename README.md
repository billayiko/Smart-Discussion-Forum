Academic Pulse Forum - Development Environment Setup Guide
1. System Requirements
Minimum Hardware Requirements
CPU: Intel Core i5 or equivalent (4+ cores recommended)

RAM: 16GB (8GB minimum)

Storage: 50GB free SSD space

Internet: Stable broadband connection

Operating System Support
Windows: Windows 10/11 (64-bit)

macOS: macOS Monterey (12) or later

Linux: Ubuntu 22.04 LTS or equivalent

2. Core Technologies Stack
Component	Technology	Version
Backend Framework	Laravel	11.x
PHP	PHP	8.2+
Database	MySQL/PostgreSQL	8.0+/15+
Cache/Queue	Redis	7.0+
WebSocket	Laravel Reverb	1.0+
Queue Worker	Laravel Horizon	5.0+
Desktop Client	Java (OpenJFX)	17+
ML Service	Python	3.10+
Web Frontend	Livewire/Vue.js	3.0+
3. Development Environment Setup
3.1. Install Essential Tools
Windows
powershell
# Install Chocolatey (Package Manager)
Set-ExecutionPolicy Bypass -Scope Process -Force
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))

# Install required packages
choco install git php composer mysql redis python java jdk openjfx nodejs-lts -y
macOS
bash
# Install Homebrew
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install required packages
brew install git php composer mysql redis python java openjdk openjfx node
Linux (Ubuntu/Debian)
bash
# Update package list
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y git php8.2 php8.2-{mysql,pdo,mbstring,xml,curl,zip,redis,intl} \
    composer mysql-server redis-server default-jdk openjfx python3 python3-pip nodejs npm
3.2. PHP Extensions Verification
bash
# Verify required PHP extensions
php -m | grep -E "pdo|mbstring|xml|curl|zip|redis|intl"

# If missing, install with (Ubuntu):
sudo apt install php8.2-{extension_name}
3.3. Java SDK Configuration
bash
# Verify Java installation
java -version
javac -version

# Set JAVA_HOME (add to .bashrc/.zshrc/.bash_profile)
export JAVA_HOME=$(dirname $(dirname $(readlink -f $(which java))))
4. Project Setup
4.1. Clone Repository
bash
# Clone the project repository
git clone https://github.com/billayiko/Smart-Discussion-Forum.git
cd Smart-Discussion-Forum
4.2. Backend (Laravel) Setup
bash
# Install PHP dependencies
composer install

# Copy environment configuration
cp .env.example .env

# Generate application key
php artisan key:generate

# Install Node.js dependencies (for frontend assets)
npm install
npm run build

# Configure database (edit .env file)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=academic_pulse_forum
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations with seeders
php artisan migrate --seed

# Create symbolic link for storage
php artisan storage:link
4.3. Environment Configuration (.env)
ini
APP_NAME="Academic Pulse Forum"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=academic_pulse_forum
DB_USERNAME=root
DB_PASSWORD=

# Redis (for cache, queue, and WebSocket)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ML Service
ML_SERVICE_URL=http://localhost:5000

# WebSocket (Reverb)
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
5. Database Setup
5.1. Create Database
sql
CREATE DATABASE academic_pulse_forum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
5.2. Run Migrations
bash
php artisan migrate:fresh --seed
6. ML Microservice Setup
6.1. Setup Python Environment
bash
cd ml-service

# Create virtual environment
python -m venv venv

# Activate virtual environment
# Windows:
venv\Scripts\activate
# macOS/Linux:
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt
6.2. ML Service Configuration
bash
# requirements.txt
flask==2.3.3
scikit-learn==1.3.2
pandas==2.1.1
numpy==1.24.3
joblib==1.3.2
python-dotenv==1.0.0
6.3. Start ML Service
bash
# In ml-service directory
python app.py
# Runs on http://localhost:5000
7. Java Desktop Client Setup
7.1. Project Configuration (pom.xml)
xml
<?xml version="1.0" encoding="UTF-8"?>
<project xmlns="http://maven.apache.org/POM/4.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://maven.apache.org/POM/4.0.0 
         http://maven.apache.org/xsd/maven-4.0.0.xsd">
    <modelVersion>4.0.0</modelVersion>

    <groupId>com.academicpulse</groupId>
    <artifactId>forum-client</artifactId>
    <version>1.0.0</version>

    <properties>
        <maven.compiler.source>17</maven.compiler.source>
        <maven.compiler.target>17</maven.compiler.target>
        <project.build.sourceEncoding>UTF-8</project.build.sourceEncoding>
        <javafx.version>17.0.2</javafx.version>
    </properties>

    <dependencies>
        <dependency>
            <groupId>org.openjfx</groupId>
            <artifactId>javafx-controls</artifactId>
            <version>${javafx.version}</version>
        </dependency>
        <dependency>
            <groupId>org.openjfx</groupId>
            <artifactId>javafx-fxml</artifactId>
            <version>${javafx.version}</version>
        </dependency>
        <dependency>
            <groupId>org.xerial</groupId>
            <artifactId>sqlite-jdbc</artifactId>
            <version>3.42.0.0</version>
        </dependency>
        <dependency>
            <groupId>com.squareup.okhttp3</groupId>
            <artifactId>okhttp</artifactId>
            <version>4.11.0</version>
        </dependency>
        <dependency>
            <groupId>com.fasterxml.jackson.core</groupId>
            <artifactId>jackson-databind</artifactId>
            <version>2.15.2</version>
        </dependency>
    </dependencies>

    <build>
        <plugins>
            <plugin>
                <groupId>org.openjfx</groupId>
                <artifactId>javafx-maven-plugin</artifactId>
                <version>0.0.8</version>
                <configuration>
                    <mainClass>com.academicpulse.ForumApp</mainClass>
                </configuration>
            </plugin>
        </plugins>
    </build>
</project>
7.2. Build Desktop Client
bash
cd desktop-client
mvn clean install
mvn javafx:run
8. Running the Application
8.1. Start All Services
Terminal 1: Laravel Backend
bash
php artisan serve
# Runs on http://localhost:8000
Terminal 2: WebSocket Server
bash
php artisan reverb:start
# Runs WebSocket on port 8080
Terminal 3: Queue Worker
bash
php artisan horizon
# Processes background jobs
Terminal 4: ML Service
bash
cd ml-service
source venv/bin/activate  # macOS/Linux
# venv\Scripts\activate   # Windows
python app.py
# Runs on http://localhost:5000
Terminal 5: Frontend Development (optional)
bash
npm run dev
# Vite development server
9. Development Tools & Extensions
VS Code Extensions
Laravel Extension Pack (amiralizadeh9480.laravel-extension-pack)

PHP Intelephense (bmewburn.vscode-intelephense-client)

Vue Volar (vue.volar)

EditorConfig (editorconfig.editorconfig)

Prettier (esbenp.prettier-vscode)

MySQL (cweijan.vscode-mysql-client2)

Redis (cweijan.vscode-redis-client)

PHPStorm Plugins
Laravel Idea (laravel-idea.laravel-idea)

Laravel Query (laravel-query.laravel-query)

Database Tools
MySQL Workbench / DBeaver / TablePlus

API Testing
Postman / Insomnia / Bruno

10. Troubleshooting Common Issues
10.1. PHP Extension Missing
bash
# Check which extensions are missing
php artisan optimize
# Install missing extensions based on error messages
10.2. Permission Issues
bash
# Laravel storage permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache

# Linux: Add user to www-data group
sudo usermod -a -G www-data $USER
10.3. Redis Connection Issues
bash
# Check Redis status
redis-cli ping
# Should return: PONG

# Start Redis if not running
sudo service redis-server start  # Linux
brew services start redis        # macOS
10.4. Java FX Issues
bash
# Export JavaFX path if needed
export PATH_TO_FX=/path/to/javafx-sdk/lib
10.5. Database Connection Refused
bash
# Check MySQL status
sudo service mysql status
sudo service mysql start

# Reset root password
sudo mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
11. Testing Commands
Backend Tests
bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage (requires XDebug)
php artisan test --coverage
Frontend Tests
bash
npm run test
npm run test:unit
npm run test:e2e
12. Deployment Commands
Build for Production
bash
# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Build frontend assets
npm run build

# Build desktop client
cd desktop-client
mvn clean package
13. Useful Aliases (Add to .bashrc/.zshrc)
bash
# Laravel shortcuts
alias artisan="php artisan"
alias tinker="php artisan tinker"
alias horizon="php artisan horizon"

# Start all services
alias pulse-start="php artisan serve & php artisan reverb:start & php artisan horizon"
alias pulse-stop="pkill -f 'artisan (serve|reverb|horizon)'"

# Database
alias pulse-migrate="php artisan migrate:fresh --seed"
14. VS Code Launch Configuration (.vscode/launch.json)
json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for XDebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        },
        {
            "type": "java",
            "name": "Debug Desktop Client",
            "request": "launch",
            "mainClass": "com.academicpulse.ForumApp",
            "projectName": "forum-client"
        }
    ]
}
15. Database Migration Reference (Quick Setup)
bash
# Create a new migration
php artisan make:migration create_{table_name}_table

# Create model with migration
php artisan make:model {ModelName} -m

# Rollback and migrate
php artisan migrate:fresh --seed
16. Environment File Example (.env.local)
ini
APP_NAME="Academic Pulse Forum [DEV]"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=daily
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=academic_pulse_forum_dev
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@academicpulse.local"
MAIL_FROM_NAME="${APP_NAME}"

REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

ML_SERVICE_URL=http://localhost:5000
17. Docker Setup (Alternative)
docker-compose.yml
yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: academic-pulse-forum
    ports:
      - "8000:8000"
    volumes:
      - ./:/var/www/html
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
    depends_on:
      - mysql
      - redis
      - ml-service

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: academic_pulse_forum
      MYSQL_ROOT_PASSWORD: root
    ports:
      - "3306:3306"
    volumes:
      - mysql-data:/var/lib/mysql

  redis:
    image: redis:7.0-alpine
    ports:
      - "6379:6379"

  ml-service:
    build:
      context: ./ml-service
      dockerfile: Dockerfile
    ports:
      - "5000:5000"
    volumes:
      - ./ml-service:/app

volumes:
  mysql-data:
Dockerfile (Laravel)
dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    libzip-dev libpq-dev redis-tools

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-interaction

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000
18. Quick Start Checklist
Git cloned repository

PHP 8.2+ installed with required extensions

Composer installed

MySQL/PostgreSQL installed and running

Redis installed and running

Node.js and npm installed

Java 17+ installed with OpenJFX

Python 3.10+ installed

Environment file (.env) configured

Database created and migrations run

ML service installed and running

All services started

Application accessible at http://localhost:8000

19. Support & Resources
Laravel Documentation: https://laravel.com/docs

JavaFX Documentation: https://openjfx.io/

Reverb Documentation: https://laravel.com/docs/11.x/reverb

Project Repository: https://github.com/billayiko/Smart-Discussion-Forum

# Academic Pulse Forum

A Laravel-based discussion forum for a school setting, with separate
dashboards for **students**, **lecturers**, and **admins**, plus a native
**Java desktop client** that talks to the same backend over a REST API.

## Features

### Discussion Forum
- Topic-scoped discussions: students see bubbles for the topics they've
  subscribed to, lecturers see the topics they teach, admins see everything
  in a flat, searchable table.
- Each topic has its own forum page (threads list + "ask a question"
  composer); opening a thread shows the full conversation with replies.
- Per-reply visibility control ("exclude" specific group members from
  seeing a given reply), reply-level topic labels, view counts, and
  last-activity tracking.
- Reporting/complaints on questions, reviewed by admins.

### Dashboards
- **Student**: enrolled topics, upcoming quizzes, quiz-load-by-subject
  chart, discussion forum activity, announcements.
- **Lecturer**: quiz management, submissions, quiz-status breakdown chart,
  student discussions, class schedule.
- **Admin**: platform-wide quiz management table, topic/lecturer
  assignment, member management (warnings/blacklisting), complaint review,
  analytics.

### Quizzes
Create, schedule, and import (CSV) quizzes; link a quiz to a course topic so
its countdown appears on that topic's discussion page; mark quizzes as
proctored.

### Messaging
Direct messages and group conversations between students, lecturers, and
admins, with membership management on group threads.

### Moderation
Automatic inactivity warnings and blacklisting (`members:check-inactivity`,
scheduled daily), configurable via `ModerationSetting`.

### Teams
Livewire/Jetstream-style personal team workspaces with invitations
(unrelated to the course-topic discussion groups above).

### Desktop client (`desktop/`)
A JavaFX app (Java 25, Maven) that logs in and browses/asks/replies in the
Discussion Forum against the same data, via a token-authenticated REST API.
See the [Desktop client](#desktop-client-1) section below.

## Tech stack

| Layer | Stack |
|---|---|
| Backend | Laravel 13, PHP 8.3+ |
| Auth | Laravel Fortify (web sessions, 2FA, passkeys) + Laravel Sanctum (API tokens) |
| Frontend | Livewire 4 (Flux UI components), Blade, Tailwind CSS 4, Vite |
| Database | SQLite by default (any Laravel-supported DB works) |
| Desktop client | Java 25, JavaFX 21, Maven, Jackson (JSON), `java.net.http` |
| Testing | PHPUnit (Pest-style feature/unit tests), Larastan (PHPStan), Pint |

## Project structure

```
app/
  Http/Controllers/          Web controllers (Blade views)
  Http/Controllers/Admin/    Admin-only controllers
  Http/Controllers/Lecturer/ Lecturer-only controllers
  Http/Controllers/Api/      JSON API controllers (used by the desktop client)
  Models/                    Eloquent models
  Console/Commands/          Scheduled artisan commands
resources/views/
  pages/dashboards/          Role-specific dashboard & feature views
  components/layouts/        Shared Blade layout (navy/gold theme, dashboard shell)
routes/
  web.php                    Session-authenticated web routes
  api.php                    Sanctum-authenticated JSON API routes
  console.php                Scheduled task definitions
database/
  migrations/                Schema
  seeders/                   Sample data
desktop/
  src/main/java/...          JavaFX client source
  pom.xml                    Maven build config
```

## Getting started (web app)

Requirements: PHP 8.3+, Composer, Node.js, and a database (SQLite works
out of the box).

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if using the default SQLite setup
php artisan migrate
npm install
npm run build      # or `npm run dev` while developing
php artisan serve
```

Or, in one shot: `composer run setup`.

Visit `http://127.0.0.1:8000`. The registration form asks you to pick a role
(`student`, `lecturer`, or `admin`) to sign up with directly, or seed sample
data with `php artisan db:seed`. (Accounts created via the API's
`/api/register` instead default to a roleless `member` — promote them with
`php artisan tinker` if you go that route.)

### Running tests

```bash
composer test        # config:clear + lint:check + types:check + phpunit
php artisan test      # phpunit only
composer lint         # Pint, auto-fix
composer types:check  # Larastan/PHPStan
```

### Scheduled tasks

`php artisan schedule:work` (or a real cron entry calling
`schedule:run` every minute) runs:
- Daily cleanup of expired team invitations.
- `members:check-inactivity` — warns, then blacklists, inactive members
  per the thresholds in `ModerationSetting`.

## REST API (for the desktop client)

Base path: `/api`. Authentication is via Laravel Sanctum bearer tokens —
call `/api/login`, then send `Authorization: Bearer <token>` on subsequent
requests.

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/register` | Create an account (role defaults to `member`) |
| POST | `/api/login` | Authenticate, returns a bearer token + user |
| POST | `/api/logout` | Revoke the current token *(auth required)* |
| GET | `/api/me` | Current authenticated user *(auth required)* |
| GET | `/api/topics` | Topics relevant to the current user — subscribed (student), taught (lecturer), or all (admin) *(auth required)* |
| GET | `/api/topics/{topic}/questions` | A topic's threads, with reply counts *(auth required)* |
| GET | `/api/questions/{question}` | A single thread with its replies (excluded replies filtered per-viewer) *(auth required)* |
| POST | `/api/questions` | Ask a new question (`title`, `body`, `course_topic_id`) *(auth required)* |
| POST | `/api/questions/{question}/answers` | Post a reply (`body`) *(auth required)* |

## Desktop client

`desktop/` is a Maven/JavaFX project. It's independent of the web app's
asset pipeline — it just needs the Laravel API reachable over HTTP.

```bash
cd desktop
mvn clean javafx:run
```

By default it points at `http://127.0.0.1:8000/api` (i.e. `php artisan serve`'s
default address) — see `ApiClient`'s constructor in
`src/main/java/com/academicpulse/desktop/api/ApiClient.java` to change it.
Log in with any existing web-app user's credentials (student or lecturer —
admins have no discussion-forum bubbles to browse).

**Note:** on a fresh machine, the JavaFX runtime must be resolvable via
Maven the first time (`javafx-maven-plugin` downloads the platform-specific
jars). If the app window renders solid black, that's typically GPU
acceleration failing under a remote/virtualized display — rerun with
`-Dprism.order=sw` to force software rendering.

## Roles

The `users.role` column drives all authorization (`role:...` route
middleware and `EnsureUserHasRole`): `student`, `lecturer`, `admin`, and the
default `member` (an unassigned/just-registered account with no dashboard
access yet).

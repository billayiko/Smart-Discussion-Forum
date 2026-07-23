# Academic Pulse — Desktop Client

A JavaFX desktop client for the Academic Pulse Forum. It doesn't touch the
database directly — it talks to the Laravel app over a token-authenticated
REST API (`routes/api.php`), so the web app and desktop app share one
source of truth.

Currently implemented: login, the Discussion Forum (browse your
subscribed/taught topics, view a topic's threads, ask a question, open a
thread and reply), and direct messaging (list conversations, start a new
1:1 conversation, view/send messages).

## Architecture

```
src/main/java/com/academicpulse/desktop/
  Main.java                     Application entry point — shows the login screen
  Router.java                   Holds the primary Stage, the shared ApiClient,
                                 and the logged-in User; loads FXML + applies app.css
  api/
    ApiClient.java              HTTP calls (java.net.http.HttpClient) + JSON
                                 (Jackson, snake_case mapping) + bearer-token auth
    ApiException.java           Thrown for non-2xx API responses
  model/
    User.java, Topic.java,      Plain DTOs matching the API's JSON shapes
    Question.java, Answer.java,
    Conversation.java,
    ChatMessage.java
  controllers/
    LoginController             login.fxml
    TopicsController            topics.fxml — the subscribed/taught topic list
    TopicThreadsController      topic-threads.fxml — one topic's threads + "ask" form
    ThreadDetailController      thread-detail.fxml — one thread + replies
    MessagesController          messages.fxml — the user's direct conversations
    ConversationDetailController conversation-detail.fxml — one conversation + replies
    NewMessageController        new-message.fxml — pick a contact to start a conversation
    SidebarController           sidebar.fxml — shared left nav (see below)

src/main/resources/
  *.fxml                        One per screen, plus sidebar.fxml
  app.css                       Shared stylesheet (navy/gold sidebar theme)
```

**Navigation** is intentionally simple: `Router.navigate(fxmlPath, title)`
loads a new FXML into the single primary `Stage` and returns its
controller, so the caller can pass data to the next screen (e.g.
`TopicsController` hands the selected `Topic` to `TopicThreadsController`
via a `setTopic(...)` method) — there's no separate navigation framework.

**The left sidebar** (`sidebar.fxml` / `SidebarController`) is shared across
every authenticated screen via `<fx:include source="sidebar.fxml"/>` rather
than being duplicated three times. It shows the brand, a "Discussion Forum"
nav item, the current user's name/role, and Logout — styled navy blue to
match the web app's own dashboard sidebar. Logout lives only here now
(screens no longer have their own logout button).

**Auth**: `LoginController` calls `ApiClient.login(...)`, which POSTs to
`/api/login` and stores the returned Sanctum bearer token in memory
(`ApiClient`); every subsequent call sends `Authorization: Bearer <token>`.
`Router.setCurrentUser(...)` stores the logged-in `User` so any screen
(currently just the sidebar) can read who's logged in without a network
round-trip.

## Building and running

**Easiest**: double-click `desktop/run.bat`. It checks whether the Laravel
dev server is already listening on port 8000 and starts one (in its own
minimized `Academic Pulse Laravel Server` window) if not, then compiles the
client against the JavaFX/Jackson jars already cached in your local Maven
repository and launches it — no Maven install, no typing commands.

**With Maven** (if installed):
```bash
cd desktop
mvn clean javafx:run
```
`javafx-maven-plugin` resolves the platform-specific JavaFX jars and sets
up the module path for you.

**Without Maven, by hand** — compile and run directly against a JDK with
the JavaFX/Jackson jars on the classpath (see the root
[README](../README.md#desktop-client) for the exact PowerShell commands —
this is what `run.bat` automates).

Either way, the client needs the Laravel app reachable at
`http://127.0.0.1:8000/api` by default; change the base URL in
`ApiClient`'s constructor if your server runs elsewhere.

**If the window renders solid black**: that's GPU acceleration failing
under a remote/virtualized display, not a bug in the app. Add
`-Dprism.order=sw` to the `java` command to force JavaFX's software
rendering pipeline.

## Development log

- **Initial scaffold**: bare JavaFX skeleton — `Main.java` loading an empty
  `main.fxml`, with an (unused) `sqlite-jdbc` dependency suggesting direct
  database access had been considered.
- **REST API client + Discussion Forum**: added `Api\TopicController` and
  `Api\QuestionController` on the Laravel side (role-scoped topics, topic
  threads, single-thread view with reply exclusion, ask/reply endpoints)
  plus `logout`/`me` on `AuthController`. Replaced the placeholder FXML
  with four real screens (login, topics, topic-threads, thread-detail)
  backed by `ApiClient`/`Router`. Dropped `sqlite-jdbc` (no longer
  relevant), added `javafx-fxml` (the app needed it but the dependency was
  missing) and `jackson-databind`.
- **Left navigation sidebar**: added the shared navy-blue `sidebar.fxml`
  described above, and centralized Logout there instead of duplicating a
  logout button/handler on every screen.
- **Topic access enforcement + direct messages**: closed a gap where
  `Api\TopicController::questions()` and `Api\QuestionController::store()`
  did no authorization check at all — any authenticated user could read or
  post into any topic by ID regardless of subscription. Both now use a new
  `App\Concerns\ChecksTopicAccess` trait (students must be subscribed,
  lecturers must be assigned, admins unrestricted) and return 403
  otherwise. `Api\TopicController::index()` was already correctly scoped
  and needed no change. Also added a new `Api\MessageController` (list
  conversations, view one, send a message, start a new 1:1 conversation,
  list contacts) reusing the existing `Conversation`/`Message` models as-is
  — the desktop client only supports direct messages, not the web app's
  group chats. Added `messages.fxml`, `conversation-detail.fxml`, and
  `new-message.fxml` plus their controllers, and a "Messages" button in
  the sidebar.

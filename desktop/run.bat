@echo off
setlocal

rem Double-click this file to compile and launch the Academic Pulse desktop
rem client. It uses the JavaFX/Jackson jars already cached in your local
rem Maven repository, so no Maven install is required. It also makes sure
rem the Laravel dev server is running first, starting it in its own
rem minimized window if it isn't.

set "JDK=%USERPROFILE%\.jdk\jdk-25.0.2\bin"
set "M2=%USERPROFILE%\.m2\repository"
set "FX=%M2%\org\openjfx"
set "JACKSON=%M2%\com\fasterxml\jackson"

set "CP=%JACKSON%\core\jackson-databind\2.17.2\jackson-databind-2.17.2.jar;%JACKSON%\core\jackson-core\2.17.2\jackson-core-2.17.2.jar;%JACKSON%\core\jackson-annotations\2.17.2\jackson-annotations-2.17.2.jar;%JACKSON%\datatype\jackson-datatype-jsr310\2.17.2\jackson-datatype-jsr310-2.17.2.jar"

set "MODPATH=%FX%\javafx-base\21.0.2\javafx-base-21.0.2-win.jar;%FX%\javafx-graphics\21.0.2\javafx-graphics-21.0.2-win.jar;%FX%\javafx-controls\21.0.2\javafx-controls-21.0.2-win.jar;%FX%\javafx-fxml\21.0.2\javafx-fxml-21.0.2-win.jar"

cd /d "%~dp0"

set "LARAVEL_DIR=%~dp0.."

echo Checking Laravel dev server...
netstat -ano | findstr ":8000" | findstr "LISTENING" >nul
if errorlevel 1 (
    echo Starting Laravel dev server in a separate window...
    start "Academic Pulse Laravel Server" /min cmd /c "cd /d "%LARAVEL_DIR%" && php artisan serve"
    timeout /t 3 /nobreak >nul
) else (
    echo Laravel dev server already running.
)

if not exist target\classes mkdir target\classes

echo Compiling...
dir /s /b src\main\java\*.java > "%TEMP%\academic-pulse-sources.txt"
"%JDK%\javac.exe" -d target\classes --module-path "%MODPATH%" --add-modules javafx.controls,javafx.fxml -cp "%CP%" @"%TEMP%\academic-pulse-sources.txt"
if errorlevel 1 (
    echo.
    echo Build failed - see errors above.
    pause
    exit /b 1
)
del "%TEMP%\academic-pulse-sources.txt"

copy /Y src\main\resources\*.fxml target\classes\ >nul
copy /Y src\main\resources\*.css target\classes\ >nul

echo Starting Academic Pulse desktop app...
"%JDK%\java.exe" -Dprism.order=sw --module-path "%MODPATH%" --add-modules javafx.controls,javafx.fxml -cp "target\classes;%CP%" com.academicpulse.desktop.Main

if errorlevel 1 (
    echo.
    echo The app exited with an error - see above.
    pause
)

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Pulse - Loading Dashboard...</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #0a1628;
            color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .spinner {
            color: #c9a84c;
            font-size: 3rem;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        p { margin-top: 15px; font-size: 0.9rem; color: #a8893a; font-weight: 600; letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <i class="fas fa-spinner spinner"></i>
    <p>Loading your Academic Pulse Workspace...</p>

    <script>
        // Check if the user is authenticated via local sessionStorage
        const token = sessionStorage.getItem('academic_pulse_token');
        const userProfile = JSON.parse(sessionStorage.getItem('user_profile'));

        if (!token || !userProfile) {
            // Redirect back if unauthenticated
            window.location.href = "{{ url('/login') }}";
        } else {
            // Dynamic redirection based on user database 'role' mapping
            const role = userProfile.role ? userProfile.role.toLowerCase() : 'student';
            
            if (role === 'admin') {
                window.location.href = "{{ url('/dashboard/admin') }}";
            } else if (role === 'lecturer') {
                window.location.href = "{{ url('/dashboard/lecturer') }}";
            } else {
                window.location.href = "{{ url('/dashboard/student') }}";
            }
        }
    </script>
</body>
</html>
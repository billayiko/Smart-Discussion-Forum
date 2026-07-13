<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <div class="mx-auto max-w-6xl px-6 py-10">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Quiz Management</h1>
                    <p class="mt-1 text-sm text-slate-500">Manage quizzes for your lectures and import content quickly.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('quizzes.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Create quiz</a>
                    <form action="{{ route('quizzes.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="file" name="file" accept=".csv,.xlsx,.xls" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Import</button>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="mt-6 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm text-slate-500">Active quizzes</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $stats['active_count'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm text-slate-500">Published this week</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $stats['published_this_week'] }}</p>
                </div>
            </div>

            <div class="mt-8 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Title</th>
                            <th class="px-4 py-3 text-left font-semibold">Subject</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Questions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($quizzes as $quiz)
                            <tr>
                                <td class="px-4 py-3">{{ $quiz->title }}</td>
                                <td class="px-4 py-3">{{ $quiz->subject }}</td>
                                <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $quiz->status) }}</td>
                                <td class="px-4 py-3">{{ $quiz->total_questions }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">No quizzes yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

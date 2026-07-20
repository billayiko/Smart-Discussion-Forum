<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <div class="mx-auto max-w-3xl px-6 py-10">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-semibold">Create a new quiz</h1>
            <p class="mt-1 text-sm text-slate-500">Add the quiz details below and publish it to your students.</p>

            <form action="{{ route('quizzes.store') }}" method="POST" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2" required>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Total Questions</label>
                        <input type="number" name="total_questions" value="{{ old('total_questions', 10) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2" min="1" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2" min="1" required>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Scheduled At</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Status</label>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                            <option value="draft">Draft</option>
                            <option value="planned">Planned</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="due_soon">Due Soon</option>
                            <option value="active">Active</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Course Topic</label>
                        <select name="course_topic_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                            <option value="">None</option>
                            @foreach ($topics as $topic)
                                <option value="{{ $topic->id }}" @selected(old('course_topic_id') == $topic->id)>{{ $topic->title }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Linking a topic shows this quiz's countdown on that topic's discussion threads.</p>
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-sm font-medium">
                            <input type="checkbox" name="proctored" value="1" @checked(old('proctored'))>
                            Proctored
                        </label>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save quiz</button>
                    <a href="{{ route('quizzes.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

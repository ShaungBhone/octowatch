@vite('resources/css/app.css')

<div class="space-y-4">
    <!-- Issue Details -->
    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
        <h3 class="font-semibold text-lg">
            {{ $record->title }}
        </h3>
        <!-- Opened By Section -->
        <div class="flex items-center space-x-2 mt-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Opened by</span>
            <div class="flex items-center space-x-2">
                @if($record->author_avatar_url)
                <img
                    src="{{ $record->author_avatar_url }}"
                    alt="{{ $record->author_login }}"
                    class="w-5 h-5 rounded-full">
                @else
                <div class="w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                        {{ substr($record->author_login ?? 'U', 0, 1) }}
                    </span>
                </div>
                @endif
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                    {{ $record->author_login }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{
                        \Carbon\Carbon::parse($record->created_at_github)->diffForHumans()
                    }}
                </span>
            </div>
        </div>
        @if($record->body)
        <div class="text-sm text-gray-700 dark:text-gray-300 mt-2 p-3 bg-white dark:bg-gray-800 rounded border">
            {!! $record->body !!}
        </div>
        @endif
    </div>

    <!-- Comments Section -->
    <div class="space-y-3 flex flex-col">
        <div class="flex justify-between items-center w-full">
            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                Comments ({{ $comments->count() }})
            </h4>
        </div>

        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($comments as $comment)
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start space-x-3">
                    @if($comment->author_avatar_url)
                    <img src="{{ $comment->author_avatar_url }}" alt="{{ $comment->author_login }}" class="w-8 h-8 rounded-full">
                    @else
                    <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ substr($comment->author_login ?? 'U', 0, 1) }}
                        </span>
                    </div>
                    @endif
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="font-medium text-sm text-gray-900 dark:text-gray-100">
                                {{ $comment->author_login ?? 'Unknown' }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $comment->created_at_github ? $comment->created_at_github->diffForHumans() : $comment->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 prose prose-sm max-w-none">
                            {!! nl2br(e($comment->body)) !!}
                        </div>
                        @if($comment->html_url)
                        <a href="{{ $comment->html_url }}" target="_blank" class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mt-2">
                            View on GitHub
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
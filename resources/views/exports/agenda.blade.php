<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>{{ $meeting->name }} 次第</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; color: #1f2937; }
        h1 { font-size: 1.25rem; }
        dl { margin-bottom: 1.5rem; }
        dt { color: #6b7280; font-size: 0.75rem; }
        dd { margin: 0 0 0.5rem; }
        ol { padding-left: 1.5rem; }
        li { margin-bottom: 0.75rem; }
        a { color: #4f46e5; }
    </style>
</head>
<body>
    <h1>{{ $meeting->name }}</h1>
    <dl>
        <dt>開催日時</dt>
        <dd>{{ $meeting->held_at?->format('Y-m-d H:i') ?? '-' }}</dd>
        <dt>開催場所</dt>
        <dd>{{ $meeting->location ?? '-' }}</dd>
    </dl>
    <h2>次第</h2>
    <ol>
        @forelse ($meeting->topLevelAgendaItems as $item)
            <li>
                {{ $item->title }}
                @if ($item->assigneeLabel())
                    (担当: {{ $item->assigneeLabel() }})
                @endif
                @if ($item->site)
                    <br><a href="sites/{{ $item->site->uuid }}/{{ $item->site->index_path }}">議案を見る</a>
                @endif

                @if ($item->children->isNotEmpty())
                    <ol>
                        @foreach ($item->children as $child)
                            <li>
                                {{ $child->title }}
                                @if ($child->assigneeLabel())
                                    (担当: {{ $child->assigneeLabel() }})
                                @endif
                                @if ($child->site)
                                    <br><a href="sites/{{ $child->site->uuid }}/{{ $child->site->index_path }}">議案を見る</a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </li>
        @empty
            <li>次第は登録されていません。</li>
        @endforelse
    </ol>
</body>
</html>

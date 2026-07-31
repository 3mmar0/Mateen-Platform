<!doctype html><html dir="rtl" lang="ar"><meta charset="utf-8"><body>
<h1>تقرير الطلاب</h1>
@foreach($students as $student)<p>{{ $student->name }} — {{ $student->email }} — {{ $student->role->value }}</p>@endforeach
</body></html>

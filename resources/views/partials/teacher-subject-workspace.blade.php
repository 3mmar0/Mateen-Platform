{{-- Shared hook for teacher subject workspaces. Individual pages remain full converted Blade documents for live parity. --}}
@isset($subjectSlug)
  <div data-teacher-subject="{{ $subjectSlug }}" hidden></div>
@endisset

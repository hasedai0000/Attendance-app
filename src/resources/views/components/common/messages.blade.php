@if (session('message'))
  <div class="message message-success">
    {{ session('message') }}
  </div>
@endif

@if (session('error'))
  <div class="message message-error">
    {{ session('error') }}
  </div>
@endif

@if (session('success'))
  <div class="message message-success">
    {{ session('success') }}
  </div>
@endif

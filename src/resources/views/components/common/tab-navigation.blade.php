@props(['tabs' => []])

<div class="tab-navigation">
  @foreach ($tabs as $tab)
    <button class="tab-button {{ $tab['active'] ? 'active' : '' }}" onclick="showTab('{{ $tab['id'] }}')">
      {{ $tab['label'] }}
    </button>
  @endforeach
</div>

<script>
  function showTab(tabName) {
    // すべてのタブボタンからactiveクラスを削除
    document.querySelectorAll('.tab-button').forEach(button => {
      button.classList.remove('active');
    });

    // すべてのタブコンテンツを非表示
    document.querySelectorAll('.tab-content').forEach(content => {
      content.classList.remove('active');
    });

    // クリックされたタブボタンにactiveクラスを追加
    event.target.classList.add('active');

    // 対応するタブコンテンツを表示
    document.getElementById(tabName + '-tab').classList.add('active');
  }
</script>

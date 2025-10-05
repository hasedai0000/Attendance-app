@props(['title', 'activePage' => null])

@extends('layouts.app')

@section('title', $title)

@section('content')
  <div class="admin-container">
    <!-- ヘッダー -->
    <x-admin.header :active-page="$activePage" />

    <!-- メインコンテンツ -->
    <div class="admin-content">
      <!-- タイトル -->
      <x-common.page-title :title="$title" />

      <!-- メッセージ -->
      <x-common.messages />

      {{ $slot }}
    </div>
  </div>
@endsection

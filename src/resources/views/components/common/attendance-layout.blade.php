@props(['title', 'activePage' => null])

@extends('layouts.app')

@section('title', $title)

@section('css')
  <link rel="stylesheet" href="{{ asset('css/components.css') }}">
  @stack('styles')
@endsection

@section('content')
  <div class="attendance-container">
    <!-- ヘッダー -->
    <x-attendance.header :active-page="$activePage" />

    <!-- メインコンテンツ -->
    <div class="attendance-main">
      <!-- メッセージ -->
      <x-common.messages />

      <!-- タイトル -->
      <x-common.page-title :title="$title" />

      {{ $slot }}
    </div>
  </div>
@endsection

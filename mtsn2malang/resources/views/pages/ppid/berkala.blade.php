@extends('layouts.app')
@section('title', 'Informasi Berkala - MTsN 2 Kota Malang')
@section('content')
<x-archive-list
    :archives="$archives"
    :years="$years"
    title="Informasi Berkala"
    subtitle="Informasi yang wajib disediakan dan diumumkan secara berkala"
    :breadcrumb="['PPID', 'Informasi Berkala']" />
@endsection

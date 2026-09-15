@extends('layouts.app')
@section('title', 'Informasi Serta Merta - MTsN 2 Kota Malang')
@section('content')
<x-archive-list
    :archives="$archives"
    :years="$years"
    title="Informasi Serta Merta"
    subtitle="Informasi yang wajib diumumkan serta merta"
    :breadcrumb="['PPID', 'Informasi Serta Merta']" />
@endsection

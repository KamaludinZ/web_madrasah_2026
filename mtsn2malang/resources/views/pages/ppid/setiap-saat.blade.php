@extends('layouts.app')
@section('title', 'Informasi Setiap Saat - MTsN 2 Kota Malang')
@section('content')
<x-archive-list
    :archives="$archives"
    :years="$years"
    title="Informasi Setiap Saat"
    subtitle="Informasi yang wajib tersedia setiap saat dan dapat diakses publik"
    :breadcrumb="['PPID', 'Informasi Setiap Saat']" />
@endsection

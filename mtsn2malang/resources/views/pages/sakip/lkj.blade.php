@extends('layouts.app')
@section('title', 'LKJ - MTsN 2 Kota Malang')
@section('content')
<x-archive-list
    :archives="$archives"
    :years="$years"
    title="Laporan Kinerja (LKJ)"
    subtitle="Laporan pertanggungjawaban capaian kinerja madrasah setiap tahun"
    :breadcrumb="['SAKIP', 'LKJ']" />
@endsection
